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

	/**
	 * Simpan snapshot metadata part ke form_part_snapshot saat form di-submit.
	 * Dipanggil sekali di dalam transaction add(), setelah INSERT record berhasil.
	 * Kalau tabel belum ada (environment lama sebelum migration), error diabaikan
	 * dengan try/catch -- alur submit tetap berhasil, fallback ke resolver lama.
	 * @param int   $form_id   ID record baru yang baru saja di-INSERT
	 * @param array $parts_meta Hasil partsForRecord() pada saat submit (field_name => label)
	 */
	protected function savePartSnapshot($form_id, $parts_meta)
	{
		if (empty($parts_meta) || !$form_id) { return; }
		$db = $this->GetModel();
		// Ambil metadata lengkap dari master_part sesuai part yang aktif saat submit.
		$field_names = array_keys($parts_meta);
		$db->where('machine_key', $this->machineKey)->where('field_name', $field_names, 'in');
		$master_rows = $db->get('master_part');
		if (empty($master_rows)) { return; }
		try {
			foreach ($master_rows as $row) {
				$db->rawQuery(
					'INSERT INTO "form_part_snapshot"
						(machine_key, form_id, field_name, label, section, metode, alat, standard, durasi, pelaksanaan, highlight, image_path, urutan, snapshot_at)
					VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)
					ON CONFLICT (machine_key, form_id, field_name) DO NOTHING',
					array(
						$this->machineKey,
						$form_id,
						$row['field_name'],
						$row['label'],
						$row['section']      ?? null,
						$row['metode']       ?? null,
						$row['alat']         ?? null,
						$row['standard']     ?? null,
						$row['durasi']       ?? null,
						$row['pelaksanaan']  ?? null,
						$row['highlight']    ?? null,
						$row['image_path']   ?? null,
						$row['urutan']       ?? null,
					)
				);
			}
		} catch (\Exception $e) {
			// Tabel form_part_snapshot belum ada di environment lama -- jangan crash
			// submit form. Migration harus dijalankan terpisah di environment target.
			error_log('savePartSnapshot skipped (migration belum dijalankan?): ' . $e->getMessage());
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
	protected function partsForAdd($formdata = null)
	{
		if (!in_array('shift', $this->extraFields, true)) { return $this->parts; }
		$shift = is_array($formdata) ? (string) ($formdata['shift'] ?? '') : (string) ($this->request->shift ?? '');
		$this->view->uses_shift = true; $this->view->selected_shift = $shift;
		if (!in_array($shift, array('1', '2', '3'), true)) { return array(); }
		$db = $this->GetModel(); $parts = array();
		$rows = $db->where('machine_key', $this->machineKey)->where('taken_out_at', null, 'IS')->orderBy('urutan', 'ASC')->get('master_part', null, array('field_name', 'label', 'shift_schedule'));
		foreach ($rows as $row) { $shifts = array_filter(array_map('trim', explode(',', (string) $row['shift_schedule']))); if (in_array($shift, $shifts, true)) { $parts[$row['field_name']] = $row['label']; } }
		return $parts;
	}

	/** Return pesan error bila konteks Add tidak sah, atau null bila valid. */
	protected function addContextError($formdata)
	{
		if (!in_array('shift', $this->extraFields, true)) { return null; }
		return in_array((string) ($formdata['shift'] ?? ''), array('1', '2', '3'), true) ? null : 'Shift wajib dipilih (1, 2, atau 3).';
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

	function add($formdata = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		if ($formdata) {
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
			if ($this->validated() && $valid_no_wr) {
				$db->where('mesin', $modeldata['mesin'])->where('operational_date', $modeldata['operational_date']);
				if (in_array('shift', $this->extraFields, true)) { $db->where('shift', $modeldata['shift']); }
				if ($db->has($sql)) {
					$this->view->page_error[] = in_array('shift', $this->extraFields, true) ? 'Shift ini sudah diisi untuk tanggal operasional tersebut.' : 'Form mesin ini sudah diisi untuk tanggal operasional tersebut.';
				}
			}
			if ($this->validated() && $valid_no_wr) {
				$db->startTransaction();
				$rec_id = $this->rec_id = $db->insert($sql, $modeldata);
				if ($rec_id) {
					foreach ($parts_for_add as $field => $label) {
						$kondisi_part = $formdata[$field] ?? ($modeldata[$field] ?? null);
						if ($kondisi_part === 'NOK' && !empty($_POST['kendala_' . $field])) {
							$db->insert($this->kendalaTable(), array('id_am' => $rec_id, 'mesin' => $modeldata['mesin'], 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'no_wr' => $this->noWrForField($formdata, $field), 'created_at' => datetime_now()));
						}
					}
					// PR-1: simpan snapshot metadata part saat submit -- mencegah perubahan
					// label/section/metode/standard master_part di kemudian hari mengubah
					// tampilan laporan lama. Dipanggil di dalam transaction yang sama.
					$this->savePartSnapshot($rec_id, $parts_for_add);
					$db->commit();
					$this->write_to_log('add', 'true'); $this->set_flash_msg("Berhasil tambah AM {$this->displayName}", 'success');
					return $this->redirect($table . '/view/' . $rec_id);
				}
				$db->rollback();
				$this->set_page_error();
			}
		}
		$this->view->page_title = "Add New AM {$this->displayName}"; return $this->render_view("$table/add.php", array('parts' => $this->partsForAdd()));
	}

	function view($rec_id = null, $value = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$fields = array_merge(array("$sql.$idcol", "$sql.mesin", 'mesin.nama_mesin AS nm_mesin', "$sql.created_at", "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.tanggal_perubahan", "$sql.user_perubah", "$sql.updated_at", "$sql.perubahan"), array_map(function ($p) use ($sql) { return "$sql.$p"; }, array_merge($this->historicalPartFields(), $this->extraFields)));
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
		$rows = $db->where('mesin', $mesin)->where('operational_date', $start, '>=')->where('operational_date', $end, '<=')->orderBy('operational_date', 'ASC')->orderBy('created_at', 'ASC')->get($sql);
		$machine = $db->where('id', $mesin)->getOne('mesin', array('nama_mesin'));
		$checks = array(); $all_approved = !empty($rows);
		foreach ($rows as $row) {
			if (($row['approval'] ?? null) !== 'Approved') { $all_approved = false; }
			$day = intval((new DateTime($row['operational_date']))->format('j'));
			foreach ($this->partsForRecord($row['operational_date'], $row['created_at'] ?? null, $row[$idcol] ?? null) as $field => $label) {
				if (!empty($row[$field])) { $checks[$field][$day][] = array('shift' => $row['shift'] ?? null, 'value' => $row[$field]); }
			}
		}
		$part_details = $this->partDetailsForRows($rows, $start);
		$data = array('selection_only' => false, 'machine_key' => $this->machineKey, 'display_name' => $this->displayName, 'machine_name' => $machine['nama_mesin'] ?? '-', 'year' => $year, 'month' => $month, 'period' => $period, 'start_day' => $start_day, 'end_day' => $end_day, 'parts' => $this->partsForRows($rows, $start), 'part_details' => $part_details, 'checks' => $checks);
		$data['all_approved'] = $all_approved;
		$this->view->page_title = 'Check Sheet ' . $this->displayName;
		$this->set_report_props('Check-Sheet-' . $this->machineKey . '-' . $year . '-' . $month . '-P' . $period, 'landscape');
		return $this->render_view('machine_period_report.php', $data);
	}

	/** Satu layar report harian yang menggabungkan semua submit shift pada operational_date yang sama. */
	function daily_report()
	{
		$mesin = intval($this->request->mesin ?? 0); $date = trim((string)($this->request->date ?? ''));
		if (!$mesin || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { return $this->redirect($this->machineKey); }
		// Hitung sebelum query laporan dibuat: PDODb memakai query builder yang
		// sama, sehingga filter mesin/tanggal tidak boleh terbawa ke master_part.
		$has_shift_history = $this->machineHasShiftHistory();
		$db = $this->GetModel(); $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db->where('mesin', $mesin)->where('operational_date', $date);
		if ($has_shift_history) {
			$db->orderBy('shift', 'ASC');
		}
		$rows = $db->orderBy('created_at', 'ASC')->get($sql);
		$machine = $db->where('id', $mesin)->getOne('mesin', array('nama_mesin'));
		$this->view->page_title = 'Report Harian ' . $this->displayName;
		return $this->render_view('machine_daily_report.php', array('display_name' => $this->displayName, 'machine_key' => $this->machineKey, 'machine_name' => $machine['nama_mesin'] ?? '-', 'operational_date' => $date, 'rows' => $rows, 'parts' => $this->partsForRows($rows, $date), 'id_column' => $idcol));
	}

	function edit($rec_id = null, $formdata = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		if ($formdata) {
			$this->fields = array('approval'); $postdata = $this->format_request_data($formdata);
			$this->rules_array = array('approval' => 'required'); $this->sanitize_array = array('approval' => 'sanitize_string');
			$modeldata = $this->validate_form($postdata);
			$modeldata['updated_at'] = datetime_now(); $modeldata['tanggal_perubahan'] = datetime_now(); $modeldata['user_approve'] = USER_NAME;
			if ($this->validated()) {
				$db->where("$sql.$idcol", $rec_id);
				$bool = $db->update($sql, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) { $this->write_to_log('edit', 'true'); $this->set_flash_msg('Approval berhasil diperbarui', 'success'); return $this->redirect($table); }
				if ($db->getLastError()) {
					$this->set_page_error();
				} elseif (!$numRows) {
					$page_error = 'No record updated';
					$this->set_page_error($page_error); $this->set_flash_msg($page_error, 'warning'); return $this->redirect($table);
				}
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

		//URS 3.1: Manager/Supervisor/Staff-Operator cuma boleh edit_data submission sendiri; Administrator bebas.
		$db->where($idcol, $rec_id);
		$owner_row = $db->getOne($sql, 'user_create');
		$is_owner = (!empty($owner_row) && $owner_row['user_create'] === USER_NAME);
		if (!$is_owner && intval(get_active_user('user_role_id')) !== 1) {
			http_response_code(403);
			return $this->render_view('errors/forbidden.php', null, 'info_layout.php');
		}

		if ($formdata) {
			$postdata = $this->format_request_data($formdata);
			$this->fields = array_merge(array('perubahan'), $this->part_fields(), $this->extraFields);
			$this->rules_array = array('perubahan' => 'required');
			$this->sanitize_array = array('perubahan' => 'sanitize_string');
			foreach (array_merge($this->part_fields(), $this->extraFields) as $field) { $this->sanitize_array[$field] = 'sanitize_string'; }
			// Edit Data tidak selalu menampilkan extra field (contohnya shift). Pertahankan nilai
			// yang tersimpan agar validasi required tidak gagal dan kolom lama tidak tertimpa.
			$existing_record = $db->where($idcol, $rec_id)->getOne($sql);
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
			if ($this->validated() && $valid_no_wr) {
				$db->startTransaction();
				$db->where($idcol, $rec_id);
				$bool = $db->update($sql, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool) {
					$db->where($idcol, $rec_id);
					$row = $db->getOne($sql, 'mesin');
					$mesin_id = $row['mesin'];
					$db->where('id_am', $rec_id);
					$db->delete($this->kendalaTable());
					foreach ($this->parts as $field => $label) {
						$kondisi_part = $formdata[$field] ?? ($modeldata[$field] ?? null);
						if ($kondisi_part === 'NOK' && !empty($_POST['kendala_' . $field])) {
							$db->insert($this->kendalaTable(), array('id_am' => $rec_id, 'mesin' => $mesin_id, 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'no_wr' => $this->noWrForField($formdata, $field), 'created_at' => datetime_now()));
						}
					}
					$db->commit();
					$this->write_to_log('edit_data', 'true');
					$this->set_flash_msg('Data berhasil diperbarui', 'success');
					return $this->redirect("$table/view/$rec_id");
				}
				$db->rollback();
				if ($db->getLastError()) {
					$this->set_page_error();
				} elseif (!$numRows) {
					$page_error = 'Tidak ada perubahan data yang disimpan';
					$this->set_page_error($page_error); $this->set_flash_msg($page_error, 'warning'); return $this->redirect("$table/view/$rec_id");
				}
			}
		}
		$db->where("$sql.$idcol", $rec_id);
		$record = $db->join('mesin', "$sql.mesin = mesin.id", 'LEFT')->getOne($sql, array("$sql.*", 'mesin.nama_mesin AS nm_mesin'));
		if ($record) {
			$details = $db->rawQuery("SELECT k.* FROM {$this->kendalaTable()} k WHERE k.id_am=?", array($rec_id));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
		} else {
			$this->set_page_error($db->getLastError() ?: 'No record found'); $record = array();
		}
		$record['parts'] = !empty($record) ? $this->partsForRecord($record['operational_date'] ?? $this->operationalDate($record['created_at'] ?? null), $record['created_at'] ?? null, $record[$idcol] ?? null) : $this->parts;
		$record['part_details'] = !empty($record) ? $this->partDetailsForRecord($record['operational_date'] ?? $this->operationalDate($record['created_at'] ?? null), $record['created_at'] ?? null, $record[$idcol] ?? null) : array();
		$this->view->page_title = "Edit Data AM {$this->displayName}";
		return $this->render_view("$table/edit_data.php", $record);
	}

	function editfield($rec_id = null, $formdata = null)
	{
		$sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$this->fields = array($idcol, 'updated_at', 'approval', 'user_approve', 'perubahan', 'user_perubah', 'tanggal_perubahan');
		$page_error = null;
		if ($formdata) {
			$postdata = array();
			$fieldname = $formdata['name']; $fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array('approval' => 'required');
			$this->sanitize_array = array('approval' => 'sanitize_string', 'perubahan' => 'sanitize_string', 'user_perubah' => 'sanitize_string', 'tanggal_perubahan' => 'sanitize_string');
			$this->filter_rules = true;
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if ($this->validated()) {
				$db->where($idcol, $rec_id);
				$bool = $db->update($sql, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) {
					$this->write_to_log('edit', 'true');
					return render_json(array('num_rows' => $numRows, 'rec_id' => $rec_id));
				} else {
					if ($db->getLastError()) { $page_error = $db->getLastError(); }
					elseif (!$numRows) { $page_error = 'No record updated'; }
					render_error($page_error);
				}
			} else {
				render_error($this->view->page_error);
			}
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
}
