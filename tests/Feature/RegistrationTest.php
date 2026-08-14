<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Registrasi akun baru (URS 1.1). Akun baru statusnya "Pending" sampai
 * diaktivasi Administrator -- jadi abis submit, percobaan auto-login internal
 * bakal ditolak dengan pesan "not active", itu justru tandanya sukses
 * (row ke-insert), bukan gagal.
 *
 * Dites buat SEMUA 4 role_id (form registrasi ngizinin milih role manapun --
 * gak dibatasi cuma Staff/Operator, admin yang approve belakangan lewat
 * aktivasi akun) -- bukan cuma 1 role kayak sebelumnya.
 */
class RegistrationTest extends TestCase
{
    public static function roleIdProvider(): array
    {
        return array(
            'administrator (role 1)' => array(1),
            'manager (role 2)' => array(2),
            'supervisor (role 3)' => array(3),
            'staff/operator (role 4)' => array(4),
        );
    }

    /** @dataProvider roleIdProvider */
    public function test_registrasi_akun_baru_sukses_tapi_belum_aktif(int $roleId): void
    {
        $anon = new ApiClient();
        $username = $this->randomNikUsername();

        $resp = $anon->postWithCsrfFrom('index/register', 'index/register', array(
            'nama' => "Test PHPUnit Registration role $roleId",
            'email' => $username . '@example.test',
            'username' => $username,
            'area' => 'Test Area',
            'mesin' => 'Test',
            'password' => 'TestPU@1',
            'confirm_password' => 'TestPU@1',
            'user_role_id' => (string) $roleId,
            'pict' => 'uploads/photos/dummy.png',
        ));

        $body = (string) $resp->getBody();
        $this->assertStringContainsString('not active', strtolower($body), "role $roleId: registrasi harusnya sukses insert row tapi status Pending (belum bisa login)");

        // Cleanup: cari & hapus akun test ini lewat admin.
        $admin = (new ApiClient())->loginAs('administrator');
        $list = (string) $admin->get('users?search=' . $username)->getBody();
        if (preg_match('#/users/view/(\d+)#', $list, $m)) {
            $admin->deleteWithCsrf('users/view/' . $m[1], 'users/delete/' . $m[1]);
        }
    }

    public function test_username_bukan_format_nik_ditolak(): void
    {
        $anon = new ApiClient();
        // Huruf dicampur angka -- format LAMA yang sekarang gak lagi diterima
        // (NIK di sini = Nomor Induk Karyawan, full angka, 8 digit).
        $username = 'AB' . substr((string) time(), -6);

        $resp = $anon->postWithCsrfFrom('index/register', 'index/register', array(
            'nama' => 'Test PHPUnit Invalid NIK',
            'email' => strtolower($username) . '@example.test',
            'username' => $username,
            'area' => 'Test Area',
            'mesin' => 'Test',
            'password' => 'TestPU@1',
            'confirm_password' => 'TestPU@1',
            'user_role_id' => '4',
            'pict' => 'uploads/photos/dummy.png',
        ));

        $body = (string) $resp->getBody();
        $this->assertStringContainsString('NIK', $body, 'Username huruf+angka harusnya ditolak dengan pesan format NIK, bukan sukses register');
        $this->assertStringNotContainsString('not active', strtolower($body), 'Registrasi dengan username invalid harusnya GAGAL, bukan sukses insert row');
    }

    private function randomNikUsername(): string
    {
        // 8 digit angka -- generate dari waktu + random biar gak collision antar test.
        return str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }
}
