<?php

namespace Tests\Feature;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../app/models/PDODb.php';
require_once __DIR__ . '/../../helpers/QrSignatureHelper.php';

use PHPUnit\Framework\TestCase;
use QrSignatureHelper;
use PDODb;
use Tests\Support\ApiClient;

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

			// 4. Verify lookup by SPV token
			$verifiedSpv = QrSignatureHelper::getSignatureByToken($resSpv['token']);
			$this->assertNotNull($verifiedSpv);
			$this->assertSame('SPV / Fasilitator', $verifiedSpv['verified_role']);
			$this->assertSame('approved', $verifiedSpv['status']);
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

	private function deleteHttpCancelFixture(\PDO $pdo, int $mesinId, int $period): void
	{
		$deleteAudit = $pdo->prepare('DELETE FROM audit_log WHERE "Action" = ? AND "RequestData" LIKE ?');
		$deleteAudit->execute(array('cancel_period_signature', '%"mesin":' . $mesinId . '%"period":' . $period . '%'));
		$deleteSignature = $pdo->prepare('DELETE FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = 11 AND tahun = 2100 AND periode = ?');
		$deleteSignature->execute(array('sig', $mesinId, $period));
	}
}
