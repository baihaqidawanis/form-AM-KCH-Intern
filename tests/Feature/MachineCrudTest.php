<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Full lifecycle test (submit -> view -> edit_data -> delete) buat 3 kasus
 * paling berisiko di BaseMachineController:
 *  - Chimei: mesin single (hidden input), representatif buat 11 dari 17 modul.
 *  - Illapak 1-2: mesin dropdown/multi-mesin, kasus yang paling gampang salah
 *    kalau ada bug filter/binding mesin (persis yang ketauan di Round 12).
 *  - SIG: satu-satunya modul yang pakai $extraFields (value_tekanan_angin).
 * Tiap test bikin data sendiri lalu HAPUS lagi di akhir (tearDown per-test),
 * jadi DB balik bersih walau ada test yang gagal di tengah jalan.
 */
class MachineCrudTest extends TestCase
{
    private ApiClient $client;
    private ?string $createdViewPath = null;
    private ?string $createdDeletePath = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdViewPath && $this->createdDeletePath) {
            $this->client->deleteWithCsrf($this->createdViewPath, $this->createdDeletePath);
        }
    }

    public function test_lifecycle_chimei_single_mesin_1_nok(): void
    {
        $this->runLifecycle('chimei', 'roller_opp', 'Test PHPUnit — roller aus, ganti bearing');
    }

    public function test_lifecycle_illapak_1_2_dropdown_mesin_1_nok(): void
    {
        $this->runLifecycle('illapak_1_2', 'sealing_horizontal', 'Test PHPUnit — kebocoran seal horizontal');
    }

    public function test_lifecycle_sig_extra_field_tekanan_angin(): void
    {
        $addPage = $this->client->get('sig/add');
        $html = (string) $addPage->getBody();

        $payload = FormScraper::buildOneNokPayload($html, 'antistatic', 'Test PHPUnit — antistatic aus', array('value_tekanan_angin' => '5'));
        $submit = $this->client->postWithCsrf('sig/add', $payload);

        $this->assertSame(200, $submit->getStatusCode());
        $this->assertStringContainsString('Berhasil tambah AM SIG', (string) $submit->getBody());

        $id = FormScraper::firstViewId((string) $submit->getBody(), 'sig');
        $this->assertNotNull($id, 'Gagal nemu id_sig record baru dari halaman list setelah submit');

        $this->createdViewPath = "sig/view/$id";
        $this->createdDeletePath = "sig/delete/$id";

        $view = $this->client->get($this->createdViewPath);
        $viewBody = (string) $view->getBody();
        $this->assertStringContainsString('Test PHPUnit — antistatic aus', $viewBody);
        $this->assertStringContainsString('(5 BAR)', $viewBody, 'value_tekanan_angin ($extraFields) gak muncul di halaman view');

        $editData = $this->client->get("sig/edit_data/$id");
        $this->assertSame(200, $editData->getStatusCode());
        $this->assertStringContainsString('part-image-link', (string) $editData->getBody(), 'sig: gambar part hilang dari edit_data (regresi, termasuk kasus antistatic 3-opsi)');
    }

    private function runLifecycle(string $machine, string $nokField, string $kendalaText): void
    {
        $addPage = $this->client->get("$machine/add");
        $html = (string) $addPage->getBody();

        $payload = FormScraper::buildOneNokPayload($html, $nokField, $kendalaText);
        $submit = $this->client->postWithCsrf("$machine/add", $payload);

        $this->assertSame(200, $submit->getStatusCode(), "$machine: submit add gagal");
        $body = (string) $submit->getBody();

        $id = FormScraper::firstViewId($body, $machine);
        $this->assertNotNull($id, "$machine: gagal nemu id record baru setelah submit");

        $this->createdViewPath = "$machine/view/$id";
        $this->createdDeletePath = "$machine/delete/$id";

        // View: badge NOK + teks kendala harus muncul
        $view = $this->client->get($this->createdViewPath);
        $this->assertSame(200, $view->getStatusCode(), "$machine: view gagal dibuka");
        $viewBody = (string) $view->getBody();
        $this->assertStringContainsString($kendalaText, $viewBody, "$machine: teks kendala gak muncul di view");
        $this->assertStringContainsString('badge-danger', $viewBody, "$machine: badge NOK gak muncul di view");

        // edit_data: prefill harus bawa teks kendala yang sama
        $editData = $this->client->get("$machine/edit_data/$id");
        $this->assertSame(200, $editData->getStatusCode(), "$machine: edit_data gagal dibuka");
        $editDataBody = (string) $editData->getBody();
        $this->assertStringContainsString($kendalaText, $editDataBody, "$machine: edit_data gak prefill kendala dengan benar");
        // Regresi guard: edit_data.php awalnya gak nampilin gambar part sama
        // sekali (cuma ada di add.php) -- operator gak bisa lihat foto acuan
        // pas mau koreksi data. Ditemukan & difix di semua 17 modul.
        $this->assertStringContainsString('part-image-link', $editDataBody, "$machine: gambar part hilang dari edit_data (regresi)");

        // PDF export tetap valid
        $pdf = $this->client->get("$machine/view/$id?format=pdf");
        $this->assertSame(200, $pdf->getStatusCode(), "$machine: export PDF gagal");
        $this->assertStringStartsWith('%PDF-1.7', (string) $pdf->getBody(), "$machine: hasil export bukan PDF valid");
    }
}
