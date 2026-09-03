<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;

require_once dirname(__DIR__, 2) . '/config.php';

/**
 * Master Data Part (CRUD detail part mesin oleh Administrator) -- full
 * lifecycle add -> edit -> delete, urutan otomatis (drag-and-drop only, gak
 * ada input manual lagi), section picker, dan escaping label/section (regresi
 * bug "&amp;" ganda + celah XSS, lihat
 * database/migrations/2026-08-20_fix_master_part_encoding.sql).
 */
class MasterPartTest extends TestCase
{
    private ApiClient $client;
    private ?int $createdId = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdId !== null) {
            $this->cleanupTestPart($this->createdId);
            $this->createdId = null;
        }
    }

    private function cleanupTestPart(int $partId): void
    {
        $pdo = new \PDO("pgsql:host=" . \DB_HOST . ";port=" . \DB_PORT . ";dbname=" . \DB_NAME, \DB_USERNAME, \DB_PASSWORD, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        $find = $pdo->prepare('SELECT machine_key, field_name FROM master_part WHERE id = ?');
        $find->execute(array($partId));
        $part = $find->fetch(\PDO::FETCH_ASSOC);
        if (!$part || !preg_match('/^phpunit_[a-z0-9_]+$/', $part['field_name']) || $part['machine_key'] !== 'sig') { return; }

$hasSnapshot = (bool) $pdo->query("SELECT to_regclass('public.form_part_snapshot') IS NOT NULL")->fetchColumn();
        $pdo->beginTransaction();
        try {
            if ($hasSnapshot) { $pdo->prepare('DELETE FROM form_part_snapshot WHERE machine_key = ? AND field_name = ?')->execute(array($part['machine_key'], $part['field_name'])); }
            $pdo->prepare('DELETE FROM master_part WHERE id = ?')->execute(array($partId));
            $pdo->exec('ALTER TABLE "tb_mesin_sig" DROP COLUMN IF EXISTS "' . $part['field_name'] . '"');
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    /** Cari data-id baris tabel list.php yang ngandung $needle -- lebih aman
     * daripada satu regex raksasa karena discope per-baris (<tr>...</tr>). */
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

    private function basePayload(array $overrides = array()): array
    {
        return array_merge(array(
            'machine_key' => 'sig',
            'field_name' => 'phpunit_test_part',
            'label' => 'PHPUnit Test Part',
            'section' => 'STANDAR PEMBERSIHAN (CLEANING)',
            'metode' => 'Tes metode',
            'alat' => 'Tes alat',
            'standard' => 'Tes standard',
            'durasi' => "1'",
            'pelaksanaan' => 'Harian (Setiap Awal Shift 1)',
            'highlight' => '',
        ), $overrides);
    }

    public function test_form_add_part_gak_ada_input_urutan_manual(): void
    {
        $html = (string) $this->client->get('master_part/add/sig')->getBody();
        $this->assertStringNotContainsString('name="urutan"', $html, 'Input Urutan manual mestinya udah dihapus -- urutan cuma dari drag-and-drop di list');
        $this->assertSame(3, substr_count($html, 'class="custom-control-input shift-schedule-option"'), 'Jadwal shift harus berupa tiga checkbox independen, bukan daftar kombinasi dropdown.');
        $this->assertStringContainsString('name="shift_schedule"', $html);
    }

    public function test_form_edit_part_gak_ada_input_urutan_manual(): void
    {
        $submit = $this->client->postWithCsrf('master_part/add/sig', $this->basePayload());
        $id = $this->findRowId((string) $submit->getBody(), 'phpunit_test_part');
        $this->assertNotNull($id);
        $this->createdId = $id;

        $editHtml = (string) $this->client->get("master_part/edit/$id")->getBody();
        $this->assertStringNotContainsString('name="urutan"', $editHtml, 'Input Urutan manual mestinya udah dihapus dari form Edit Part juga');
    }

    public function test_section_picker_isi_section_yang_udah_ada(): void
    {
        $html = (string) $this->client->get('master_part/add/sig')->getBody();
        $this->assertStringContainsString('ctrl-section-picker', $html, 'Dropdown Section picker gak ada');
        $this->assertStringContainsString('STANDAR PEMBERSIHAN (CLEANING)', $html, 'Section yang udah ada mestinya keembed buat picker JS');
    }

    public function test_tambah_part_urutan_otomatis_lanjut_dari_terakhir(): void
    {
        $listBefore = (string) $this->client->get('master_part/index/sig')->getBody();
        preg_match_all('/class="urutan-cell">(\d+)</', $listBefore, $m);
        $this->assertNotEmpty($m[1], 'Gagal baca urutan existing dari list SIG');
        $maxBefore = max(array_map('intval', $m[1]));

        $submit = $this->client->postWithCsrf('master_part/add/sig', $this->basePayload());
        $this->assertSame(200, $submit->getStatusCode());
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Part berhasil ditambahkan', $body);

        $id = $this->findRowId($body, 'phpunit_test_part');
        $this->assertNotNull($id, 'Gagal nemu id part baru dari list setelah submit');
        $this->createdId = $id;

        preg_match_all('/class="urutan-cell">(\d+)</', $body, $m2);
        $maxAfter = max(array_map('intval', $m2[1]));
        $this->assertSame($maxBefore + 1, $maxAfter, 'Part baru mestinya urutan-nya = urutan terakhir + 1 (auto-append)');
    }

    public function test_full_lifecycle_add_edit_delete(): void
    {
        // ADD
        $submit = $this->client->postWithCsrf('master_part/add/sig', $this->basePayload(array(
            'field_name' => 'phpunit_test_lifecycle',
            'label' => 'Label Awal',
        )));
        $this->assertSame(200, $submit->getStatusCode());
        $addBody = (string) $submit->getBody();
        $this->assertStringContainsString('Part berhasil ditambahkan', $addBody);
        $this->assertStringContainsString('Label Awal', $addBody);

        $id = $this->findRowId($addBody, 'phpunit_test_lifecycle');
        $this->assertNotNull($id, 'Gagal nemu id part baru');
        $this->createdId = $id;

        // Part baru harus langsung nongol di form Add AM SIG beneran
        $addAm = (string) $this->client->get('sig/add')->getBody();
        $this->assertStringContainsString('Label Awal', $addAm, 'Part baru mestinya langsung muncul di form Add AM SIG');

        // EDIT
        $editEdit = $this->client->postWithCsrf("master_part/edit/$id", array(
            'label' => 'Label Sudah Diubah',
            'section' => 'STANDAR PEMBERSIHAN (CLEANING)',
            'metode' => 'Metode baru',
            'alat' => 'Alat baru',
            'standard' => 'Standard baru',
            'durasi' => "2'",
            'pelaksanaan' => 'Mingguan',
            'highlight' => 'mingguan',
        ));
        $this->assertSame(200, $editEdit->getStatusCode());
        $editBody = (string) $editEdit->getBody();
        $this->assertStringContainsString('Label Sudah Diubah', $editBody, 'List gak nunjukin label yang udah diedit');
        $this->assertStringNotContainsString('Label Awal', $editBody, 'Label lama mestinya udah ke-replace, bukan nambah baris baru');

        // machine_key & field_name read-only -- gak boleh berubah biar identitas part tetap sama
        $listAfterEdit = (string) $this->client->get('master_part/index/sig')->getBody();
        $this->assertStringContainsString('phpunit_test_lifecycle', $listAfterEdit);

        // Perubahan highlight ke "mingguan" -> Add AM sekarang harus nawarin opsi Tidak Dilakukan buat part ini
        $addAmAfterEdit = (string) $this->client->get('sig/add')->getBody();
        $this->assertStringContainsString('Label Sudah Diubah', $addAmAfterEdit);

        // Endpoint DELETE harus ditolak; cleanup fisik hanya dilakukan langsung pada database test di tearDown().
        $delete = $this->client->deleteWithCsrf('master_part/index/sig', "master_part/delete/$id");
        $this->assertSame(200, $delete->getStatusCode());
        $deleteBody = (string) $delete->getBody();
        $this->assertStringContainsString('Penghapusan fisik part dilarang', $deleteBody);
        $this->assertStringContainsString('phpunit_test_lifecycle', $deleteBody, 'Part harus tetap ada setelah endpoint delete ditolak');
    }

    public function test_label_dan_section_di_escape_pas_ditampilkan_bukan_disimpan_ter_encode(): void
    {
        $submit = $this->client->postWithCsrf('master_part/add/sig', $this->basePayload(array(
            'field_name' => 'phpunit_test_xss',
            'label' => 'Cek & <script>alert(1)</script>',
        )));
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Part berhasil ditambahkan', $body);

        $id = $this->findRowId($body, 'phpunit_test_xss');
        $this->assertNotNull($id);
        $this->createdId = $id;

        // "&" HARUS tampil apa adanya (bukan "&amp;" -- regresi bug double-encode),
        // dan "<script>" HARUS ke-escape (bukan tag beneran -- celah XSS) di list Master Data Part.
        $this->assertStringNotContainsString('&amp;amp;', $body, 'Ampersand kena double-encode -- regresi bug lama');
        $this->assertStringContainsString('Cek &amp; &lt;script&gt;', $body, 'Label mestinya di-escape pas DITAMPILKAN, bukan disimpan ter-encode');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $body, 'Tag script gak ke-escape di list Master Data Part -- celah XSS');

        // Halaman Add AM SIG beneran (yang dipakai operator) juga harus aman.
        $addAm = (string) $this->client->get('sig/add')->getBody();
        $this->assertStringContainsString('Cek &amp; &lt;script&gt;', $addAm, 'Label part mestinya di-escape juga pas ditampilkan di form Add AM');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $addAm, 'Form Add AM SIG rentan XSS lewat label part');
    }

    public function test_field_name_gak_bisa_duplikat_buat_mesin_yang_sama(): void
    {
        $submit1 = $this->client->postWithCsrf('master_part/add/sig', $this->basePayload(array(
            'field_name' => 'phpunit_test_dup',
        )));
        $id = $this->findRowId((string) $submit1->getBody(), 'phpunit_test_dup');
        $this->assertNotNull($id);
        $this->createdId = $id;

        $submit2 = $this->client->postWithCsrf('master_part/add/sig', $this->basePayload(array(
            'field_name' => 'phpunit_test_dup',
            'label' => 'Duplikat Harusnya Gagal',
        )));
        $this->assertSame(200, $submit2->getStatusCode());
        $this->assertStringContainsString('sudah ada', (string) $submit2->getBody(), 'Field Name duplikat buat mesin yang sama mestinya ditolak');
    }
}
