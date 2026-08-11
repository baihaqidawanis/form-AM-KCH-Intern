<?php
/** Autonomous Maintenance Illapak 1-2 (Filling). */
class Illapak_1_2Controller extends SecureController
{
	private $parts = array(
		'sealing_horizontal' => 'Sealing Horizontal',
		'sealing_vertikal' => 'Sealing Vertikal',
		'body_mesin' => 'Body Mesin dan Conveyor',
		'roller_foil_film' => 'Roller Foil (Setelah Inkjet)',
		'position_indicator_sealing_vertical' => 'Position Indicator Sealing Vertical',
		'vacum_sliter' => 'Vacum Sliter',
		'piston_pengisian' => 'Piston Pengisian',
		'pneumatic_valves_pengisian' => 'Pneumatic Valves Pengisian',
		'baut_sealing_vertikal' => 'Baut Sealing Vertikal',
		'rubber_penarik_foil' => 'Rubber Penarik Foil',
		'sensor_eyemark_dan_sambungan_foil' => 'Sensor Eyemark dan Sambungan Foil',
		'guarding_mesin' => 'Guarding Mesin',
		'pressure_blow_sealing_vertical' => 'Pressure Blow Sealing Vertical',
		'inkjet' => 'Inkjet',
		'pengunci_nozzle_pengisian' => 'Pengunci Nozzle Pengisian',
	);

	function __construct() { parent::__construct(); $this->tablename = 'illapak_1_2'; }
	private function part_fields() { return array_keys($this->parts); }
	private function page_data($records, $total) {
		$data = new stdClass; $data->records = $records; $data->record_count = count($records);
		$data->total_records = intval($total->totalCount); $data->total_page = ceil($data->total_records / MAX_RECORD_COUNT);
		return $data;
	}
	private function set_report_props($title, $orientation = 'portrait') {
		$this->view->report_filename = date('Y-m-d') . '-' . $title;
		$this->view->report_title = $title;
		$this->view->report_layout = 'report_layout.php';
		$this->view->report_paper_size = 'A4';
		$this->view->report_orientation = $orientation;
	}

