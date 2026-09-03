<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

require_once dirname(__DIR__, 2) . '/config.php';
use Tests\Support\FormScraper;

/**
 * Regression test untuk alur shift pada mesin NON-Illapak (Joeya & SIG).
 *
 * Gap yang ditutup (dicatat di GPT.md PR #2):
 * IllapakShiftTest hanya cover Illapak 1-2. Mesin lain yang memakai fitur shift
 * (lewat Master Data Part) belum pernah di-test end-to-end: tambah part baru
 * dengan shift_schedule=2, submit shift 1 (OK) dan shift 2 (NOK), takeout, lalu
 * pastikan daily_report dan period_report tetap terbaca dengan benar secara historis.
 *
 * Skenario per mesin (Joeya, SIG):
 * 1. Tambah master part baru dengan shift_schedule=2 via master_part/add
 * 2. Submit Shift 1 semua OK -> auto-approved, part shift-2 TIDAK muncul di form shift-1
 * 3. Submit Shift 2 dengan part baru NOK -> pending approval
 * 4. Takeout part tersebut -> hanya hilang dari form BARU
 * 5. Cek daily_report -> kedua shift tetap terbaca (HTTP 200, bukan error 500)
 * 6. Cek period_report -> bisa dibuka (HTTP 200, response '%PDF' atau halaman HTML pemilihan)
 * 7. Teardown: hapus semua record + master part yang dibuat
 */
class GenericShiftLifecycleTest extends TestCase
{
    private ApiClient $client;
    /** @var array<string, string|int|null> track resource untuk teardown */
    private array $cleanup = [
        'machine'        => null,
        'shift1_id'      => null,
        'shift2_id'      => null,
        'master_part_id' => null,
    ];

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        $machine = $this->cleanup['machine'];
        if (!$machine) { return; }

        foreach (['shift2_id', 'shift1_id'] as $key) {
            $id = $this->cleanup[$key];
            if ($id) {
                $this->client->deleteWithCsrf("$machine/view/$id", "$machine/delete/$id");
            }
        }

