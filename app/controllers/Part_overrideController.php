<?php
/** Override part untuk template yang dipakai lebih dari satu unit fisik. Khusus Administrator. */
class Part_overrideController extends SecureController
{
	function __construct()
	{
		parent::__construct();
		$this->tablename = 'master_part_machine_override';
	}

	private function requireAdmin()
	{
		if (intval(get_active_user('user_role_id')) !== 1) {
			http_response_code(403);
			$this->set_page_error('Akses hanya untuk Administrator.');
			$this->redirect('home');
			return false;
		}
		return true;
	}

	private function redirectToSelection($machine_key, $mesin_id)
	{
		$query = http_build_query(array('machine_key' => $machine_key, 'mesin_id' => intval($mesin_id)));
		return $this->redirect('part_override?' . $query);
	}

	function index()
	{
		if (!$this->requireAdmin()) { return; }
		$request = $this->request;
		$machine_key = trim((string)($request->machine_key ?? ''));
		$mesin_id = intval($request->mesin_id ?? 0);
		$search = trim((string)($request->search ?? ''));
		$modules = array();
		$units = array(); $units_by_module = array(); $parts = array(); $override_records = array(); $selected_unit = null;
		try {
			$db = $this->GetModel();
			$mapped_units = $db->rawQuery('SELECT u.machine_key, m.id, m.nama_mesin, m.nomor_seri FROM machine_module_units u JOIN mesin m ON m.id = u.mesin_id ORDER BY u.machine_key ASC, m.nama_mesin ASC') ?: array();
			foreach ($mapped_units as $unit) {
				$key = (string)$unit['machine_key'];
				if (!isset($units_by_module[$key])) { $units_by_module[$key] = array(); }
				$units_by_module[$key][] = $unit;
			}
			foreach ($units_by_module as &$module_units) {
				usort($module_units, function ($a, $b) { return strnatcasecmp((string)$a['nama_mesin'], (string)$b['nama_mesin']); });
			}
			unset($module_units);
			foreach (Master_partController::$machine_keys as $key => $label) {
				if (count($units_by_module[$key] ?? array()) <= 1) { continue; }
				$modules[$key] = array('label' => $label);
			}
			uasort($modules, function ($a, $b) { return strnatcasecmp((string)$a['label'], (string)$b['label']); });
			if (!isset($modules[$machine_key])) { $machine_key = ''; $mesin_id = 0; }
			$units = $machine_key !== '' ? ($units_by_module[$machine_key] ?? array()) : array();
			foreach ($units as $unit) { if (intval($unit['id']) === $mesin_id) { $selected_unit = $unit; break; } }
			$override_where = array('mp.taken_out_at IS NULL', '(SELECT COUNT(*) FROM machine_module_units mapped WHERE mapped.machine_key = mp.machine_key) > 1');
			$override_params = array();
			if ($machine_key !== '') { $override_where[] = 'mp.machine_key = ?'; $override_params[] = $machine_key; }
			if ($selected_unit) { $override_where[] = 'o.mesin_id = ?'; $override_params[] = intval($selected_unit['id']); }
			if ($search !== '') {
				$override_where[] = '(mp.label ILIKE ? OR mp.field_name ILIKE ? OR m.nama_mesin ILIKE ?)';
				$like = '%' . $search . '%'; $override_params[] = $like; $override_params[] = $like; $override_params[] = $like;
			}
			$override_records = $db->rawQuery(
				'SELECT o.id AS override_id, o.is_applicable, o.durasi AS override_durasi, o.updated_at, mp.machine_key, mp.field_name, mp.label, mp.section, m.id AS mesin_id, m.nama_mesin, m.nomor_seri '
				. 'FROM master_part_machine_override o '
				. 'JOIN master_part mp ON mp.id = o.master_part_id '
				. 'JOIN machine_module_units u ON u.machine_key = mp.machine_key AND u.mesin_id = o.mesin_id '
				. 'JOIN mesin m ON m.id = o.mesin_id '
				. 'WHERE ' . implode(' AND ', $override_where)
				. ' ORDER BY m.nama_mesin ASC, mp.urutan ASC, mp.label ASC',
				$override_params
			) ?: array();
			if ($selected_unit) {
				$params = array($mesin_id, $machine_key);
				$filter = '';
				if ($search !== '') {
					$filter = ' AND (mp.label ILIKE ? OR mp.field_name ILIKE ?)';
					$params[] = '%' . $search . '%'; $params[] = '%' . $search . '%';
				}
				$parts = $db->rawQuery('SELECT mp.id, mp.field_name, mp.label, mp.section, mp.durasi AS default_durasi, o.id AS override_id, o.is_applicable, o.durasi AS override_durasi FROM master_part mp LEFT JOIN master_part_machine_override o ON o.master_part_id = mp.id AND o.mesin_id = ? WHERE mp.machine_key = ? AND mp.taken_out_at IS NULL' . $filter . ' ORDER BY mp.urutan ASC, mp.id ASC', $params) ?: array();
			}
		} catch (Throwable $e) {
			$this->set_page_error('Pemetaan unit belum tersedia. Jalankan database/postgres/update.sql.');
		}

		$this->view->page_title = 'Override Part per Unit';
		return $this->render_view('part_override/index.php', array(
			'modules' => $modules,
			'machine_key' => $machine_key, 'mesin_id' => $mesin_id, 'units' => $units,
			'units_by_module' => $units_by_module, 'selected_unit' => $selected_unit, 'parts' => $parts, 'search' => $search,
			'override_records' => $override_records,
		));
	}

