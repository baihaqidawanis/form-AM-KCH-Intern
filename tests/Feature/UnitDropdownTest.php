<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Dropdown pilihan unit fisik mesin (ganti hidden input hardcode single unit)
 * -- Storage Tank Silverson & Tetrapak (2 modul terpisah), Mixing Tank, Chimei.
 * Lihat database/migrations/2026-08-20_add_storage_tank_units.sql,
 * 2026-08-20_add_mixing_tank_units.sql, 2026-08-20_add_chimei_units.sql.
 */
class UnitDropdownTest extends TestCase
{
    private ApiClient $client;
    private ?string $createdMachine = null;
    private ?string $createdId = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdMachine && $this->createdId) {
            $this->client->deleteWithCsrf(
                "{$this->createdMachine}/view/{$this->createdId}",
                "{$this->createdMachine}/delete/{$this->createdId}"
            );
        }
    }

    public static function machineProvider(): array
    {
        return array(
            'storage_tank (Silverson)' => array('storage_tank', 'ST Liq No'),
            'storage_tank_tetrapak' => array('storage_tank_tetrapak', 'ST Liq 2 No'),
            'mixing_tank' => array('mixing_tank', 'MT '),
            'chimei' => array('chimei', 'Chimei '),
        );
    }

    /** @dataProvider machineProvider */
    public function test_dropdown_unit_ada_dan_submit_kesimpen_dengan_nama_unit_yang_benar(string $machine, string $unitPrefix): void
    {
        $addPage = $this->client->get("$machine/add");
        $this->assertSame(200, $addPage->getStatusCode());
        $html = (string) $addPage->getBody();

        $this->assertStringContainsString('id="ctrl-mesin"', $html, "$machine: dropdown unit (id=ctrl-mesin) gak ada -- masih hidden input hardcode?");
        $this->assertStringContainsString($unitPrefix, $html, "$machine: opsi dropdown unit ($unitPrefix...) gak ketemu");
        $this->assertStringNotContainsString('type="hidden" name="mesin"', $html, "$machine: masih ada hidden input mesin hardcode, mestinya udah diganti dropdown");

        $payload = FormScraper::buildAllOkPayload($html);
        $this->assertArrayHasKey('mesin', $payload, "$machine: gagal ambil value unit pertama dari dropdown");

        $submit = $this->client->postWithCsrf("$machine/add", $payload);
        $this->assertSame(200, $submit->getStatusCode(), "$machine: submit gagal");
        $body = (string) $submit->getBody();

        $id = FormScraper::firstViewId($body, $machine);
        $this->assertNotNull($id, "$machine: gagal nemu id record baru setelah submit");
        $this->createdMachine = $machine;
        $this->createdId = $id;

        $view = (string) $this->client->get("$machine/view/$id")->getBody();
        $this->assertStringContainsString($unitPrefix, $view, "$machine: nama unit yang dipilih gak muncul di kolom Nama Mesin halaman view");
    }

    public function test_storage_tank_silverson_dan_tetrapak_tetap_modul_terpisah(): void
    {
        // Catatan: sidebar nampilin KEDUA nama mesin di semua halaman (menu),
        // jadi yang dicek di sini bukan "Tetrapak"/"Silverson" gak boleh
        // muncul sama sekali, tapi filter dropdown "Nama Mesin" di masing2
        // halaman list2 -- itu yang HARUS gak kecampur (unit mesin lain
        // gak boleh nongol jadi opsi filter di modul yang salah).
        $silverson = (string) $this->client->get('storage_tank/list2')->getBody();
        $tetrapak = (string) $this->client->get('storage_tank_tetrapak/list2')->getBody();

        preg_match('/<select[^>]*id="filter-mesin"[^>]*>(.*?)<\/select>/is', $silverson, $silversonFilter);
        preg_match('/<select[^>]*id="filter-mesin"[^>]*>(.*?)<\/select>/is', $tetrapak, $tetrapakFilter);
        $this->assertNotEmpty($silversonFilter, 'Filter Nama Mesin gak ketemu di storage_tank/list2');
        $this->assertNotEmpty($tetrapakFilter, 'Filter Nama Mesin gak ketemu di storage_tank_tetrapak/list2');

        $this->assertStringContainsString('ST Liq No', $silversonFilter[1]);
        $this->assertStringNotContainsString('ST Liq 2 No', $silversonFilter[1], 'Filter mesin Storage Tank Silverson gak boleh nawarin unit Tetrapak');

        $this->assertStringContainsString('ST Liq 2 No', $tetrapakFilter[1]);
        $this->assertStringNotContainsString('>ST Liq No', $tetrapakFilter[1], 'Filter mesin Storage Tank Tetrapak gak boleh nawarin unit Silverson');
    }

    public function test_sidebar_storage_tank_kepisah_jadi_2_menu(): void
    {
        $home = (string) $this->client->get('cosmec/list2')->getBody();
        $this->assertStringContainsString('Storage Tank Silverson', $home, 'Sidebar mestinya punya menu terpisah buat Storage Tank Silverson');
        $this->assertStringContainsString('Storage Tank Tetrapak', $home, 'Sidebar mestinya punya menu terpisah buat Storage Tank Tetrapak');
    }
}
