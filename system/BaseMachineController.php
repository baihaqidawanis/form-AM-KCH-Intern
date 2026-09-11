<?php
/**
 * Base class generik buat semua 17 controller mesin AM (Filling/Packaging/Compounding).
 * Tiap modul cuma perlu declare 3 property: $machineKey, $displayName, $parts.
 * Method di sini (index/list2/add/view/edit/edit_data/editfield/delete) sama
 * persis polanya di 17 modul sebelum di-refactor -- lihat DOCS_MD/FINAL_IMPROVEMENT.md
 * buat histori kenapa/gimana refactor ini dikerjain.
 */
abstract class BaseMachineController extends SecureController
{
	/** @var string nama tabel utama, contoh 'chimei', 'sig' */
	protected $machineKey;

	/** @var string label buat judul halaman/laporan, contoh 'Chimei', 'Illapak 1 - 2' */
	protected $displayName;

	/** @var array field_name => label part (OK/NOK), urutan menentukan urutan tampil */
	protected $parts = array();

	/**
	 * Field TAMBAHAN di luar $parts yang wajib diisi pas add()/edit_data() tapi
	 * BUKAN field OK/NOK part (jadi gak ikut dicek di logic auto-approve).
	 * Dipakai SIG buat 'value_tekanan_angin'.
	 * @var array
	 */
	protected $extraFields = array();
	private $hasRetriedTransientRead = false;

	private $snapshotSchema = null;
	function __construct()
	{
		parent::__construct();
		$this->tablename = $this->machineKey;
		$this->loadDynamicParts();
		if ($this->machineUsesConfiguredShift() && !in_array('shift', $this->extraFields, true)) { $this->extraFields[] = 'shift'; }
	}

	/**
	 * Kalau superadmin udah nambahin part lewat menu Master Data Part
	 * (Master_partController) buat mesin ini, $parts di-override dari database
	 * (urutan sesuai kolom urutan) -- kalau belum ada row master_part sama
	 * sekali buat mesin ini, tetap pakai $parts hardcoded di subclass (mesin
	 * yang belum dimigrasikan ke master data).
	 */
	/** Shift aktif mengatur form BARU dan dropdown shift. */
	private function machineUsesConfiguredShift()
	{
		$db = $this->GetModel();
		$db->where('machine_key', $this->machineKey)->where('taken_out_at', null, 'IS')->where("(shift_schedule LIKE '%2%' OR shift_schedule LIKE '%3%')");
		return $db->has('master_part');
	}

	/** Shift historis mengatur cara membaca report lama setelah takeout. */
	private function machineHasShiftHistory()
	{
		$db = $this->GetModel();
		$db->where('machine_key', $this->machineKey)->where("(shift_schedule LIKE '%2%' OR shift_schedule LIKE '%3%')");
		return $db->has('master_part');
	}
	/** Shift yang benar-benar tersedia pada part aktif mesin ini. */
	protected function getConfiguredShifts()
	{
		$db = $this->GetModel();
		$rows = $db->where('machine_key', $this->machineKey)->where('taken_out_at', null, 'IS')->get('master_part', null, array('shift_schedule'));
		$shifts = array('1');
		foreach ($rows as $row) {
			foreach (explode(',', (string)($row['shift_schedule'] ?? '')) as $shift) {
				$shift = trim($shift);
				if (in_array($shift, array('1', '2', '3'), true)) { $shifts[] = $shift; }
			}
		}
		$shifts = array_values(array_unique($shifts));
		sort($shifts, SORT_NUMERIC);
		return $shifts;
	}

	private function loadDynamicParts()
	{
		$db = $this->GetModel();
		// Part yang sudah takeout tidak boleh muncul pada form baru. Data historis
		// memakai partsForRecord(), bukan daftar aktif ini.
		$db->where('machine_key', $this->machineKey)->where('taken_out_at', null, 'IS')->orderBy('urutan', 'ASC');
		$rows = $db->get('master_part', null, array('field_name', 'label'));
		if (!empty($rows)) {
			$parts = array();
			foreach ($rows as $row) { $parts[$row['field_name']] = $row['label']; }
			$this->parts = $parts;
		}
	}

	protected function idColumn() { return 'id_' . $this->machineKey; }
	protected function kendalaTable() { return 'kendala_' . $this->machineKey; }
	/** Nama tabel fisik di DB, terpisah dari $machineKey (yang tetap dipakai buat URL/nama folder view/idColumn). */
	protected function sqlTable() { return 'tb_mesin_' . $this->machineKey; }
	protected function part_fields() { return array_keys($this->parts); }
	/** Field master yang pernah ada, termasuk part takeout, untuk membaca histori. */
	protected function historicalPartFields()
	{
		$db = $this->GetModel();
		$rows = $db->where('machine_key', $this->machineKey)->orderBy('urutan', 'ASC')->get('master_part', null, array('field_name'));
		if (empty($rows)) { return $this->part_fields(); }
		return array_values(array_unique(array_map(function ($row) { return $row['field_name']; }, $rows)));
	}

	/** Tanggal operasional dimulai 06:45 dan selesai 05:45 esok harinya. */
	protected function operationalDate($at = null)
	{
		$time = $at ? new DateTime($at, new DateTimeZone('Asia/Jakarta')) : new DateTime('now', new DateTimeZone('Asia/Jakarta'));
		if ($time->format('H:i') < '06:45') { $time->modify('-1 day'); }
		return $time->format('Y-m-d');
	}

	/** Daftar part sesuai saat record dibuat; takeout tengah hari tidak mengubah form shift lama. */
	protected function partsForRecord($operational_date, $record_created_at = null, $form_id = null)
	{
		if ($form_id) {
			$snapshots = $this->GetModel()->where('machine_key', $this->machineKey)->where('form_id', intval($form_id))->orderBy('urutan', 'ASC')->get('form_part_snapshot', null, array('field_name', 'label'));
			if (!empty($snapshots)) {
				$parts = array();
				foreach ($snapshots as $snapshot) { $parts[$snapshot['field_name']] = $snapshot['label']; }
				return $parts;
			}
		}
		$db = $this->GetModel();
		$db->where('machine_key', $this->machineKey)->where('active_from', $operational_date, '<=');
		if ($record_created_at) {
			// active_from hanya DATE. created_at memastikan part yang dibuat belakangan
			// pada hari sama tidak masuk ke form yang sudah lebih dulu disubmit.
			$db->where('(created_at IS NULL OR created_at <= ?)', array($record_created_at));
			$db->where('(taken_out_at IS NULL OR taken_out_at > ?)', array($record_created_at));
		} else { $db->where('(taken_out_at IS NULL OR taken_out_at::date > ?)', array($operational_date)); }
		$db->orderBy('urutan', 'ASC');
		$rows = $db->get('master_part', null, array('field_name', 'label'));
		if (empty($rows)) {
			// Fallback hanya untuk mesin lama yang BENAR-BENAR belum punya master part;
			// jangan jadikan daftar aktif hari ini sebagai isi form historis yang kosong.
			$has_master = $this->GetModel()->where('machine_key', $this->machineKey)->has('master_part');
			return $has_master ? array() : $this->parts;
		}
		$parts = array(); foreach ($rows as $row) { $parts[$row['field_name']] = $row['label']; }
		return $parts;
	}

	protected function partDetailsForRecord($operational_date, $record_created_at = null, $form_id = null)
	{
		if ($form_id) {
			$snapshots = $this->GetModel()->where('machine_key', $this->machineKey)->where('form_id', intval($form_id))->orderBy('urutan', 'ASC')->get('form_part_snapshot');
			if (!empty($snapshots)) { return $snapshots; }
		}
		$db = $this->GetModel();
		$db->where('machine_key', $this->machineKey)->where('active_from', $operational_date, '<=');
		if ($record_created_at) {
			$db->where('(created_at IS NULL OR created_at <= ?)', array($record_created_at));
			$db->where('(taken_out_at IS NULL OR taken_out_at > ?)', array($record_created_at));
		} else { $db->where('(taken_out_at IS NULL OR taken_out_at::date > ?)', array($operational_date)); }
		$db->orderBy('urutan', 'ASC');
		return $db->get('master_part');
	}

	/** Union part yang benar-benar berlaku pada semua row sebuah report. */
	protected function partsForRows($rows, $operational_date)
	{
		$parts = array();
		foreach ($rows as $row) {
			$row_date = $row['operational_date'] ?? $operational_date;
			foreach ($this->partsForRecord($row_date, $row['created_at'] ?? null, $row[$this->idColumn()] ?? null) as $field => $label) { $parts[$field] = $label; }
		}
		return (!empty($parts) || !empty($rows)) ? $parts : $this->partsForRecord($operational_date);
	}

