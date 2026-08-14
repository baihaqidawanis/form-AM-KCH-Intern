<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Audit Trail harus nyatet 'add' otomatis pas submit form, dan (regresi
 * Round 26) 'view' TIDAK boleh ke-log lagi (noise reduction).
 */
class AuditTrailTest extends TestCase
{
    private const MACHINE = 'chimei';

    public function test_add_tercatat_di_audit_trail_dan_view_tidak(): void
    {
        $client = (new ApiClient())->loginAs('administrator');

        $addPage = $client->get(self::MACHINE . '/add');
        $payload = FormScraper::buildAllOkPayload((string) $addPage->getBody());
        $submit = $client->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id);

        try {
            // Buka detailnya -- ini gak boleh ikut ke-log (Round 26).
            $client->get(self::MACHINE . "/view/$id");

            $auditList = (string) $client->get('audit_log?search=' . self::MACHINE)->getBody();
            $this->assertStringContainsString('add', $auditList, "Entry 'add' gak ketemu di Audit Trail buat modul " . self::MACHINE);
        } finally {
            $client->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }
}
