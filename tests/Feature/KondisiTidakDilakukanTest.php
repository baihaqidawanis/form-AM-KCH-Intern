<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Opsi kondisi "Tidak Dilakukan" (value N/A) -- cuma muncul buat part
 * Mingguan/Bulanan (bukan Harian), dan auto-approve system kalau gak ada
 * part yang NOK (OK+OK atau OK+Tidak Dilakukan tetap auto-approve; ada NOK
 * mana pun -- meski cuma 1 dari banyak part -- balik ke antrian approval
 * manual). Lihat Menu::kondisi_options() & BaseMachineController::add()/edit_data().
 */
class KondisiTidakDilakukanTest extends TestCase
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

    public function test_part_harian_cuma_2_opsi_kondisi(): void
    {
        // Cosmec: semua part-nya harian (highlight kosong) -> gak boleh ada
        // radio "Tidak Dilakukan" (value N/A) sama sekali.
        $html = (string) $this->client->get('cosmec/add')->getBody();
        $this->assertStringNotContainsString('value="N/A"', $html, 'Cosmec (semua part harian) mestinya cuma 2 opsi kondisi (OK/NOK), gak ada Tidak Dilakukan');
    }

    public function test_part_mingguan_bulanan_ada_3_opsi_kondisi(): void
    {
        // Storage Tank Silverson: semua part-nya mingguan/bulanan -> HARUS
        // ada opsi ke-3 "Tidak Dilakukan".
        $html = (string) $this->client->get('storage_tank/add')->getBody();
        $this->assertStringContainsString('value="N/A"', $html, 'Storage Tank (semua part mingguan/bulanan) mestinya ada opsi Tidak Dilakukan');
        $this->assertStringContainsString('Tidak Dilakukan', $html);
    }

    public function test_kombinasi_ok_dan_tidak_dilakukan_auto_approve(): void
    {
        $addPage = $this->client->get('storage_tank/add');
        $html = (string) $addPage->getBody();
        $fields = FormScraper::partFieldNames($html);
        $this->assertNotEmpty($fields, 'Gagal baca daftar part Storage Tank dari form add');

        $payload = FormScraper::buildAllOkPayload($html);
        $payload[$fields[0]] = 'N/A'; // 1 part Tidak Dilakukan, sisanya OK

        $submit = $this->client->postWithCsrf('storage_tank/add', $payload);
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();

        $id = FormScraper::firstViewId($body, 'storage_tank');
        $this->assertNotNull($id, 'Gagal nemu id record baru');
        $this->createdMachine = 'storage_tank';
        $this->createdId = $id;

        $view = (string) $this->client->get("storage_tank/view/$id")->getBody();
        $this->assertStringContainsString('badge-secondary">N/A', $view, 'Badge N/A gak muncul di halaman view buat part Tidak Dilakukan');
        $this->assertStringContainsString('>Approved<', $view, 'Kombinasi OK + Tidak Dilakukan (gak ada NOK) mestinya auto-approve by System');
        $this->assertStringContainsString('>System<', $view, 'User Approve mestinya "System" buat auto-approve');
    }

    public function test_ada_satu_saja_nok_gak_auto_approve(): void
    {
        $addPage = $this->client->get('storage_tank/add');
        $html = (string) $addPage->getBody();
        $fields = FormScraper::partFieldNames($html);

        $payload = FormScraper::buildOneNokPayload($html, $fields[0], 'Test PHPUnit — kondisi Tidak Baik, harus pending approval');
        $submit = $this->client->postWithCsrf('storage_tank/add', $payload);
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();

        $id = FormScraper::firstViewId($body, 'storage_tank');
        $this->assertNotNull($id);
        $this->createdMachine = 'storage_tank';
        $this->createdId = $id;

        $view = (string) $this->client->get("storage_tank/view/$id")->getBody();
        $this->assertStringContainsString('<th>Approval</th><td>-</td>', $view, 'Ada part NOK mestinya approval masih kosong (nunggu approval manual), bukan auto-approve');
    }
}
