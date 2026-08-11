<?php
/** Autonomous Maintenance Jinsung 5 (Packaging). */
class Jinsung_5Controller extends SecureController
{
	private $parts = array(
		'belt_conveyor_dan_pusher_pack' => 'Belt Conveyor dan Pusher Pack',
		'pocket_pembawa_sachet' => 'Pocket Pembawa Sachet',
		'shaft_bushing_pusher_piston' => 'Shaft dan Bushing Pusher dan Piston',
		'rantai_penggerak_utama_cartoning' => 'Rantai Penggerak Utama Cartoning',
		'regulator_angin_conveyor_cartoning_check_weigher' => 'Regulator Angin Conveyor Unit 1, Conveyor Unit 2, dan Mesin Cartoning, Check Weigher',
		'regulator_angin_chamber_hot_melt' => 'Regulator Angin Chamber Hot Melt',
		'sensor_sachet_pack_nozzle' => 'Sensor : Sachet, Pack, Nozzle',
		'timing_belt_conveyor' => 'Timing Belt Conveyor',
	);

	function __construct() { parent::__construct(); $this->tablename = 'jinsung_5'; }
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
		$request = $this->request; $db = $this->GetModel(); $fields = array('jinsung_5.id_jinsung_5', 'mesin.nama_mesin AS nm_mesin', 'jinsung_5.created_at', 'jinsung_5.user_create', 'jinsung_5.user_approve', 'jinsung_5.approval', 'jinsung_5.tanggal_perubahan');
		foreach ($this->part_fields() as $part) { $fields[] = 'jinsung_5.' . $part; }
		if (!empty($request->search)) {
			$like = '%' . trim($request->search) . '%';
			$search_fields = array_merge(array('mesin.nama_mesin', 'jinsung_5.user_create', 'jinsung_5.user_approve', 'jinsung_5.approval', 'jinsung_5.kendala'), array_map(function($part) { return 'jinsung_5.' . $part; }, $this->part_fields()));
			$conditions = array(); $params = array();
			foreach ($search_fields as $f) { $conditions[] = "$f LIKE ?"; $params[] = $like; }
			$db->where('(' . implode(' OR ', $conditions) . ')', $params);
		}
		if (!empty($request->date_from)) { $db->where('jinsung_5.created_at', trim($request->date_from) . ' 00:00:00', '>='); }
		if (!empty($request->date_to)) { $db->where('jinsung_5.created_at', trim($request->date_to) . ' 23:59:59', '<='); }
		if (!empty($request->mesin)) { $db->where('jinsung_5.mesin', $request->mesin); }
		if ($fieldname) { $db->where($fieldname, $fieldvalue); }
		$db->join('mesin', 'jinsung_5.mesin = mesin.id', 'LEFT')->orderBy('jinsung_5.id_jinsung_5', ORDER_TYPE);
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); $tc = $db->withTotalCount(); $records = $db->get($this->tablename, $pagination, $fields);
		$this->view->page_title = 'Jinsung 5'; $this->set_report_props('Jinsung 5', 'landscape');
		return $this->render_view('jinsung_5/list2.php', $this->page_data($records, $tc));
	}

	function add($formdata = null)
	{
		if ($formdata) {
			$db = $this->GetModel(); $fields = array_merge(array('mesin'), $this->part_fields()); $this->fields = $fields;
			$postdata = $this->format_request_data($formdata); $this->rules_array = array(); $this->sanitize_array = array();
			foreach ($fields as $field) { $this->rules_array[$field] = 'required'; $this->sanitize_array[$field] = 'sanitize_string'; }
			$modeldata = $this->modeldata = $this->validate_form($postdata); $modeldata['created_at'] = datetime_now(); $modeldata['user_create'] = USER_NAME;
			if ($this->validated()) {
				$rec_id = $this->rec_id = $db->insert($this->tablename, $modeldata);
				if ($rec_id) {
					foreach ($this->parts as $field => $label) {
						if (!empty($_POST['kendala_' . $field])) {
							$db->insert('kendala_jinsung_5', array('id_am' => $rec_id, 'mesin' => $modeldata['mesin'], 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'created_at' => datetime_now()));
						}
					}
					$this->write_to_log('add', 'true'); $this->set_flash_msg('Berhasil tambah AM Jinsung 5', 'success'); return $this->redirect('jinsung_5');
				}
				$this->set_page_error();
			}
		}
		$this->view->page_title = 'Add New AM Jinsung 5'; return $this->render_view('jinsung_5/add.php', array('parts' => $this->parts));
	}

	function view($rec_id = null, $value = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id; $fields = array_merge(array('jinsung_5.id_jinsung_5', 'jinsung_5.mesin', 'mesin.nama_mesin AS nm_mesin', 'jinsung_5.created_at', 'jinsung_5.user_create', 'jinsung_5.user_approve', 'jinsung_5.approval', 'jinsung_5.tanggal_perubahan'), array_map(function($part) { return 'jinsung_5.' . $part; }, $this->part_fields()));
		if ($value) { $db->where($rec_id, urldecode($value)); } else { $db->where('jinsung_5.id_jinsung_5', urldecode($rec_id)); }
		$record = $db->join('mesin', 'jinsung_5.mesin = mesin.id', 'LEFT')->getOne($this->tablename, $fields);
		if ($record) {
			$details = $db->rawQuery('SELECT k.*, t1.kategori_tag AS teks_kategori, t2.nama AS teks_korelasi, t3.nama AS teks_klasifikasi, t4.kategori AS teks_ketidaksesuaian FROM kendala_jinsung_5 k LEFT JOIN tag t1 ON k.kategori_tag=t1.id LEFT JOIN korelasi t2 ON k.korelasi_tag=t2.id LEFT JOIN klasifikasi t3 ON k.klasifikasi_tag=t3.id LEFT JOIN kategori t4 ON k.kategori_ketidaksesuaian=t4.id WHERE k.id_am=?', array($record['id_jinsung_5']));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
			$this->write_to_log('view', 'true');
		} else { $this->set_page_error($db->getLastError() ?: 'No record found'); }
		if (!$record) { $record = array(); }
		$record['parts'] = $this->parts; $this->view->page_title = 'View AM Jinsung 5'; $this->set_report_props('View AM Jinsung 5');
		return $this->render_view('jinsung_5/view.php', $record);
	}

	function edit($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		if ($formdata) {
			$this->fields = array('approval'); $postdata = $this->format_request_data($formdata); $this->rules_array = array('approval' => 'required'); $this->sanitize_array = array('approval' => 'sanitize_string'); $modeldata = $this->validate_form($postdata);
			$modeldata['updated_at'] = datetime_now(); $modeldata['tanggal_perubahan'] = datetime_now(); $modeldata['user_approve'] = USER_NAME;
			if ($this->validated()) {
				$db->where('jinsung_5.id_jinsung_5', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) { $this->write_to_log('edit', 'true'); $this->set_flash_msg('Approval berhasil diperbarui', 'success'); return $this->redirect('jinsung_5'); }
				if ($db->getLastError()) {
					$this->set_page_error();
				} elseif (!$numRows) {
					$page_error = 'No record updated';
					$this->set_page_error($page_error); $this->set_flash_msg($page_error, 'warning'); return $this->redirect('jinsung_5');
				}
			}
		}
		$db->where('jinsung_5.id_jinsung_5', $rec_id); $data = $db->getOne($this->tablename, array('id_jinsung_5', 'approval'));
		$this->view->page_title = 'Approve AM Jinsung 5'; return $this->render_view('jinsung_5/edit.php', $data);
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
				$db->where('id_jinsung_5', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool) {
					$db->where('id_jinsung_5', $rec_id);
					$jinsung_data = $db->getOne($this->tablename, 'mesin');
					$mesin_id = $jinsung_data['mesin'];
					$db->where('id_am', $rec_id);
					$db->delete('kendala_jinsung_5');
					foreach ($this->parts as $field => $label) {
						if (!empty($_POST['kendala_' . $field])) {
							$db->insert('kendala_jinsung_5', array('id_am' => $rec_id, 'mesin' => $mesin_id, 'nama_bagian' => $field, 'kendala' => $_POST['kendala_' . $field], 'kategori_tag' => $_POST['kategori_tag_' . $field], 'korelasi_tag' => $_POST['korelasi_tag_' . $field], 'klasifikasi_tag' => $_POST['klasifikasi_tag_' . $field], 'kategori_ketidaksesuaian' => $_POST['kategori_ketidaksesuaian_' . $field], 'created_at' => datetime_now()));
						}
					}
					$this->write_to_log('edit_data', 'true');
					$this->set_flash_msg('Data berhasil diperbarui', 'success');
					return $this->redirect('jinsung_5/view/' . $rec_id);
				}
				if ($db->getLastError()) {
					$this->set_page_error();
				} elseif (!$numRows) {
					$page_error = 'Tidak ada perubahan data yang disimpan';
					$this->set_page_error($page_error); $this->set_flash_msg($page_error, 'warning'); return $this->redirect('jinsung_5/view/' . $rec_id);
				}
			}
		}
		$db->where('jinsung_5.id_jinsung_5', $rec_id);
		$record = $db->join('mesin', 'jinsung_5.mesin = mesin.id', 'LEFT')->getOne($this->tablename, array('jinsung_5.*', 'mesin.nama_mesin AS nm_mesin'));
		if ($record) {
			$details = $db->rawQuery('SELECT k.* FROM kendala_jinsung_5 k WHERE k.id_am=?', array($rec_id));
			$record['abnormalitas'] = array(); foreach ($details as $detail) { $record['abnormalitas'][$detail['nama_bagian']] = $detail; }
		} else {
			$this->set_page_error($db->getLastError() ?: 'No record found'); $record = array();
		}
		$record['parts'] = $this->parts; $this->view->page_title = 'Edit Data AM Jinsung 5';
		return $this->render_view('jinsung_5/edit_data.php', $record);
	}

	function editfield($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$this->fields = array('id_jinsung_5', 'updated_at', 'approval', 'user_approve', 'perubahan', 'user_perubah', 'tanggal_perubahan');
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
				$db->where('id_jinsung_5', $rec_id);
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

	function delete($rec_id = null) { Csrf::cross_check(); $db = $this->GetModel(); $this->rec_id = $rec_id; $ids = array_map('trim', explode(',', $rec_id)); $db->where('id_jinsung_5', $ids, 'in'); if ($db->delete($this->tablename)) { $this->write_to_log('delete', 'true'); $this->set_flash_msg('Record deleted successfully', 'success'); } else { $this->set_flash_msg($db->getLastError(), 'danger'); } return $this->redirect('jinsung_5'); }
}
