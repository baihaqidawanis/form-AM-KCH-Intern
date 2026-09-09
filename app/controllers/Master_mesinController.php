<?php
class Master_mesinController extends SecureController
{
	private function allowed()
	{
		return in_array(intval(get_active_user('user_role_id')), array(1, 2, 3), true);
	}

	function index()
	{
		if (!$this->allowed()) {
			$this->set_page_error('Akses ditolak.');
			return $this->redirect('home');
		}
		$db = $this->GetModel();
		$records = $db->rawQuery("
			SELECT m.*, 
			       h.id AS current_deactivation_id, 
			       h.reason, 
			       h.notes, 
			       h.started_at, 
			       h.action_by_username, 
			       CASE WHEN h.id IS NOT NULL THEN 'DEAKTIVASI' ELSE 'AKTIF' END AS status_operasional 
			FROM mesin m 
			LEFT JOIN riwayat_status_mesin h ON h.mesin_id = m.id AND h.ended_at IS NULL 
			ORDER BY m.nama_mesin ASC
		");
		$this->view->page_title = 'Status Operasional Mesin';
		return $this->render_view('master_mesin/list.php', array('records' => $records));
	}

	function deactivate($formdata = null)
	{
		if (!$this->allowed() || !$formdata) {
			return $this->redirect('master_mesin');
		}
		$id = intval($formdata['mesin_id'] ?? 0);
		$reason = trim((string)($formdata['reason'] ?? ''));
		if ($reason === 'Lainnya') { $custom_reason = trim((string)($formdata['reason_custom'] ?? '')); $reason = $custom_reason === '' ? '' : 'Lainnya: ' . $custom_reason; }
		if (!$id || $reason === '') {
			$this->set_page_error('Mesin dan alasan wajib diisi.');
			return $this->redirect('master_mesin');
		}

		$db = $this->GetModel();
		try {
			$db->startTransaction();
			$username = get_active_user('username') ?? (defined('USER_NAME') ? USER_NAME : 'system');
			$userId = get_active_user('id_user') ?? (defined('USER_ID') ? USER_ID : null);
			$history = $db->insert('riwayat_status_mesin', array(
				'mesin_id' => $id,
				'reason' => $reason,
				'notes' => trim((string)($formdata['notes'] ?? '')),
				'action_by_user_id' => $userId,
				'action_by_username' => $username,
				'started_at' => datetime_now()
			));
			if (!$history) {
				throw new RuntimeException('Gagal menyimpan riwayat deaktivasi.');
			}
			$db->commit();
			try { $db->where('id', $id)->update('mesin', array('status_operasional' => 'DEAKTIVASI', 'current_deactivation_id' => $history)); } catch (Throwable $e) { error_log('Machine cache deactivate skipped: ' . $e->getMessage()); }
			$this->set_flash_msg('Mesin berhasil dideaktivasi.', 'success');
		} catch (Throwable $e) {
			$db->rollback();
			$this->set_page_error('Gagal deaktivasi mesin: ' . $e->getMessage());
		}
		return $this->redirect('master_mesin');
	}

	function reactivate($formdata = null)
	{
		if (!$this->allowed() || !$formdata) {
			return $this->redirect('master_mesin');
		}
		$id = intval($formdata['mesin_id'] ?? 0);
		$db = $this->GetModel();
		$active = $db->where('mesin_id', $id)->where('ended_at', null, 'IS')->getOne('riwayat_status_mesin', array('id'));
		if (!$active) {
			$this->set_page_error('Tidak ada status deaktivasi aktif.');
			return $this->redirect('master_mesin');
		}

		try {
			$db->startTransaction();
			$username = get_active_user('username') ?? (defined('USER_NAME') ? USER_NAME : 'system');
			$userId = get_active_user('id_user') ?? (defined('USER_ID') ? USER_ID : null);
			if (!$db->where('id', $active['id'])->update('riwayat_status_mesin', array(
				'ended_at' => datetime_now(),
				'reactivated_by_user_id' => $userId,
				'reactivated_by_username' => $username
			))) {
				throw new RuntimeException('Gagal memperbarui riwayat aktivasi.');
			}
			$db->commit();
			try { $db->where('id', $id)->update('mesin', array('status_operasional' => 'AKTIF', 'current_deactivation_id' => null)); } catch (Throwable $e) { error_log('Machine cache reactivate skipped: ' . $e->getMessage()); }
			$this->set_flash_msg('Mesin berhasil diaktifkan kembali.', 'success');
		} catch (Throwable $e) {
			$db->rollback();
			$this->set_page_error('Gagal mengaktifkan mesin: ' . $e->getMessage());
		}
		return $this->redirect('master_mesin');
	}
}
