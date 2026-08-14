<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Regresi otomatis buat gap RBAC yang ketemu & difix di Round 33 (URS 3.1):
 * Operator cuma boleh edit_data punya sendiri, Manager gak boleh add tapi
 * boleh delete siapapun. Kalau ada yang nge-refactor ACL.php atau
 * BaseMachineController::edit_data() ownership check ke depan dan gak
 * sengaja ngerusak ini, test ini bakal merah dalam hitungan detik — bukan
 * ketauan pas user lapor kayak insiden Round 33 (lupa nambahin ACL role 4).
 */
class RbacTest extends TestCase
{
    private const MACHINE = 'chimei';

    public function test_manager_tidak_bisa_add(): void
    {
        $manager = (new ApiClient())->loginAs('manager');
        $resp = $manager->get(self::MACHINE . '/add');
        $this->assertSame(403, $resp->getStatusCode(), 'Manager harusnya 403 diakses halaman add mesin');
    }

    public function test_manager_bisa_delete_record_siapapun(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $id = $this->createRecord($admin);

        $manager = (new ApiClient())->loginAs('manager');
        $resp = $manager->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        $this->assertContains($resp->getStatusCode(), array(200, 302), 'Manager harusnya bisa delete record siapapun (URS 3.1)');

        // Verifikasi beneran hilang (kalau tearDown butuh, admin coba delete lagi -> gak masalah, delete() idempotent di WHERE IN)
        $view = $admin->get(self::MACHINE . "/view/$id");
        $this->assertStringNotContainsString('badge', (string) $view->getBody());
    }

    public function test_operator_tidak_bisa_edit_data_punya_orang_lain(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $id = $this->createRecord($admin);

        try {
            $operator = (new ApiClient())->loginAs('operator');
            $resp = $operator->get(self::MACHINE . "/edit_data/$id");
            $this->assertSame(403, $resp->getStatusCode(), 'Operator harusnya 403 edit_data punya orang lain');
        } finally {
            $admin->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }

    public function test_operator_bisa_edit_data_punya_sendiri(): void
    {
        $operator = (new ApiClient())->loginAs('operator');
        $id = $this->createRecord($operator);

        try {
            $resp = $operator->get(self::MACHINE . "/edit_data/$id");
            $this->assertSame(200, $resp->getStatusCode(), 'Operator harusnya bisa buka edit_data punya sendiri');
        } finally {
            // Operator gak punya akses delete (URS) -> bersihin pakai administrator.
            $admin = (new ApiClient())->loginAs('administrator');
            $admin->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }

    private function createRecord(ApiClient $client): string
    {
        $addPage = $client->get(self::MACHINE . '/add');
        $payload = FormScraper::buildAllOkPayload((string) $addPage->getBody());
        $submit = $client->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id, 'Gagal bikin record buat setup test RBAC');
        return $id;
    }
}
