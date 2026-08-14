<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Lockout 3x salah password (URS 1.2). Ini SATU-SATUNYA test yang boleh
 * "ngunci" akun, jadi SENGAJA bikin akun throwaway sendiri (bukan salah satu
 * dari 4 akun dummy yang dipakai test lain) dan dihapus lagi di akhir --
 * biar gak ganggu AuthTest/RbacTest/dst kalau dijalanin bareng.
 */
class LockoutTest extends TestCase
{
    public function test_3x_salah_password_mengunci_akun(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $username = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT); // NIK: 8 digit angka
        $password = 'LockTest@1';

        $admin->postWithCsrf('users/add', array(
            'nama' => 'Test PHPUnit Lockout',
            'email' => $username . '@example.test',
            'username' => $username,
            'area' => 'Test Area',
            'mesin' => 'Test',
            'password' => $password,
            'confirm_password' => $password,
            'account_status' => 'Active',
            'user_role_id' => '4',
            'pict' => 'uploads/photos/dummy.png',
        ));

        $list = (string) $admin->get('users?search=' . $username)->getBody();
        preg_match('#/users/view/(\d+)#', $list, $m);
        $this->assertNotEmpty($m, 'Gagal bikin akun throwaway buat test lockout');
        $userId = $m[1];

        try {
            $anon1 = new ApiClient();
            $anon1->postWithCsrfFrom('index/login', 'index/login', array('username' => $username, 'password' => 'password-salah-1'));
            $anon2 = new ApiClient();
            $anon2->postWithCsrfFrom('index/login', 'index/login', array('username' => $username, 'password' => 'password-salah-2'));
            $anon3 = new ApiClient();
            $fail3 = $anon3->postWithCsrfFrom('index/login', 'index/login', array('username' => $username, 'password' => 'password-salah-3'));

            $body3 = (string) $fail3->getBody();
            $this->assertStringContainsString('terkunci', strtolower($body3), 'Akun harusnya kekunci setelah 3x salah password');

            // Password BENER pun tetap ditolak selama status Blocked.
            $anon4 = new ApiClient();
            $anon4->postWithCsrfFrom('index/login', 'index/login', array('username' => $username, 'password' => $password));
            $home = (string) $anon4->get('Home')->getBody();
            $this->assertStringContainsString('name="password"', $home, 'Akun blocked harusnya tetap ditolak walau password bener');
        } finally {
            $admin->deleteWithCsrf("users/view/$userId", "users/delete/$userId");
        }
    }
}
