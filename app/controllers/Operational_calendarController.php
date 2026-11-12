<?php
require_once dirname(__DIR__, 2) . '/helpers/OperationalCalendar.php';
class Operational_calendarController extends SecureController
{
	function __construct()
	{
		parent::__construct();
		$this->tablename = 'operational_calendar';
	}

	function index($formdata = null)
	{
		$db = $this->GetModel();
		if ($formdata) {
			if (!is_post_request()) { http_response_code(405); return $this->redirect('operational_calendar'); }
			Csrf::cross_check();
			$date = trim((string)($formdata['operational_date'] ?? ''));
			$label = trim((string)($formdata['label'] ?? '')) ?: 'Holiday/Off';
			$notes = trim((string)($formdata['notes'] ?? ''));
			$isWorking = ($formdata['day_type'] ?? 'holiday') === 'working';
			if ($isWorking) $label = 'Hari Operasional';
			$valid_date = DateTime::createFromFormat('Y-m-d', $date);
			if (!$valid_date || $valid_date->format('Y-m-d') !== $date) {
				$this->set_page_error('Tanggal operasional tidak valid.');
			} else {
				try {
					$this->modeldata = array('operational_date' => $date, 'label' => $label, 'notes' => $notes === '' ? null : $notes, 'is_working_day' => $isWorking);
					$saved = $db->rawQuery('INSERT INTO operational_calendar (operational_date, label, notes, created_by_user_id, created_by_username, is_working_day, updated_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP) ON CONFLICT (operational_date) DO UPDATE SET label = EXCLUDED.label, notes = EXCLUDED.notes, is_working_day = EXCLUDED.is_working_day, updated_at = CURRENT_TIMESTAMP RETURNING id', array($date, $label, $notes === '' ? null : $notes, USER_ID ? intval(USER_ID) : null, USER_NAME, $isWorking ? 'true' : 'false'));
					if (empty($saved[0]['id'])) { throw new RuntimeException('Record kalender tidak tersimpan.'); }
					$this->rec_id = intval($saved[0]['id']);
					$this->write_to_log('save_operational_calendar', 'true');
					$this->set_flash_msg('Penanda hari operasional berhasil disimpan.', 'success');
					return $this->redirect('operational_calendar');
				} catch (Throwable $e) { $this->set_page_error('Gagal menyimpan. Pastikan migrasi 20261112_weekend_calendar.sql sudah dijalankan.'); }
			}
		}
		$records = array();
		try { $records = $db->orderBy('operational_date', 'DESC')->get('operational_calendar') ?: array(); }
		catch (Throwable $e) { $this->set_page_error('Kalender belum tersedia. Jalankan database/postgres/update.sql.'); }
		$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
		$previewStart = $today->format('Y-m-01');
		$previewEnd = $today->modify('first day of next month')->modify('last day of this month')->format('Y-m-d');
		$manualDates = array_column($records, 'operational_date');
		foreach (OperationalCalendar::flags($previewStart, $previewEnd, array()) as $date => $flag) {
			if (!in_array($date, $manualDates, true)) $records[] = $flag + array('id'=>null, 'automatic'=>true, 'created_by_username'=>'Otomatis');
		}
		usort($records, static fn($a, $b) => strcmp($b['operational_date'], $a['operational_date']));
		$this->view->page_title = 'Kalender Operasional';
		return $this->render_view('operational_calendar/index.php', array('records' => $records));
	}

	function delete($rec_id = null, $formdata = null)
	{
		if (!is_post_request()) { http_response_code(405); return $this->redirect('operational_calendar'); }
		Csrf::cross_check();
		try {
			$db = $this->GetModel();
			$record = $db->where('id', intval($rec_id))->getOne('operational_calendar');
			if (!$record) { $this->set_flash_msg('Penanda hari tidak ditemukan.', 'warning'); return $this->redirect('operational_calendar'); }
			$this->rec_id = intval($rec_id); $this->modeldata = $record;
			if (!$db->where('id', intval($rec_id))->delete('operational_calendar')) { throw new RuntimeException('Delete gagal.'); }
			$this->write_to_log('delete_operational_calendar', 'true');
			$this->set_flash_msg('Penanda hari dihapus.', 'success');
		} catch (Throwable $e) { $this->set_page_error('Penanda hari tidak dapat dihapus.'); }
		return $this->redirect('operational_calendar');
	}
}
