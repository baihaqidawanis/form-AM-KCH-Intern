<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Login dasar 4 role. SENGAJA GAK ADA test "3x salah password = lockout" di
 * sini — kalau dijalanin otomatis bakal ngunci akun dummy yang dipakai test
 * lain juga, bikin seluruh suite gagal setelahnya. Skenario lockout tetap
 * dites manual, lihat DOCS_MD/MANUAL_TESTING_CHECKLIST.md bagian 1.
 */
class AuthTest extends TestCase
{
    public static function roleProvider(): array
    {
        return array(
            'administrator' => array('administrator'),
            'manager' => array('manager'),
            'supervisor' => array('supervisor'),
            'operator' => array('operator'),
        );
    }

    /** @dataProvider roleProvider */
    public function test_login_berhasil_dan_bisa_akses_home(string $role): void
    {
        $client = new ApiClient();
        $client->loginAs($role);
        $resp = $client->get('Home');

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertStringContainsString('logout', strtolower((string) $resp->getBody()));
    }

    public function test_login_salah_password_tidak_masuk(): void
    {
        $client = new ApiClient();
        $client->postWithCsrfFrom('', 'index/login', array('username' => 'superadmin', 'password' => 'password-salah-banget'));

        $home = $client->get('Home');
        // Gagal login -> masih ke-redirect ke halaman login (ada form password), bukan Home beneran
        $this->assertStringContainsString('name="password"', (string) $home->getBody());
    }
}
