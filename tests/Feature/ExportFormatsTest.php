<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Regresi guard buat bug nyata yang ketemu Round 37: export CSV & Excel
 * CRASH (Fatal error TypeError, ke-cascade lagi ke exception_handler yang
 * kena bug sama) kalau list mesin lagi KOSONG (0 record) -- kena karena
 * `current(array())` balikin false, bukan array, terus `array_keys(false)`
 * meledak di PHP 8. Excel-nya sendiri kepisah lagi butuh extension `zip`
 * yang gak aktif dari awal (baru ketauan pas nge-tes fix pertamanya).
 * Test ini sengaja mulai dari state KOSONG (bukan abis submit record) biar
 * kasus paling gampang kelewat itu yang selalu ketes.
 */
class ExportFormatsTest extends TestCase
{
    private const MACHINE = 'best_pack'; // dipilih krn kemungkinan besar kosong terus (jarang dipakai testing lain)

    public function test_export_csv_word_excel_gak_crash_walau_list_kosong(): void
    {
        $client = (new ApiClient())->loginAs('administrator');

        $csv = $client->get(self::MACHINE . '/list2?format=csv');
        $this->assertSame(200, $csv->getStatusCode(), 'CSV export crash pas list kosong');

        $word = $client->get(self::MACHINE . '/list2?format=word');
        $this->assertSame(200, $word->getStatusCode(), 'Word export crash pas list kosong');

        $excel = $client->get(self::MACHINE . '/list2?format=excel');
        $this->assertSame(200, $excel->getStatusCode(), 'Excel export crash pas list kosong');
        $this->assertStringStartsWith("PK\x03\x04", (string) $excel->getBody(), 'Hasil export Excel bukan file .xlsx (zip) yang valid');
    }

    public function test_export_csv_dan_excel_valid_dengan_data(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        $addPage = $client->get('chimei/add');
        $payload = FormScraper::buildAllOkPayload((string) $addPage->getBody());
        $submit = $client->postWithCsrf('chimei/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), 'chimei');
        $this->assertNotNull($id);

        try {
            $csv = (string) $client->get('chimei/list2?format=csv')->getBody();
            $this->assertStringContainsString('id_chimei', $csv, 'Header kolom gak muncul di CSV pas ada data');
            $this->assertStringContainsString((string) $id, $csv);

            $excel = (string) $client->get('chimei/list2?format=excel')->getBody();
            $this->assertStringStartsWith("PK\x03\x04", $excel);
        } finally {
            $client->deleteWithCsrf("chimei/view/$id", "chimei/delete/$id");
        }
    }
}
