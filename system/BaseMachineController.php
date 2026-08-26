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
	}

	/**
	 * Kalau superadmin udah nambahin part lewat menu Master Data Part
	 * (Master_partController) buat mesin ini, $parts di-override dari database
	 * (urutan sesuai kolom urutan) -- kalau belum ada row master_part sama
	 * sekali buat mesin ini, tetap pakai $parts hardcoded di subclass (mesin
	 * yang belum dimigrasikan ke master data).
	 */
	private function loadDynamicParts()
	{
		$db = $this->GetModel();
		$db->where('machine_key', $this->machineKey)->orderBy('urutan', 'ASC');
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

	private function page_data($records, $total)
	{
		$data = new stdClass; $data->records = $records; $data->record_count = count($records);
		$data->total_records = intval($total->totalCount); $data->total_page = ceil($data->total_records / MAX_RECORD_COUNT);
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
		$fields = array("$sql.$idcol", 'mesin.nama_mesin AS nm_mesin', "$sql.created_at", "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.tanggal_perubahan", "$sql.user_perubah", "$sql.updated_at");
		foreach ($this->part_fields() as $part) { $fields[] = "$sql.$part"; }
		if (!empty($request->search)) {
			$like = '%' . trim($request->search) . '%';
			$search_fields = array_merge(array('mesin.nama_mesin', "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.kendala"), array_map(function ($part) use ($sql) { return "$sql.$part"; }, $this->part_fields()));
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
		return $this->render_view("$table/list2.php", $this->page_data($records, $tc));
	}

	function add($formdata = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		if ($formdata) {
			$db = $this->GetModel();
			$fields = array_merge(array('mesin'), $this->part_fields(), $this->extraFields);
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
			$modeldata['created_at'] = datetime_now(); $modeldata['user_create'] = USER_NAME;
			// Auto-approve kalau gak ada part yang NOK -- OK semua ATAU campuran
			// OK/"Tidak Dilakukan" (N/A) tetap auto-approve, cuma NOK yang bikin
			// masuk antrian review manual.
			$all_ok = true;
			foreach ($this->part_fields() as $pf) { if ((isset($modeldata[$pf]) ? $modeldata[$pf] : null) === 'NOK') { $all_ok = false; break; } }
			if ($all_ok) { $modeldata['approval'] = 'Approved'; $modeldata['user_approve'] = 'System'; $modeldata['tanggal_perubahan'] = datetime_now(); }
			if ($this->validated()) {
				$db->startTransaction();
				$rec_id = $this->rec_id = $db->insert($sql, $modeldata);
				if ($rec_id) {
					foreach ($this->parts as $field => $label) {
						if (!empty($_POST['kendala_' . $field])) {
							$db->insert($this->kendalaTable(), array('id_am' => $rec_id, 'mesin' => $modeldata['mesin'], 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'created_at' => datetime_now()));
						}
					}
					$db->commit();
					$this->write_to_log('add', 'true'); $this->set_flash_msg("Berhasil tambah AM {$this->displayName}", 'success'); return $this->redirect($table);
				}
				$db->rollback();
				$this->set_page_error();
			}
		}
		$this->view->page_title = "Add New AM {$this->displayName}"; return $this->render_view("$table/add.php", array('parts' => $this->parts));
	}

	function view($rec_id = null, $value = null)
	{
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$fields = array_merge(array("$sql.$idcol", "$sql.mesin", 'mesin.nama_mesin AS nm_mesin', "$sql.created_at", "$sql.user_create", "$sql.user_approve", "$sql.approval", "$sql.tanggal_perubahan", "$sql.user_perubah", "$sql.updated_at", "$sql.perubahan"), array_map(function ($p) use ($sql) { return "$sql.$p"; }, array_merge($this->part_fields(), $this->extraFields)));
		if ($value) { $db->where($rec_id, urldecode($value)); } else { $db->where("$sql.$idcol", urldecode($rec_id)); }
		$record = $db->join('mesin', "$sql.mesin = mesin.id", 'LEFT')->getOne($sql, $fields);
		if ($record) {
			$details = $db->rawQuery("SELECT k.*, t1.kategori_tag AS teks_kategori, t2.nama AS teks_korelasi, t3.nama AS teks_klasifikasi, t4.kategori AS teks_ketidaksesuaian FROM {$this->kendalaTable()} k LEFT JOIN tag t1 ON k.kategori_tag=t1.id LEFT JOIN korelasi t2 ON k.korelasi_tag=t2.id LEFT JOIN klasifikasi t3 ON k.klasifikasi_tag=t3.id LEFT JOIN kategori t4 ON k.kategori_ketidaksesuaian=t4.id WHERE k.id_am=?", array($record[$idcol]));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
		} else { $this->set_page_error($db->getLastError() ?: 'No record found'); }
		if (!$record) { $record = array(); }
		$record['parts'] = $this->parts; $this->view->page_title = "View AM {$this->displayName}"; $this->set_report_props("View AM {$this->displayName}");
		return $this->render_view("$table/view.php", $record);
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
			if ($this->validated()) {
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
						if (!empty($_POST['kendala_' . $field])) {
							$db->insert($this->kendalaTable(), array('id_am' => $rec_id, 'mesin' => $mesin_id, 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'created_at' => datetime_now()));
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
		$record['parts'] = $this->parts; $this->view->page_title = "Edit Data AM {$this->displayName}";
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
		$table = $this->machineKey; $sql = $this->sqlTable(); $idcol = $this->idColumn();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$ids = array_map('trim', explode(',', $rec_id));
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
