<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Users (menu Administrator/Supervisor buat kelola akun) -- full CRUD +
 * validasi khusus (NIK, password complexity, duplikat email/username).
 * Guard super-admin (gak bisa diedit/dihapus admin lain) udah ke-cover di
 * RbacTest, di sini fokus ke behavior CRUD normalnya.
 */
class UsersCrudTest extends TestCase
{
    private ApiClient $client;
    private ?int $createdId = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdId !== null) {
            $this->client->deleteWithCsrf('users/view/' . $this->createdId, 'users/delete/' . $this->createdId);
        }
    }

    private function basePayload(array $overrides = array()): array
    {
        return array_merge(array(
            'nama' => 'PHPUnit Test User',
            'email' => 'phpunit.test.' . uniqid() . '@example.com',
            'username' => 'PHPTEST01',
            'area' => 'Test Area',
            'mesin' => 'Test Mesin',
            'password' => 'Test@1234',
            'confirm_password' => 'Test@1234',
            'account_status' => 'Active',
            'user_role_id' => '4',
            'pict' => 'uploads/files/dummy.png',
        ), $overrides);
    }

    public function test_full_lifecycle_add_edit_delete(): void
    {
        $submit = $this->client->postWithCsrf('users/add', $this->basePayload());
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Record added successfully', $body);

        $id = FormScraper::firstViewId($body, 'users');
        $this->assertNotNull($id, 'Gagal nemu id user baru');
        $this->createdId = (int) $id;

        $edit = $this->client->postWithCsrf("users/edit/$id", array(
            'nama' => 'PHPUnit Test User Diedit',
            'username' => 'PHPTEST01',
            'area' => 'Test Area',
            'mesin' => 'Test Mesin',
            'account_status' => 'Active',
            'user_role_id' => '4',
            'pict' => 'uploads/files/dummy.png',
        ));
        $this->assertSame(200, $edit->getStatusCode());
        $this->assertStringContainsString('PHPUnit Test User Diedit', (string) $edit->getBody());

        $delete = $this->client->deleteWithCsrf("users/view/$id", "users/delete/$id");
        $this->assertStringNotContainsString('PHPUnit Test User Diedit', (string) $delete->getBody());
        $this->createdId = null;
    }

    public function test_username_bukan_format_nik_ditolak(): void
    {
        $submit = $this->client->postWithCsrf('users/add', $this->basePayload(array(
            'username' => 'ini-username-kepanjangan-banget-bukan-nik',
        )));
        $this->assertSame(200, $submit->getStatusCode());
        $this->assertStringContainsString('NIK', (string) $submit->getBody(), 'Username bukan format NIK mestinya ditolak dengan pesan yang jelas');
    }

    public function test_password_lemah_ditolak(): void
    {
        $submit = $this->client->postWithCsrf('users/add', $this->basePayload(array(
            'password' => 'lemah',
            'confirm_password' => 'lemah',
        )));
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Password minimal 8 karakter', $body, 'Password lemah mestinya ditolak dengan pesan kompleksitas');
    }

    public function test_email_duplikat_ditolak(): void
    {
        $email = 'phpunit.dup.' . uniqid() . '@example.com';
        $submit1 = $this->client->postWithCsrf('users/add', $this->basePayload(array('email' => $email, 'username' => 'PHPTEST02')));
        $id = FormScraper::firstViewId((string) $submit1->getBody(), 'users');
        $this->assertNotNull($id);
        $this->createdId = (int) $id;

        $submit2 = $this->client->postWithCsrf('users/add', $this->basePayload(array('email' => $email, 'username' => 'PHPTEST03')));
        $this->assertStringContainsString('Already exist', (string) $submit2->getBody(), 'Email duplikat mestinya ditolak');
    }

    public function test_manager_dan_operator_dilarang_akses_users(): void
    {
        foreach (array('manager', 'operator') as $role) {
            $client = (new ApiClient())->loginAs($role);
            $this->assertSame(403, $client->get('users')->getStatusCode(), "$role harusnya 403 buka menu Users (URS 2.2)");
        }
    }

    public function test_supervisor_bisa_akses_users(): void
    {
        $supervisor = (new ApiClient())->loginAs('supervisor');
        $this->assertSame(200, $supervisor->get('users')->getStatusCode(), 'Supervisor mestinya bisa kelola Users (URS 2.2)');
    }
}
