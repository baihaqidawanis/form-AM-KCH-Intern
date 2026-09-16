<?php

namespace Tests\Feature;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../app/models/PDODb.php';
require_once __DIR__ . '/../../helpers/QrSignatureHelper.php';

use PHPUnit\Framework\TestCase;
use QrSignatureHelper;
use PDODb;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

class DigitalSignatureTest extends TestCase
{
	private ?\PDO $pdo = null;

	private function database(): \PDO
    {
		if ($this->pdo === null) {
			$this->pdo = new \PDO('pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
			]);
		}
		return $this->pdo;
    }

    public function test_document_hash_is_deterministic(): void
    {
        $checks = ['part_1' => [1 => ['1' => 'OK']]];
        $hash1 = QrSignatureHelper::computeDocumentHash('sig', 1, 9, 2026, 1, $checks);
        $hash2 = QrSignatureHelper::computeDocumentHash('sig', 1, 9, 2026, 1, $checks);
        $hashDifferent = QrSignatureHelper::computeDocumentHash('sig', 1, 9, 2026, 1, ['part_1' => [1 => ['1' => 'NOK']]]);

        $this->assertSame(64, strlen($hash1));
        $this->assertSame($hash1, $hash2);
        $this->assertNotSame($hash1, $hashDifferent);
    }

    public function test_qr_code_with_kalbe_logo_generation(): void
    {
        $url = 'http://localhost/form-am/verify/signature/testtoken1234567890';
        $qrBase64 = QrSignatureHelper::generateQrBase64($url);

        $this->assertStringStartsWith('data:image/png;base64,', $qrBase64);
        $binary = base64_decode(str_replace('data:image/png;base64,', '', $qrBase64));
        $this->assertNotEmpty($binary);

        $img = imagecreatefromstring($binary);
        $this->assertNotFalse($img);
        $this->assertGreaterThan(50, imagesx($img));
        $this->assertGreaterThan(50, imagesy($img));
        imagedestroy($img);
    }

    public function test_period_signature_lifecycle(): void
    {
		$pdo = $this->database();
        // Find superadmin user
		$stmt = $pdo->query("SELECT id_user FROM users WHERE username = 'superadmin' LIMIT 1");
        $userId = (int)$stmt->fetchColumn();
        $this->assertGreaterThan(0, $userId);

		$mesinSlug = 'phpunit_digital_signature_' . getmypid();
		$mesinId = 2147483000;
		$bulan = 12;
		$tahun = 2100;
        $periode = 2;
        $docHash = hash('sha256', 'unit-test-hash');

        // Clean previous test entry if any
		$del = $pdo->prepare("DELETE FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = ? AND tahun = ? AND periode = ?");
        $del->execute([$mesinSlug, $mesinId, $bulan, $tahun, $periode]);

		try {
			// 1. Operator signs
			$resOp = QrSignatureHelper::savePeriodSignature($mesinSlug, $mesinId, $bulan, $tahun, $periode, $userId, 'operator', $docHash);
			$this->assertTrue($resOp['success']);
			$this->assertNotEmpty($resOp['token']);

			// 2. SPV signs
			$resSpv = QrSignatureHelper::savePeriodSignature($mesinSlug, $mesinId, $bulan, $tahun, $periode, $userId, 'spv', $docHash);
			$this->assertTrue($resSpv['success']);
			$this->assertNotEmpty($resSpv['token']);

			// 3. Verify lookup by operator token
			$verifiedOp = QrSignatureHelper::getSignatureByToken($resOp['token']);
			$this->assertNotNull($verifiedOp);
			$this->assertSame('Operator Produksi', $verifiedOp['verified_role']);
			$this->assertSame($docHash, $verifiedOp['document_hash']);
			$this->assertTrue($verifiedOp['signature_authentic']);
			$this->assertNull(QrSignatureHelper::getSignatureByToken(substr($resOp['token'], 0, 8)));

			// 4. Verify lookup by SPV token
			$verifiedSpv = QrSignatureHelper::getSignatureByToken($resSpv['token']);
			$this->assertNotNull($verifiedSpv);
			$this->assertSame('Supervisor', $verifiedSpv['verified_role']);
			$this->assertSame('approved', $verifiedSpv['status']);
			$this->assertTrue($verifiedSpv['signature_authentic']);
			$this->assertNull(QrSignatureHelper::getSignatureByToken(substr($resSpv['token'], 0, 8)));
			$this->assertNull(QrSignatureHelper::getSignatureByToken(substr($resSpv['token'], 0, 7)));
		} finally {
			// Cleanup hanya menyasar namespace data milik test ini.
			$del->execute([$mesinSlug, $mesinId, $bulan, $tahun, $periode]);
		}
    }

    public function test_user_paraf_columns_exist_and_writable(): void
    {
		$stmt = $this->database()->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name IN ('paraf_image', 'user_initials')");
        $cols = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertContains('paraf_image', $cols);
        $this->assertContains('user_initials', $cols);
    }

	public function test_superadmin_cannot_cancel_another_users_signatures(): void
	{
		$pdo = $this->database();
		$adminId = (int)$pdo->query("SELECT id_user FROM users WHERE username = 'superadmin' LIMIT 1")->fetchColumn();
		$otherId = (int)$pdo->query("SELECT id_user FROM users WHERE id_user <> {$adminId} ORDER BY id_user LIMIT 1")->fetchColumn();
		$this->assertGreaterThan(0, $otherId, 'Test memerlukan minimal satu user selain superadmin.');

		$mesinId = 2147481000 + (getmypid() % 500);
		$this->deleteHttpCancelFixture($pdo, $mesinId, 1);
		$stmt = $pdo->prepare('INSERT INTO am_period_signatures (mesin_slug, mesin_id, bulan, tahun, periode, document_hash, operator_id, operator_signed_at, operator_token, spv_id, spv_signed_at, spv_token, status) VALUES (?, ?, 11, 2100, 1, ?, ?, CURRENT_TIMESTAMP, ?, ?, CURRENT_TIMESTAMP, ?, ?)');
		$stmt->execute(array('sig', $mesinId, hash('sha256', 'strict-owner-other'), $otherId, hash('sha256', 'operator-other-' . $mesinId), $otherId, hash('sha256', 'spv-other-' . $mesinId), 'approved'));

		try {
			$client = (new ApiClient())->loginAs('administrator');
			$operatorResponse = $client->postWithCsrfFrom('home', 'sig/cancel_period_signature', array(
				'mesin' => $mesinId, 'year' => 2100, 'month' => 11, 'period' => 1,
				'role_type' => 'operator', 'reason' => 'PHPUnit strict owner operator'
			));
			$this->assertSame(403, $operatorResponse->getStatusCode());
			$this->assertStringContainsString('Hanya operator penandatangan dokumen ini', (string)$operatorResponse->getBody());

			$spvResponse = $client->postWithCsrfFrom('home', 'sig/cancel_period_signature', array(
				'mesin' => $mesinId, 'year' => 2100, 'month' => 11, 'period' => 1,
				'role_type' => 'spv', 'reason' => 'PHPUnit strict owner SPV'
			));
			$this->assertSame(403, $spvResponse->getStatusCode());
			$this->assertStringContainsString('Hanya SPV penandatangan dokumen ini', (string)$spvResponse->getBody());
		} finally {
			$this->deleteHttpCancelFixture($pdo, $mesinId, 1);
		}
	}

	public function test_original_owner_can_cancel_own_operator_signature(): void
	{
		$pdo = $this->database();
		$adminId = (int)$pdo->query("SELECT id_user FROM users WHERE username = 'superadmin' LIMIT 1")->fetchColumn();
		$mesinId = 2147481500 + (getmypid() % 500);
		$this->deleteHttpCancelFixture($pdo, $mesinId, 2);
		$token = hash('sha256', 'operator-own-' . $mesinId);
		$stmt = $pdo->prepare('INSERT INTO am_period_signatures (mesin_slug, mesin_id, bulan, tahun, periode, document_hash, operator_id, operator_signed_at, operator_token, status) VALUES (?, ?, 11, 2100, 2, ?, ?, CURRENT_TIMESTAMP, ?, ?)');
		$stmt->execute(array('sig', $mesinId, hash('sha256', 'strict-owner-own'), $adminId, $token, 'signed_operator'));

		try {
			$client = (new ApiClient())->loginAs('administrator');
			$response = $client->postWithCsrfFrom('home', 'sig/cancel_period_signature', array(
				'mesin' => $mesinId, 'year' => 2100, 'month' => 11, 'period' => 2,
				'role_type' => 'operator', 'reason' => 'PHPUnit owner cancellation'
			));
			$this->assertSame(200, $response->getStatusCode());
			$this->assertStringContainsString('"success":true', (string)$response->getBody());
			$check = $pdo->prepare('SELECT operator_token, status FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = 11 AND tahun = 2100 AND periode = 2');
			$check->execute(array('sig', $mesinId));
			$row = $check->fetch(\PDO::FETCH_ASSOC);
			$this->assertNull($row['operator_token']);
			$this->assertSame('draft', $row['status']);
		} finally {
			$this->deleteHttpCancelFixture($pdo, $mesinId, 2);
		}
	}

	public function test_ambiguous_manual_reference_is_rejected(): void
	{
		$pdo = $this->database();
		$userId = (int)$pdo->query("SELECT id_user FROM users WHERE username = 'superadmin' LIMIT 1")->fetchColumn();
		$slug = 'phpunit_prefix_collision_' . getmypid();
		$prefix = 'abcdef12';
		$delete = $pdo->prepare('DELETE FROM am_period_signatures WHERE mesin_slug = ?');
		$delete->execute(array($slug));
		$insert = $pdo->prepare('INSERT INTO am_period_signatures (mesin_slug, mesin_id, bulan, tahun, periode, document_hash, operator_id, operator_signed_at, operator_token, status) VALUES (?, ?, 10, 2100, 1, ?, ?, CURRENT_TIMESTAMP, ?, ?)');

		try {
			$insert->execute(array($slug, 2147479001, hash('sha256', 'prefix-a'), $userId, $prefix . str_repeat('1', 56), 'signed_operator'));
			$insert->execute(array($slug, 2147479002, hash('sha256', 'prefix-b'), $userId, $prefix . str_repeat('2', 56), 'signed_operator'));
			$this->assertNull(QrSignatureHelper::getSignatureByToken($prefix));
		} finally {
			$delete->execute(array($slug));
		}
	}

	public function test_spv_cannot_sign_before_operator(): void
	{
		$mesinId = 2147478500 + (getmypid() % 400);
		$pdo = $this->database();
		$delete = $pdo->prepare('DELETE FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = 10 AND tahun = 2100 AND periode = 2');
		$delete->execute(array('sig', $mesinId));

		try {
			$client = (new ApiClient())->loginAs('supervisor');
			$response = $client->postWithCsrfFrom('home', 'sig/sign_period', array(
				'mesin' => $mesinId,
				'year' => 2100,
				'month' => 10,
				'period' => 2,
				'role_type' => 'spv'
			));
			$this->assertSame(422, $response->getStatusCode());
			$this->assertStringContainsString('Operator Produksi harus menandatangani', (string)$response->getBody());
		} finally {
			$delete->execute(array('sig', $mesinId));
		}
	}

	public function test_add_is_blocked_when_period_is_signed(): void
	{
		$pdo = $this->database();
		$mesinId = 41; // Cosmec
		$now = new \DateTime();
		$month = intval($now->format('n'));
		$year = intval($now->format('Y'));
		$period = intval($now->format('j')) <= 16 ? 1 : 2;

		$delete = $pdo->prepare('DELETE FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = ? AND tahun = ? AND periode = ?');
		$delete->execute(array('cosmec', $mesinId, $month, $year, $period));

		// Insert dummy signature for current period
		$adminId = (int)$pdo->query("SELECT id_user FROM users WHERE username = 'superadmin' LIMIT 1")->fetchColumn();
		$dummyToken = hash('sha256', 'dummy-token-for-add-block');
		$stmt = $pdo->prepare('INSERT INTO am_period_signatures (mesin_slug, mesin_id, bulan, tahun, periode, document_hash, operator_id, operator_signed_at, operator_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?)');
		$stmt->execute(array('cosmec', $mesinId, $month, $year, $period, hash('sha256', 'doc-hash-test'), $adminId, $dummyToken, 'signed_operator'));

		try {
			$client = (new ApiClient())->loginAs('administrator');
			$formData = array(
				'mesin' => $mesinId,
				'cleaning_body_mesin' => 'OK',
				'cleaning_panel_fbd' => 'OK',
				'inspection_hmi_panel_fbd' => 'OK',
				'inspection_seal_bagtight' => 'OK',
				'inspection_container_updown' => 'OK',
				'inspection_shaking' => 'OK',
				'inspection_pressure_gauge_damper' => 'OK',
				'inspection_seal_container' => 'OK',
				'inspection_guarding_pengunci' => 'OK',
				'inspection_container_mesh_roda' => 'OK',
				'inspection_filter_bag_tight' => 'OK'
			);
			$response = $client->postWithCsrfFrom('home', 'cosmec/add', $formData);
			$this->assertSame(200, $response->getStatusCode());
			$this->assertStringContainsString('Form AM pada periode ini telah ditandatangani secara digital', (string)$response->getBody());
		} finally {
			$delete->execute(array('cosmec', $mesinId, $month, $year, $period));
		}
	}

	public function test_staff_role_cannot_sign_operator(): void
	{
		$pdo = $this->database();
		$mesinId = 2147477500 + (getmypid() % 300);
		$now = new \DateTime();
		$month = intval($now->format('n'));
		$year = intval($now->format('Y'));
		$period = intval($now->format('j')) <= 16 ? 1 : 2;

		$originalRole = (int)$pdo->query("SELECT user_role_id FROM users WHERE username = 'STAFOP01'")->fetchColumn();
		// Ubah STAFOP01 sementara ke role 4 (Staff).
		$pdo->prepare("UPDATE users SET user_role_id = 4 WHERE username = 'STAFOP01'")->execute();
		try {
			$client = (new ApiClient())->loginAs('operator');
			$response = $client->postWithCsrfFrom('home', 'sig/sign_period', array(
				'mesin' => $mesinId,
				'year' => $year,
				'month' => $month,
				'period' => $period,
				'role_type' => 'operator'
			));
			$this->assertSame(403, $response->getStatusCode());
		} finally {
			$restore = $pdo->prepare("UPDATE users SET user_role_id = ? WHERE username = 'STAFOP01'");
			$restore->execute(array($originalRole));
		}
	}

	public function test_sign_period_rejects_unreviewed_checklist(): void
	{
		$operator = (new ApiClient())->loginAs('operator');
		$record = $this->createJoeyaRecord($operator);
		$pdo = $this->database();
		$pdo->prepare('UPDATE tb_mesin_joeya SET approval = NULL WHERE id_joeya = ?')->execute(array($record['id']));

		try {
			$response = $operator->postWithCsrfFrom('home', 'joeya/sign_period', $this->signPayload($record));
			$this->assertSame(422, $response->getStatusCode());
			$result = json_decode((string)$response->getBody(), true);
			$this->assertSame(
				'Tanda tangan digital belum dapat dilakukan: Masih ada checklist harian pada periode ini yang belum direview/diapprove oleh Supervisor.',
				$result['message'] ?? null
			);
		} finally {
			$this->cleanupJoeyaRecord($record);
		}
	}

	public function test_approved_nok_can_be_signed_and_qr_rehash_detects_tampering(): void
	{
		$operator = (new ApiClient())->loginAs('operator');
		$record = $this->createJoeyaRecord($operator);
		$pdo = $this->database();
		$pdo->prepare("UPDATE tb_mesin_joeya SET sealing_horizontal = 'NOK', approval = 'Approved' WHERE id_joeya = ?")
			->execute(array($record['id']));

		try {
			$signResponse = $operator->postWithCsrfFrom('home', 'joeya/sign_period', $this->signPayload($record));
			$this->assertSame(200, $signResponse->getStatusCode());
			$signResult = json_decode((string)$signResponse->getBody(), true);
			$this->assertTrue($signResult['success'] ?? false, (string)$signResponse->getBody());
			$this->assertNotEmpty($signResult['token'] ?? null);

			$validBody = (string)(new ApiClient())->get('verify/signature/' . $signResult['token'])->getBody();
			$this->assertStringContainsString('Dokumen Sah &amp; Integritas Terjamin', $validBody);

			// Simulasikan perubahan langsung setelah TTD untuk memastikan scan QR
			// membandingkan ulang isi database, bukan sekadar menerima token.
			$pdo->prepare("UPDATE tb_mesin_joeya SET sealing_horizontal = 'OK' WHERE id_joeya = ?")
				->execute(array($record['id']));
			$invalidBody = (string)(new ApiClient())->get('verify/signature/' . $signResult['token'])->getBody();
			$this->assertStringContainsString('PERINGATAN: Integritas Dokumen Telah Berubah / Data Tidak Valid!', $invalidBody);
		} finally {
			$this->cleanupJoeyaRecord($record);
		}
	}

	private function createJoeyaRecord(ApiClient $operator): array
	{
		$addPage = $operator->get('joeya/add');
		$payload = FormScraper::buildAllOkPayload((string)$addPage->getBody());
		$submit = $operator->postWithCsrf('joeya/add', $payload);
		$id = FormScraper::firstViewId((string)$submit->getBody(), 'joeya');
		$this->assertNotNull($id, 'Gagal membuat checklist Joeya untuk fixture TTD.');
		$stmt = $this->database()->prepare('SELECT mesin, operational_date FROM tb_mesin_joeya WHERE id_joeya = ?');
		$stmt->execute(array($id));
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		$this->assertIsArray($row);
		$date = new \DateTime($row['operational_date']);
		return array(
			'id' => (string)$id,
			'mesin' => (int)$row['mesin'],
			'year' => (int)$date->format('Y'),
			'month' => (int)$date->format('n'),
			'period' => (int)$date->format('j') <= 16 ? 1 : 2,
		);
	}

	private function signPayload(array $record): array
	{
		return array(
			'mesin' => $record['mesin'],
			'year' => $record['year'],
			'month' => $record['month'],
			'period' => $record['period'],
			'role_type' => 'operator',
			'password' => 'Test@1234',
		);
	}

	private function cleanupJoeyaRecord(array $record): void
	{
		$deleteSignature = $this->database()->prepare('DELETE FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = ? AND tahun = ? AND periode = ?');
		$deleteSignature->execute(array('joeya', $record['mesin'], $record['month'], $record['year'], $record['period']));
		$admin = (new ApiClient())->loginAs('administrator');
		$admin->deleteWithCsrf('joeya/view/' . $record['id'], 'joeya/delete/' . $record['id']);
	}

	private function deleteHttpCancelFixture(\PDO $pdo, int $mesinId, int $period): void
	{
		$deleteAudit = $pdo->prepare('DELETE FROM audit_log WHERE "Action" = ? AND "RequestData" LIKE ?');
		$deleteAudit->execute(array('cancel_period_signature', '%"mesin":' . $mesinId . '%"period":' . $period . '%'));
		$deleteSignature = $pdo->prepare('DELETE FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = 11 AND tahun = 2100 AND periode = ?');
		$deleteSignature->execute(array('sig', $mesinId, $period));
	}
}
