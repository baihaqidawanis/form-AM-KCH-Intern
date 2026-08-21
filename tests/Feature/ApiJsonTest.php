<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * ApiController::json() -- endpoint AJAX generik yang dipakai dropdown
 * dinamis (Kategori Ketidaksesuaian dependent-select di form Kendala,
 * cek duplikat email/username realtime). Whitelist $allowed_actions
 * (security fix Round-an sebelumnya, cegah panggil method BaseController
 * sembarangan lewat endpoint publik ini) -- yang paling penting ditest.
 */
class ApiJsonTest extends TestCase
{
    public function test_action_yang_di_whitelist_bisa_dipanggil(): void
    {
        $client = (new ApiClient())->loginAs('operator');
        $resp = $client->get('api/json/sig_kategori_ketidaksesuaian_option_list/1');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded, 'Response mestinya JSON array yang valid');
    }

    public function test_action_yang_gak_di_whitelist_ditolak(): void
    {
        // write_to_log() ada di BaseController & PUBLIC -- kalau whitelist
        // ApiController::$allowed_actions bolong, ini bisa dipanggil bebas
        // buat nyuntik entri palsu ke audit_log tanpa validasi (celah yang
        // udah pernah difix, lihat komentar di ApiController.php).
        $client = (new ApiClient())->loginAs('operator');
        $resp = $client->get('api/json/write_to_log/hack');
        $this->assertSame(200, $resp->getStatusCode());
        $this->assertSame('null', trim((string) $resp->getBody()), 'Action di luar whitelist mestinya balikin null, bukan beneran manggil method-nya');
    }

    public function test_cek_username_duplikat(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        // superadmin pasti udah ada -- endpoint ini dipakai realtime-check pas Add User.
        $resp = $client->get('api/json/users_username_value_exist/superadmin');
        $this->assertSame(200, $resp->getStatusCode());
    }

    /**
     * CATATAN PENTING: ApiController extends BaseController, BUKAN
     * SecureController -- artinya endpoint ini SENGAJA gak lewat cek login
     * sama sekali (bukan bug, karena users_email_value_exist &
     * users_username_value_exist dipanggil dari halaman Register yang
     * emang harus bisa diakses SEBELUM login -- lihat register.php).
     * Konsekuensinya: SEMUA action di whitelist bisa diakses guest, termasuk
     * yang harusnya cuma dipakai di form yang udah login (misalnya
     * kategori ketidaksesuaian). Efek sampingnya masih ringan (cuma expose
     * data lookup + bisa buat enumerasi username/email terdaftar), tapi
     * dicatat di sini sebagai batasan desain yang perlu diketahui, bukan
     * dianggap "gak sengaja ketutup validasi".
     */
    public function test_endpoint_json_sengaja_bisa_diakses_guest_demi_halaman_register(): void
    {
        $guest = new ApiClient();
        $resp = $guest->get('api/json/sig_kategori_ketidaksesuaian_option_list/1');
        $this->assertSame(200, $resp->getStatusCode());
        $decoded = json_decode((string) $resp->getBody(), true);
        $this->assertIsArray($decoded, 'Endpoint whitelist mestinya tetap balikin JSON valid walau diakses guest (dipakai halaman Register)');
    }
}
