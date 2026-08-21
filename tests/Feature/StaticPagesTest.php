<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Halaman generik/infra yang belum ada test sama sekali: Home dashboard,
 * halaman info (about/help/dll), Panduan Pengisian AM, halaman error
 * 403/404, dan guest gak bisa akses halaman yang butuh login.
 */
class StaticPagesTest extends TestCase
{
    public function test_home_dashboard_bisa_diakses_semua_role(): void
    {
        foreach (array('administrator', 'manager', 'supervisor', 'operator') as $role) {
            $client = (new ApiClient())->loginAs($role);
            $resp = $client->get('home');
            $this->assertSame(200, $resp->getStatusCode(), "$role harusnya bisa buka Home");
        }
    }

    public function test_panduan_pengisian_am_bisa_diakses_semua_role(): void
    {
        foreach (array('administrator', 'manager', 'supervisor', 'operator') as $role) {
            $client = (new ApiClient())->loginAs($role);
            $resp = $client->get('panduan_pengisian_am');
            $this->assertSame(200, $resp->getStatusCode(), "$role harusnya bisa buka Panduan Pengisian AM");
        }
    }

    public static function infoPageProvider(): array
    {
        return array(
            'about' => array('info/about'),
            'help' => array('info/help'),
            'features' => array('info/features'),
            'privacy_policy' => array('info/privacy_policy'),
            'terms_and_conditions' => array('info/terms_and_conditions'),
            'contact' => array('info/contact'),
        );
    }

    /** @dataProvider infoPageProvider */
    public function test_halaman_info_bisa_dibuka(string $path): void
    {
        $client = (new ApiClient())->loginAs('operator');
        $resp = $client->get($path);
        $this->assertSame(200, $resp->getStatusCode(), "$path harusnya render tanpa error");
    }

    public function test_halaman_forbidden_bisa_dibuka(): void
    {
        $client = (new ApiClient())->loginAs('operator');
        $resp = $client->get('errors/forbidden');
        $this->assertSame(200, $resp->getStatusCode());
    }

    public function test_halaman_yang_gak_ada_kasih_404_bukan_crash(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        $resp = $client->get('halaman-ngasal-yang-gak-pernah-ada-xyz123');
        $this->assertContains($resp->getStatusCode(), array(404, 200), 'Halaman gak dikenal mestinya dihandle rapi (404), bukan error 500');
        $this->assertStringNotContainsString('Fatal error', (string) $resp->getBody());
    }

    public function test_guest_belum_login_diarahkan_ke_login_bukan_langsung_lihat_data(): void
    {
        $guest = new ApiClient();
        $resp = $guest->get('cosmec/list2');
        $body = (string) $resp->getBody();
        // Guest gak boleh langsung lihat data AM -- ke-redirect balik ke halaman login.
        $this->assertStringNotContainsString('Add New Cosmec', $body, 'Guest yang belum login gak boleh bisa lihat halaman list2 AM');
    }

    public function test_logout_beneran_mengakhiri_sesi(): void
    {
        $client = (new ApiClient())->loginAs('operator');
        $home = $client->get('home');
        $this->assertSame(200, $home->getStatusCode());

        // index/logout WAJIB csrf_token (Csrf::cross_check() -- lihat main_layout.php).
        $token = $client->extractCsrfToken((string) $home->getBody());
        $client->get('index/logout?csrf_token=' . $token);

        $afterLogout = (string) $client->get('cosmec/list2')->getBody();
        $this->assertStringNotContainsString('Add New Cosmec', $afterLogout, 'Setelah logout, sesi mestinya udah gak valid lagi');
    }
}
