<?php
/**
 * Sig Page Controller
 * @category  Controller
 */
class SigController extends SecureController
{
	function __construct()
	{
		parent::__construct();
		$this->tablename = "sig";
	}
	/**
	 * List page records
	 * @param $fieldname (filter record by a field) 
	 * @param $fieldvalue (filter field value)
	 * @return BaseView
	 */
	function index($fieldname = null, $fieldvalue = null)
	{
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array(
			"sig.id_sig",
			"sig.sealing_cross_dan_vertikal",
			"sig.guarding_akrilik",
			"sig.jalur_conveyor",
			"sig.antistatic",
			"sig.vacuum_hood",
			"sig.tekanan_angin_suplai",
			"sig.value_tekanan_angin",
			"sig.jarak_slider_dengan_nozzle",
			"sig.rol_penarik_sachet_dan_foil_slitting_shim",
			"sig.pisau_belah",
			"sig.modul_pisau",
			"sig.inkjet",
			"sig.kendala",
			"sig.created_at",
			"sig.updated_at",
			"sig.user_create",
			"sig.user_approve",
			"sig.approval",
			"sig.perubahan",
			"sig.user_perubah",
			"sig.tanggal_perubahan",
			"sig.kategori_tag",
			"sig.korelasi_tag",
			"sig.kategori_ketidaksesuaian",
			"sig.id_tagging"
		);
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if (!empty($request->search)) {
			$text = trim($request->search);
			$search_condition = "(
				sig.id_sig LIKE ? OR
				sig.sealing_cross_dan_vertikal LIKE ? OR
				sig.guarding_akrilik LIKE ? OR
				sig.jalur_conveyor LIKE ? OR
				sig.antistatic LIKE ? OR
				sig.vacuum_hood LIKE ? OR
				sig.tekanan_angin_suplai LIKE ? OR
				sig.value_tekanan_angin LIKE ? OR
				sig.jarak_slider_dengan_nozzle LIKE ? OR
				sig.rol_penarik_sachet_dan_foil_slitting_shim LIKE ? OR
				sig.pisau_belah LIKE ? OR
				sig.modul_pisau LIKE ? OR
				sig.inkjet LIKE ? OR
				sig.kendala LIKE ? OR
				sig.created_at LIKE ? OR
				sig.updated_at LIKE ? OR
				sig.user_create LIKE ? OR
				sig.user_approve LIKE ? OR
				sig.approval LIKE ? OR
				sig.perubahan LIKE ? OR
				sig.user_perubah LIKE ? OR
				sig.tanggal_perubahan LIKE ?
			)";
			// Jumlah placeholder "?" di atas = 22, jadi param juga harus 22.
			$search_params = array_fill(0, 22, "%$text%");
			//setting search conditions
			$db->where($search_condition, $search_params);
			//template to use when ajax search
			$this->view->search_template = "sig/search.php";
		}
		$db->join("mesin", "sig.Mesin = mesin.id", "INNER");

		// --- Filter Tanggal (created_at) ---
		if (!empty($request->date_from)) {
			$db->where("sig.created_at", trim($request->date_from) . " 00:00:00", ">=");
		}
		if (!empty($request->date_to)) {
			$db->where("sig.created_at", trim($request->date_to) . " 23:59:59", "<=");
		}
		// --- Filter Nama Mesin ---
		if (!empty($request->mesin)) {
			$db->where("sig.Mesin", $request->mesin);
		}