	function save($formdata = null)
	{
		if (!$this->requireAdmin()) { return; }
		if (!is_post_request() || !$formdata) { http_response_code(405); return $this->redirect('part_override'); }
		Csrf::cross_check();
		$part_id = intval($formdata['master_part_id'] ?? 0);
		$mesin_id = intval($formdata['mesin_id'] ?? 0);
		$machine_key = trim((string)($formdata['machine_key'] ?? ''));
		$is_applicable = (string)($formdata['is_applicable'] ?? '1') === '1';
		$durasi = trim((string)($formdata['durasi'] ?? ''));
		if (strlen($durasi) > 50) { $this->set_page_error('Durasi khusus maksimal 50 karakter.'); return $this->redirectToSelection($machine_key, $mesin_id); }
		$db = $this->GetModel();
		try {
			$unit_count_row = $db->rawQueryOne('SELECT COUNT(*) AS total FROM machine_module_units WHERE machine_key = ?', array($machine_key));
			$unit_count = intval($unit_count_row['total'] ?? 0);
			if ($unit_count <= 1) {
				$this->set_page_error('Override hanya tersedia untuk template Form AM dengan lebih dari satu unit mesin.');
				return $this->redirect('part_override');
			}
			$valid = $db->rawQueryOne('SELECT mp.id FROM master_part mp JOIN machine_module_units u ON u.machine_key = mp.machine_key AND u.mesin_id = ? WHERE mp.id = ? AND mp.machine_key = ? AND mp.taken_out_at IS NULL', array($mesin_id, $part_id, $machine_key));
			if (!$valid) { $this->set_page_error('Kombinasi modul, unit, dan part tidak valid.'); return $this->redirectToSelection($machine_key, $mesin_id); }
			$this->modeldata = array('master_part_id' => $part_id, 'mesin_id' => $mesin_id, 'is_applicable' => $is_applicable, 'durasi' => $durasi === '' ? null : $durasi);
			$saved = $db->rawQuery('INSERT INTO master_part_machine_override (master_part_id, mesin_id, is_applicable, durasi, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP) ON CONFLICT (master_part_id, mesin_id) DO UPDATE SET is_applicable = EXCLUDED.is_applicable, durasi = EXCLUDED.durasi, updated_at = CURRENT_TIMESTAMP RETURNING id', array($part_id, $mesin_id, $is_applicable, $durasi === '' ? null : $durasi));
			if (empty($saved[0]['id'])) { throw new RuntimeException('Override tidak tersimpan.'); }
			$this->rec_id = intval($saved[0]['id']); $this->write_to_log('save_override', 'true');
			$this->set_flash_msg('Override part berhasil disimpan.', 'success');
		} catch (Throwable $e) { $this->set_page_error('Override part gagal disimpan.'); }
		return $this->redirectToSelection($machine_key, $mesin_id);
	}

	function delete($formdata = null)
	{
		if (!$this->requireAdmin()) { return; }
		if (!is_post_request() || !$formdata) { http_response_code(405); return $this->redirect('part_override'); }
		Csrf::cross_check();
		$override_id = intval($formdata['override_id'] ?? 0);
		$mesin_id = intval($formdata['mesin_id'] ?? 0);
		$machine_key = trim((string)($formdata['machine_key'] ?? ''));
		$db = $this->GetModel();
		try {
			$record = $db->rawQueryOne('SELECT o.* FROM master_part_machine_override o JOIN master_part mp ON mp.id = o.master_part_id JOIN machine_module_units u ON u.machine_key = mp.machine_key AND u.mesin_id = o.mesin_id WHERE o.id = ? AND o.mesin_id = ? AND mp.machine_key = ?', array($override_id, $mesin_id, $machine_key));
			if (!$record) { $this->set_flash_msg('Override tidak ditemukan.', 'warning'); return $this->redirectToSelection($machine_key, $mesin_id); }
			$this->rec_id = $override_id; $this->modeldata = $record;
			if ($db->where('id', $override_id)->delete($this->tablename)) {
				$this->write_to_log('delete_override', 'true');
				$this->set_flash_msg('Override dihapus; part kembali memakai nilai default.', 'success');
			} else { $this->set_page_error('Override tidak dapat dihapus.'); }
		} catch (Throwable $e) { $this->set_page_error('Override tidak dapat dihapus. Pastikan update.sql sudah dijalankan.'); }
		return $this->redirectToSelection($machine_key, $mesin_id);
	}
}
