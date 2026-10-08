<?php
/**
 * Master data detail part mesin (foto, Metode, Alat, Standard, Durasi,
 * Pelaksanaan). Khusus Administrator (default-deny lewat ACL karena controller
 * ini gak didaftarkan di role 2/3/4 -- lihat libs/ACL.php).
 *
 * Semua 18 mesin di $machine_keys sudah baca part detail-nya dari tabel ini
 * (add.php/edit_data.php tiap mesin query master_part langsung) -- nambah/edit
 * row di sini langsung berefek ke form Add AM mesin terkait.
 * @category  Controller
 */
class Master_partController extends SecureController
{
	/** Daftar machineKey yang valid, sinkron sama $machineKey di tiap *Controller.php mesin. */
	public static $machine_keys = array(
		'sig' => 'SIG', 'joeya' => 'JOYEA', 'illapak_1_2' => 'Ilapak 1 - 2', 'illapak_3_12' => 'Ilapak 3 - 12', 'unifill_b' => 'Unifill',
		'chimei' => 'Chimei', 'temach' => 'Temach', 'check_weigher' => 'Check Weigher', 'conveyor_sig' => 'Conveyor SIG', 'jihcheng' => 'Jihcheng', 'jinsung_1_4' => 'Jinsung 1 - 4', 'jinsung_5' => 'Jinsung 5', 'best_pack' => 'Best Pack',
		'cosmec' => 'Cosmec', 'fbd_jaw_chuan' => 'FBD Jaw Chuan', 'fbd_glatt' => 'FBD Glatt', 'supermixer' => 'Supermixer', 'granulator' => 'Granulator', 'storage_tank' => 'Storage Tank Silverson', 'storage_tank_tetrapak' => 'Storage Tank Tetrapak', 'mixing_tank' => 'Mixing Tank',
	);

	// Highlight juga nentuin jumlah pilihan Kondisi yang muncul di form
	// add/edit_data (lihat Menu::kondisi_options()): Harian cuma Baik/Tidak
	// Baik (2 pilihan), Mingguan/Bulanan tambah "Tidak Dilakukan" (3 pilihan).
	public static $highlight_options = array(
		'' => '(Tidak ada / Harian) -- 2 pilihan kondisi (Baik/Tidak Baik)',
		'mingguan' => 'Mingguan -- 3 pilihan kondisi (+ Tidak Dilakukan)',
		'bulanan' => 'Bulanan / 2 Mingguan -- 3 pilihan kondisi (+ Tidak Dilakukan)',
	);

	/**
	 * image_path WAJIB disimpan relatif ("uploads/files/x.png"), jangan absolut
	 * ("http://localhost/form-am/uploads/files/x.png") -- host-nya ikut kebawa
	 * dan gambarnya rusak begitu aplikasi dipindah ke server lain. Ini jaring
	 * pengaman kalau ada jalur upload yang terlanjur balikin URL penuh.
	 * @return string
	 */
	private function relative_image_path($path)
	{
		$path = trim((string) $path);
		if ($path !== '' && stripos($path, SITE_ADDR) === 0) {
			$path = substr($path, strlen(SITE_ADDR));
		}
		return ltrim($path, '/');
	}

	/**
	 * Section yang udah ada per mesin, dikelompokin machine_key => [section, ...]
	 * -- dikirim ke view add/edit buat isi dropdown "Section" (pilih yang udah
	 * ada, biar gak typo bikin grup ganda) sekaligus opsi ketik baru.
	 *
	 * Hanya part AKTIF (taken_out_at IS NULL) yang dimasukkan ke picker --
	 * section dari part yang sudah di-takeout atau dihapus tidak perlu muncul
	 * lagi karena tidak ada part aktif yang mereferensikan section itu.
	 * @return array
	 */
	private function sections_by_machine()
	{
		$db = $this->GetModel();
		$db->where('taken_out_at', null, 'IS')->groupBy('machine_key')->groupBy('section')->orderBy('section', 'ASC');
		$rows = $db->get('master_part', null, array('machine_key', 'section'));
		$grouped = array();
		foreach ($rows as $row) {
			if (empty($row['section'])) { continue; }
			$grouped[$row['machine_key']][] = $row['section'];
		}
		return $grouped;
	}