        $partId = $this->cleanup['master_part_id'];
        if ($partId) {
            $this->cleanupTestPart($machine, (int) $partId);
        }
    }

    private function cleanupTestPart(string $machine, int $partId): void
    {
        if (!preg_match('/^[a-z0-9_]+$/', $machine)) { return; }
        $pdo = new \PDO("pgsql:host=" . \DB_HOST . ";port=" . \DB_PORT . ";dbname=" . \DB_NAME, \DB_USERNAME, \DB_PASSWORD, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        $find = $pdo->prepare('SELECT machine_key, field_name FROM master_part WHERE id = ?');
        $find->execute(array($partId));
        $part = $find->fetch(\PDO::FETCH_ASSOC);
        if (!$part || $part['machine_key'] !== $machine || !preg_match('/^phpunit_[a-z0-9_]+$/', $part['field_name'])) { return; }

$hasSnapshot = (bool) $pdo->query("SELECT to_regclass('public.form_part_snapshot') IS NOT NULL")->fetchColumn();
        $pdo->beginTransaction();
        try {
            if ($hasSnapshot) { $pdo->prepare('DELETE FROM form_part_snapshot WHERE machine_key = ? AND field_name = ?')->execute(array($machine, $part['field_name'])); }
            $pdo->prepare('DELETE FROM master_part WHERE id = ?')->execute(array($partId));
            $pdo->exec('ALTER TABLE "tb_mesin_' . $machine . '" DROP COLUMN IF EXISTS "' . $part['field_name'] . '"');
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    private function findRowId(string $html, string $needle): ?int
    {
        $rows = preg_split('/(?=<tr\b)/', $html);
        foreach ($rows as $row) {
            if (strpos($row, $needle) !== false && preg_match('/data-id="(\d+)"/', $row, $m)) {
                return (int) $m[1];
            }
        }
        return null;
    }

    /**
     * Skenario shift lifecycle lengkap untuk satu mesin.
     * @param string $machine     machineKey ('joeya', 'sig', dll)
     * @param array  $extraSubmit field tambahan saat submit (misal value_tekanan_angin)
     */
    private function runShiftLifecycle(string $machine, array $extraSubmit = []): void
    {
        $this->cleanup['machine'] = $machine;
        $suffix    = substr(uniqid(), -8);
        $fieldName = "phpunit_shift2_{$machine}_{$suffix}";
        $label     = "PHPUnit Shift-2 Part {$machine} {$suffix}";

        // Step 1: Tambah master part baru shift_schedule=2
        $addPartResp = $this->client->postWithCsrf("master_part/add/$machine", [
            'machine_key'    => $machine,
            'field_name'     => $fieldName,
            'label'          => $label,
            'section'        => 'TEST SHIFT LIFECYCLE',
            'metode'         => 'Visual',
            'alat'           => 'Mata',
            'standard'       => 'OK',
            'durasi'         => "1'",
            'pelaksanaan'    => 'Harian Shift 2',
            'shift_schedule' => '2',
            'highlight'      => '',
        ]);
        $this->assertSame(200, $addPartResp->getStatusCode(), "$machine: gagal tambah master part shift-2");
        $addPartBody = (string) $addPartResp->getBody();
        $this->assertStringContainsString('Part berhasil ditambahkan', $addPartBody, "$machine: pesan sukses tambah part tidak ditemukan");

        $listBody = (string) $this->client->get("master_part/index/$machine")->getBody();
        $partId   = $this->findRowId($listBody, $fieldName);
        $this->assertNotNull($partId, "$machine: gagal nemu ID master part baru dari list");
        $this->cleanup['master_part_id'] = $partId;

        // Step 2: Form shift-1 harus TIDAK menampilkan part shift-2
        $shift1Html = (string) $this->client->get("$machine/add?shift=1")->getBody();
        $this->assertStringNotContainsString($label, $shift1Html, "$machine: part shift-2 bocor ke form shift-1");

        // Form shift-2 HARUS menampilkan part baru
        $shift2Html = (string) $this->client->get("$machine/add?shift=2")->getBody();
        $this->assertStringContainsString($label, $shift2Html, "$machine: part shift-2 tidak muncul di form shift-2");

        // Submit shift-1 semua OK
        $payload1 = FormScraper::buildAllOkPayload($shift1Html, array_merge(['shift' => '1'], $extraSubmit));
        $submit1  = $this->client->postWithCsrf("$machine/add", $payload1);
        $this->assertSame(200, $submit1->getStatusCode(), "$machine: submit shift-1 gagal");
        $body1 = (string) $submit1->getBody();
        $this->assertStringContainsString('Berhasil tambah AM', $body1, "$machine: submit shift-1 tidak ada pesan sukses");

        $id1 = FormScraper::firstViewId($body1, $machine);
        $this->assertNotNull($id1, "$machine: gagal nemu id record shift-1 setelah submit");
        $this->cleanup['shift1_id'] = $id1;

        $view1 = (string) $this->client->get("$machine/view/$id1")->getBody();
        $this->assertStringContainsString('>Approved<', $view1, "$machine: shift-1 semua OK mestinya auto-approved");
        $this->assertStringNotContainsString($label, $view1, "$machine: part shift-2 bocor ke view shift-1");

        // Step 3: Submit shift-2 dengan part baru NOK
        $payload2 = FormScraper::buildOneNokPayload(
            $shift2Html,
            $fieldName,
            "PHPUnit $machine shift-2 kondisi NOK",
            array_merge(['shift' => '2'], $extraSubmit)
        );
        $submit2 = $this->client->postWithCsrf("$machine/add", $payload2);
        $this->assertSame(200, $submit2->getStatusCode(), "$machine: submit shift-2 NOK gagal");
        $body2 = (string) $submit2->getBody();
        $this->assertStringContainsString('Berhasil tambah AM', $body2, "$machine: submit shift-2 NOK tidak ada pesan sukses");

        $id2 = FormScraper::firstViewId($body2, $machine);
        $this->assertNotNull($id2, "$machine: gagal nemu id record shift-2");
        $this->cleanup['shift2_id'] = $id2;

        $view2 = (string) $this->client->get("$machine/view/$id2")->getBody();
        $this->assertStringContainsString('badge-danger', $view2, "$machine: shift-2 NOK harus tampil badge-danger");
        $this->assertStringContainsString($label, $view2, "$machine: label part shift-2 tidak muncul di view shift-2");
        $this->assertStringNotContainsString('>Approved<', $view2, "$machine: shift-2 NOK mestinya belum approved");

        // Step 4: Takeout part shift-2
        $takeoutResp = $this->client->postWithCsrfFrom(
            "master_part/takeout/$partId",
            "master_part/takeout/$partId",
            ['takeout_reason' => "PHPUnit $machine: takeout shift-2 untuk test historis"]
        );
        $this->assertStringContainsString(
            'Report historis tetap mempertahankan part ini',
            (string) $takeoutResp->getBody(),
            "$machine: pesan sukses takeout tidak ditemukan"
        );

        // Setelah takeout: form shift-2 baru TIDAK boleh menampilkan part
        $shift2After = (string) $this->client->get("$machine/add?shift=2")->getBody();
        $this->assertStringNotContainsString($label, $shift2After, "$machine: part takeout masih muncul di form shift-2 baru");

        // Step 5: daily_report harus tetap HTTP 200
        preg_match('/date=(\d{4}-\d{2}-\d{2})/', $body1, $dateMatch);
        $opDate  = $dateMatch[1] ?? date('Y-m-d');
        $mesinId = FormScraper::firstMesinValue($shift1Html);
        $this->assertNotNull($mesinId, "$machine: gagal ambil mesin_id dari form add");

        $dailyResp = $this->client->get("$machine/daily_report?mesin=$mesinId&date=$opDate");
        $this->assertSame(200, $dailyResp->getStatusCode(), "$machine: daily_report setelah takeout bukan HTTP 200");
        $dailyBody = (string) $dailyResp->getBody();
        $this->assertStringNotContainsString('Fatal error', $dailyBody, "$machine: daily_report crash setelah takeout");
        $this->assertStringContainsString($label, $dailyBody, "$machine: part takeout hilang dari daily_report historis -- audit rusak");

        // Step 6: period_report harus bisa dibuka
        [$year, $month] = explode('-', $opDate);
        $day    = (int) date('j', strtotime($opDate));
        $period = $day <= 16 ? 1 : 2;
        $periodResp = $this->client->get("$machine/period_report?mesin=$mesinId&year=$year&month=$month&period=$period");
        $this->assertSame(200, $periodResp->getStatusCode(), "$machine: period_report gagal (bukan HTTP 200)");
        $periodBody = (string) $periodResp->getBody();
        $this->assertStringNotContainsString('Fatal error', $periodBody, "$machine: period_report crash");
        $this->assertTrue(
            str_starts_with($periodBody, '%PDF') || strpos($periodBody, '<html') !== false,
            "$machine: period_report tidak menghasilkan PDF maupun HTML yang valid"
        );
    }

    // -------------------------------------------------------------------------
    // Test cases per mesin
    // -------------------------------------------------------------------------

    public function test_joeya_shift_lifecycle_add_nok_takeout_report(): void
    {
        $this->runShiftLifecycle('joeya');
    }

    public function test_sig_shift_lifecycle_add_nok_takeout_report(): void
    {
        // SIG memerlukan value_tekanan_angin (extraFields) saat submit
        $this->runShiftLifecycle('sig', ['value_tekanan_angin' => '5']);
    }
}
