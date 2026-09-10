<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiClient;
use Tests\Support\FormScraper;

/**
 * Audit Trail harus nyatet 'add' otomatis pas submit form, dan (regresi
 * Round 26) 'view' TIDAK boleh ke-log lagi (noise reduction).
 */
class AuditTrailTest extends TestCase
{
    private const MACHINE = 'chimei';

    public function test_add_tercatat_di_audit_trail_dan_view_tidak(): void
    {
        $client = (new ApiClient())->loginAs('administrator');

        $addPage = $client->get(self::MACHINE . '/add');
		$addHtml = (string)$addPage->getBody();
		$payload = FormScraper::buildAllOkPayload($addHtml);
		// Jangan bergantung pada unit pertama: unit itu mungkin sudah memiliki
		// checklist hari ini dari operasional normal atau eksekusi test terdahulu.
		if (preg_match('/<select[^>]*name="mesin"[^>]*>(.*?)<\/select>/is', $addHtml, $select)) {
			preg_match_all('/<option[^>]*value="([0-9]+)"/i', $select[1], $options);
			$pdo = new \PDO(
				'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
				DB_USERNAME,
				DB_PASSWORD,
				array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
			);
			$operationalDate = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
			if ($operationalDate->format('H:i') < '06:45') {
				$operationalDate->modify('-1 day');
			}
			$usedStmt = $pdo->prepare('SELECT mesin FROM tb_mesin_chimei WHERE operational_date = ?');
			$usedStmt->execute(array($operationalDate->format('Y-m-d')));
			$used = array_map('intval', $usedStmt->fetchAll(\PDO::FETCH_COLUMN));
			$available = array_values(array_diff(array_map('intval', $options[1] ?? array()), $used));
			$this->assertNotEmpty($available, 'Tidak ada unit Chimei kosong yang aman untuk fixture audit test hari ini.');
			$payload['mesin'] = (string)$available[0];
		}
        $submit = $client->postWithCsrf(self::MACHINE . '/add', $payload);
        $id = FormScraper::firstViewId((string) $submit->getBody(), self::MACHINE);
        $this->assertNotNull($id);

        try {
            // Buka detailnya -- ini gak boleh ikut ke-log (Round 26).
            $client->get(self::MACHINE . "/view/$id");

            $auditList = (string) $client->get('audit_log?search=' . self::MACHINE)->getBody();
            $this->assertStringContainsString('add', $auditList, "Entry 'add' gak ketemu di Audit Trail buat modul " . self::MACHINE);
        } finally {
            $client->deleteWithCsrf(self::MACHINE . "/view/$id", self::MACHINE . "/delete/$id");
        }
    }

	public function test_update_paraf_logs_only_compact_action_detail(): void
	{
		$pdo = new \PDO(
			'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
			DB_USERNAME,
			DB_PASSWORD,
			array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
		);
		$before = $pdo->query("SELECT id_user, paraf_image, user_initials FROM users WHERE username = 'superadmin' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
		$this->assertNotFalse($before);
		$logId = null;
		$png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

		try {
			$client = (new ApiClient())->loginAs('administrator');
			$response = $client->postWithCsrfFrom('account/paraf', 'account/save_paraf', array('paraf_image' => $png));
			$this->assertSame(200, $response->getStatusCode());
			$this->assertStringContainsString('"success":true', (string)$response->getBody());

			$stmt = $pdo->prepare('SELECT log_id, "RequestData" FROM audit_log WHERE "Action" = ? AND "UserID" = ? ORDER BY log_id DESC LIMIT 1');
			$stmt->execute(array('update_paraf_specimen', (string)$before['id_user']));
			$log = $stmt->fetch(\PDO::FETCH_ASSOC);
			$this->assertNotFalse($log);
			$logId = intval($log['log_id']);
			$this->assertSame('{"action_detail":"update_canvas_signature"}', $log['RequestData']);
			$this->assertStringNotContainsString('data:image', $log['RequestData']);
		} finally {
			$restore = $pdo->prepare('UPDATE users SET paraf_image = ?, user_initials = ? WHERE id_user = ?');
			$restore->execute(array($before['paraf_image'], $before['user_initials'], $before['id_user']));
			if ($logId) {
				$deleteLog = $pdo->prepare('DELETE FROM audit_log WHERE log_id = ?');
				$deleteLog->execute(array($logId));
			}
		}
	}
}
