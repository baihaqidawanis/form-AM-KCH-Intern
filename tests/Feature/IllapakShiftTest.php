<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

require_once dirname(__DIR__, 2) . '/config.php';

class IllapakShiftTest extends TestCase
{
    private ApiClient $client;
    private ?string $createdDeletePath = null;
    private ?int $createdMasterPartId = null;
	private ?int $createdMachineId = null;

    protected function setUp(): void
    {
        $this->client = (new ApiClient())->loginAs('administrator');
    }

    protected function tearDown(): void
    {
        if ($this->createdDeletePath) {
            $this->client->deleteWithCsrf('illapak_1_2', $this->createdDeletePath);
        }
        if ($this->createdMasterPartId !== null) {
            $pdo = $this->database();
            $stmt = $pdo->prepare("SELECT field_name FROM master_part WHERE id = ?");
            $stmt->execute(array($this->createdMasterPartId));
            $fieldName = $stmt->fetchColumn();
            if ($fieldName && preg_match('/^[a-z0-9_]+$/', $fieldName)) {
                $pdo->exec("ALTER TABLE tb_mesin_illapak_1_2 DROP COLUMN IF EXISTS {$fieldName}");
            }
            $delStmt = $pdo->prepare("DELETE FROM master_part WHERE id = ?");
            $delStmt->execute(array($this->createdMasterPartId));
        }
		if ($this->createdMachineId !== null) {
			$pdo = $this->database();
			$stmt = $pdo->prepare("DELETE FROM mesin WHERE id = ? AND nama_mesin = 'Ilapak 2'");
			$stmt->execute(array($this->createdMachineId));
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

    public function test_shift_2_only_shows_and_saves_eligible_parts(): void
    {
        $add = $this->client->get('illapak_1_2/add?shift=2');
        $html = (string) $add->getBody();
		if (!$this->hasAvailableShiftUnit($html, '2')) {
			$pdo = $this->database();
			$this->createdMachineId = (int)$pdo->query("INSERT INTO mesin (nama_mesin) VALUES ('Ilapak 2') RETURNING id")->fetchColumn();
			$add = $this->client->get('illapak_1_2/add?shift=2');
			$html = (string)$add->getBody();
		}

        $this->assertSame(200, $add->getStatusCode());
        $fields = FormScraper::partFieldNames($html);
        $this->assertContains('position_indicator_sealing_vertical', $fields);
        $this->assertContains('vacum_sliter', $fields);
        $this->assertNotContains('sealing_horizontal', $fields);

        $payload = FormScraper::buildAllOkPayload($html, array('shift' => '2'));
        // Percobaan mengirim part Shift 1 secara manual harus diabaikan server.
        $payload['sealing_horizontal'] = 'NOK';
        $submit = $this->client->postWithCsrf('illapak_1_2/add', $payload);
        $body = (string) $submit->getBody();

        $this->assertSame(200, $submit->getStatusCode());
        $id = FormScraper::firstViewId($body, 'illapak_1_2');
		$this->assertNotNull($id, 'Submit Shift 2 gagal: ' . substr(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 0, 800));
        $this->createdDeletePath = "illapak_1_2/delete/$id";

        $view = $this->client->get("illapak_1_2/view/$id");
        $viewHtml = (string) $view->getBody();
        $this->assertStringContainsString('Shift 2', $viewHtml);
        $this->assertStringContainsString('Approved', $viewHtml);
    }

	private function database(): \PDO
	{
		return new \PDO(
			'pgsql:host=' . \DB_HOST . ';port=' . \DB_PORT . ';dbname=' . \DB_NAME,
			\DB_USERNAME,
			\DB_PASSWORD,
			array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
		);
	}

	private function hasAvailableShiftUnit(string $html, string $shift): bool
	{
		if (!preg_match('/<select[^>]*name="mesin"[^>]*>(.*?)<\/select>/is', $html, $select)) {
			return false;
		}
		preg_match_all('/<option\s+value="([0-9]+)"/', $select[1], $options);
		$machineIds = array_map('intval', $options[1] ?? array());
		if (empty($machineIds)) { return false; }

		$now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
		if ($now->format('H:i') < '06:45') { $now->modify('-1 day'); }
		$placeholders = implode(',', array_fill(0, count($machineIds), '?'));
		$stmt = $this->database()->prepare("SELECT mesin FROM tb_mesin_illapak_1_2 WHERE operational_date = ? AND shift = ? AND mesin IN ($placeholders)");
		$stmt->execute(array_merge(array($now->format('Y-m-d'), $shift), $machineIds));
		$used = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
		return count(array_diff($machineIds, $used)) > 0;
	}

    public function test_add_without_shift_shows_shift_selector(): void
    {
        $add = $this->client->get('illapak_1_2/add');
        $this->assertSame(200, $add->getStatusCode());
        $this->assertStringContainsString('Pilih Shift Pemeriksaan', (string) $add->getBody());
    }

    public function test_new_master_part_automatically_follows_its_shift_schedule(): void
    {
        $suffix = substr(uniqid(), -8);
        $fieldName = 'phpunit_part_shift_' . $suffix;
        $label = 'PHPUnit Part Shift 2 dan 3 ' . $suffix;
        $submit = $this->client->postWithCsrf('master_part/add/illapak_1_2', array(
            'machine_key' => 'illapak_1_2',
            'field_name' => $fieldName,
            'label' => $label,
            'section' => 'TEST SHIFT',
            'metode' => 'Visual',
            'alat' => 'Mata',
            'standard' => 'OK',
            'durasi' => "1'",
            'pelaksanaan' => 'Harian Shift 2 dan 3',
            'shift_schedule' => '2,3',
            'highlight' => '',
        ));
        $body = (string) $submit->getBody();
        $this->assertStringContainsString('Part berhasil ditambahkan', $body);

        $list = $this->client->get('master_part/index/illapak_1_2');
        $this->createdMasterPartId = $this->findRowId((string) $list->getBody(), $label);
        $this->assertNotNull($this->createdMasterPartId, 'Gagal menemukan ID master part baru.');

        $this->assertStringNotContainsString($label, (string) $this->client->get('illapak_1_2/add?shift=1')->getBody());
        $this->assertStringContainsString($label, (string) $this->client->get('illapak_1_2/add?shift=2')->getBody());
        $this->assertStringContainsString($label, (string) $this->client->get('illapak_1_2/add?shift=3')->getBody());
    }
}