	function __construct()
	{
		parent::__construct();
		$this->tablename = 'master_part';
		require_once dirname(__DIR__, 2) . '/system/RtwtMasterPartSync.php';
		// Reuse profil upload 'pict' yang udah didaftarkan global di
		// BaseController (dipakai juga buat foto profil user) -- dropzone widget
		// di view pakai fieldname="pict" biar endpoint upload generic ketemu
		// settingnya, walau hasil akhirnya disimpan ke kolom image_path.
	}

	/**
	 * Part tetap SELALU per 1 template mesin. Halaman awal menampilkan semua
	 * template agar admin dapat langsung memilihnya; Area/Search hanya menyaring.
	 */
	function index($machine_key = null)
	{
		$request = $this->request;
		$search = trim((string)($request->search ?? ''));
		$area = trim((string)($request->area ?? ''));
		$records = array();
		$machine_results = array();

		if (!empty($machine_key) && array_key_exists($machine_key, self::$machine_keys)) {
			$db = $this->GetModel();
			$db->where('machine_key', $machine_key);
			$db->orderBy('urutan', 'ASC')->orderBy('id', 'ASC');
			$records = $db->get($this->tablename);
		} else {
			$machine_key = null;
			foreach (self::$machine_keys as $key => $label) {
				$machine_area = $this->machine_area($key);
				if ($area !== '' && $machine_area !== $area) { continue; }
				if ($search !== '' && stripos($label, $search) === false && stripos($key, $search) === false) { continue; }
				$machine_results[] = array('key' => $key, 'label' => $label, 'area' => $machine_area);
			}
		}
		$this->view->page_title = 'Master Data Part Mesin';
		$this->view->selected_machine = $machine_key;
		return $this->render_view('master_part/list.php', array(
			'records' => $records,
			'machine_results' => $machine_results,
			'search' => $search,
			'area' => $area,
		));
	}

	/** Queue master changes independently from the existing ticket integration. */
	private function sync_rtwt_master_part($part, $previous_label = '')
	{
		if (empty($part['machine_key']) || empty($part['field_name']) || empty($part['label'])) { return; }
		$machine_key = (string)$part['machine_key'];
		if (!isset(self::$machine_keys[$machine_key])) { return; }
		$units = $this->GetModel()->rawQuery('SELECT m.id, m.nama_mesin FROM machine_module_units u JOIN mesin m ON m.id=u.mesin_id WHERE u.machine_key=? ORDER BY m.id ASC', array($machine_key)) ?: array();
		foreach ($units as $unit) {
			$machine_name = trim((string)($unit['nama_mesin'] ?? ''));
			if ($machine_name === '') { continue; }
			RtwtMasterPartSync::queueAndSend($this->GetModel(), array(
				'machine_key' => $machine_key,
				'field_name' => (string)$part['field_name'],
				'source_machine_id' => (int)$unit['id'],
				'label' => (string)$part['label'],
				'previous_label' => (string)$previous_label,
				'area_name' => (string)$this->machine_area($machine_key),
				'machine_name' => $machine_name,
			));
		}
	}

	/** Area template mengikuti satu-satunya mapping resmi ACL. */
	private function machine_area($machine_key)
	{
		foreach (ACL::$area_machines as $area => $machines) {
			if (in_array($machine_key, $machines, true)) { return $area; }
		}
		return '';
	}

