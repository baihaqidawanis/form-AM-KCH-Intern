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