	/** Preflight skema dilakukan sebelum transaksi AM dimulai agar query gagal tidak meng-abort PostgreSQL transaction. */
	private function snapshotSchema()
	{
		if ($this->snapshotSchema !== null) { return $this->snapshotSchema; }
		try {
			$rows = $this->GetModel()->rawQuery("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = current_schema() AND table_name = 'form_part_snapshot') AS has_snapshot_table, EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = 'form_part_snapshot' AND column_name = 'shift_schedule') AS has_shift_schedule");
			$row = $rows[0] ?? array();
			$truthy = array(true, 1, '1', 't', 'true');
			$this->snapshotSchema = array(
				'table' => in_array($row['has_snapshot_table'] ?? null, $truthy, true),
				'shift_schedule' => in_array($row['has_shift_schedule'] ?? null, $truthy, true)
			);
		} catch (\Throwable $e) {
			error_log('Snapshot schema preflight skipped: ' . $e->getMessage());
			$this->snapshotSchema = array('table' => false, 'shift_schedule' => false);
		}
		return $this->snapshotSchema;
	}

	/**
	 * Simpan snapshot metadata part ke form_part_snapshot saat form di-submit.
	 * Skema sudah diperiksa sebelum BEGIN; tabel lama tetap kompatibel tanpa
	 * shift_schedule. Error write lain diteruskan agar transaksi di-rollback.
	 * @param int   $form_id   ID record baru yang baru saja di-INSERT
	 * @param array $parts_meta Hasil partsForRecord() pada saat submit (field_name => label)
	 */
	protected function savePartSnapshot($form_id, $parts_meta, $snapshot_schema = null)
	{
		if (empty($parts_meta) || !$form_id) { return; }
		$schema = $snapshot_schema ?: $this->snapshotSchema();
		if (empty($schema['table'])) { return; }
		$db = $this->GetModel();
		$field_names = array_keys($parts_meta);
		$db->where('machine_key', $this->machineKey)->where('field_name', $field_names, 'in');
		$master_rows = $db->get('master_part');
		if (empty($master_rows)) { return; }
		foreach ($master_rows as $row) {
			$columns = 'machine_key, form_id, field_name, label, section, metode, alat, standard, durasi, pelaksanaan, highlight, image_path, urutan';
			$values = '?,?,?,?,?,?,?,?,?,?,?,?,?';
			$params = array($this->machineKey, $form_id, $row['field_name'], $row['label'], $row['section'] ?? null, $row['metode'] ?? null, $row['alat'] ?? null, $row['standard'] ?? null, $row['durasi'] ?? null, $row['pelaksanaan'] ?? null, $row['highlight'] ?? null, $row['image_path'] ?? null, $row['urutan'] ?? null);
			if (!empty($schema['shift_schedule'])) {
				$columns .= ', shift_schedule';
				$values .= ',?';
				$params[] = trim((string) ($row['shift_schedule'] ?? '')) ?: '1';
			}
			$query = 'INSERT INTO "form_part_snapshot" (' . $columns . ', snapshot_at) VALUES (' . $values . ',CURRENT_TIMESTAMP) ON CONFLICT (machine_key, form_id, field_name) DO NOTHING';
			if ($db->rawQuery($query, $params) === false) {
				throw new \RuntimeException($db->getLastError() ?: 'Gagal menyimpan snapshot part.');
			}
		}
	}
	/** Metadata PDF harus mengikuti snapshot part yang sama dengan report form. */
	protected function partDetailsForRows($rows, $operational_date)
	{
		$details = array();
		foreach ($rows as $row) {
			$row_date = $row['operational_date'] ?? $operational_date;
			foreach ($this->partDetailsForRecord($row_date, $row['created_at'] ?? null, $row[$this->idColumn()] ?? null) as $part) {
				if (!isset($details[$part['field_name']])) { $details[$part['field_name']] = $part; }
			}
		}
		return !empty($details) || !empty($rows) ? array_values($details) : $this->partDetailsForRecord($operational_date);
	}

	/**
	 * Part yang wajib diisi pada halaman add. Default-nya seluruh part aktif.
	 * Subclass boleh override untuk memilih part berdasarkan konteks form, misalnya
	 * shift kerja. Daftar ini dipakai juga saat validasi/simpan, bukan hanya view,
	 * supaya field yang sengaja dikirim dari browser tidak bisa melewati filter.
	 */
	protected function rollbackTransactionSafely($db, $context)
	{
		try { $db->rollback(); }
		catch (Throwable $e) { error_log($context . ' rollback failed: ' . $e->getMessage()); }
	}

	/** Retry sekali setelah DDL hanya untuk error prepared-plan PostgreSQL yang terbukti transient. */
	private function retryTransientSchemaRead(\PDOException $e, $context)
	{
		if ($this->hasRetriedTransientRead) { return false; }
		$code = (string)$e->getCode(); $message = strtolower($e->getMessage());
		$is_cached_plan = $code === '0A000' && strpos($message, 'cached plan must not change result type') !== false;
		$is_missing_prepared = $code === '26000' && strpos($message, 'prepared statement') !== false;
		if (!$is_cached_plan && !$is_missing_prepared) { return false; }
		$this->hasRetriedTransientRead = true;
		error_log('Transient PostgreSQL schema read retry [' . $context . '] SQLSTATE=' . $code . ': ' . $e->getMessage());
		// PDODb tidak persistent, tetapi instance request ini tetap diganti agar
		// SELECT kedua diprepare lewat koneksi baru tanpa plan lama.
		$this->db = null;
		return true;
	}

	/** Jadwal shift historis per part untuk membedakan data tidak dijadwalkan dari belum diisi. */
	protected function partShiftSchedulesForRows($rows, $operational_date)
	{
		$schedules = array();
		$master_schedules = array();
		try {
			$db = $this->GetModel();
			$mp_rows = $db->where('machine_key', $this->machineKey)->get('master_part', null, array('field_name', 'shift_schedule'));
			foreach ($mp_rows as $mp) {
				$master_schedules[$mp['field_name']] = $mp['shift_schedule'];
			}
		} catch (\Throwable $e) {
			error_log('partShiftSchedulesForRows master_part fallback skipped: ' . $e->getMessage());
		}

		foreach ($rows as $row) {
			$row_date = $row['operational_date'] ?? $operational_date;
			$record_id = $row[$this->idColumn()] ?? null;
			foreach ($this->partDetailsForRecord($row_date, $row['created_at'] ?? null, $row[$this->idColumn()] ?? null) as $part) {
				$field = $part['field_name'] ?? null;
				if (!$field || !$record_id) { continue; }
				$raw_sched = !empty($part['shift_schedule']) ? $part['shift_schedule'] : ($master_schedules[$field] ?? '1');
				$values = array_filter(array_map('trim', explode(',', (string)$raw_sched)));
				$values = array_values(array_intersect($values, array('1', '2', '3')));
				$schedules[$record_id][$field] = $values ?: array('1');
			}
		}
		return $schedules;
	}
	protected function partsForAdd($formdata = null)
	{
		if (!in_array('shift', $this->extraFields, true)) { return $this->parts; }
		$shift = is_array($formdata) ? (string) ($formdata['shift'] ?? '') : (string) ($this->request->shift ?? '');
		$this->view->uses_shift = true; $this->view->selected_shift = $shift;
		$this->view->configured_shifts = $this->getConfiguredShifts();
		if (!in_array($shift, $this->view->configured_shifts, true)) { return array(); }
		$db = $this->GetModel(); $parts = array();
		$rows = $db->where('machine_key', $this->machineKey)->where('taken_out_at', null, 'IS')->orderBy('urutan', 'ASC')->get('master_part', null, array('field_name', 'label', 'shift_schedule'));
		foreach ($rows as $row) {
			$shifts = array_filter(array_map('trim', explode(',', (string)($row['shift_schedule'] ?? '')))) ?: array('1');
			if (in_array($shift, $shifts, true)) { $parts[$row['field_name']] = $row['label']; }
		}
		return $parts;
	}

	/** Return pesan error bila konteks Add tidak sah, atau null bila valid. */
	protected function addContextError($formdata)
	{
		if (!in_array('shift', $this->extraFields, true)) { return null; }
		return in_array((string) ($formdata['shift'] ?? ''), $this->getConfiguredShifts(), true) ? null : 'Shift yang dipilih tidak tersedia untuk mesin ini.';
	}

