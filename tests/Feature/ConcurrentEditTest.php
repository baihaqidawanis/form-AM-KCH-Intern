<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * App ini gak punya optimistic locking (gak ada kolom version/updated_at
 * dicek pas UPDATE) -- jadi 2 user edit_data record yang sama nyaris
 * bersamaan itu emang expected last-write-wins, BUKAN bug. Yang divalidasi
 * di sini: skenario itu gak bikin data KORUP (misal field ke-mix dari 2
 * submission, atau baris kendala dobel/orphan) -- cuma "yang terakhir
 * menang, bersih".
 */
class ConcurrentEditTest extends TestCase
{
    private const MACHINE = 'chimei';

    public function test_dua_edit_data_beruntun_ke_record_sama_hasil_akhir_konsisten(): void
    {
        $admin = (new ApiClient())->loginAs('administrator');
        $addPage = $admin->get(self::MACHINE . '/add');
        $html = (string) $addPage->getBody();
        $fields = FormScraper::partFieldNames($html);
        $this->assertGreaterThanOrEqual(2, count($fields), 'Butuh minimal 2 part buat test ini');

        $payload = FormScraper::buildAllOkPayload($html);
        $submit = $admin->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id);

        try {
            // 2 "user" (2 ApiClient/session terpisah) edit_data record yang sama,
            // beruntun cepat (bukan literal paralel -- PHP single-threaded test
            // gampangnya begini, tapi cukup buat mastiin gak ada state ke-mix).
            // Dua-duanya Administrator (2 sesi login terpisah) -- edit_data
            // dibatasi ke pemilik record ATAU Administrator (lihat
            // BaseMachineController::edit_data()), jadi user non-pemilik/non-admin
            // bakal 403 duluan sebelum sempet nyoba race condition-nya.
            $userA = (new ApiClient())->loginAs('administrator');
            $userB = (new ApiClient())->loginAs('administrator');

            $editPageA = (string) $userA->get(self::MACHINE . "/edit_data/$id")->getBody();
            $payloadA = FormScraper::buildOneNokPayload($editPageA, $fields[0], 'Kendala dari User A');
            $payloadA['perubahan'] = 'Perubahan oleh User A';

            $editPageB = (string) $userB->get(self::MACHINE . "/edit_data/$id")->getBody();
            $payloadB = FormScraper::buildOneNokPayload($editPageB, $fields[1], 'Kendala dari User B');
            $payloadB['perubahan'] = 'Perubahan oleh User B';

            // A submit duluan, B nyusul (B "menang" -- last write wins).
            $userA->postWithCsrf(self::MACHINE . "/edit_data/$id", $payloadA);
            $userB->postWithCsrf(self::MACHINE . "/edit_data/$id", $payloadB);

            $final = (string) $admin->get(self::MACHINE . "/view/$id")->getBody();

            // Hasil akhir HARUS cuma bawa versi User B (yang terakhir nulis) --
            // bukan campuran, bukan dobel kendala dari A yang nyangkut.
            $this->assertStringContainsString('Kendala dari User B', $final, 'Submission terakhir (User B) mestinya yang kepakai');
            $this->assertStringNotContainsString('Kendala dari User A', $final, 'Kendala dari User A (submission lebih awal) mestinya udah ke-replace, bukan nyangkut/nge-mix');

            // Baris kendala anak (tb kendala_*) gak boleh dobel/orphan -- cuma
            // 1 baris kendala aktif per part yang NOK di record ini.
            $kendalaCount = substr_count($final, 'Kendala dari User');
            $this->assertSame(1, $kendalaCount, 'Cuma boleh ada 1 kendala aktif tersisa (dari submission terakhir), bukan nyangkut dari submission sebelumnya');
        } finally {
            $admin->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }
}
