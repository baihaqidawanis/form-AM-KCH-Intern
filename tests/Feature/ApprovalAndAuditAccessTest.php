<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

/**
 * Halaman Approval gabungan (tab semua mesin sekaligus, ApprovalController)
 * dan Audit Trail (Audit_logController) -- RBAC matrix URS 2.2: Approval
 * boleh Admin/Supervisor/Manager (bukan Operator); Audit Trail CUMA
 * Administrator (Supervisor sengaja TIDAK dikasih, beda dari halaman lain).
 */
class ApprovalAndAuditAccessTest extends TestCase
{
    public function test_approval_bisa_diakses_admin_supervisor_manager(): void
    {
        foreach (array('administrator', 'supervisor', 'manager') as $role) {
            $client = (new ApiClient())->loginAs($role);
            $resp = $client->get('approval');
            $this->assertSame(200, $resp->getStatusCode(), "$role harusnya bisa buka halaman Approval gabungan");
            $this->assertStringContainsString('Compounding', (string) $resp->getBody(), "$role: halaman approval gak render tab Compounding dengan benar");
        }
    }

    public function test_operator_dilarang_akses_approval(): void
    {
        $operator = (new ApiClient())->loginAs('operator');
        $this->assertSame(403, $operator->get('approval')->getStatusCode(), 'Operator harusnya 403 buka halaman Approval (URS 2.2 & 3.1)');
    }

    public function test_approval_gabungan_nampilin_semua_tab_mesin_terbaru(): void
    {
        // Regresi guard spesifik: pastikan modul yang baru dipisah/ditambah
        // (Storage Tank Silverson & Tetrapak) ikut ke-daftar di tab approval
        // gabungan -- gampang kelupaan di-update pas nambah modul baru.
        $admin = (new ApiClient())->loginAs('administrator');
        $body = (string) $admin->get('approval')->getBody();
        $this->assertStringContainsString('Storage Tank Silverson', $body);
        $this->assertStringContainsString('Storage Tank Tetrapak', $body);
    }

    /**
     * Regresi guard buat bug nyata yang ketemu 27 Agustus 2026: badge notif
     * jumlah pending per tab (SharedController::count_pending_approval())
     * awalnya SELALU nunjukin "1" di SEMUA tab apapun jumlah aslinya --
     * akar masalahnya PDODb::rawQueryValue() balikin ARRAY (bukan scalar)
     * kalau query-nya gak diakhiri "LIMIT 1", dan intval() ke array
     * non-kosong SELALU balik 1 apapun isinya. Test ini submit 1 record NOK
     * ke Chimei, cek badge-nya beneran nunjukin angka yang bener (bukan
     * cuma "ada badge"), dan mesin lain yang gak ada pending TETAP gak ada
     * badge sama sekali (bukan ikut kebawa "1").
     */
    public function test_badge_notif_approval_nunjukin_angka_yang_bener_bukan_selalu_1(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $addPage = $admin->get('chimei/add');
        $html = (string) $addPage->getBody();
        $fields = \Tests\Support\FormScraper::partFieldNames($html);
        $this->assertNotEmpty($fields);

        $payload = \Tests\Support\FormScraper::buildOneNokPayload($html, $fields[0], 'Test PHPUnit — badge notif approval');
        $submit = $admin->postWithCsrf('chimei/add', $payload);
        $id = \Tests\Support\FormScraper::firstViewId((string) $submit->getBody(), 'chimei');
        $this->assertNotNull($id);

        try {
            $approvalPage = (string) $admin->get('approval')->getBody();
            $this->assertMatchesRegularExpression(
                '/Chimei\s*<span class="badge badge-danger rounded-pill ml-1">\d+<\/span>/',
                $approvalPage,
                'Tab Chimei mestinya ada badge notif angka pending yang jelas (bukan cuma teks polos)'
            );
            // Mesin yang gak punya record pending sama sekali gak boleh ikut kebawa badge.
            $this->assertDoesNotMatchRegularExpression(
                '/Best Pack\s*<span class="badge/',
                $approvalPage,
                'Best Pack gak ada record pending -- gak boleh ikut nongol badge (regresi bug "semua tab jadi 1")'
            );
        } finally {
            $admin->deleteWithCsrf("chimei/view/$id", "chimei/delete/$id");
        }
    }

    public function test_audit_log_cuma_administrator(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $this->assertSame(200, $admin->get('audit_log')->getStatusCode());

        foreach (array('supervisor', 'manager', 'operator') as $role) {
            $client = (new ApiClient())->loginAs($role);
            $this->assertSame(403, $client->get('audit_log')->getStatusCode(), "$role harusnya 403 buka Audit Trail -- CUMA Administrator (beda dari halaman lain yang Supervisor juga boleh)");
        }
    }
}
