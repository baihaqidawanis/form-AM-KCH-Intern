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
		'sig' => 'SIG', 'joeya' => 'Joeya', 'illapak_1_2' => 'Illapak 1 - 2', 'illapak_3_12' => 'Illapak 3 - 12', 'unifill_b' => 'Unifill',
		'chimei' => 'Chimei', 'temach' => 'Temach', 'jihcheng' => 'Jihcheng', 'jinsung_1_4' => 'Jinsung 1 - 4', 'jinsung_5' => 'Jinsung 5', 'best_pack' => 'Best Pack',
		'cosmec' => 'Cosmec', 'fbd_jaw_chuan' => 'FBD Jaw Chuan', 'fbd_glatt' => 'FBD Glatt', 'supermixer' => 'Supermixer', 'storage_tank' => 'Storage Tank Silverson', 'storage_tank_tetrapak' => 'Storage Tank Tetrapak', 'mixing_tank' => 'Mixing Tank',
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
	 * @return array
	 */
	private function sections_by_machine()
	{
		$db = $this->GetModel();
		$db->groupBy('machine_key')->groupBy('section')->orderBy('section', 'ASC');
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
		// Reuse profil upload 'pict' yang udah didaftarkan global di
		// BaseController (dipakai juga buat foto profil user) -- dropzone widget
		// di view pakai fieldname="pict" biar endpoint upload generic ketemu
		// settingnya, walau hasil akhirnya disimpan ke kolom image_path.
	}

	/**
	 * List part SELALU per 1 mesin -- gak ada mode "semua mesin dicampur"
	 * (bikin bingung: part antar mesin keliatan nyampur padahal gak ada
	 * hubungannya, dan urutan drag-drop cuma masuk akal dalam 1 mesin).
	 * Kalau machine_key kosong/gak dikenal, default ke mesin pertama.
	 */
	function index($machine_key = null)
	{
		if (empty($machine_key) || !array_key_exists($machine_key, self::$machine_keys)) {
			$machine_key = $this->default_machine_key();
		}
		$db = $this->GetModel();
		$db->where('machine_key', $machine_key);
		$db->orderBy('urutan', 'ASC')->orderBy('id', 'ASC');
		$records = $db->get($this->tablename);
		$this->view->page_title = 'Master Data Part Mesin';
		$this->view->selected_machine = $machine_key;
		return $this->render_view('master_part/list.php', array('records' => $records));
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
			$this->fields = array('machine_key', 'field_name', 'label', 'section', 'metode', 'alat', 'standard', 'durasi', 'pelaksanaan', 'highlight', 'image_path');
			$postdata = $this->format_request_data($formdata);
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
				if ($db->has($this->tablename)) {
					$this->view->page_error[] = 'Part dengan Field Name ini sudah ada buat mesin tersebut.';
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
		if ($formdata) {
			//Urutan gak ikut diedit di sini -- cuma diatur lewat drag-and-drop di
			//list (lihat reorder()). Field deskriptif juga gak di-sanitize_string,
			//sama alasannya kayak di add() -- lihat catatan di sana.
			$this->fields = array('label', 'section', 'metode', 'alat', 'standard', 'durasi', 'pelaksanaan', 'highlight', 'image_path');
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array('label' => 'required');
			$this->sanitize_array = array();
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			//foto lama dibiarkan (gak dihapus) kalau admin gak upload foto baru pas edit.
			if (empty($modeldata['image_path'])) { unset($modeldata['image_path']); }
			else { $modeldata['image_path'] = $this->relative_image_path($modeldata['image_path']); }
			if ($this->validated()) {
				$modeldata['updated_at'] = datetime_now();
				$db->where('id', $rec_id);
				$bool = $db->update($this->tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) {
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

	/**
	 * Hapus row master_part = part gak muncul lagi di form Add AM. Kolom fisik
	 * di tabel mesin & data historis yang udah ke-submit SENGAJA gak ikut
	 * dihapus/di-DROP (safe, non-destruktif) -- kalau field_name yang sama
	 * ditambah lagi belakangan, datanya lama tetap ada.
	 */
	function delete($rec_id = null)
	{
		Csrf::cross_check();
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$ids = array_map('trim', explode(',', $rec_id));
		//Catat machine_key SEBELUM dihapus, biar bisa balik ke list mesin yang bener.
		$db->where('id', $ids, 'in');
		$owner_row = $db->getOne($this->tablename, 'machine_key');
		$back_url = 'master_part/index/' . (!empty($owner_row['machine_key']) ? $owner_row['machine_key'] : '');
		$db->where('id', $ids, 'in');
		if ($db->delete($this->tablename)) {
			$this->write_to_log('delete', 'true');
			$this->set_flash_msg('Part berhasil dihapus', 'success');
		} else {
			$this->set_flash_msg($db->getLastError(), 'danger');
		}
		return $this->redirect($back_url);
	}
}