	private function page_data($records, $total)
	{
		$data = new stdClass; $data->records = $records; $data->record_count = count($records);
		$data->total_records = intval($total->totalCount); $data->total_page = ceil($data->total_records / MAX_RECORD_COUNT);
		// Dikirim ke list2.php biar badge OK/NOK di overview ikut part yang
		// AKTIF SEKARANG (termasuk yang baru ditambah/dihapus lewat Master
		// Data Part) -- sebelum ini tiap list2.php nge-hardcode daftar nama
		// field sendiri, jadi part baru gak pernah ke-deteksi NOK-nya di
		// overview (padahal view.php udah bener, karena itu baca $parts dinamis).
		$data->part_fields = $this->historicalPartFields();
		$data->machine_key = $this->machineKey;
		$data->display_name = $this->displayName;
		$data->id_column = $this->idColumn();
		$data->machine_serial = null;
		foreach ($records as $record) {
			if (empty($record['mesin'])) { continue; }
			$serial = $this->GetModel()->where('id', intval($record['mesin']))->getValue('mesin', 'nomor_seri');
			if (!empty($serial)) { $data->machine_serial = $serial; break; }
		}
		// Kontrol UI ini hanya pelengkap; otorisasi penghapusan tetap diverifikasi
		// ulang di delete(), sehingga URL tidak bisa dipakai oleh role lain.
		$data->can_delete_reports = intval(get_active_user('user_role_id')) === 1;
		$data->bulk_delete_url = SITE_ADDR . $this->machineKey . '/delete/{sel_ids}/?csrf_token=' . urlencode(Csrf::$token);
		return $data;
	}

	private function set_report_props($title, $orientation = 'portrait')
	{
		$this->view->report_filename = date('Y-m-d') . '-' . $title;
		$this->view->report_title = $title;
		$this->view->report_layout = 'report_layout.php';
		$this->view->report_paper_size = 'A4';
		$this->view->report_orientation = $orientation;
	}

	function index($fieldname = null, $fieldvalue = null) { return $this->list2($fieldname, $fieldvalue); }

