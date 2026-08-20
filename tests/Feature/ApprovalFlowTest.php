<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Alur approval manual (URS 4.2) buat record NOK -- submit 1-NOK gak
 * auto-approve, terus manual approve/reject lewat edit(), cek status
 * ke-update di view/list2.
 */
class ApprovalFlowTest extends TestCase
{
    private const MACHINE = 'chimei';

    public function test_approve_manual_record_nok(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        $id = $this->createNokRecord($client);

        try {
            $editPage = $client->get(self::MACHINE . "/edit/$id");
            $this->assertSame(200, $editPage->getStatusCode());
            $this->assertStringContainsString('name="approval"', (string) $editPage->getBody());

            $resp = $client->postWithCsrf(self::MACHINE . "/edit/$id", array('approval' => 'Approved'));
            $this->assertContains($resp->getStatusCode(), array(200, 302));

            // NB: view.php gak nampilin teks status approval sama sekali (cuma tombol
            // "Approval"), jadi dicek dari list2.php yang punya kolom Approval.
            $list = (string) $client->get(self::MACHINE)->getBody();
            $this->assertStringContainsString('Approved', $list);
            // Regresi guard: kolom "Approval Oleh" (user_approve) sempat gak
            // ditampilkan sama sekali di list2.php walau datanya udah ke-fetch
            // (gap vs URS Gambar 23, difix di semua 17 modul).
            $this->assertStringContainsString('Approval Oleh', $list);
            $this->assertStringContainsString('>superadmin<', $list, 'Kolom Approval Oleh gak nunjukin siapa yang approve manual');
        } finally {
            $client->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }

    public function test_reject_manual_record_nok(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        $id = $this->createNokRecord($client);

        try {
            $resp = $client->postWithCsrf(self::MACHINE . "/edit/$id", array('approval' => 'Not Approved'));
            $this->assertContains($resp->getStatusCode(), array(200, 302));

            $list = (string) $client->get(self::MACHINE)->getBody();
            $this->assertStringContainsString('Not Approved', $list);
        } finally {
            $client->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }

    /**
     * Regresi 20 Agustus 2026: edit_data() (operator koreksi data sendiri) dulu
     * gak pernah re-evaluate status approval sama sekali -- record yang OK
     * semua (auto-approved) diedit jadi ada NOK tetap nampilin "Approved",
     * padahal harusnya balik butuh review manual. Sebaliknya, record yang
     * NOK dikoreksi jadi OK semua harusnya auto-approve lagi kayak submission
     * baru, bukan nyangkut status lama.
     */
    public function test_edit_data_ubah_ke_nok_reset_approval_ke_pending(): void
    {
        $client = (new ApiClient())->loginAs('administrator');
        $addPage = $client->get(self::MACHINE . '/add');
        $payload = FormScraper::buildAllOkPayload((string) $addPage->getBody());
        $submit = $client->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id, 'Gagal bikin record all-OK buat setup test');

        try {
            $listBeforeEdit = (string) $client->get(self::MACHINE)->getBody();
            $this->assertStringContainsString('Approved', $listBeforeEdit, 'Record all-OK harusnya auto-approved dulu sebelum diedit');

            $editDataPage = $client->get(self::MACHINE . "/edit_data/$id");
            $nokField = FormScraper::partFieldNames((string) $editDataPage->getBody())[0];
            $editPayload = FormScraper::buildOneNokPayload((string) $editDataPage->getBody(), $nokField, 'Test PHPUnit re-approval');
            $editPayload['perubahan'] = 'Test ubah jadi NOK - regresi re-approval';
            $client->postWithCsrf(self::MACHINE . "/edit_data/$id", $editPayload);

            $viewAfter = (string) $client->get(self::MACHINE . "/view/$id")->getBody();
            $this->assertStringNotContainsString('<td>Approved</td>', $viewAfter, 'Approval harusnya di-reset ke pending abis ada part jadi NOK, bukan tetap "Approved"');

            // Koreksi lagi balik ke semua OK -> harus auto-approve ulang.
            $editDataPage2 = $client->get(self::MACHINE . "/edit_data/$id");
            $editPayload2 = FormScraper::buildAllOkPayload((string) $editDataPage2->getBody());
            $editPayload2['perubahan'] = 'Test balikin ke OK - regresi re-approval';
            $client->postWithCsrf(self::MACHINE . "/edit_data/$id", $editPayload2);

            $viewAfter2 = (string) $client->get(self::MACHINE . "/view/$id")->getBody();
            $this->assertStringContainsString('<td>Approved</td>', $viewAfter2, 'Balik ke semua OK harusnya auto-approve lagi otomatis');
            $this->assertStringContainsString('<td>System</td>', $viewAfter2, 'User Approve harusnya "System" lagi pas auto-approve ulang');
        } finally {
            $client->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }

    private function createNokRecord(ApiClient $client): string
    {
        $addPage = $client->get(self::MACHINE . '/add');
        $payload = FormScraper::buildOneNokPayload((string) $addPage->getBody(), FormScraper::partFieldNames((string) $addPage->getBody())[0], 'Test PHPUnit approval flow');
        $submit = $client->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id, 'Gagal bikin record NOK buat test approval');
        return $id;
    }
}
