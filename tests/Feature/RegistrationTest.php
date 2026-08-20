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
 * Round 19 Agustus 2026: form registrasi TIDAK LAGI ngasih pilihan role --
 * selalu dipaksa Staff/Operator (role_id 4) di server (IndexController::register()),
 * gak peduli value apa yang dikirim di field user_role_id (hidden input bisa
 * dimanipulasi lewat devtools/curl). Naikkan role dilakukan superadmin manual
 * lewat menu Users setelah akun diaktivasi. Dites dengan tetap ngirim role_id
 * 1/2/3 (simulasi percobaan tampering) untuk buktiin server-side override-nya
 * jalan, bukan cuma UI yang disembunyiin.
 */
class RegistrationTest extends TestCase
{
    public static function roleIdProvider(): array
    {
        return array(
            'coba kirim administrator (role 1)' => array(1),
            'coba kirim manager (role 2)' => array(2),
            'coba kirim supervisor (role 3)' => array(3),
            'kirim staff/operator (role 4)' => array(4),
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
            'area' => 'Filling',
            'mesin' => 'SIG',
            'password' => 'TestPU@1',
            'confirm_password' => 'TestPU@1',
            'user_role_id' => (string) $roleId,
            'pict' => 'uploads/photos/dummy.png',
        ));

        $body = (string) $resp->getBody();
        $this->assertStringContainsString('not active', strtolower($body), "role $roleId: registrasi harusnya sukses insert row tapi status Pending (belum bisa login)");

        // Cari akun test ini & pastikan role_id-nya KEPAKSA jadi 4 (Staff/Operator),
        // gak peduli role_id apa yang dikirim di form -- baru hapus.
        $admin = (new ApiClient())->loginAs('administrator');
        $list = (string) $admin->get('users?search=' . $username)->getBody();
        if (preg_match('#/users/view/(\d+)#', $list, $m)) {
            $view = (string) $admin->get('users/view/' . $m[1])->getBody();
            $this->assertStringContainsString('data-value="4"', $view, "role $roleId yang dikirim harusnya diabaikan server, akun baru harus selalu jadi role_id 4 (Staff/Operator)");
            $admin->deleteWithCsrf('users/view/' . $m[1], 'users/delete/' . $m[1]);
        }
    }

    public function test_username_bukan_format_nik_ditolak(): void
    {
        $anon = new ApiClient();
        // NIK boleh campur huruf+angka (format pabrik: 1-3 huruf prefix + 7-8 digit),
        // yang TETAP ditolak itu karakter non-alfanumerik (mis. tanda baca) atau
        // yang lebih dari 11 karakter.
        $username = 'AB-' . substr((string) time(), -7);

        $resp = $anon->postWithCsrfFrom('index/register', 'index/register', array(
            'nama' => 'Test PHPUnit Invalid NIK',
            'email' => strtolower(str_replace('-', '', $username)) . '@example.test',
            'username' => $username,
            'area' => 'Filling',
            'mesin' => 'SIG',
            'password' => 'TestPU@1',
            'confirm_password' => 'TestPU@1',
            'user_role_id' => '4',
            'pict' => 'uploads/photos/dummy.png',
        ));

        $body = (string) $resp->getBody();
        $this->assertStringContainsString('NIK', $body, 'Username dengan karakter non-alfanumerik harusnya ditolak dengan pesan format NIK, bukan sukses register');
        $this->assertStringNotContainsString('not active', strtolower($body), 'Registrasi dengan username invalid harusnya GAGAL, bukan sukses insert row');
    }

    private function randomNikUsername(): string
    {
        // Angka 11 digit -- valid juga di bawah regex alfanumerik baru (digit
        // adalah subset alfanumerik). Generate dari random biar gak collision antar test.
        return str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
    }
}