	function index($fieldname = null, $fieldvalue = null) { return $this->list2($fieldname, $fieldvalue); }
	function list2($fieldname = null, $fieldvalue = null)
	{
		$request = $this->request; $db = $this->GetModel(); $fields = array('illapak_1_2.id_illapak_1_2', 'mesin.nama_mesin AS nm_mesin', 'illapak_1_2.created_at', 'illapak_1_2.user_create', 'illapak_1_2.user_approve', 'illapak_1_2.approval', 'illapak_1_2.tanggal_perubahan');
		foreach ($this->part_fields() as $part) { $fields[] = 'illapak_1_2.' . $part; }
		if (!empty($request->search)) {
			$like = '%' . trim($request->search) . '%';
			$search_fields = array_merge(array('mesin.nama_mesin', 'illapak_1_2.user_create', 'illapak_1_2.user_approve', 'illapak_1_2.approval', 'illapak_1_2.kendala'), array_map(function($part) { return 'illapak_1_2.' . $part; }, $this->part_fields()));
			$conditions = array(); $params = array();
			foreach ($search_fields as $f) { $conditions[] = "$f LIKE ?"; $params[] = $like; }
			$db->where('(' . implode(' OR ', $conditions) . ')', $params);
		}
		if (!empty($request->date_from)) { $db->where('illapak_1_2.created_at', trim($request->date_from) . ' 00:00:00', '>='); }
		if (!empty($request->date_to)) { $db->where('illapak_1_2.created_at', trim($request->date_to) . ' 23:59:59', '<='); }
		if (!empty($request->mesin)) { $db->where('illapak_1_2.mesin', $request->mesin); }
		if ($fieldname) { $db->where($fieldname, $fieldvalue); }
		$db->join('mesin', 'illapak_1_2.mesin = mesin.id', 'LEFT')->orderBy('illapak_1_2.id_illapak_1_2', ORDER_TYPE);
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); $tc = $db->withTotalCount(); $records = $db->get($this->tablename, $pagination, $fields);
		$this->write_to_log('list', 'true');
		$this->view->page_title = 'Illapak 1 - 2'; $this->set_report_props('Illapak 1 - 2', 'landscape');
		return $this->render_view('illapak_1_2/list2.php', $this->page_data($records, $tc));
	}

	function add($formdata = null)
	{
		if ($formdata) {
			$db = $this->GetModel(); $fields = array_merge(array('mesin'), $this->part_fields()); $this->fields = $fields;
			$postdata = $this->format_request_data($formdata);
			$rules = array('mesin' => 'required'); $sanitize = array('mesin' => 'sanitize_string');
			foreach ($this->part_fields() as $field) { $rules[$field] = 'required'; $sanitize[$field] = 'sanitize_string'; }
			$this->rules_array = $rules; $this->sanitize_array = $sanitize;
			$modeldata = $this->validate_form($postdata);
			$modeldata['created_at'] = datetime_now(); $modeldata['user_create'] = USER_NAME;
			if ($this->validated()) {
				$rec_id = $this->rec_id = $db->insert($this->tablename, $modeldata);
				if ($rec_id) {
					foreach ($this->parts as $field => $label) {
						if (!empty($_POST['kendala_' . $field])) {
							$db->insert('kendala_illapak_1_2', array('id_am' => $rec_id, 'mesin' => $modeldata['mesin'], 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'created_at' => datetime_now()));
						}
					}
					$this->write_to_log('add', 'true'); $this->set_flash_msg('Berhasil tambah AM Illapak 1-2', 'success'); return $this->redirect('illapak_1_2');
				}
				$this->set_page_error();
			}
		}
		$this->view->page_title = 'Add New AM Illapak 1-2'; return $this->render_view('illapak_1_2/add.php', array('parts' => $this->parts));
	}

	function view($rec_id = null, $value = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id; $fields = array_merge(array('illapak_1_2.id_illapak_1_2', 'illapak_1_2.mesin', 'mesin.nama_mesin AS nm_mesin', 'illapak_1_2.created_at', 'illapak_1_2.user_create', 'illapak_1_2.user_approve', 'illapak_1_2.approval', 'illapak_1_2.tanggal_perubahan'), array_map(function($part) { return 'illapak_1_2.' . $part; }, $this->part_fields()));
		if ($value) { $db->where($rec_id, urldecode($value)); } else { $db->where('illapak_1_2.id_illapak_1_2', urldecode($rec_id)); }
		$record = $db->join('mesin', 'illapak_1_2.mesin = mesin.id', 'LEFT')->getOne($this->tablename, $fields);
		if ($record) {
			$details = $db->rawQuery('SELECT k.*, t1.kategori_tag AS teks_kategori, t2.nama AS teks_korelasi, t3.nama AS teks_klasifikasi, t4.kategori AS teks_ketidaksesuaian FROM kendala_illapak_1_2 k LEFT JOIN tag t1 ON k.kategori_tag=t1.id LEFT JOIN korelasi t2 ON k.korelasi_tag=t2.id LEFT JOIN klasifikasi t3 ON k.klasifikasi_tag=t3.id LEFT JOIN kategori t4 ON k.kategori_ketidaksesuaian=t4.id WHERE k.id_am=?', array($record['id_illapak_1_2']));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
			$this->write_to_log('view', 'true');
		} else { $this->set_page_error($db->getLastError() ?: 'No record found'); }
		if (!$record) { $record = array(); }
		$record['parts'] = $this->parts; $this->view->page_title = 'View AM Illapak 1-2'; $this->set_report_props('View AM Illapak 1-2');
		return $this->render_view('illapak_1_2/view.php', $record);
	}

	function edit($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		if ($formdata) {
			$this->fields = array('approval'); $postdata = $this->format_request_data($formdata); $this->rules_array = array('approval' => 'required'); $this->sanitize_array = array('approval' => 'sanitize_string'); $modeldata = $this->validate_form($postdata);
			$modeldata['user_approve'] = USER_NAME; $modeldata['tanggal_perubahan'] = datetime_now();
			if ($this->validated()) {
				$db->where('id_illapak_1_2', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) { $this->write_to_log('edit', 'true'); $this->set_flash_msg('Approval berhasil diperbarui', 'success'); return $this->redirect('illapak_1_2'); }
				if ($db->getLastError()) {
					$this->set_page_error();
				} elseif (!$numRows) {
					$page_error = 'No record updated';
					$this->set_page_error($page_error); $this->set_flash_msg($page_error, 'warning'); return $this->redirect('illapak_1_2');
				}
			}
		}
		$db->where('id_illapak_1_2', $rec_id); $data = $db->getOne($this->tablename, array('id_illapak_1_2', 'approval'));
		if (!$data) { $this->set_page_error('No record found'); }
		$this->view->page_title = 'Edit Approval AM Illapak 1-2'; return $this->render_view('illapak_1_2/edit.php', $data);
	}

	/** Operator pembuat record edit ulang isian datanya sendiri (terpisah dari flow approval di edit()). */
	function edit_data($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		if ($formdata) {
			$postdata = $this->format_request_data($formdata);
			// Only main-table fields may be updated; kendala_* fields are saved separately.
			$this->fields = array_merge(array('perubahan'), $this->part_fields());
			$this->rules_array = array('perubahan' => 'required');
			$this->sanitize_array = array('perubahan' => 'sanitize_string');
			foreach ($this->part_fields() as $field) { $this->sanitize_array[$field] = 'sanitize_string'; }
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$modeldata['updated_at'] = datetime_now(); $modeldata['user_perubah'] = USER_NAME;
			if ($this->validated()) {
				$db->where('id_illapak_1_2', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool) {
					$db->where('id_illapak_1_2', $rec_id);
					$rec_data = $db->getOne($this->tablename, 'mesin');
					$mesin_id = $rec_data['mesin'];
					$db->where('id_am', $rec_id);
					$db->delete('kendala_illapak_1_2');
					foreach ($this->parts as $field => $label) {
						if (!empty($_POST['kendala_' . $field])) {
							$db->insert('kendala_illapak_1_2', array('id_am' => $rec_id, 'mesin' => $mesin_id, 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'created_at' => datetime_now()));
						}
					}
					$this->write_to_log('edit_data', 'true');
					$this->set_flash_msg('Data berhasil diperbarui', 'success');
					return $this->redirect('illapak_1_2/view/' . $rec_id);
				}
				if ($db->getLastError()) {
					$this->set_page_error();
				} elseif (!$numRows) {
					$page_error = 'Tidak ada perubahan data yang disimpan';
					$this->set_page_error($page_error); $this->set_flash_msg($page_error, 'warning'); return $this->redirect('illapak_1_2/view/' . $rec_id);
				}
			}
		}
		$db->where('illapak_1_2.id_illapak_1_2', $rec_id);
		$record = $db->join('mesin', 'illapak_1_2.mesin = mesin.id', 'LEFT')->getOne($this->tablename, array('illapak_1_2.*', 'mesin.nama_mesin AS nm_mesin'));
		if ($record) {
			$details = $db->rawQuery('SELECT k.* FROM kendala_illapak_1_2 k WHERE k.id_am=?', array($rec_id));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
		} else {
			$this->set_page_error($db->getLastError() ?: 'No record found'); $record = array();
		}
		$record['parts'] = $this->parts; $this->view->page_title = 'Edit Data AM Illapak 1-2';
		return $this->render_view('illapak_1_2/edit_data.php', $record);
	}

	function editfield($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$this->fields = array('id_illapak_1_2', 'updated_at', 'approval', 'user_approve', 'perubahan', 'user_perubah', 'tanggal_perubahan');
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
				$db->where('id_illapak_1_2', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
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

	function delete($rec_id = null) { Csrf::cross_check(); $db = $this->GetModel(); $this->rec_id = $rec_id; $ids = array_map('trim', explode(',', $rec_id)); $db->where('id_illapak_1_2', $ids, 'in'); if ($db->delete($this->tablename)) { $this->write_to_log('delete', 'true'); $this->set_flash_msg('Record deleted successfully', 'success'); } else { $this->set_flash_msg($db->getLastError(), 'danger'); } return $this->redirect('illapak_1_2'); }
}
