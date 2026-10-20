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

	/** Best Pack tanpa Inkjet memang tidak memiliki part ini; aturan checklist tetap di Best_packController. */
	private function isFixedNonInkjetExclusion($machine_key, $field_name, $unit_name)
	{
		return $machine_key === 'best_pack'
			&& $field_name === 'print_head_inkjet'
			&& strpos((string)$unit_name, 'Best Pack (non Inkjet) - ') === 0;
	}

	private function redirectToSelection($machine_key, $mesin_id, $part_id = 0)
	{
		$query = http_build_query(array('machine_key' => $machine_key, 'mesin_id' => intval($mesin_id), 'part_id' => intval($part_id), 'view' => intval($part_id) > 0 ? 'part' : 'unit'));
		return $this->redirect('part_override?' . $query . (intval($part_id) > 0 ? '#part-unit-status' : ''));
	}

	function index()
	{
		if (!$this->requireAdmin()) { return; }
		$request = $this->request;
		$machine_key = trim((string)($request->machine_key ?? ''));
		$mesin_id = intval($request->mesin_id ?? 0);
		$part_id = intval($request->part_id ?? 0);
		$view_mode = (string)($request->view ?? '') === 'unit' || ($mesin_id > 0 && $part_id === 0) ? 'unit' : 'part';
		$search = trim((string)($request->search ?? ''));
		$modules = array();
		$units = array(); $units_by_module = array(); $parts = array(); $part_catalog = array(); $part_unit_rows = array(); $selected_part = null; $override_records = array(); $selected_unit = null; $fixed_excluded_unit_ids = array();
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
			if (!isset($modules[$machine_key])) { $machine_key = ''; $mesin_id = 0; $part_id = 0; $view_mode = 'part'; }
			$units = $machine_key !== '' ? ($units_by_module[$machine_key] ?? array()) : array();
			if ($machine_key === 'best_pack') {
				foreach ($units as $unit) {
					if ($this->isFixedNonInkjetExclusion($machine_key, 'print_head_inkjet', $unit['nama_mesin'])) { $fixed_excluded_unit_ids[intval($unit['id'])] = true; }
				}
			}
			if ($view_mode === 'unit') { foreach ($units as $unit) { if (intval($unit['id']) === $mesin_id) { $selected_unit = $unit; break; } } }
			if ($machine_key !== '' && $view_mode === 'part') {
				$catalog_params = array($machine_key);
				$catalog_filter = '';
				if ($search !== '') {
					$catalog_filter = ' AND (label ILIKE ? OR field_name ILIKE ? OR section ILIKE ?)';
					$like = '%' . $search . '%';
					$catalog_params[] = $like; $catalog_params[] = $like; $catalog_params[] = $like;
				}
				$part_catalog = $db->rawQuery('SELECT id, field_name, label, section, durasi FROM master_part WHERE machine_key = ? AND taken_out_at IS NULL' . $catalog_filter . ' ORDER BY urutan ASC, id ASC', $catalog_params) ?: array();
				if ($part_id > 0) {
					$selected_part = $db->rawQueryOne('SELECT id, field_name, label, section, durasi FROM master_part WHERE id = ? AND machine_key = ? AND taken_out_at IS NULL', array($part_id, $machine_key));
					if ($selected_part) {
						$part_overrides = $db->rawQuery('SELECT id, mesin_id, is_applicable, durasi FROM master_part_machine_override WHERE master_part_id = ?', array($part_id)) ?: array();
						$overrides_by_unit = array();
						foreach ($part_overrides as $override) { $overrides_by_unit[intval($override['mesin_id'])] = $override; }
						foreach ($units as $unit) {
							$unit['override'] = $overrides_by_unit[intval($unit['id'])] ?? null;
							$part_unit_rows[] = $unit;
						}
					}
				}
			}
			if ($machine_key !== '' && $view_mode === 'part' && !$selected_part) {
				$override_records = $db->rawQuery(
				'SELECT o.id AS override_id, o.is_applicable, o.durasi AS override_durasi, o.updated_at, mp.id AS master_part_id, mp.machine_key, mp.field_name, mp.label, mp.section, m.id AS mesin_id, m.nama_mesin, m.nomor_seri '
				. 'FROM master_part_machine_override o '
				. 'JOIN master_part mp ON mp.id = o.master_part_id '
				. 'JOIN machine_module_units u ON u.machine_key = mp.machine_key AND u.mesin_id = o.mesin_id '
				. 'JOIN mesin m ON m.id = o.mesin_id '
				. 'WHERE mp.taken_out_at IS NULL AND mp.machine_key = ? '
				. ' ORDER BY NULLIF(substring(m.nama_mesin FROM \'([0-9]+)[[:space:]]*$\'), \'\')::int NULLS LAST, m.nama_mesin ASC, mp.urutan ASC, mp.label ASC',
				array($machine_key)
				) ?: array();
			}
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
			'machine_key' => $machine_key, 'mesin_id' => $mesin_id, 'units' => $units, 'view_mode' => $view_mode,
			'units_by_module' => $units_by_module, 'selected_unit' => $selected_unit, 'parts' => $parts, 'search' => $search,
			'override_records' => $override_records,
			'part_catalog' => $part_catalog, 'selected_part' => $selected_part, 'part_unit_rows' => $part_unit_rows,
			'fixed_excluded_unit_ids' => $fixed_excluded_unit_ids,
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
		$return_part_id = intval($formdata['return_part_id'] ?? 0);
		$is_applicable = (string)($formdata['is_applicable'] ?? '1') === '1';
		$durasi = trim((string)($formdata['durasi'] ?? ''));
		if (strlen($durasi) > 50) { $this->set_page_error('Durasi khusus maksimal 50 karakter.'); return $this->redirectToSelection($machine_key, $return_part_id > 0 ? 0 : $mesin_id, $return_part_id); }
		$db = $this->GetModel();
		try {
			$unit_count_row = $db->rawQueryOne('SELECT COUNT(*) AS total FROM machine_module_units WHERE machine_key = ?', array($machine_key));
			$unit_count = intval($unit_count_row['total'] ?? 0);
			if ($unit_count <= 1) {
				$this->set_page_error('Override hanya tersedia untuk template Form AM dengan lebih dari satu unit mesin.');
				return $this->redirect('part_override');
			}
			$valid = $db->rawQueryOne('SELECT mp.id, mp.field_name, m.nama_mesin FROM master_part mp JOIN machine_module_units u ON u.machine_key = mp.machine_key AND u.mesin_id = ? JOIN mesin m ON m.id = u.mesin_id WHERE mp.id = ? AND mp.machine_key = ? AND mp.taken_out_at IS NULL', array($mesin_id, $part_id, $machine_key));
			if (!$valid) { $this->set_page_error('Kombinasi modul, unit, dan part tidak valid.'); return $this->redirectToSelection($machine_key, $mesin_id); }
			if ($this->isFixedNonInkjetExclusion($machine_key, $valid['field_name'], $valid['nama_mesin'])) {
				$this->set_page_error('Print Head Inkjet tidak berlaku pada Best Pack non-Inkjet berdasarkan aturan unit. Status ini tidak dapat diubah melalui override.');
				return $this->redirectToSelection($machine_key, $return_part_id > 0 ? 0 : $mesin_id, $return_part_id);
			}
			$this->modeldata = array('master_part_id' => $part_id, 'mesin_id' => $mesin_id, 'is_applicable' => $is_applicable, 'durasi' => $durasi === '' ? null : $durasi);
			$saved = $db->rawQuery('INSERT INTO master_part_machine_override (master_part_id, mesin_id, is_applicable, durasi, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP) ON CONFLICT (master_part_id, mesin_id) DO UPDATE SET is_applicable = EXCLUDED.is_applicable, durasi = EXCLUDED.durasi, updated_at = CURRENT_TIMESTAMP RETURNING id', array($part_id, $mesin_id, $is_applicable, $durasi === '' ? null : $durasi));
			if (empty($saved[0]['id'])) { throw new RuntimeException('Override tidak tersimpan.'); }
			$this->rec_id = intval($saved[0]['id']); $this->write_to_log('save_override', 'true');
			$this->set_flash_msg('Override part berhasil disimpan.', 'success');
		} catch (Throwable $e) { $this->set_page_error('Override part gagal disimpan.'); }
		return $this->redirectToSelection($machine_key, $return_part_id > 0 ? 0 : $mesin_id, $return_part_id);
	}

	/** Simpan perubahan beberapa unit untuk satu part secara atomik. */
	function save_bulk($formdata = null)
	{
		if (!$this->requireAdmin()) { return; }
		if (!is_post_request() || !$formdata) { http_response_code(405); return $this->redirect('part_override'); }
		Csrf::cross_check();
		$part_id = intval($formdata['master_part_id'] ?? 0);
		$machine_key = trim((string)($formdata['machine_key'] ?? ''));
		$submitted_units = $formdata['units'] ?? null;
		$db = $this->GetModel();
		$transaction_started = false;
		try {
			if ($part_id <= 0 || !is_array($submitted_units)) { throw new DomainException('Data override tidak lengkap. Muat ulang halaman lalu coba lagi.'); }
			$part = $db->rawQueryOne('SELECT id, field_name FROM master_part WHERE id = ? AND machine_key = ? AND taken_out_at IS NULL', array($part_id, $machine_key));
			$units = $db->rawQuery('SELECT m.id, m.nama_mesin FROM machine_module_units u JOIN mesin m ON m.id = u.mesin_id WHERE u.machine_key = ?', array($machine_key)) ?: array();
			if (!$part || count($units) <= 1) { throw new DomainException('Modul atau part tidak valid untuk override per unit.'); }
			$units_by_id = array();
			foreach ($units as $unit) { $units_by_id[intval($unit['id'])] = $unit; }
			$changes = array();
			foreach ($submitted_units as $key => $entry) {
				$unit_id = intval($key);
				if (!ctype_digit((string)$key) || $unit_id <= 0 || !isset($units_by_id[$unit_id]) || !is_array($entry)) {
					throw new DomainException('Unit dalam permintaan tidak valid. Muat ulang halaman.');
				}
				foreach (array('original_has_override', 'original_is_applicable', 'original_durasi', 'is_applicable', 'durasi', 'reset_to_baseline') as $field) {
					if (!array_key_exists($field, $entry)) { throw new DomainException('Data unit tidak lengkap. Muat ulang halaman.'); }
				}
				foreach (array('original_has_override', 'original_is_applicable', 'is_applicable', 'reset_to_baseline') as $field) {
					if (!in_array((string)$entry[$field], array('0', '1'), true)) { throw new DomainException('Status unit tidak valid.'); }
				}
				if ($this->isFixedNonInkjetExclusion($machine_key, $part['field_name'], $units_by_id[$unit_id]['nama_mesin'])) {
					throw new DomainException('Print Head Inkjet pada Best Pack non-Inkjet ditetapkan oleh aturan unit dan tidak dapat diubah.');
				}
				$new_durasi = trim((string)$entry['durasi']);
				if (strlen($new_durasi) > 50) { throw new DomainException('Durasi khusus maksimal 50 karakter.'); }
				$original_durasi = trim((string)$entry['original_durasi']);
				$reset = (string)$entry['reset_to_baseline'] === '1';
				$new_applicable = $reset ? true : (string)$entry['is_applicable'] === '1';
				if ($reset) { $new_durasi = ''; }
				$original_applicable = (string)$entry['original_is_applicable'] === '1';
				$original_has_override = (string)$entry['original_has_override'] === '1';
				if ($new_applicable !== $original_applicable || $new_durasi !== $original_durasi || ($reset && $original_has_override)) {
					$changes[$unit_id] = array(
						'name' => (string)$units_by_id[$unit_id]['nama_mesin'],
						'original_has_override' => $original_has_override,
						'original_is_applicable' => $original_applicable,
						'original_durasi' => $original_durasi,
						'is_applicable' => $new_applicable,
						'durasi' => $new_durasi,
						'reset' => $reset,
					);
				}
			}
			if (!$changes) {
				if (is_ajax()) { render_json(array('success' => true, 'changed' => 0, 'message' => 'Tidak ada perubahan override yang perlu disimpan.')); }
				$this->set_flash_msg('Tidak ada perubahan override yang perlu disimpan.', 'warning');
				return $this->redirectToSelection($machine_key, 0, $part_id);
			}
			if ($db->startTransaction() === false) { throw new RuntimeException('Transaksi gagal dimulai.'); }
			$transaction_started = true;
			foreach ($changes as $unit_id => $change) {
				$current = $db->rawQueryOne('SELECT id, is_applicable, durasi FROM master_part_machine_override WHERE master_part_id = ? AND mesin_id = ? FOR UPDATE', array($part_id, $unit_id));
				$current_has_override = !empty($current['id']);
				$current_applicable = !$current_has_override || in_array($current['is_applicable'] ?? null, array(true, 1, '1', 't', 'true'), true);
				$current_durasi = $current_has_override ? trim((string)($current['durasi'] ?? '')) : '';
				if ($current_has_override !== $change['original_has_override'] || $current_applicable !== $change['original_is_applicable'] || $current_durasi !== $change['original_durasi']) {
					throw new DomainException('Override ' . $change['name'] . ' berubah di sesi lain. Tidak ada perubahan yang disimpan; muat ulang halaman.');
				}
				if ($change['reset'] || ($change['is_applicable'] && $change['durasi'] === '')) {
					if ($current_has_override) {
						$deleted = $db->rawQuery('DELETE FROM master_part_machine_override WHERE master_part_id = ? AND mesin_id = ? RETURNING id', array($part_id, $unit_id));
						if (empty($deleted[0]['id'])) { throw new RuntimeException('Reset override gagal.'); }
					}
				} else {
					$saved = $db->rawQuery('INSERT INTO master_part_machine_override (master_part_id, mesin_id, is_applicable, durasi, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP) ON CONFLICT (master_part_id, mesin_id) DO UPDATE SET is_applicable = EXCLUDED.is_applicable, durasi = EXCLUDED.durasi, updated_at = CURRENT_TIMESTAMP RETURNING id', array($part_id, $unit_id, $change['is_applicable'], $change['durasi'] === '' ? null : $change['durasi']));
					if (empty($saved[0]['id'])) { throw new RuntimeException('Override gagal disimpan.'); }
				}
			}
			if ($db->commit() === false) { throw new RuntimeException('Transaksi gagal diselesaikan.'); }
			$transaction_started = false;
			$this->rec_id = $part_id;
			$this->modeldata = array('master_part_id' => $part_id, 'machine_key' => $machine_key, 'unit_changes' => $changes);
			$this->write_to_log('save_override_bulk', 'true');
			$this->set_flash_msg(count($changes) . ' unit berhasil diperbarui.', 'success');
			if (is_ajax()) { render_json(array('success' => true, 'changed' => count($changes), 'message' => count($changes) . ' unit berhasil diperbarui.')); }
		} catch (Throwable $e) {
			if ($transaction_started) { $db->rollback(); }
			$message = $e instanceof DomainException ? $e->getMessage() : 'Perubahan override gagal disimpan. Tidak ada unit yang diubah.';
			if (is_ajax()) { http_response_code($e instanceof DomainException ? 409 : 500); render_json(array('success' => false, 'message' => $message)); }
			$this->set_page_error($message);
		}
		return $this->redirectToSelection($machine_key, 0, $part_id);
	}

	function delete($formdata = null)
	{
		if (!$this->requireAdmin()) { return; }
		if (!is_post_request() || !$formdata) { http_response_code(405); return $this->redirect('part_override'); }
		Csrf::cross_check();
		$override_id = intval($formdata['override_id'] ?? 0);
		$mesin_id = intval($formdata['mesin_id'] ?? 0);
		$machine_key = trim((string)($formdata['machine_key'] ?? ''));
		$return_part_id = intval($formdata['return_part_id'] ?? 0);
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
		return $this->redirectToSelection($machine_key, $return_part_id > 0 ? 0 : $mesin_id, $return_part_id);
	}
}