	function list2($fieldname = null, $fieldvalue = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$request = $this->request; $db = $this->GetModel();
		$fields = array("$sql.$idcol", "$sql.mesin", "$sql.operational_date", 'mesin.nama_mesin AS nm_mesin', "$sql.created_at", "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.tanggal_perubahan", "$sql.user_perubah", "$sql.updated_at");
		$has_shift_history = $this->machineHasShiftHistory();
if ($has_shift_history) { $fields[] = "$sql.shift"; }
		foreach ($this->historicalPartFields() as $part) { $fields[] = "$sql.$part"; }
		if (!empty($request->search)) {
			$like = '%' . trim($request->search) . '%';
			$search_fields = array_merge(array('mesin.nama_mesin', "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.kendala"), array_map(function ($part) use ($sql) { return "$sql.$part"; }, $this->historicalPartFields()));
			$conditions = array(); $params = array();
			foreach ($search_fields as $f) { $conditions[] = "$f ILIKE ?"; $params[] = $like; }
			$db->where('(' . implode(' OR ', $conditions) . ')', $params);
			$this->view->search_template = "$table/search.php";
		}
		if (!empty($request->date_from)) { $db->where("$sql.created_at", trim($request->date_from) . ' 00:00:00', '>='); }
		if (!empty($request->date_to)) { $db->where("$sql.created_at", trim($request->date_to) . ' 23:59:59', '<='); }
		if (!empty($request->mesin)) { $db->where("$sql.mesin", $request->mesin); }
		if ($fieldname) { $db->where($fieldname, $fieldvalue); }
		$db->join('mesin', "$sql.mesin = mesin.id", 'LEFT')->orderBy("$sql.$idcol", ORDER_TYPE);
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); $tc = $db->withTotalCount(); $records = $db->get($sql, $pagination, $fields);
		$this->view->page_title = $this->displayName; $this->set_report_props($this->displayName, 'landscape');
		$data = $this->page_data($records, $tc);
		// Begitu sebuah mesin memakai shift, overview harus menjadi satu report
		// harian seperti Illapak, bukan tiga form yang terpisah.
		$data->uses_shift = $has_shift_history;
		return $this->render_view($data->uses_shift ? 'machine_shift_list.php' : "$table/list2.php", $data);
	}


	/** Nomor WR tetap string agar nol depan dan hingga 20 digit tidak hilang. */
	protected function noWrForField(array $formdata, $field)
	{
		$no_wr = trim((string)($formdata['no_wr_' . $field] ?? ''));
		return $no_wr === '' ? null : $no_wr;
	}

	protected function hasValidNoWrInput(array $formdata, array $part_fields)
	{
		foreach ($part_fields as $field => $label) {
			if (($formdata[$field] ?? null) !== 'NOK') { continue; }
			$no_wr = trim((string)($formdata['no_wr_' . $field] ?? ''));
			if ($no_wr !== '' && !preg_match('/^[0-9]{1,20}$/', $no_wr)) {
				$this->view->page_error[] = 'Nomor WR untuk ' . $label . ' harus berisi maksimal 20 digit angka.';
				return false;
			}
		}
		return true;
	}

	/** Validasi status part dan detail abnormalitas dilakukan di server untuk semua mesin. */
	protected function hasValidPartAndNokInput(array $formdata, array $part_fields)
	{
		$valid = true;
		$detail_fields = array(
			'kendala_' => 'uraian kendala',
			'kategori_tag_' => 'kategori tag',
			'korelasi_tag_' => 'korelasi tag',
			'klasifikasi_tag_' => 'klasifikasi tag',
			'kategori_ketidaksesuaian_' => 'kategori ketidaksesuaian',
		);
		foreach ($part_fields as $field => $label) {
			$status = trim((string)($formdata[$field] ?? ''));
			if (!in_array($status, array('OK', 'NOK', 'N/A', 'Tidak Dilakukan'), true)) {
				$this->view->page_error[] = 'Status part ' . $label . ' tidak valid.';
				$valid = false;
				continue;
			}
			if ($status !== 'NOK') { continue; }
			foreach ($detail_fields as $prefix => $detail_label) {
				if (trim((string)($formdata[$prefix . $field] ?? '')) === '') {
					$this->view->page_error[] = ucfirst($detail_label) . ' wajib diisi untuk part NOK: ' . $label . '.';
					$valid = false;
				}
			}
		}
		return $valid;
	}

	private function nokDetailData(array $formdata, $field, $rec_id, $mesin_id)
	{
		return array(
			'id_am' => $rec_id,
			'mesin' => $mesin_id,
			'nama_bagian' => $field,
			'kendala' => trim((string)$formdata['kendala_' . $field]),
			'kategori_tag' => trim((string)$formdata['kategori_tag_' . $field]),
			'korelasi_tag' => trim((string)$formdata['korelasi_tag_' . $field]),
			'klasifikasi_tag' => trim((string)$formdata['klasifikasi_tag_' . $field]),
			'kategori_ketidaksesuaian' => trim((string)$formdata['kategori_ketidaksesuaian_' . $field]),
			'no_wr' => $this->noWrForField($formdata, $field),
			'created_at' => datetime_now(),
		);
	}

	protected function isUnitDeactivated($mesin_id, $operational_date)
	{
		try {
			$db = $this->GetModel();
			$db->where('mesin_id', intval($mesin_id))->where('ended_at', null, 'IS');
			return $db->has('riwayat_status_mesin');
		} catch (Throwable $e) { error_log('Deactivation guard skipped: ' . $e->getMessage()); return false; }
	}

	function add($formdata = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		if ($formdata) {
			$deactivated_unit = isset($formdata['mesin']) && $this->isUnitDeactivated(intval($formdata['mesin']), $this->operationalDate());
			if ($deactivated_unit) { $this->set_page_error('Unit mesin sedang DEAKTIVASI. Pemeriksaan AM tidak dapat disimpan.'); return $this->redirect($table . '/add'); }
			$context_error = $this->addContextError($formdata);
			if ($context_error) {
				$this->view->page_error[] = $context_error;
				$this->set_page_error();
				$this->view->page_title = "Add New AM {$this->displayName}";
				return $this->render_view("$table/add.php", array('parts' => $this->partsForAdd($formdata)));
			}
			$db = $this->GetModel();
			$parts_for_add = $this->partsForAdd($formdata);
			$fields = array_merge(array('mesin'), array_keys($parts_for_add), $this->extraFields);
			$this->fields = $fields;
			$postdata = $this->format_request_data($formdata); $this->rules_array = array(); $this->sanitize_array = array();
			foreach ($fields as $field) { $this->rules_array[$field] = 'required'; $this->sanitize_array[$field] = 'sanitize_string'; }
			// extraFields (misal 'value_tekanan_angin' punya SIG) kolomnya numeric di DB --
			// tanpa validasi ini, isian kayak "5 bar" lolos ke query INSERT dan bikin
			// PDOException mentah (Error 500 generik) alih-alih pesan validasi yang jelas.
			// Koma diterima juga (kebiasaan penulisan desimal Indonesia, "1,5") lalu
			// dinormalisasi ke titik sebelum divalidasi/disimpan.
			foreach ($this->extraFields as $ef) {
				if (isset($postdata[$ef]) && is_string($postdata[$ef])) { $postdata[$ef] = str_replace(',', '.', trim($postdata[$ef])); }
				$this->rules_array[$ef] = 'required|numeric';
			}
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$modeldata['created_at'] = datetime_now();
			$modeldata['operational_date'] = $this->operationalDate($modeldata['created_at']);
			$modeldata['user_create'] = USER_NAME;
			// Auto-approve kalau gak ada part yang NOK -- OK semua ATAU campuran
			// OK/"Tidak Dilakukan" (N/A) tetap auto-approve, cuma NOK yang bikin
			// masuk antrian review manual.
			$all_ok = true;
			foreach (array_keys($parts_for_add) as $pf) { if ((isset($modeldata[$pf]) ? $modeldata[$pf] : null) === 'NOK') { $all_ok = false; break; } }
			if ($all_ok) { $modeldata['approval'] = 'Approved'; $modeldata['user_approve'] = 'System'; $modeldata['tanggal_perubahan'] = datetime_now(); }
			// Mesin biasa hanya satu form per hari. Mesin shift tetap satu form per shift.
			$valid_no_wr = $this->hasValidNoWrInput($formdata, $parts_for_add);
			$valid_parts = $this->hasValidPartAndNokInput($formdata, $parts_for_add);
			$is_duplicate = false;
			if ($this->validated() && $valid_no_wr && $valid_parts) {
				$db->where('mesin', $modeldata['mesin'])->where('operational_date', $modeldata['operational_date']);
				if (in_array('shift', $this->extraFields, true)) { $db->where('shift', $modeldata['shift']); }
				if ($db->has($sql)) {
					$is_duplicate = true;
					$this->view->page_error[] = in_array('shift', $this->extraFields, true) ? 'Shift ini sudah diisi untuk tanggal operasional tersebut.' : 'Form mesin ini sudah diisi untuk tanggal operasional tersebut.';
				}
			}
			if ($this->validated() && $valid_no_wr && $valid_parts && !$is_duplicate) {
				$snapshot_schema = $this->snapshotSchema();
				try {
					$db->startTransaction();
					$rec_id = $this->rec_id = $db->insert($sql, $modeldata);
					if (!$rec_id) { throw new RuntimeException('Gagal menyimpan form AM.'); }
					foreach ($parts_for_add as $field => $label) {
						$kondisi_part = $formdata[$field] ?? ($modeldata[$field] ?? null);
						if ($kondisi_part === 'NOK') {
							if (!$db->insert($this->kendalaTable(), $this->nokDetailData($formdata, $field, $rec_id, $modeldata['mesin']))) { throw new RuntimeException('Gagal menyimpan detail kendala.'); }
						}
					}
					// PR-1: simpan snapshot metadata part saat submit -- mencegah perubahan
					// label/section/metode/standard master_part di kemudian hari mengubah
					// tampilan laporan lama. Dipanggil di dalam transaction yang sama.
					$this->savePartSnapshot($rec_id, $parts_for_add, $snapshot_schema);
					if (!$db->commit()) { throw new RuntimeException('Gagal menyelesaikan transaksi form AM.'); }
					$this->write_to_log('add', 'true'); $this->set_flash_msg("Berhasil tambah AM {$this->displayName}", 'success');
					return $this->redirect($table . '/view/' . $rec_id);
				} catch (Throwable $e) {
					$this->rollbackTransactionSafely($db, 'add ' . $this->machineKey);
					error_log('add ' . $this->machineKey . ' failed: ' . $e->getMessage() . ' | SQL Error: ' . ($db->getLastError() ?: 'none'));
					$raw_err = ($db->getLastError() ?: '') . ' ' . $e->getMessage();
					if (strpos($raw_err, '23505') !== false || stripos($raw_err, 'unique') !== false || stripos($raw_err, 'duplicate') !== false) {
						$this->set_page_error(in_array('shift', $this->extraFields, true)
							? 'Shift ini sudah diisi untuk tanggal operasional tersebut.'
							: 'Form mesin ini sudah diisi untuk tanggal operasional tersebut.');
					} else {
						$this->set_page_error('Gagal menyimpan form AM. Silakan coba kembali atau hubungi administrator.');
					}
				}
			}
		}
		$today = $this->operationalDate();
		$deactive_units = $this->GetModel()->rawQuery("
			SELECT r.mesin_id, m.nama_mesin, r.reason, r.started_at, r.notes, r.action_by_username
			FROM riwayat_status_mesin r
			JOIN mesin m ON m.id = r.mesin_id
			WHERE r.ended_at IS NULL AND ?::text IS NOT NULL AND ?::text IS NOT NULL
		", array($today . ' 23:59:59', $today . ' 00:00:00'));

		$deactive_map = array();
		foreach ($deactive_units as $du) {
			$deactive_map[intval($du['mesin_id'])] = array(
				'mesin_id' => intval($du['mesin_id']),
				'nama_mesin' => $du['nama_mesin'],
				'reason' => $du['reason'],
				'notes' => $du['notes'],
				'started_at' => $du['started_at'],
				'action_by_username' => $du['action_by_username']
			);
		}
		$this->view->deactivated_units = $deactive_map;
		$this->view->page_title = "Add New AM {$this->displayName}";
		return $this->render_view("$table/add.php", array('parts' => $this->partsForAdd(), 'deactivated_units' => $deactive_map));
	}

	function view($rec_id = null, $value = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$fields = array_merge(array("$sql.$idcol", "$sql.mesin", 'mesin.nama_mesin AS nm_mesin', "$sql.created_at", "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.tanggal_perubahan", "$sql.user_perubah", "$sql.updated_at", "$sql.perubahan"), array_map(function ($p) use ($sql) { return "$sql.$p"; }, array_merge($this->historicalPartFields(), $this->extraFields)));
		try {
		if ($value) { $db->where($rec_id, urldecode($value)); } else { $db->where("$sql.$idcol", urldecode($rec_id)); }
		$record = $db->join('mesin', "$sql.mesin = mesin.id", 'LEFT')->getOne($sql, $fields);
		if ($record) {
			$details = $db->rawQuery("SELECT k.*, t1.kategori_tag AS teks_kategori, t2.nama AS teks_korelasi, t3.nama AS teks_klasifikasi, t4.kategori AS teks_ketidaksesuaian FROM {$this->kendalaTable()} k LEFT JOIN tag t1 ON k.kategori_tag=t1.id LEFT JOIN korelasi t2 ON k.korelasi_tag=t2.id LEFT JOIN klasifikasi t3 ON k.klasifikasi_tag=t3.id LEFT JOIN kategori t4 ON k.kategori_ketidaksesuaian=t4.id WHERE k.id_am=?", array($record[$idcol]));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
		} else { $this->set_page_error($db->getLastError() ?: 'No record found'); }
		if (!$record) { $record = array(); }
		$record['parts'] = !empty($record) ? $this->partsForRecord($record['operational_date'] ?? $this->operationalDate($record['created_at'] ?? null), $record['created_at'] ?? null, $record[$idcol] ?? null) : $this->parts;
		$this->view->page_title = "View AM {$this->displayName}"; $this->set_report_props("View AM {$this->displayName}");
			return $this->render_view("$table/view.php", $record);
		} catch (\PDOException $e) {
			if ($this->retryTransientSchemaRead($e, 'view ' . $this->machineKey)) { return $this->view($rec_id, $value); }
			throw $e;
		}
	}

	/** Cetak check sheet resmi untuk Periode 1 (1-16) atau Periode 2 (17-akhir bulan). */
	function period_report()
	{
		$request = $this->request;
		$year = intval($request->year ?? date('Y'));
		$month = intval($request->month ?? date('n'));
		$period = intval($request->period ?? 1);
		$mesin = intval($request->mesin ?? 0);
		if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12 || !in_array($period, array(1, 2), true) || !$mesin) {
			$this->view->page_title = 'Cetak Check Sheet ' . $this->displayName;
			return $this->render_view('machine_period_report.php', array('selection_only' => true, 'machine_key' => $this->machineKey, 'display_name' => $this->displayName));
		}
		$first = new DateTime(sprintf('%04d-%02d-01', $year, $month));
		$start_day = $period === 1 ? 1 : 17;
		$end_day = $period === 1 ? 16 : intval($first->format('t'));
		$start = sprintf('%04d-%02d-%02d', $year, $month, $start_day);
		$end = sprintf('%04d-%02d-%02d', $year, $month, $end_day);
		$db = $this->GetModel(); $sql = $this->sqlTable(); $idcol = $this->idColumn();
		// Satu form dapat direvisi berkali-kali. Baca versi paling lama lebih dulu agar
		// assignment keyed di bawah selalu menyisakan kondisi terakhir sebagai current truth.
		$rows = $db->where('mesin', $mesin)->where('operational_date', $start, '>=')->where('operational_date', $end, '<=')->orderBy('operational_date', 'ASC')->orderBy('COALESCE(updated_at, created_at)', 'ASC')->get($sql);
		$machine = $db->where('id', $mesin)->getOne('mesin', array('nama_mesin'));
		$checks = array(); $all_approved = !empty($rows);
		foreach ($rows as $row) {
			if (($row['approval'] ?? null) !== 'Approved') { $all_approved = false; }
			$day = intval((new DateTime($row['operational_date']))->format('j'));
			foreach ($this->partsForRecord($row['operational_date'], $row['created_at'] ?? null, $row[$idcol] ?? null) as $field => $label) {
				if (!empty($row[$field])) {
					$shift_key = trim((string)($row['shift'] ?? ''));
					$shift_key = $shift_key === '' ? '__default__' : $shift_key;
					$checks[$field][$day][$shift_key] = $row[$field];
				}
			}
		}
		$part_details = $this->partDetailsForRows($rows, $start);

		// Ambil riwayat status deaktivasi unit ini pada rentang periode
		$deactivation_rows = $db->where('mesin_id', $mesin)
			->where('started_at', $end . ' 23:59:59', '<=')
			->where('(ended_at IS NULL OR ended_at >= ?)', array($start . ' 00:00:00'))
			->orderBy('started_at', 'ASC')
			->get('riwayat_status_mesin');

		$today_str = $this->operationalDate();
		$deactivated_days = array();
		for ($d_num = $start_day; $d_num <= $end_day; $d_num++) {
			$day_str = sprintf('%04d-%02d-%02d', $year, $month, $d_num);
			foreach ($deactivation_rows as $dr) {
				$s_date = substr($dr['started_at'], 0, 10);
				// Aturan kuning:
				// - ended_at NULL (masih deaktif): kuning hanya untuk hari yang SUDAH LEWAT
				//   atau HARI INI. Hari besok/future tetap putih karena belum pasti masih down.
				//   Besoknya, jika masih deaktif, baru otomatis kuning.
				// - ended_at ada: kuning jika deaktivasi melewati tengah malam hari itu
				//   (artinya masih down di akhir hari). Jika mulai & selesai hari yang sama → putih.
				$still_active_at_end_of_day = empty($dr['ended_at'])
					? ($day_str >= $s_date && $day_str <= $today_str) // sudah lewat/hari ini & masih aktif
					: ($day_str >= $s_date && substr($dr['ended_at'], 0, 10) > $day_str); // berakhir setelah hari ini
				if ($still_active_at_end_of_day) {
					$deactivated_days[$d_num] = $dr;
					break;
				}
			}
		}


		// Kumpulkan paraf penanggung jawab terakhir: revisi mengalihkan atribusi
		// dari pembuat awal kepada user_perubah.
		$daily_paraf = array();
		$daily_operators = array();
		$operator_names = array();
		foreach ($rows as $row) {
			$day = intval((new DateTime($row['operational_date']))->format('j'));
			$active_user = !empty($row['user_perubah']) ? $row['user_perubah'] : ($row['user_create'] ?? '');
			if ($active_user !== '') {
				$daily_operators[$day] = $active_user;
				$operator_names[] = $active_user;
			}
		}

		$operator_profiles = array();
		if (!empty($operator_names)) {
			$unique_ops = array_values(array_unique($operator_names));
			$placeholders = implode(',', array_fill(0, count($unique_ops), '?'));
			$user_records = $db->rawQuery("SELECT id_user, username, nama, paraf_image, user_initials FROM users WHERE username IN ($placeholders) OR nama IN ($placeholders)", array_merge($unique_ops, $unique_ops));
			foreach ($user_records as $ur) {
				$operator_profiles[$ur['username']] = $ur;
				$operator_profiles[$ur['nama']] = $ur;
			}
		}

		foreach ($daily_operators as $day => $op_identifier) {
			$prof = $operator_profiles[$op_identifier] ?? null;
			$valid_paraf = $prof && is_valid_base64_png_data_uri($prof['paraf_image'] ?? null);
			$fallback = $prof ? 'ID:' . intval($prof['id_user']) : 'ID:?';
			$full_name = trim((string)($prof['nama'] ?? $op_identifier));
			$nik = trim((string)($prof['username'] ?? $op_identifier));

			$daily_paraf[$day] = array(
				'user_create' => $op_identifier,
				'paraf_image' => $valid_paraf ? $prof['paraf_image'] : null,
				'user_initials' => $fallback,
				'tooltip' => $full_name . ' | NIK: ' . $nik
			);
		}

		// Hitung hash dokumen & ambil data tanda tangan digital periode
		$doc_hash = QrSignatureHelper::computeDocumentHash($this->machineKey, $mesin, $month, $year, $period, $checks);
		$signature = QrSignatureHelper::getPeriodSignature($this->machineKey, $mesin, $month, $year, $period);

		$operator_qr = null;
		if ($signature && !empty($signature['operator_token'])) {
			$operator_qr = QrSignatureHelper::generateQrBase64(SITE_ADDR . 'verify/signature/' . $signature['operator_token']);
		}

		$spv_qr = null;
		if ($signature && !empty($signature['spv_token'])) {
			$spv_qr = QrSignatureHelper::generateQrBase64(SITE_ADDR . 'verify/signature/' . $signature['spv_token']);
		}

		$current_user_role = intval(get_active_user('user_role_id'));
		$can_sign_operator = in_array($current_user_role, array(4, 5), true); // Staff, Operator
		$can_sign_spv = $current_user_role === 3; // Supervisor only
		$can_cancel_own_operator = !empty($signature['operator_token'])
			&& empty($signature['spv_token'])
			&& intval($signature['operator_id'] ?? 0) === intval(USER_ID);
		$can_cancel_own_spv = !empty($signature['spv_token'])
			&& intval($signature['spv_id'] ?? 0) === intval(USER_ID);

		$data = array(
			'selection_only' => false,
			'machine_key' => $this->machineKey,
			'display_name' => $this->displayName,
			'machine_name' => $machine['nama_mesin'] ?? '-',
			'mesin_id' => $mesin,
			'year' => $year,
			'month' => $month,
			'period' => $period,
			'start_day' => $start_day,
			'end_day' => $end_day,
			'parts' => $this->partsForRows($rows, $start),
			'part_details' => $part_details,
			'checks' => $checks,
			'deactivated_days' => $deactivated_days,
			'deactivation_records' => $deactivation_rows,
			'daily_paraf' => $daily_paraf,
			'doc_hash' => $doc_hash,
			'period_signature' => $signature,
			'operator_qr' => $operator_qr,
			'spv_qr' => $spv_qr,
			'can_sign_operator' => $can_sign_operator,
			'can_sign_spv' => $can_sign_spv,
			'can_cancel_own_operator' => $can_cancel_own_operator,
			'can_cancel_own_spv' => $can_cancel_own_spv
		);
		$data['all_approved'] = $all_approved;
		$this->view->page_title = 'Check Sheet ' . $this->displayName;
		$this->set_report_props('Check-Sheet-' . $this->machineKey . '-' . $year . '-' . $month . '-P' . $period, 'landscape');
		$this->view->report_layout = 'check_sheet_layout.php';
		return $this->render_view('machine_period_report.php', $data);
	}

	/** Satu layar report harian yang menggabungkan semua submit shift pada operational_date yang sama. */
	function daily_report()
	{
		$mesin = intval($this->request->mesin ?? 0); $date = trim((string)($this->request->date ?? ''));
		if (!$mesin || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { return $this->redirect($this->machineKey); }
		// Hitung sebelum query laporan dibuat: PDODb memakai query builder yang
		try {
		// sama, sehingga filter mesin/tanggal tidak boleh terbawa ke master_part.
		$has_shift_history = $this->machineHasShiftHistory();
		$db = $this->GetModel(); $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db->where('mesin', $mesin)->where('operational_date', $date);
		if ($has_shift_history) {
			$db->orderBy("COALESCE(NULLIF(shift, ''), '1')", 'ASC');
		}
		$rows = $db->orderBy('created_at', 'ASC')->get($sql);
		$machine = $db->where('id', $mesin)->getOne('mesin', array('nama_mesin'));
		$this->view->page_title = 'Report Harian ' . $this->displayName;
			return $this->render_view('machine_daily_report.php', array('display_name' => $this->displayName, 'machine_key' => $this->machineKey, 'machine_name' => $machine['nama_mesin'] ?? '-', 'operational_date' => $date, 'rows' => $rows, 'parts' => $this->partsForRows($rows, $date), 'part_shift_schedules' => $this->partShiftSchedulesForRows($rows, $date), 'id_column' => $idcol));
		} catch (\PDOException $e) {
			if ($this->retryTransientSchemaRead($e, 'daily_report ' . $this->machineKey)) { return $this->daily_report(); }
			throw $e;
		}
	}

	/** Approval manual hanya oleh SPV; row dikunci hingga status dan atribusi tersimpan atomik. */
	private function updateApprovalSafely($rec_id, $approval)
	{
		if (intval(get_active_user('user_role_id')) !== 3) {
			return array('success' => false, 'status' => 403, 'message' => 'Hanya Supervisor yang dapat melakukan approval manual.');
		}
		$approval = trim((string)$approval);
		if (!in_array($approval, array('Approved', 'Not Approved'), true)) {
			return array('success' => false, 'status' => 422, 'message' => 'Status approval tidak valid.');
		}
		$db = $this->GetModel(); $sql = $this->sqlTable(); $idcol = $this->idColumn();
		try {
			$db->startTransaction();
			$current = $db->rawQueryOne("SELECT approval, user_approve, tanggal_perubahan FROM {$sql} WHERE {$idcol} = ? FOR UPDATE", array(intval($rec_id)));
			if (!$current) {
				$this->rollbackTransactionSafely($db, 'approval record missing');
				return array('success' => false, 'status' => 404, 'message' => 'Checklist tidak ditemukan atau sudah tidak tersedia.');
			}
			if (($current['approval'] ?? null) === 'Approved') {
				$this->rollbackTransactionSafely($db, 'approval already final');
				$approved_by = trim((string)($current['user_approve'] ?? '')) ?: 'Supervisor lain';
				return array('success' => false, 'status' => 409, 'message' => "Checklist sudah disetujui oleh {$approved_by}; data tidak ditimpa.");
			}
			$now = datetime_now();
			$db->where($idcol, intval($rec_id));
			if (!$db->update($sql, array('approval' => $approval, 'user_approve' => USER_NAME, 'tanggal_perubahan' => $now, 'updated_at' => $now)) || !$db->getRowCount()) {
				throw new RuntimeException($db->getLastError() ?: 'Tidak ada record approval yang diperbarui.');
			}
			if (!$db->commit()) { throw new RuntimeException('Commit approval gagal.'); }
			return array('success' => true, 'status' => 200, 'message' => 'Approval berhasil diperbarui.');
		} catch (Throwable $e) {
			$this->rollbackTransactionSafely($db, 'approval ' . $this->machineKey);
			error_log('Approval ' . $this->machineKey . ' failed: ' . $e->getMessage());
			return array('success' => false, 'status' => 500, 'message' => 'Approval gagal disimpan. Silakan muat ulang dan coba kembali.');
		}
	}

	function edit($rec_id = null, $formdata = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		if ($formdata) {
			$this->fields = array('approval'); $postdata = $this->format_request_data($formdata);
			$this->rules_array = array('approval' => 'required'); $this->sanitize_array = array('approval' => 'sanitize_string');
			$modeldata = $this->validate_form($postdata);
			if ($this->validated()) {
				$result = $this->updateApprovalSafely($rec_id, $modeldata['approval'] ?? null);
				if ($result['success']) { $this->write_to_log('edit', 'true'); $this->set_flash_msg($result['message'], 'success'); }
				else { $this->set_flash_msg($result['message'], ($result['status'] ?? 500) === 403 ? 'danger' : 'warning'); }
				return $this->redirect($table);
			}
		}
		$db->where("$sql.$idcol", $rec_id); $data = $db->getOne($sql, array($idcol, 'approval'));
		if (!$data) { $this->set_page_error('No record found'); }
		$this->view->page_title = "Approve AM {$this->displayName}"; return $this->render_view("$table/edit.php", $data);
	}

	/** Operator pembuat record edit ulang isian datanya sendiri (terpisah dari flow approval di edit()). */
	function edit_data($rec_id = null, $formdata = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$db->where($idcol, $rec_id);
		$existing_record = $db->getOne($sql);
		if (!$existing_record) {
			$this->set_page_error($db->getLastError() ?: 'Data form AM tidak ditemukan.');
			return $this->redirect($table);
		}

		//URS 3.1: Manager/Supervisor/Staff-Operator cuma boleh edit_data submission sendiri; Administrator bebas.
		$is_owner = (($existing_record['user_create'] ?? null) === USER_NAME);
		if (!$is_owner && intval(get_active_user('user_role_id')) !== 1) {
			http_response_code(403);
			return $this->render_view('errors/forbidden.php', null, 'info_layout.php');
		}

		$op_date = new DateTime($existing_record['operational_date']);
		$m_month = intval($op_date->format('n'));
		$m_year = intval($op_date->format('Y'));
		$m_period = intval($op_date->format('j')) <= 16 ? 1 : 2;
		$sig = QrSignatureHelper::getPeriodSignature(
			$this->machineKey,
			intval($existing_record['mesin']),
			$m_month,
			$m_year,
			$m_period
		);
		if ($sig && (!empty($sig['operator_token']) || !empty($sig['spv_token']))) {
			$this->set_flash_msg('Form AM pada periode ini telah ditandatangani secara digital. Untuk melakukan revisi data, batalkan TTD terlebih dahulu oleh penandatangan terkait.', 'warning');
			return $this->redirect($table . '/view/' . $rec_id);
		}

		if ($formdata) {
			$postdata = $this->format_request_data($formdata);
			$this->fields = array_merge(array('perubahan'), $this->part_fields(), $this->extraFields);
			$this->rules_array = array('perubahan' => 'required');
			$this->sanitize_array = array('perubahan' => 'sanitize_string');
			foreach (array_merge($this->part_fields(), $this->extraFields) as $field) { $this->sanitize_array[$field] = 'sanitize_string'; }
			// Edit Data tidak selalu menampilkan extra field (contohnya shift). Pertahankan nilai
			// yang tersimpan agar validasi required tidak gagal dan kolom lama tidak tertimpa.
			foreach ($this->extraFields as $ef) {
				if (!isset($postdata[$ef]) && isset($existing_record[$ef])) { $postdata[$ef] = $existing_record[$ef]; }
			}
			// Lihat catatan sama di add(): extraFields kolomnya numeric di DB, jadi
			// perlu divalidasi + koma dinormalisasi ke titik di sini juga.
			foreach ($this->extraFields as $ef) {
				if (isset($postdata[$ef]) && is_string($postdata[$ef])) { $postdata[$ef] = str_replace(',', '.', trim($postdata[$ef])); }
				$this->rules_array[$ef] = 'required|numeric';
			}
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$modeldata['updated_at'] = datetime_now(); $modeldata['user_perubah'] = USER_NAME;
			//Re-evaluate approval abis data dikoreksi -- jangan biarin status approval
			//lama nyangkut gitu aja. Gak ada part NOK lagi (OK semua atau campuran
			//OK/"Tidak Dilakukan") -> auto-approve lagi (sama persis kayak submission
			//baru). Ada yang jadi NOK -> approval di-reset ke "belum di-approve"
			//(BUKAN langsung "Not Approved"), balik masuk antrian review manual
			//supervisor/manager -- konsisten sama alur submission NOK baru.
			$all_ok = true;
			foreach ($this->part_fields() as $pf) { if ((isset($modeldata[$pf]) ? $modeldata[$pf] : null) === 'NOK') { $all_ok = false; break; } }
			if ($all_ok) {
				$modeldata['approval'] = 'Approved'; $modeldata['user_approve'] = 'System'; $modeldata['tanggal_perubahan'] = datetime_now();
			} else {
				$modeldata['approval'] = null; $modeldata['user_approve'] = null; $modeldata['tanggal_perubahan'] = null;
			}
			$valid_no_wr = $this->hasValidNoWrInput($formdata, $this->parts);
			$valid_parts = $this->hasValidPartAndNokInput($formdata, $this->parts);
			if ($this->validated() && $valid_no_wr && $valid_parts) {
				try {
					$db->startTransaction();
					$db->where($idcol, $rec_id);
					$bool = $db->update($sql, $modeldata);
					if (!$bool) { throw new RuntimeException('Gagal memperbarui form AM.'); }
					$db->where($idcol, $rec_id);
					$row = $db->getOne($sql, 'mesin');
					if (!$row) { throw new RuntimeException('Record AM tidak ditemukan.'); }
					$mesin_id = $row['mesin'];
					$db->where('id_am', $rec_id);
					$deleted = $db->delete($this->kendalaTable());
					if ($deleted === false && $db->getLastError()) {
						throw new RuntimeException('Gagal menghapus detail kendala lama: ' . $db->getLastError());
					}
					foreach ($this->parts as $field => $label) {
						$kondisi_part = $formdata[$field] ?? ($modeldata[$field] ?? null);
						if ($kondisi_part === 'NOK') {
							if (!$db->insert($this->kendalaTable(), $this->nokDetailData($formdata, $field, $rec_id, $mesin_id))) { throw new RuntimeException('Gagal menyimpan detail kendala.'); }
						}
					}
					if (!$db->commit()) { throw new RuntimeException('Gagal menyelesaikan transaksi form AM.'); }
					$this->write_to_log('edit_data', 'true');
					$this->set_flash_msg('Data berhasil diperbarui', 'success');
					return $this->redirect("$table/view/$rec_id");
				} catch (Throwable $e) {
					$this->rollbackTransactionSafely($db, 'edit_data ' . $this->machineKey);
					error_log('edit_data ' . $this->machineKey . ' failed: ' . $e->getMessage() . ' | SQL Error: ' . ($db->getLastError() ?: 'none'));
					$this->set_page_error('Gagal memperbarui form AM. Silakan coba kembali atau hubungi administrator.');
				}
			}
		}
		$db->where("$sql.$idcol", $rec_id);
		$record = $db->join('mesin', "$sql.mesin = mesin.id", 'LEFT')->getOne($sql, array("$sql.*", 'mesin.nama_mesin AS nm_mesin'));
		if ($record) {
			$details = $db->rawQuery("SELECT k.* FROM {$this->kendalaTable()} k WHERE k.id_am=?", array($rec_id));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
			// Preservasi input yang baru saja diketik user bila submit/update gagal
			if (!empty($formdata) && is_array($formdata)) {
				foreach ($formdata as $k => $v) {
					if (!is_array($v) && !str_starts_with($k, 'kendala_') && !str_starts_with($k, 'kategori_') && !str_starts_with($k, 'korelasi_') && !str_starts_with($k, 'klasifikasi_') && !str_starts_with($k, 'no_wr_')) {
						$record[$k] = $v;
					}
				}
				if (!empty($formdata['perubahan'])) {
					$record['perubahan'] = $formdata['perubahan'];
				}
				foreach ($this->parts as $field => $label) {
					if (isset($formdata[$field])) {
						$record[$field] = $formdata[$field];
					}
					if (!empty($_POST['kendala_' . $field]) || !empty($_POST['kategori_tag_' . $field]) || !empty($_POST['no_wr_' . $field])) {
						$record['abnormalitas'][$field] = array(
							'nama_bagian' => $field,
							'kendala' => $_POST['kendala_' . $field] ?? '',
							'kategori_tag' => $_POST['kategori_tag_' . $field] ?? null,
							'korelasi_tag' => $_POST['korelasi_tag_' . $field] ?? null,
							'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field] ?? null,
							'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field] ?? null,
							'no_wr' => $this->noWrForField($formdata, $field),
						);
					}
				}
			}
		} else {
			$this->set_page_error('Data form AM tidak ditemukan.'); $record = array();
		}
		$record['parts'] = !empty($record) ? $this->partsForRecord($record['operational_date'] ?? $this->operationalDate($record['created_at'] ?? null), $record['created_at'] ?? null, $record[$idcol] ?? null) : $this->parts;
		$record['part_details'] = !empty($record) ? $this->partDetailsForRecord($record['operational_date'] ?? $this->operationalDate($record['created_at'] ?? null), $record['created_at'] ?? null, $record[$idcol] ?? null) : array();
		$this->view->page_title = "Edit Data AM {$this->displayName}";
		return $this->render_view("$table/edit_data.php", $record);
	}

	function editfield($rec_id = null, $formdata = null)
	{
		$this->rec_id = $rec_id;
		if ($formdata) {
			if (($formdata['name'] ?? '') !== 'approval') { http_response_code(403); return render_error('Kolom ini tidak diizinkan untuk inline editing.', 403); }
			$result = $this->updateApprovalSafely($rec_id, $formdata['value'] ?? null);
			if (!$result['success']) { http_response_code($result['status'] ?? 500); return render_error($result['message'], $result['status'] ?? 500); }
			$this->write_to_log('edit', 'true');
			return render_json(array('num_rows' => 1, 'rec_id' => $rec_id));
		}
		return null;
	}

	function delete($rec_id = null)
	{
		Csrf::cross_check();
		// Menghapus laporan adalah tindakan destruktif. Hanya Super Admin yang
		// boleh menjalankannya, termasuk bila URL delete dipanggil langsung.
		if (intval(get_active_user('user_role_id')) !== 1) {
			http_response_code(403);
			return $this->render_view('errors/forbidden.php', null, 'info_layout.php');
		}
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$ids = array_values(array_filter(array_map('intval', explode(',', (string) $rec_id))));
		if (empty($ids)) {
			$this->set_flash_msg('Tidak ada laporan yang dipilih.', 'warning');
			return $this->redirect($table);
		}
		//Baris kendala (abnormalitas) anaknya WAJIB ikut dihapus -- gak ada FK
		//ON DELETE CASCADE di skema ini, jadi kalau cuma hapus record induk,
		//kendala-nya nyangkut jadi orphan selamanya (bikin DB numpuk & berisiko
		//nempel ke record lain kalau id sempat kepakai ulang). Dibungkus
		//transaction supaya gak bisa kejadian induk kehapus tapi anak ketinggalan.
		$db->startTransaction();
		$db->where('id_am', $ids, 'in');
		$db->delete($this->kendalaTable());
		$db->where($idcol, $ids, 'in');
		if ($db->delete($sql)) {
			$db->commit();
			$this->write_to_log('delete', 'true');
			$this->set_flash_msg('Record deleted successfully', 'success');
		} else {
			$db->rollback();
			$this->set_flash_msg($db->getLastError(), 'danger');
		}
		return $this->redirect($table);
	}

	/**
	 * Sign period document digitally (Operator or SPV)
	 */
	function sign_period()
	{
		if (!is_post_request()) {
			http_response_code(405);
			render_json(array('success' => false, 'message' => 'Metode request tidak diizinkan.'));
			return;
		}

		$request = $this->post ?? new stdClass;
		$mesin = intval($request->mesin ?? 0);
		$year = intval($request->year ?? 0);
		$month = intval($request->month ?? 0);
		$period = intval($request->period ?? 0);
		$role_type = trim((string)($request->role_type ?? '')); // 'operator' or 'spv'

		if (!$mesin || $year < 2020 || $year > 2100 || $month < 1 || $month > 12 || !in_array($period, array(1, 2), true) || !in_array($role_type, array('operator', 'spv'), true)) {
			http_response_code(400);
			render_json(array('success' => false, 'message' => 'Parameter tidak lengkap.'));
			return;
		}

		$current_user_role = intval(get_active_user('user_role_id'));
		$user_id = USER_ID;
		if (!ACL::is_machine_allowed($this->machineKey, $current_user_role, get_active_user('area'))) {
			http_response_code(403);
			render_json(array('success' => false, 'message' => 'Mesin ini berada di luar area penugasan Anda.'));
			return;
		}

		if ($role_type === 'operator' && !in_array($current_user_role, array(4, 5), true)) {
			http_response_code(403);
			render_json(array('success' => false, 'message' => 'Hanya role Operator atau Staff yang dapat menandatangani sebagai Operator Produksi.'));
			return;
		}

		if ($role_type === 'spv' && $current_user_role !== 3) {
			http_response_code(403);
			render_json(array('success' => false, 'message' => 'Hanya role Supervisor yang dapat menandatangani sebagai SPV/Fasilitator.'));
			return;
		}

		if ($role_type === 'spv') {
			$signature = QrSignatureHelper::getPeriodSignature($this->machineKey, $mesin, $month, $year, $period);
			if (!$signature || empty($signature['operator_token'])) {
				http_response_code(422);
				render_json(array('success' => false, 'message' => 'Operator Produksi harus menandatangani Check Sheet terlebih dahulu sebelum disetujui oleh SPV/Fasilitator.'));
				return;
			}
		}

		// Calculate current document hash
		$first = new DateTime(sprintf('%04d-%02d-01', $year, $month));
		$start_day = $period === 1 ? 1 : 17;
		$end_day = $period === 1 ? 16 : intval($first->format('t'));
		$start = sprintf('%04d-%02d-%02d', $year, $month, $start_day);
		$end = sprintf('%04d-%02d-%02d', $year, $month, $end_day);

		$db = $this->GetModel();
		$sql = $this->sqlTable();
		$idcol = $this->idColumn();
		$rows = $db->where('mesin', $mesin)->where('operational_date', $start, '>=')->where('operational_date', $end, '<=')->orderBy('operational_date', 'ASC')->orderBy('COALESCE(updated_at, created_at)', 'ASC')->get($sql);

		$checks = array();
		foreach ($rows as $row) {
			$day = intval((new DateTime($row['operational_date']))->format('j'));
			foreach ($this->partsForRecord($row['operational_date'], $row['created_at'] ?? null, $row[$idcol] ?? null) as $field => $label) {
				if (!empty($row[$field])) {
					$shift_key = trim((string)($row['shift'] ?? ''));
					$shift_key = $shift_key === '' ? '__default__' : $shift_key;
					$checks[$field][$day][$shift_key] = $row[$field];
				}
			}
		}

		$doc_hash = QrSignatureHelper::computeDocumentHash($this->machineKey, $mesin, $month, $year, $period, $checks);
		$result = QrSignatureHelper::savePeriodSignature($this->machineKey, $mesin, $month, $year, $period, $user_id, $role_type, $doc_hash);

		if ($result['success']) {
			$this->write_to_log('sign_period', 'true');
			$verificationUrl = SITE_ADDR . 'verify/signature/' . $result['token'];
			$qrBase64 = QrSignatureHelper::generateQrBase64($verificationUrl);
			render_json(array(
				'success' => true,
				'message' => 'Dokumen berhasil ditandatangani secara digital!',
				'token' => $result['token'],
				'qr_code' => $qrBase64,
				'verify_url' => $verificationUrl
			));
			return;
		}

		render_json(array('success' => false, 'message' => $result['error'] ?? 'Gagal menyimpan tanda tangan digital.'));
	}

	/** Batalkan tanda tangan periode untuk membuka revisi, dengan audit trail wajib. */
	function cancel_period_signature()
	{
		if (!is_post_request()) {
			http_response_code(405);
			render_json(array('success' => false, 'message' => 'Metode request tidak diizinkan.'));
			return;
		}

		$current_user_role = intval(get_active_user('user_role_id'));

		$request = $this->post ?? new stdClass;
		$mesin = intval($request->mesin ?? 0);
		$year = intval($request->year ?? 0);
		$month = intval($request->month ?? 0);
		$period = intval($request->period ?? 0);
		$role_type = trim((string)($request->role_type ?? ''));
		$reason = trim((string)($request->reason ?? ''));

		if (!$mesin || $year < 2020 || $year > 2100 || $month < 1 || $month > 12 || !in_array($period, array(1, 2), true) || !in_array($role_type, array('operator', 'spv'), true)) {
			http_response_code(400);
			render_json(array('success' => false, 'message' => 'Parameter pembatalan tidak valid.'));
			return;
		}
		if ($reason === '' || mb_strlen($reason) > 500) {
			http_response_code(422);
			render_json(array('success' => false, 'message' => 'Alasan pembatalan wajib diisi dan maksimal 500 karakter.'));
			return;
		}
		if (!ACL::is_machine_allowed($this->machineKey, $current_user_role, get_active_user('area'))) {
			http_response_code(403);
			render_json(array('success' => false, 'message' => 'Mesin ini berada di luar area penugasan Anda.'));
			return;
		}

		$db = $this->GetModel();
		$db->startTransaction();
		try {
			$signature = $db->rawQueryOne(
				'SELECT * FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = ? AND tahun = ? AND periode = ? FOR UPDATE',
				array($this->machineKey, $mesin, $month, $year, $period)
			);
			$token_column = $role_type . '_token';
			if (!$signature || empty($signature[$token_column])) {
				$this->rollbackTransactionSafely($db, 'cancel period signature not found');
				http_response_code(404);
				render_json(array('success' => false, 'message' => 'Tanda tangan yang akan dibatalkan tidak ditemukan.'));
				return;
			}
			if ($role_type === 'operator' && intval($signature['operator_id'] ?? 0) !== intval(USER_ID)) {
				$this->rollbackTransactionSafely($db, 'cancel operator signature owner mismatch');
				http_response_code(403);
				render_json(array('success' => false, 'message' => 'Hanya operator penandatangan dokumen ini yang berhak membatalkan tanda tangan.'));
				return;
			}
			if ($role_type === 'operator' && !empty($signature['spv_token'])) {
				$this->rollbackTransactionSafely($db, 'cancel operator signature dependency');
				http_response_code(409);
				render_json(array('success' => false, 'message' => 'Batalkan TTD SPV terlebih dahulu sebelum membatalkan TTD Operator'));
				return;
			}
			if ($role_type === 'spv' && intval($signature['spv_id'] ?? 0) !== intval(USER_ID)) {
				$this->rollbackTransactionSafely($db, 'cancel spv signature owner mismatch');
				http_response_code(403);
				render_json(array('success' => false, 'message' => 'Hanya SPV penandatangan dokumen ini yang berhak membatalkan tanda tangan.'));
				return;
			}

			$update_data = array(
				$role_type . '_id' => null,
				$role_type . '_signed_at' => null,
				$token_column => null,
				'updated_at' => datetime_now()
			);
			$other_token = $role_type === 'operator' ? ($signature['spv_token'] ?? null) : ($signature['operator_token'] ?? null);
			$update_data['status'] = $other_token
				? ($role_type === 'operator' ? 'approved' : 'signed_operator')
				: 'draft';

			$db->where('id', $signature['id']);
			if (!$db->update('am_period_signatures', $update_data) || !$db->getRowCount()) {
				throw new RuntimeException($db->getLastError() ?: 'Data tanda tangan tidak berubah.');
			}
			$update_query = $db->getLastQuery();

			$audit_request = array(
				'mesin' => $mesin,
				'year' => $year,
				'month' => $month,
				'period' => $period,
				'role_type' => $role_type,
				'reason' => $reason
			);
			$audit_data = array(
				'Timestamp' => datetime_now(),
				'id_log' => (string)$signature['id'],
				'Action' => 'cancel_period_signature',
				'TableName' => 'am_period_signatures',
				'UserID' => (string)USER_ID,
				'SQLQuery' => $update_query,
				'ServerIP' => get_user_ip(),
				'RequestURL' => Router::$page_url,
				'RequestData' => json_encode($audit_request, JSON_UNESCAPED_UNICODE),
				'RequestCompleted' => 'true',
				'RequestMsg' => 'TTD ' . $role_type . ' dibatalkan: ' . mb_substr($reason, 0, 200)
			);
			if (!$db->insert('audit_log', $audit_data)) {
				throw new RuntimeException($db->getLastError() ?: 'Audit pembatalan gagal disimpan.');
			}

			$db->commit();
			render_json(array('success' => true, 'message' => 'Tanda tangan berhasil dibatalkan. Dokumen dapat direvisi.'));
		} catch (Throwable $e) {
			$this->rollbackTransactionSafely($db, 'cancel period signature');
			error_log('Cancel period signature failed: ' . $e->getMessage());
			http_response_code(500);
			render_json(array('success' => false, 'message' => 'Gagal membatalkan tanda tangan digital.'));
		}
	}
}
