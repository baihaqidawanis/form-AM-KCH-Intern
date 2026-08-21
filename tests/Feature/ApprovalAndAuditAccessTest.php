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