	/** Unit override hanya tersedia saat satu template dipakai lebih dari satu unit fisik. */
	private function override_units($machine_key)
	{
		if (!array_key_exists($machine_key, self::$machine_keys)) { return array(); }
		try {
			$units = $this->GetModel()->rawQuery(
				'SELECT m.id, m.nama_mesin FROM machine_module_units u JOIN mesin m ON m.id = u.mesin_id WHERE u.machine_key = ? ORDER BY m.nama_mesin ASC',
				array($machine_key)
			) ?: array();
			return count($units) > 1 ? $units : array();
		} catch (Throwable $e) {
			// Edit Master Part tetap dapat dibuka sebelum update.sql dijalankan.
			return array();
		}
	}

	/**
	 * Mesin default kalau URL gak nyebut mesin: mesin PERTAMA (urutan whitelist)
	 * yang sudah punya data master_part -- biar admin gak mendarat di halaman
	 * kosong selama rollout belum kelar. Kalau belum ada data sama sekali,
	 * jatuh ke mesin pertama di whitelist.
	 * @return string
	 */
	private function default_machine_key()
	{
		$machine_keys = array_keys(self::$machine_keys);
		$db = $this->GetModel();
		$rows = $db->rawQuery('SELECT DISTINCT machine_key FROM ' . $this->tablename);
		if (!empty($rows)) {
			$filled = array_map(function ($row) { return $row['machine_key']; }, $rows);
			foreach ($machine_keys as $key) {
				if (in_array($key, $filled, true)) { return $key; }
			}
		}
		return $machine_keys[0];
	}

	/**
	 * Simpan urutan baru hasil drag-and-drop di halaman list (AJAX).
	 * POST 'ids' = daftar id dipisah koma, URUTAN ARRAY-nya yang jadi patokan
	 * (posisi ke-N di array -> urutan = N+1), bukan nilai urutan lama.
	 * @return JSON
	 */
	function reorder($formdata = null)
	{
		Csrf::cross_check();
		if (empty($formdata['ids'])) {
			return render_json(array('success' => false, 'message' => 'Tidak ada data urutan yang dikirim'));
		}
		//Cuma terima id numerik -- sisanya dibuang, jangan dipercaya mentah-mentah.
		$ids = array_values(array_filter(array_map('trim', explode(',', $formdata['ids'])), 'ctype_digit'));
		if (empty($ids)) {
			return render_json(array('success' => false, 'message' => 'Daftar id tidak valid'));
		}

		$db = $this->GetModel();
		$db->where('id', $ids, 'in');
		$rows = $db->get($this->tablename, null, array('id', 'machine_key'));
		//Semua id harus ADA & satu mesin yang sama -- reorder lintas mesin gak
		//masuk akal (urutan itu relatif per mesin) sekaligus nutup percobaan
		//nyelipin id mesin lain lewat request yang dimodifikasi.
		if (count($rows) !== count($ids)) {
			return render_json(array('success' => false, 'message' => 'Ada part yang tidak ditemukan'));
		}
		$machine_keys = array_unique(array_map(function ($row) { return $row['machine_key']; }, $rows));
		if (count($machine_keys) !== 1) {
			return render_json(array('success' => false, 'message' => 'Tidak bisa mengurutkan part dari mesin berbeda sekaligus'));
		}

		$db->startTransaction();
		foreach ($ids as $index => $id) {
			$db->where('id', $id);
			if (!$db->update($this->tablename, array('urutan' => $index + 1, 'updated_at' => datetime_now()))) {
				$db->rollback();
				return render_json(array('success' => false, 'message' => 'Gagal menyimpan urutan: ' . $db->getLastError()));
			}
		}
		$db->commit();
		$this->rec_id = implode(',', $ids);
		$this->write_to_log('reorder', 'true');
		return render_json(array('success' => true, 'count' => count($ids)));
	}

