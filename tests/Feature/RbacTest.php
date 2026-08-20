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

    /**
     * Regresi buat privilege-escalation yang ketemu 19 Agustus 2026:
     * AccountController::edit() ("My Account -> Edit Account") dulu punya
     * 'account_status' & 'user_role_id' di $fields yang bisa diedit user
     * sendiri, tanpa approval siapapun -- Staff/Operator tinggal ganti
     * dropdown itu jadi Administrator/Active lewat UI biasa. Fixed dengan
     * hapus 2 field itu dari whitelist $fields di AccountController::edit().
     */
    public function test_operator_tidak_bisa_naikkan_role_sendiri_lewat_my_account(): void
    {
        $operator = (new ApiClient())->loginAs('operator');
        $editPage = (string) $operator->get('account/edit')->getBody();

        $current = array();
        foreach (array('nama', 'username', 'area', 'mesin', 'pict') as $field) {
            if (preg_match('/id="ctrl-' . $field . '"[^>]*value="([^"]*)"/', $editPage, $m)) {
                $current[$field] = html_entity_decode($m[1]);
            }
        }
        $this->assertArrayHasKey('username', $current, 'Gagal scrape data account/edit operator buat setup test');

        // Coba tampering: kirim user_role_id=1 (Administrator) & account_status=Active
        // bareng field lain yang dibiarkan sama persis (gak boleh ngerusak data asli).
        $payload = array_merge($current, array(
            'user_role_id' => '1',
            'account_status' => 'Active',
        ));
        $operator->postWithCsrf('account/edit', $payload);

        $admin = (new ApiClient())->loginAs('administrator');
        $usersList = (string) $admin->get('users?search=' . $current['username'])->getBody();
        $this->assertMatchesRegularExpression('#/users/view/(\d+)#', $usersList, 'Gagal cari akun operator test di menu Users');
        preg_match('#/users/view/(\d+)#', $usersList, $m);
        $view = (string) $admin->get('users/view/' . $m[1])->getBody();
        $this->assertStringContainsString('data-value="4"', $view, 'Operator gak boleh berhasil naikkan role sendiri jadi Administrator lewat My Account');
    }

    /**
     * Proteksi Super Admin (disetujui mentor, 20 Agustus 2026): cuma boleh ada
     * 1 akun Super Admin (users.is_super_admin, unique di level DB), dan gak
     * ada Administrator/Supervisor lain yang bisa ubah role/status/hapus akun
     * ini -- sesama Administrator biasa tetap bebas saling kelola satu sama lain.
     */
    public function test_supervisor_tidak_bisa_edit_super_admin(): void
    {
        $supervisor = (new ApiClient())->loginAs('supervisor');
        $usersList = (string) $supervisor->get('users?search=superadmin')->getBody();
        $this->assertMatchesRegularExpression('#/users/view/(\d+)#', $usersList, 'Gagal cari akun superadmin di menu Users');
        preg_match('#/users/view/(\d+)#', $usersList, $m);
        $superAdminId = $m[1];

        $resp = $supervisor->get("users/edit/$superAdminId");
        $this->assertSame(403, $resp->getStatusCode(), 'Supervisor harusnya 403 buka halaman edit Super Admin');
    }

    public function test_supervisor_tidak_bisa_hapus_super_admin(): void
    {
        $supervisor = (new ApiClient())->loginAs('supervisor');
        $usersList = (string) $supervisor->get('users?search=superadmin')->getBody();
        preg_match('#/users/view/(\d+)#', $usersList, $m);
        $superAdminId = $m[1];

        $supervisor->deleteWithCsrf("users/view/$superAdminId", "users/delete/$superAdminId");

        $admin = (new ApiClient())->loginAs('administrator');
        $stillExists = (string) $admin->get('users?search=superadmin')->getBody();
        $this->assertStringContainsString('superadmin', strtolower($stillExists), 'Akun Super Admin gak boleh berhasil terhapus oleh Supervisor');
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
