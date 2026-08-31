<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * MachineCrudTest cuma nge-tes lifecycle DALAM (submit->view->edit_data->
 * delete->export PDF) buat 3 mesin representatif (chimei, illapak_1_2, sig).
 * SmokeTest nge-cover SEMUA 18 mesin tapi cuma "halaman gak error" (gak
 * pernah beneran submit data). Di antara keduanya ada gap: 12 mesin yang
 * belum PERNAH beneran disubmit lewat automated test -- field-nya beda-beda
 * per mesin, jadi bug spesifik ke satu mesin (typo nama field, field yang
 * required tapi gak ke-generate di form, dst) bisa lolos gak ketauan.
 * Ini nutup gap itu pakai pola yang sama kayak MachineCrudTest::runLifecycle(),
 * tapi generik (field NOK diambil dinamis dari FormScraper, bukan hardcode).
 */
class MachineLifecycleAllTest extends TestCase
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
            'illapak_3_12' => array('illapak_3_12'),
            'unifill_b' => array('unifill_b'),
            'temach' => array('temach'),
            'jihcheng' => array('jihcheng'),
            'jinsung_1_4' => array('jinsung_1_4'),
            'jinsung_5' => array('jinsung_5'),
            'best_pack' => array('best_pack'),
            'cosmec' => array('cosmec'),
            'fbd_jaw_chuan' => array('fbd_jaw_chuan'),
            'fbd_glatt' => array('fbd_glatt'),
            'supermixer' => array('supermixer'),
            'granulator' => array('granulator'),
            'check_weigher' => array('check_weigher'),
            'conveyor_sig' => array('conveyor_sig'),
            'joeya' => array('joeya'),
        );
    }

    /** @dataProvider machineProvider */
    public function test_lifecycle_semua_ok_auto_approve(string $machine): void
    {
        $addPage = $this->client->get("$machine/add");
        $this->assertSame(200, $addPage->getStatusCode(), "$machine: halaman add gagal dibuka");
        $html = (string) $addPage->getBody();

        $payload = FormScraper::buildAllOkPayload($html);
        $this->assertNotEmpty(FormScraper::partFieldNames($html), "$machine: gagal baca daftar part dari form add");

        $submit = $this->client->postWithCsrf("$machine/add", $payload);
        $this->assertSame(200, $submit->getStatusCode(), "$machine: submit semua OK gagal");
        $body = (string) $submit->getBody();
        $this->assertStringNotContainsString('Fatal error', $body, "$machine: submit malah nge-crash");

        $id = FormScraper::firstViewId($body, $machine);
        $this->assertNotNull($id, "$machine: gagal nemu id record baru setelah submit");
        $this->createdMachine = $machine;
        $this->createdId = $id;

        $view = $this->client->get("$machine/view/$id");
        $this->assertSame(200, $view->getStatusCode(), "$machine: view gagal dibuka");
        $viewBody = (string) $view->getBody();
        $this->assertStringContainsString('badge-success', $viewBody, "$machine: semua OK mestinya nunjukin badge OK di view");
        $this->assertStringContainsString('>Approved<', $viewBody, "$machine: semua OK mestinya auto-approve");

        $editData = $this->client->get("$machine/edit_data/$id");
        $this->assertSame(200, $editData->getStatusCode(), "$machine: edit_data gagal dibuka");

        $pdf = $this->client->get("$machine/view/$id?format=pdf");
        $this->assertSame(200, $pdf->getStatusCode(), "$machine: export PDF gagal");
        $this->assertStringStartsWith('%PDF', (string) $pdf->getBody(), "$machine: hasil export bukan PDF valid");
    }

    /** @dataProvider machineProvider */
    public function test_lifecycle_satu_nok_pending_approval(string $machine): void
    {
        $addPage = $this->client->get("$machine/add");
        $html = (string) $addPage->getBody();
        $fields = FormScraper::partFieldNames($html);
        $this->assertNotEmpty($fields, "$machine: gagal baca daftar part");

        $payload = FormScraper::buildOneNokPayload($html, $fields[0], "Test PHPUnit $machine — kondisi tidak baik");
        $submit = $this->client->postWithCsrf("$machine/add", $payload);
        $this->assertSame(200, $submit->getStatusCode(), "$machine: submit dengan 1 NOK gagal");
        $body = (string) $submit->getBody();

        $id = FormScraper::firstViewId($body, $machine);
        $this->assertNotNull($id, "$machine: gagal nemu id record baru (submit NOK)");
        $this->createdMachine = $machine;
        $this->createdId = $id;

        $view = (string) $this->client->get("$machine/view/$id")->getBody();
        $this->assertStringContainsString('badge-danger', $view, "$machine: ada part NOK mestinya nunjukin badge NOK di view");
        $this->assertStringContainsString("Test PHPUnit $machine", $view, "$machine: teks kendala gak muncul di view");

        // Approve manual (Administrator) -- pastikan alur approval jalan.
        $edit = $this->client->postWithCsrf("$machine/edit/$id", array('approval' => 'Approved'));
        $this->assertSame(200, $edit->getStatusCode(), "$machine: approve manual gagal");
        $afterApprove = (string) $this->client->get("$machine/view/$id")->getBody();
        $this->assertStringContainsString('>Approved<', $afterApprove, "$machine: approval manual gak ke-apply");
    }
}