	function add($machine_key = null, $formdata = null)
	{
		//Kalau form di-POST ke URL tanpa segmen mesin, Router naruh $_POST di
		//argumen PERTAMA -- geser biar tetap kebaca sebagai formdata.
		if (is_array($machine_key) && $formdata === null) {
			$formdata = $machine_key;
			$machine_key = null;
		}
		//Mesin yang lagi dibuka di list, dipakai buat preselect dropdown &
		//tombol Batal (biar gak mental ke mesin default tiap kali).
		$this->view->preselect_machine = (!empty($machine_key) && array_key_exists($machine_key, self::$machine_keys)) ? $machine_key : '';
		//Section yang udah ada per mesin -- dropdown "Section" di view milih dari
		//sini (JS, difilter per mesin yang lagi dipilih), plus opsi ketik baru.
		$this->view->sections_by_machine = $this->sections_by_machine();
		if ($formdata) {
			$db = $this->GetModel();
			//Urutan BUKAN dari form -- part baru selalu ditaruh di akhir urutan
			//mesinnya (dihitung di bawah), biar-biar posisinya cuma bisa diatur
			//lewat drag-and-drop di list (satu-satunya sumber kebenaran urutan,
			//gak ada lagi input angka manual yang bisa bentrok/duplikat/minus).
			$this->fields = array('machine_key', 'field_name', 'label', 'section', 'metode', 'alat', 'standard', 'durasi', 'pelaksanaan', 'shift_schedule', 'highlight', 'image_path');
			$postdata = $this->format_request_data($formdata);
			// Kalau validasi gagal, form Add dirender lagi pada request yang sama.
			// Simpan nilai POST untuk diisi ulang oleh view, supaya admin tidak
			// kehilangan isian yang sudah panjang hanya karena satu field salah.
			$this->view->form_values = $postdata;
			$this->rules_array = array(
				'machine_key' => 'required',
				'field_name' => 'required',
				'label' => 'required',
			);
			//Field deskriptif TIDAK di-sanitize_string (itu jalanin htmlspecialchars
			//di save time) -- teks yang ngandung "&"/"<"/dll bakal kesimpen literal
			//jadi "&amp;"/dll di DB (kejadian nyata, cek migrasi
			//2026-08-20_fix_master_part_encoding.sql). View yang nampilin part detail
			//(add.php/edit_data.php mesin + list.php di sini) semua udah escape pas
			//nge-echo, jadi aman disimpan mentah -- escape di output, bukan di input.
			$this->sanitize_array = array(
				'machine_key' => 'sanitize_string', 'field_name' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if (!empty($modeldata['image_path'])) {
				$modeldata['image_path'] = $this->relative_image_path($modeldata['image_path']);
			}
			// Backward-compatible untuk integrasi/data lama yang belum mengirim
			// field ini: perilaku historis semua part adalah Shift 1.
			$modeldata['shift_schedule'] = $this->normalize_shift_schedule(!empty($modeldata['shift_schedule']) ? $modeldata['shift_schedule'] : '1');
			$modeldata['active_from'] = date('Y-m-d');
			if ($modeldata['shift_schedule'] === null) { $this->view->page_error[] = 'Jadwal shift harus berisi minimal satu shift yang valid (1, 2, atau 3).'; }
			//machine_key wajib dari whitelist -- dropdown di UI udah batasin, tapi
			//tetap divalidasi ulang di server karena nilai ini dipakai bangun nama
			//tabel mentah buat ALTER TABLE di bawah (jangan percaya POST mentah-mentah).
			if (!empty($modeldata['machine_key']) && !array_key_exists($modeldata['machine_key'], self::$machine_keys)) {
				$this->view->page_error[] = 'Mesin tidak dikenal.';
			}
			//field_name cuma boleh snake_case (harus cocok sama nama kolom di tabel mesin terkait,
			//dan ini juga yang dipakai bangun nama kolom mentah buat ALTER TABLE di bawah).
			if (!empty($modeldata['field_name']) && !preg_match('/^[a-z][a-z0-9_]*$/', $modeldata['field_name'])) {
				$this->view->page_error[] = 'Field Name harus huruf kecil/angka/underscore, diawali huruf (harus persis sama dengan nama kolom di tabel mesinnya).';
			}
			if (!empty($modeldata['machine_key'])) {
				$db->where('machine_key', $modeldata['machine_key'])->where('field_name', isset($modeldata['field_name']) ? $modeldata['field_name'] : '');
				$existing_part = $db->getOne($this->tablename, array('id', 'taken_out_at'));
				if ($existing_part) {
					if (empty($existing_part['taken_out_at'])) {
						$this->view->page_error[] = 'Part dengan Field Name ini sudah ada dan sedang aktif untuk mesin tersebut.';
					} else {
						$this->view->page_error[] = 'Part dengan Field Name ini sudah ada di daftar arsip/takeout. Silakan gunakan tombol Aktifkan Kembali pada baris part tersebut di daftar Master Part alih-alih membuat baru.';
					}
				}
			}
			if ($this->validated()) {
				$modeldata['created_at'] = datetime_now();
				$db->where('machine_key', $modeldata['machine_key'])->orderBy('urutan', 'DESC');
				$last = $db->getOne($this->tablename, 'urutan');
				$modeldata['urutan'] = (!empty($last['urutan']) ? intval($last['urutan']) : 0) + 1;
				//Insert row master_part + ALTER TABLE dibungkus 1 transaction -- kalau
				//ALTER TABLE gagal (misal user DB gak punya izin DDL), row master_part
				//IKUT di-rollback juga. Tanpa ini, bisa kejadian row master_part sukses
				//kesimpan padahal kolom fisiknya gak pernah ke-buat -- part keliatan di
				//list tapi bikin error pas submit AM beneran.
				$db->startTransaction();
				$rec_id = $this->rec_id = $db->insert($this->tablename, $modeldata);
				if ($rec_id) {
					//machine_key & field_name udah divalidasi ketat di atas (whitelist +
					//regex snake_case), jadi aman diselipkan langsung sebagai identifier SQL.
					$physical_table = 'tb_mesin_' . $modeldata['machine_key'];
					$alter_ok = $db->rawQuery('ALTER TABLE "' . $physical_table . '" ADD COLUMN IF NOT EXISTS "' . $modeldata['field_name'] . '" varchar(255) DEFAULT NULL');
					if ($alter_ok !== false && !$db->getLastError()) {
						$db->commit();
						$this->sync_rtwt_master_part(array('machine_key' => $modeldata['machine_key'], 'field_name' => $modeldata['field_name'], 'label' => $modeldata['label']));
						$this->write_to_log('add', 'true');
						$this->set_flash_msg('Part berhasil ditambahkan (kolom baru otomatis dibuat di tabel mesin)', 'success');
						//Balik ke list mesin yang barusan diisi, bukan ke mesin default.
						return $this->redirect('master_part/index/' . $modeldata['machine_key']);
					}
					$db->rollback();
					$this->view->page_error[] = 'Gagal membuat kolom baru di tabel mesin: ' . $db->getLastError();
				} else {
					$db->rollback();
				}
				$this->set_page_error();
			}
		}
		$this->view->page_title = 'Add New Part';
		return $this->render_view('master_part/add.php');
	}

	function edit($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		//Dipakai buat balik ke list mesin yang bersangkutan (bukan mesin default)
		//abis simpan/batal -- machine_key sendiri gak ikut diedit (read-only).
		$db->where('id', $rec_id);
		$owner_row = $db->getOne($this->tablename, 'machine_key');
		$back_url = 'master_part/index/' . (!empty($owner_row['machine_key']) ? $owner_row['machine_key'] : '');
		$this->view->back_url = $back_url;
		$this->view->sections_by_machine = $this->sections_by_machine();
		$this->view->override_units = $this->override_units($owner_row['machine_key'] ?? '');
		$this->view->part_overrides = array();
		try {
			$this->view->part_overrides = $db->rawQuery('SELECT o.*, m.nama_mesin FROM master_part_machine_override o JOIN mesin m ON m.id = o.mesin_id WHERE o.master_part_id = ? ORDER BY m.id ASC', array((int)$rec_id)) ?: array();
		} catch (Throwable $e) { /* update.sql belum dijalankan */ }
		if ($formdata) {
			$previous_part = $db->where('id', $rec_id)->getOne($this->tablename, array('machine_key', 'field_name', 'label'));
			//Urutan gak ikut diedit di sini -- cuma diatur lewat drag-and-drop di
			//list (lihat reorder()). Field deskriptif juga gak di-sanitize_string,
			//sama alasannya kayak di add() -- lihat catatan di sana.
			$this->fields = array('label', 'section', 'metode', 'alat', 'standard', 'durasi', 'pelaksanaan', 'shift_schedule', 'highlight', 'image_path');
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array('label' => 'required');
			$this->sanitize_array = array();
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			//foto lama dibiarkan (gak dihapus) kalau admin gak upload foto baru pas edit.
			if (empty($modeldata['image_path'])) { unset($modeldata['image_path']); }
			else { $modeldata['image_path'] = $this->relative_image_path($modeldata['image_path']); }
			$modeldata['shift_schedule'] = $this->normalize_shift_schedule(!empty($modeldata['shift_schedule']) ? $modeldata['shift_schedule'] : '1');
			if ($modeldata['shift_schedule'] === null) { $this->view->page_error[] = 'Jadwal shift harus berisi minimal satu shift yang valid (1, 2, atau 3).'; }
			if ($this->validated()) {
				$modeldata['updated_at'] = datetime_now();
				$db->where('id', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) {
					if ($previous_part) {
						$this->sync_rtwt_master_part(array('machine_key' => $previous_part['machine_key'], 'field_name' => $previous_part['field_name'], 'label' => $modeldata['label']), (string)($previous_part['label'] ?? ''));
					}
					$this->write_to_log('edit', 'true');
					$this->set_flash_msg('Part berhasil diperbarui', 'success');
					return $this->redirect($back_url);
				}
				if ($db->getLastError()) { $this->set_page_error(); }
				elseif (!$numRows) {
					$this->set_flash_msg('Tidak ada perubahan data yang disimpan', 'warning');
					return $this->redirect($back_url);
				}
			}
		}
		$db->where('id', $rec_id);
		$data = $db->getOne($this->tablename);
		if (!$data) { $this->set_page_error('No record found'); $data = array(); }
		$this->view->page_title = 'Edit Part';
		return $this->render_view('master_part/edit.php', $data);
	}

	function save_override($rec_id = null, $formdata = null)
	{
		if (!is_post_request()) { http_response_code(405); return $this->redirect('master_part/edit/' . intval($rec_id)); }
		if (!$formdata) { return $this->redirect('master_part/edit/' . intval($rec_id)); }
		Csrf::cross_check();
		$db = $this->GetModel();
		$part = $db->where('id', intval($rec_id))->getOne($this->tablename, array('id', 'machine_key'));
		$mesin_id = intval($formdata['mesin_id'] ?? 0);
		$allowed_ids = array_map('intval', array_column($this->override_units($part['machine_key'] ?? ''), 'id'));
		$is_applicable = (string)($formdata['is_applicable'] ?? '1') === '1';
		$durasi = trim((string)($formdata['durasi'] ?? ''));
		if (strlen($durasi) > 50) { $this->set_page_error('Durasi khusus maksimal 50 karakter.'); return $this->redirect('master_part/edit/' . intval($rec_id)); }
		if (!$part || !$mesin_id || !in_array($mesin_id, $allowed_ids, true)) {
			$this->set_page_error('Unit mesin tidak valid untuk template part ini.');
			return $this->redirect('master_part/edit/' . intval($rec_id));
		}
		try {
			$this->modeldata = array('master_part_id' => (int)$rec_id, 'mesin_id' => $mesin_id, 'is_applicable' => $is_applicable, 'durasi' => $durasi === '' ? null : $durasi);
			$saved = $db->rawQuery('INSERT INTO master_part_machine_override (master_part_id, mesin_id, is_applicable, durasi, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP) ON CONFLICT (master_part_id, mesin_id) DO UPDATE SET is_applicable = EXCLUDED.is_applicable, durasi = EXCLUDED.durasi, updated_at = CURRENT_TIMESTAMP RETURNING id', array((int)$rec_id, $mesin_id, $is_applicable, $durasi === '' ? null : $durasi));
			if (empty($saved[0]['id'])) { throw new RuntimeException('Override tidak tersimpan.'); }
			$this->rec_id = intval($saved[0]['id']); $this->tablename = 'master_part_machine_override';
			$this->write_to_log('save_override', 'true');
			$this->set_flash_msg('Override unit berhasil disimpan.', 'success');
		} catch (Throwable $e) { $this->set_page_error('Override gagal disimpan. Pastikan update.sql sudah dijalankan.'); }
		return $this->redirect('master_part/edit/' . intval($rec_id));
	}

	function delete_override($rec_id = null, $formdata = null)
	{
		if (!is_post_request()) { http_response_code(405); return $this->redirect('master_part/edit/' . intval($rec_id)); }
		Csrf::cross_check();
		$db = $this->GetModel();
		try {
			$override_id = intval($formdata['override_id'] ?? 0);
			$override = $db->where('id', $override_id)->where('master_part_id', intval($rec_id))->getOne('master_part_machine_override');
			if (!$override) { $this->set_flash_msg('Override unit tidak ditemukan.', 'warning'); return $this->redirect('master_part/edit/' . intval($rec_id)); }
			$this->rec_id = $override_id; $this->modeldata = $override; $this->tablename = 'master_part_machine_override';
			if (!$db->where('id', $override_id)->where('master_part_id', intval($rec_id))->delete('master_part_machine_override')) { throw new RuntimeException('Delete gagal.'); }
			$this->write_to_log('delete_override', 'true');
			$this->set_flash_msg('Override unit dihapus; part kembali memakai nilai default.', 'success');
		} catch (Throwable $e) { $this->set_page_error('Override tidak dapat dihapus.'); }
		return $this->redirect('master_part/edit/' . intval($rec_id));
	}

	/** Canonical form: "1", "1,2", atau "1,2,3". */
	private function normalize_shift_schedule($value)
	{
		$values = array_unique(array_filter(array_map('trim', explode(',', (string) $value)), function ($shift) { return in_array($shift, array('1', '2', '3'), true); }));
		sort($values, SORT_NUMERIC);
		return empty($values) ? null : implode(',', $values);
	}

	/**
	 * Hapus row master_part = part gak muncul lagi di form Add AM. Kolom fisik
	 * di tabel mesin & data historis yang udah ke-submit SENGAJA gak ikut
	 * dihapus/di-DROP (safe, non-destruktif) -- kalau field_name yang sama
	 * ditambah lagi belakangan, datanya lama tetap ada.
	 */
	function delete($rec_id = null)
	{
		Csrf::cross_check();
		$this->set_flash_msg('Penghapusan fisik part dilarang demi kepatuhan audit trail GMP. Silakan gunakan fitur Takeout untuk menonaktifkan part.', 'warning');
		return $this->redirect('master_part');
	}

	/** Aktifkan kembali part takeout tanpa menghapus definisi atau riwayat AM. */
	private function write_part_status_history($part_id, $status, $started_at, $ended_at = null, $reason = null)
	{
		try {
			$db = $this->GetModel();
			if ($status === 'TO') {
				$db->rawQuery('UPDATE "master_part_status_history" SET "ended_at" = ? WHERE "master_part_id" = ? AND "status" = \'ACTIVE\' AND "ended_at" IS NULL', array($started_at, (int)$part_id));
			}
			if ($status === 'ACTIVE') {
				$db->rawQuery('UPDATE "master_part_status_history" SET "ended_at" = ? WHERE "master_part_id" = ? AND "status" = \'TO\' AND "ended_at" IS NULL', array($started_at, (int)$part_id));
			}
			$db->rawQuery('INSERT INTO "master_part_status_history" ("master_part_id", "status", "started_at", "ended_at", "reason", "changed_by_user_id", "changed_by_username") VALUES (?, ?, ?, ?, ?, ?, ?)', array((int)$part_id, $status, $started_at, $ended_at, $reason, USER_ID ? (int)USER_ID : null, USER_NAME));
		} catch (Throwable $e) {
			error_log('Part status history unavailable: ' . $e->getMessage());
		}
	}

	function reactivate($rec_id = null)
	{
		Csrf::cross_check();
		$db = $this->GetModel(); $this->rec_id = $rec_id;
		$db->where('id', $rec_id);
		$part = $db->getOne($this->tablename, array('id', 'machine_key', 'taken_out_at'));
		if (!$part) {
			$this->set_flash_msg('Part tidak ditemukan.', 'warning');
			return $this->redirect('master_part');
		}
		$back_url = 'master_part/index/' . $part['machine_key'];
		if (empty($part['taken_out_at'])) {
			$this->set_flash_msg('Part ini sudah aktif.', 'warning');
			return $this->redirect($back_url);
		}
		$db->where('id', $rec_id)->where('taken_out_at', null, 'IS NOT');
		$update_data = array('taken_out_at' => null, 'taken_out_by' => null, 'takeout_reason' => null, 'active_from' => date('Y-m-d'), 'updated_at' => datetime_now());
		if ($db->update($this->tablename, $update_data)) {
			$this->write_part_status_history($rec_id, 'ACTIVE', datetime_now());
			$active_part = $db->where('id', $rec_id)->getOne($this->tablename, array('machine_key', 'field_name', 'label'));
			if ($active_part) { $this->sync_rtwt_master_part($active_part); }
			$this->write_to_log('reactivate', 'true');
			$this->set_flash_msg('Part berhasil diaktifkan kembali dan kini aktif di form AM.', 'success');
			return $this->redirect($back_url);
		}
		$this->set_page_error($db->getLastError() ?: 'Part tidak dapat diaktifkan kembali.');
		return $this->redirect($back_url);
	}

	/**
	 * Takeout menghentikan part dari form baru tanpa menghapus definisi atau
	 * nilai pada report lama. Ini satu-satunya jalur normal untuk part aktif.
	 */
	function takeout($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel();
		$db->where('id', $rec_id);
		$part = $db->getOne($this->tablename, array('id', 'machine_key', 'label', 'taken_out_at'));
		if (!$part) { $this->set_page_error('Part tidak ditemukan'); return $this->redirect('master_part'); }
		if ($formdata) {
			Csrf::cross_check();
			$reason = trim((string)($formdata['takeout_reason'] ?? ''));
			if ($reason === '') { $this->view->page_error[] = 'Alasan takeout wajib diisi.'; }
			else {
				$db->where('id', $rec_id);
				$taken_out_at = datetime_now();
				if ($db->update($this->tablename, array('taken_out_at' => $taken_out_at, 'taken_out_by' => USER_NAME, 'takeout_reason' => $reason))) {
					$this->write_part_status_history($rec_id, 'TO', $taken_out_at, null, $reason);
					$this->write_to_log('takeout', 'true');
					$this->set_flash_msg('Part ditakeout. Report historis tetap mempertahankan part ini.', 'success');
					return $this->redirect('master_part/index/' . $part['machine_key']);
				}
				$this->set_page_error($db->getLastError());
			}
		}
		$this->view->page_title = 'Takeout Part';
		return $this->render_view('master_part/takeout.php', $part);
	}
}
