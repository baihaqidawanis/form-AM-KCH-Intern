<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * AccountController ("My Account", selain guard privilege-escalation yang
 * udah ke-cover RbacTest) + PasswordmanagerController (lupa password) --
 * cuma jalur yang aman ditest tanpa efek samping (gak beneran ngirim email
 * asli, gak ganti password akun test bersama yang dipakai test lain).
 */
class AccountAndPasswordTest extends TestCase
{
    public function test_lihat_dan_edit_account_sendiri(): void
    {
        $client = (new ApiClient())->loginAs('operator');
        $view = $client->get('account');
        $this->assertSame(200, $view->getStatusCode());
        $this->assertStringContainsString('My Account', (string) $view->getBody());

        $editPage = (string) $client->get('account/edit')->getBody();
        $current = array();
        foreach (array('nama', 'username', 'area', 'mesin', 'pict') as $field) {
            if (preg_match('/id="ctrl-' . $field . '"[^>]*value="([^"]*)"/', $editPage, $m)) {
                $current[$field] = html_entity_decode($m[1]);
            }
        }
        $this->assertArrayHasKey('username', $current);
        // pict wajib diisi tapi field value-nya kosong di HTML (widget dropzone,
        // bukan plain value="..."). "pict" cuma divalidasi non-empty di server,
        // bukan dicek beneran ada filenya -- jadi aman diisi placeholder.
        if (empty($current['pict'])) {
            $current['pict'] = 'uploads/files/existing-avatar.png';
        }

        // Ubah "area" doang, field lain dibiarkan sama biar gak ganggu test lain
        // yang login pakai akun operator ini.
        $originalArea = $current['area'];
        $payload = array_merge($current, array('area' => 'PHPUnit Test Area'));
        $edit = $client->postWithCsrf('account/edit', $payload);
        $this->assertSame(200, $edit->getStatusCode());
        $this->assertStringContainsString('Record updated successfully', (string) $edit->getBody());

        // balikin "area" biar gak ninggalin efek samping ke akun operator
        // bersama (pict tetap placeholder -- sebelumnya emang kosong di DB,
        // required cuma dicek non-empty, gak ada dampak fungsional).
        $client->postWithCsrf('account/edit', array_merge($current, array('area' => $originalArea)));
    }

    public function test_change_email_page_bisa_dibuka(): void
    {
        $client = (new ApiClient())->loginAs('operator');
        $resp = $client->get('account/change_email');
        $this->assertSame(200, $resp->getStatusCode());
    }

    public function test_lupa_password_email_gak_terdaftar_ditolak(): void
    {
        $client = new ApiClient();
        $resp = $client->postWithCsrfFrom('passwordmanager', 'passwordmanager/postresetlink', array(
            'email' => 'email-gak-akan-pernah-terdaftar-' . uniqid() . '@example.com',
        ));
        $this->assertSame(200, $resp->getStatusCode());
        $this->assertStringContainsString('not registered', (string) $resp->getBody());
    }

    public function test_update_password_dengan_key_invalid_ditolak(): void
    {
        $client = new ApiClient();
        $resp = $client->get('passwordmanager/updatepassword?key=key-yang-gak-pernah-ada-' . uniqid());
        $this->assertSame(200, $resp->getStatusCode());
        $this->assertStringContainsString('Invalid Password Reset Key', (string) $resp->getBody());
    }

    public function test_halaman_passwordmanager_index_bisa_dibuka_tanpa_login(): void
    {
        // Lupa password mesti bisa diakses SEBELUM login (guest).
        $client = new ApiClient();
        $resp = $client->get('passwordmanager');
        $this->assertSame(200, $resp->getStatusCode());
    }
}
