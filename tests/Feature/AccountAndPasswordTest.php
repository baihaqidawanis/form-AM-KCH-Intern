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
        if (empty($current['mesin'])) {
            if (preg_match('/<select[^>]*name="mesin"[^>]*>[\s\S]*?<option[^>]*value="([^"]+)"[^>]*selected/i', $editPage, $m)) {
                $current['mesin'] = $m[1];
            } else if (preg_match('/<select[^>]*name="mesin"[^>]*>[\s\S]*?<option[^>]*value="([^"]+)"/i', $editPage, $m)) {
                $current['mesin'] = $m[1];
            }
        }
		$this->assertArrayHasKey('username', $current);
		$this->assertArrayHasKey('area', $current);
		$this->assertMatchesRegularExpression('/id="ctrl-area"[^>]*disabled[^>]*readonly/', $editPage);

		// Area adalah penugasan akses. Coba tampering POST dengan area lain;
		// backend harus mengabaikannya walaupun field disisipkan manual.
		$originalArea = $current['area'];
		$tamperedArea = strcasecmp($originalArea, 'Filling') === 0 ? 'Compounding' : 'Filling';
		$payload = array_merge($current, array('area' => $tamperedArea));
		$edit = $client->postWithCsrf('account/edit', $payload);
		$this->assertSame(200, $edit->getStatusCode());
		$this->assertStringContainsString('Record updated successfully', (string) $edit->getBody());

		$afterEdit = (string)$client->get('account/edit')->getBody();
		$this->assertMatchesRegularExpression('/id="ctrl-area"[^>]*value="' . preg_quote(htmlspecialchars($originalArea, ENT_QUOTES, 'UTF-8'), '/') . '"/', $afterEdit);
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
