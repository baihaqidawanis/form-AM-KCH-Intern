<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Nomor seri fisik mesin (mesin.nomor_seri) ditampilkan polos di bawah judul
 * halaman list2 -- tanpa label "Series Number", cuma buat Cosmec/FBD
 * Glatt/FBD Jaw Chuan/Supermixer (lihat database/migrations/2026-08-20_add_mesin_nomor_seri.sql).
 */
class NomorSeriTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    public static function serialProvider(): array
    {
        return array(
            'cosmec' => array('cosmec', 'Cosmec', '2BMIX13011'),
            'fbd_glatt' => array('fbd_glatt', 'FBD Glatt', '3BDRY13001'),
            'fbd_jaw_chuan' => array('fbd_jaw_chuan', 'FBD Jaw Chuan', '2BDRY13003'),
            'supermixer' => array('supermixer', 'Supermixer', '2BMIX13001'),
        );
    }

    /** @dataProvider serialProvider */
    public function test_nomor_seri_tampil_di_list2_tanpa_label(string $machine, string $title, string $serial): void
    {
        $resp = $this->client->get("$machine/list2");
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();

        $this->assertStringContainsString($title, $body, "$machine: judul halaman gak muncul");
        $this->assertStringContainsString($serial, $body, "$machine: nomor seri $serial gak muncul di list2");
        $this->assertStringNotContainsString('Series Number', $body, "$machine: gak boleh ada label 'Series Number' -- diminta tampil polos aja");

        // Nomor seri cuma boleh nongol SEKALI (di bawah judul) -- kalau lebih
        // dari sekali, kemungkinan kebawa ke tempat lain kayak sidebar/menu.
        $this->assertSame(1, substr_count($body, $serial), "$machine: nomor seri $serial muncul lebih dari sekali di halaman (kemungkinan nyasar ke sidebar)");
    }

    public function test_mesin_yang_belum_diisi_nomor_seri_gak_error(): void
    {
        // Chimei belum dikasih nomor_seri (cuma dikasih dropdown unit) --
        // pastikan halaman tetap render normal tanpa nomor seri kosong "nyasar".
        $resp = $this->client->get('chimei/list2');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Chimei', $body);
    }
}