		if (!empty($request->orderby)) {
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		} else {
			$db->orderBy("sig.id_sig", ORDER_TYPE);
		}
		if ($fieldname) {
			$db->where($fieldname, $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		$records_count = count($records);
		$total_records = intval($tc->totalCount);
		$page_limit = $pagination[1];
		$total_pages = ceil($total_records / $page_limit);
		$data = new stdClass;
		$data->records = $records;
		$data->record_count = $records_count;
		$data->total_records = $total_records;
		$data->total_page = $total_pages;
		if ($db->getLastError()) {
			$this->set_page_error();
		}
		$page_title = $this->view->page_title = "SIG";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "landscape";
		$this->render_view("sig/list.php", $data); //render the full page
	}
	/**
	 * View record detail 
	 * @param $rec_id (select record by table primary key) 
	 * @param $value value (select record by value of field name(rec_id))
	 * @return BaseView
	 */
	function view($rec_id = null, $value = null)
	{
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = urldecode($rec_id);
		$tablename = $this->tablename;
		$fields = array(
			"sig.id_sig",
			"mesin.nama_mesin as nm_mesin",
			"sig.sealing_cross_dan_vertikal",
			"sig.guarding_akrilik",
			"sig.jalur_conveyor",
			"sig.antistatic",
			"sig.vacuum_hood",
			"sig.tekanan_angin_suplai",
			"sig.value_tekanan_angin",
			"sig.jarak_slider_dengan_nozzle",
			"sig.rol_penarik_sachet_dan_foil_slitting_shim",
			"sig.pisau_belah",
			"sig.modul_pisau",
			"sig.inkjet",
			// "sig.kendala",
			"sig.created_at",
			"sig.updated_at",
			"sig.user_create",
			"sig.user_approve",
			"sig.approval",
			"sig.perubahan",
			"sig.user_perubah",
			"sig.tanggal_perubahan",
			// "sig.kategori_tag",
			// "sig.korelasi_tag",
			// "sig.kategori_ketidaksesuaian",
			// "sig.id_tagging",
			"sig.mesin"
		);
		if ($value) {
			$db->where($rec_id, urldecode($value)); //select record based on field name
		} else {
			$db->where("sig.id_sig", $rec_id);
			; //select record based on primary key
		}
		$db->join("mesin", "sig.Mesin = mesin.id", "INNER");
		$record = $db->getOne($tablename, $fields);

		if ($record) {
			$kendala_list = $db->rawQuery("
                SELECT k.*, 
                       t1.kategori_tag AS teks_kategori,
                       t2.nama AS teks_korelasi,
                       t3.kategori AS teks_ketidaksesuaian,
					   t4.nama AS teks_klasifikasi
                FROM kendala_sig k
                LEFT JOIN tag t1 ON k.kategori_tag = t1.id
                LEFT JOIN korelasi t2 ON k.korelasi_tag = t2.id
                LEFT JOIN kategori t3 ON k.kategori_ketidaksesuaian = t3.id
                LEFT JOIN klasifikasi t4 ON k.klasifikasi_tag = t4.id
                WHERE k.id_am = ?
            ", array($record['id_sig']));

			$abnormalitas = array();
			if (!empty($kendala_list)) {
				foreach ($kendala_list as $k) {
					// Make 'nama_bagian' an array key to make matching easier in the view.
					$abnormalitas[$k['nama_bagian']] = $k;
				}
			}
			// Masukkan array abnormalitas ke dalam record utama
			$record['abnormalitas'] = $abnormalitas;
		}

		if ($record) {
			$this->write_to_log("view", "true");
			$page_title = $this->view->page_title = "View AM Mesin SIG";
			$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
			$this->view->report_title = $page_title;
			$this->view->report_layout = "report_layout.php";
			$this->view->report_paper_size = "F4";
			$this->view->report_orientation = "portrait";
		} else {
			if ($db->getLastError()) {
				$this->set_page_error();
			} else {
				$this->set_page_error("No record found");
			}
		}
		return $this->render_view("sig/view.php", $record);
	}
	/**
	 * Insert new record to the database table
	 * @param $formdata array() from $_POST
	 * @return BaseView
	 */
	function add($formdata = null)
	{
		if ($formdata) {
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
			$fields = $this->fields = array("Mesin", "Sealing_Cross_dan_Vertikal", "Guarding_Akrilik", "Jalur_Conveyor", "Antistatic", "Vacuum_Hood", "Tekanan_Angin_Suplai", "Value_Tekanan_Angin", "Jarak_Slider_dengan_Nozzle", "Rol_Penarik_Sachet_dan_Foil_Slitting_Shim", "Pisau_Belah", "Modul_Pisau", "Inkjet", "created_at", "user_create");
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'Mesin' => 'required',
				'Sealing_Cross_dan_Vertikal' => 'required',
				'Guarding_Akrilik' => 'required',
				'Jalur_Conveyor' => 'required',
				'Antistatic' => 'required',
				'Vacuum_Hood' => 'required',
				'Tekanan_Angin_Suplai' => 'required',
				'Value_Tekanan_Angin' => 'required',
				'Jarak_Slider_dengan_Nozzle' => 'required',
				'Rol_Penarik_Sachet_dan_Foil_Slitting_Shim' => 'required',
				'Pisau_Belah' => 'required',
				'Modul_Pisau' => 'required',
				'Inkjet' => 'required',
			);
			$this->sanitize_array = array(
				'Mesin' => 'sanitize_string',
				'Sealing_Cross_dan_Vertikal' => 'sanitize_string',
				'Guarding_Akrilik' => 'sanitize_string',
				'Jalur_Conveyor' => 'sanitize_string',
				'Antistatic' => 'sanitize_string',
				'Vacuum_Hood' => 'sanitize_string',
				'Tekanan_Angin_Suplai' => 'sanitize_string',
				'Value_Tekanan_Angin' => 'sanitize_string',
				'Jarak_Slider_dengan_Nozzle' => 'sanitize_string',
				'Rol_Penarik_Sachet_dan_Foil_Slitting_Shim' => 'sanitize_string',
				'Pisau_Belah' => 'sanitize_string',
				'Modul_Pisau' => 'sanitize_string',
				'Inkjet' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$modeldata['created_at'] = datetime_now();
			$modeldata['user_create'] = USER_NAME;
			if ($this->validated()) {
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if ($rec_id) {
					$this->write_to_log("add", "true");
					# Statement to execute after adding record
					$mesin_id = $modeldata['Mesin'];
					$now = datetime_now();

					$daftar_bagian = array(
						"Sealing_Cross_dan_Vertikal",
						"Guarding_Akrilik",
						"Jalur_Conveyor",
						"Antistatic",
						"Vacuum_Hood",
						"Tekanan_Angin_Suplai",
						"Jarak_Slider_dengan_Nozzle",
						"Rol_Penarik_Sachet_dan_Slitting_Shim",
						"Pisau_Belah",
						"Modul_Pisau",
						"Inkjet",
					);

					foreach ($daftar_bagian as $bagian) {
						// Check if the constraints for this section are filled (meaning the condition is NOK)
						if (!empty($_POST["Kendala_" . $bagian])) {

							$detail_data = array(
								"id_am" => $rec_id,
								"mesin" => $mesin_id,
								"nama_bagian" => $bagian,
								"kendala" => $_POST["Kendala_" . $bagian],
								"kategori_tag" => $_POST["kategori_tag_" . $bagian],
								"korelasi_tag" => $_POST["korelasi_tag_" . $bagian],
								"kategori_ketidaksesuaian" => $_POST["kategori_ketidaksesuaian_" . $bagian],
								"klasifikasi_tag" => $_POST["klasifikasi_tag_" . $bagian],
								"created_at" => $now
							);

							$db->insert("kendala_sig", $detail_data);

							// (Catatan: Jika kamu masih butuh mencatat ini ke tabel `tag_am` seperti kode lamamu, 
							// kamu bisa menambahkan $db->insert("tag_am", [...]); di dalam blok if ini juga)
						}
					}
					// if (!empty($kendala)) {
					// 	$botToken = '***TELEGRAM_TOKEN_REDACTED***';
					// 	$chatID = '-1002428961148';
					// 	// Construct the message
					// 	$message = "Autonomous Maintenance sudah diisi oleh operator\n\n";
					// 	$message .= "Line           :" . $row['line_name'] . "\n";
					// 	$message .= "Nama Mesin     : RVS\n";
					// 	$message .= "Lokasi Mesin   : Lantai 1\n";
					// 	$message .= "Kondisi        : $kendala\n\n";
					// 	$message .= "Mohon segera Approve melalui website Produksi Cikarang\n";
					// 	$keyboard = [
					// 		[
					// 			['text' => 'Buka Website', 'url' => 'http://10.127.17.10/produksicikarang/approval']
					// 		]
					// 	];
					// 	$replyMarkup = json_encode(['inline_keyboard' => $keyboard]);
					// 	$url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatID&text=" . urlencode($message) . "&reply_markup=" . urlencode($replyMarkup);
					// 	$ch = curl_init();
					// 	curl_setopt($ch, CURLOPT_URL, $url);
					// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					// 	$response = curl_exec($ch);
					// 	curl_close($ch);
					// 	// Send notification to assigner if it's a red tag
					// 	$kategori_tag = $_POST["kategori_tag"];
					// 	// Getting the last inserted id_tagging
					// 	$result = $db->rawQueryOne("SELECT id_tagging FROM rvs ORDER BY id_rvs DESC LIMIT 1");
					// 	$id_tagging = $result['id_tagging'];
					// 	if ($kategori_tag == 1) {
					// 		// Construct the second custom message
					// 		$secondMessage = "<b>LAPORAN RED TAGGING AM BARU</b>\n";
					// 		$secondMessage .= "=========================\n\n";
					// 		$secondMessage .= "<b>- Detail Red Tagging -\n\n</b>";
					// 		$secondMessage .= "Lokasi Mesin   : Lantai 1\n";
					// 		$secondMessage .= "Line           :" . $row['line_name'] . "\n";
					// 		$secondMessage .= "Nama Mesin     : RVS\n";
					// 		$secondMessage .= "Kondisi        : $kendala\n\n";
					// 		// Create an assigner link
					// 		$timestampLink = "http://10.127.17.10/breakdown_management/tag_filling_kemas/edit_assigner/" . $id_tagging;
					// 		$secondMessage .= "Konfirmasi Assigner : <a href='$timestampLink'>Assign Sekarang</a>\n";
					// 		// Send the second message to another chat
					// 		$telegramData = [
					// 			'text' => $secondMessage,
					// 			'chat_id' => '-4547166344', // Group Assigner
					// 			'parse_mode' => 'HTML'
					// 		];
					// 		// Send the message using the Telegram API
					// 		$secondUrl = "https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query($telegramData);
					// 		file_get_contents($secondUrl); // Assuming allow_url_fopen is enabled
					// 	}
					// }
					# End of after add statement
					$this->set_flash_msg("Berhasil tambah AM", "success");
					return $this->redirect("sig");
				} else {
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Add New AM SIG";
		$this->render_view("sig/add.php");
	}
	/**
	 * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
	 * @return array
	 */
	function edit($rec_id = null, $formdata = null)
	{
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
		$fields = $this->fields = array("id_sig", "updated_at", "approval", "user_approve", "perubahan", "user_perubah", "tanggal_perubahan");
		if ($formdata) {
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'approval' => 'required',
			);
			$this->sanitize_array = array(
				'approval' => 'sanitize_string',
				'tanggal_perubahan' => 'sanitize_string', //ganti tanggal approve
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$modeldata['tanggal_perubahan'] = datetime_now();
			$modeldata['user_approve'] = USER_NAME;
			if ($this->validated()) {
				$db->where("sig.id_sig", $rec_id);
				;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if ($bool && $numRows) {
					$this->write_to_log("edit", "true");
					$this->set_flash_msg("Record updated successfully", "success");
					return $this->redirect("sig");
				} else {
					if ($db->getLastError()) {
						$this->set_page_error();
					} elseif (!$numRows) {
						//not an error, but no record was updated
						$page_error = "No record updated";
						$this->set_page_error($page_error);
						$this->set_flash_msg($page_error, "warning");
						return $this->redirect("sig");
					}
				}
			}
		}
		$db->where("sig.id_sig", $rec_id);
		;
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "Approve AM SIG";
		if (!$data) {
			$this->set_page_error();
		}
		return $this->render_view("sig/edit.php", $data);
	}
	/**
	 * Update single field
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
	 * @return array
	 */
	function editfield($rec_id = null, $formdata = null)
	{
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
		$fields = $this->fields = array("id_sig", "updated_at", "approval", "user_approve", "perubahan", "user_perubah", "tanggal_perubahan");
		$page_error = null;
		if ($formdata) {
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'approval' => 'required',
			);
			$this->sanitize_array = array(
				'approval' => 'sanitize_string',
				'perubahan' => 'sanitize_string',
				'user_perubah' => 'sanitize_string',
				'tanggal_perubahan' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if ($this->validated()) {
				$db->where("sig.id_sig", $rec_id);
				;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if ($bool && $numRows) {
					$this->write_to_log("edit", "true");
					return render_json(
						array(
							'num_rows' => $numRows,
							'rec_id' => $rec_id,
						)
					);
				} else {
					if ($db->getLastError()) {
						$page_error = $db->getLastError();
					} elseif (!$numRows) {
						$page_error = "No record updated";
					}
					render_error($page_error);
				}
			} else {
				render_error($this->view->page_error);
			}
		}
		return null;
	}
	/**
	 * Delete record from the database
	 * Support multi delete by separating record id by comma.
	 * @return BaseView
	 */
	function delete($rec_id = null)
	{
		Csrf::cross_check();
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
		//form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		$db->where("sig.id_sig", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
		if ($bool) {
			$this->write_to_log("delete", "true");
			$this->set_flash_msg("Record deleted successfully", "success");
		} elseif ($db->getLastError()) {
			$page_error = $db->getLastError();
			$this->set_flash_msg($page_error, "danger");
		}
		return $this->redirect("sig");
	}
	/**
	 * List page records
	 * @param $fieldname (filter record by a field) 
	 * @param $fieldvalue (filter field value)
	 * @return BaseView
	 */
	function list2($fieldname = null, $fieldvalue = null)
	{
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array(
			"sig.id_sig",
			"mesin.nama_mesin AS nm_mesin",
			"sig.sealing_cross_dan_vertikal",
			"sig.guarding_akrilik",
			"sig.jalur_conveyor",
			"sig.antistatic",
			"sig.vacuum_hood",
			"sig.tekanan_angin_suplai",
			"sig.value_tekanan_angin",
			"sig.jarak_slider_dengan_nozzle",
			"sig.rol_penarik_sachet_dan_foil_slitting_shim",
			"sig.pisau_belah",
			"sig.modul_pisau",
			"sig.inkjet",
			"sig.kendala",
			"sig.created_at",
			"sig.updated_at",
			"sig.user_create",
			"sig.user_approve",
			"sig.approval",
			"sig.user_perubah",
			"sig.tanggal_perubahan",
		);
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if (!empty($request->search)) {
			$text = trim($request->search);
			$search_condition = "(
				sig.id_sig LIKE ? OR
				mesin.nama_mesin LIKE ? OR
				sig.sealing_cross_dan_vertikal LIKE ? OR
				sig.guarding_akrilik LIKE ? OR
				sig.jalur_conveyor LIKE ? OR
				sig.antistatic LIKE ? OR
				sig.vacuum_hood LIKE ? OR
				sig.tekanan_angin_suplai LIKE ? OR
				sig.value_tekanan_angin LIKE ? OR
				sig.jarak_slider_dengan_nozzle LIKE ? OR
				sig.rol_penarik_sachet_dan_foil_slitting_shim LIKE ? OR
				sig.pisau_belah LIKE ? OR
				sig.modul_pisau LIKE ? OR
				sig.inkjet LIKE ? OR
				sig.kendala LIKE ? OR
				sig.created_at LIKE ? OR
				sig.updated_at LIKE ? OR
				sig.user_create LIKE ? OR
				sig.user_approve LIKE ? OR
				sig.approval LIKE ? OR
				sig.perubahan LIKE ? OR
				sig.user_perubah LIKE ? OR
				sig.tanggal_perubahan LIKE ?
			)";
			// Jumlah placeholder "?" di atas = 23, jadi param juga harus 23.
			// Pakai array_fill supaya jumlahnya otomatis selalu sinkron.
			$search_params = array_fill(0, 23, "%$text%");
			//setting search conditions
			$db->where($search_condition, $search_params);
			//template to use when ajax search
			$this->view->search_template = "sig/search.php";
		}
		$db->join("mesin", "sig.Mesin = mesin.id", "INNER");

		// --- Filter Tanggal (created_at) ---
		if (!empty($request->date_from)) {
			$db->where("sig.created_at", trim($request->date_from) . " 00:00:00", ">=");
		}
		if (!empty($request->date_to)) {
			$db->where("sig.created_at", trim($request->date_to) . " 23:59:59", "<=");
		}
		// --- Filter Nama Mesin ---
		if (!empty($request->mesin)) {
			$db->where("sig.Mesin", $request->mesin);
		}

		if (!empty($request->orderby)) {
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		} else {
			$db->orderBy("sig.id_sig", ORDER_TYPE);
		}
		if ($fieldname) {
			$db->where($fieldname, $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		$records_count = count($records);
		$total_records = intval($tc->totalCount);
		$page_limit = $pagination[1];
		$total_pages = ceil($total_records / $page_limit);
		$data = new stdClass;
		$data->records = $records;
		$data->record_count = $records_count;
		$data->total_records = $total_records;
		$data->total_page = $total_pages;
		if ($db->getLastError()) {
			$this->set_page_error();
		}
		$page_title = $this->view->page_title = "Sig";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		$this->render_view("sig/list2.php", $data); //render the full page
	}

	/**
	 * Update isi data oleh user pembuat
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
	 * @return array
	 */
	function edit_data($rec_id = null, $formdata = null)
	{
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;

		// 1. Tentukan field apa saja yang boleh diupdate dari tabel
		$fields = $this->fields = array(
			"sealing_cross_dan_vertikal",
			"guarding_akrilik",
			"jalur_conveyor",
			"antistatic",
			"vacuum_hood",
			"tekanan_angin_suplai",
			"value_tekanan_angin",
			"jarak_slider_dengan_nozzle",
			"rol_penarik_sachet_dan_foil_slitting_shim",
			"pisau_belah",
			"modul_pisau",
			"inkjet",
			"perubahan",         // Tangkap log perubahan
			"updated_at",
			"user_perubah",
			"tanggal_perubahan"
		);

		if ($formdata) {
			$postdata = $this->format_request_data($formdata);

			// 2. Set Rules (Minimal perubahan harus diisi)
			$this->rules_array = array(
				'perubahan' => 'required',
			);

			// 3. Set Sanitasi input
			$this->sanitize_array = array(
				'sealing_cross_dan_vertikal' => 'sanitize_string',
				'guarding_akrilik' => 'sanitize_string',
				'jalur_conveyor' => 'sanitize_string',
				'antistatic' => 'sanitize_string',
				'vacuum_hood' => 'sanitize_string',
				'tekanan_angin_suplai' => 'sanitize_string',
				'value_tekanan_angin' => 'sanitize_string',
				'jarak_slider_dengan_nozzle' => 'sanitize_string',
				'rol_penarik_sachet_dan_foil_slitting_shim' => 'sanitize_string',
				'pisau_belah' => 'sanitize_string',
				'modul_pisau' => 'sanitize_string',
				'inkjet' => 'sanitize_string',
				'perubahan' => 'sanitize_string',
			);

			$modeldata = $this->modeldata = $this->validate_form($postdata);

			// 4. Set nilai otomatis untuk tracking
			$modeldata['updated_at'] = datetime_now();
			$modeldata['user_perubah'] = USER_NAME; // Ambil session user aktif
			// $modeldata['tanggal_perubahan'] = datetime_now();

			if ($this->validated()) {
				$db->where("sig.id_sig", $rec_id);

				// 5. Lakukan Update
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();

				if ($bool) {
					// --- TAMBAHAN UPDATE KENDALA ---
					$db->where("id_sig", $rec_id);
					$sig_data = $db->getOne($tablename, "Mesin");
					$mesin_id = $sig_data['Mesin'];

					$daftar_bagian = array(
						"Sealing_Cross_dan_Vertikal",
						"Guarding_Akrilik",
						"Jalur_Conveyor",
						"Antistatic",
						"Vacuum_Hood",
						"Tekanan_Angin_Suplai",
						"Jarak_Slider_dengan_Nozzle",
						"Rol_Penarik_Sachet_dan_Foil_Slitting_Shim",
						"Pisau_Belah",
						"Modul_Pisau",
						"Inkjet"
					);

					// A. Hapus data kendala lama
					$db->where("id_am", $rec_id);
					$db->delete("kendala_sig");

					// B. Insert data kendala baru
					foreach ($daftar_bagian as $bagian) {
						if (!empty($_POST["Kendala_" . $bagian])) {
							$detail_data = array(
								"id_am" => $rec_id,
								"mesin" => $mesin_id,
								"nama_bagian" => $bagian,
								"kendala" => $_POST["Kendala_" . $bagian],
								"kategori_tag" => $_POST["kategori_tag_" . $bagian],
								"korelasi_tag" => $_POST["korelasi_tag_" . $bagian],
								"kategori_ketidaksesuaian" => $_POST["kategori_ketidaksesuaian_" . $bagian],
								"klasifikasi_tag" => $_POST["klasifikasi_tag_" . $bagian],
								"created_at" => datetime_now()
							);
							$db->insert("kendala_sig", $detail_data);
						}
					}
					// --- AKHIR TAMBAHAN KENDALA ---

					$this->write_to_log("edit_data", "true");
				} else {
					if ($db->getLastError()) {
						$this->set_page_error();
					} elseif (!$numRows) {
						$page_error = "Tidak ada perubahan data yang disimpan";
						$this->set_page_error($page_error);
						$this->set_flash_msg($page_error, "warning");
						return $this->redirect("sig/view/$rec_id");
					}
				}
			}
		}

		// Jika request GET, panggil data untuk ditampilkan di form edit_data.php
		$db->where("sig.id_sig", $rec_id);
		// Kita butuh join dengan mesin untuk menampilkan nama mesin di UI (seperti di view)
		$db->join("mesin", "sig.Mesin = mesin.id", "INNER");

		// Ambil data menggunakan query yang hampir sama dengan fungsi view()
		$record = $db->getOne($tablename, array(
			"sig.*",
			"mesin.nama_mesin as nm_mesin"
		));

		// (Opsional: Jika kamu ingin memunculkan detail abnormalitas seperti di View, 
		// kamu bisa meng-copy logika query 'kendala_sig' dari function view() ke sini)

		// AMBIL DATA KENDALA (ABNORMALITAS) AGAR MUNCUL DI FORM EDIT
		if ($record) {
			$kendala_list = $db->rawQuery("
                SELECT k.*, 
                       t1.kategori_tag AS teks_kategori,
                       t2.nama AS teks_korelasi,
                       t3.kategori AS teks_ketidaksesuaian,
					   t4.nama AS teks_klasifikasi
                FROM kendala_sig k
                LEFT JOIN tag t1 ON k.kategori_tag = t1.id
                LEFT JOIN korelasi t2 ON k.korelasi_tag = t2.id
                LEFT JOIN kategori t3 ON k.kategori_ketidaksesuaian = t3.id
                LEFT JOIN klasifikasi t4 ON k.klasifikasi_tag = t4.id
                WHERE k.id_am = ?
            ", array($record['id_sig']));

			$abnormalitas = array();
			if (!empty($kendala_list)) {
				foreach ($kendala_list as $k) {
					// Jadikan 'nama_bagian' sebagai key array agar mudah dipanggil di View
					$abnormalitas[$k['nama_bagian']] = $k;
				}
			}
			// Masukkan array abnormalitas ke dalam record utama yang dikirim ke view
			$record['abnormalitas'] = $abnormalitas;
		}

		$page_title = $this->view->page_title = "Edit Data AM SIG";
		if (!$record) {
			$this->set_page_error();
		}

		// Render halaman edit_data.php
		return $this->render_view("sig/edit_data.php", $record);
	}
}