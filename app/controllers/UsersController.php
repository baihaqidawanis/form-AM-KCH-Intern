<?php 
/**
 * Users Page Controller
 * @category  Controller
 */
class UsersController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "users";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function index($fieldname = null , $fieldvalue = null){
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array("id_user", 
			"nama", 
			"email", 
			"username", 
			"area", 
			"mesin", 
			"account_status", 
			"user_role_id", 
			"pict");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			// id_user & user_role_id integer -- Postgres gak izinin LIKE ke integer
			// tanpa CAST eksplisit (beda dari MySQL yang izinin implicit), sama
			// kelas bug yang ketemu di audit_log.log_id (Round 31).
			$search_condition = "(
				CAST(users.id_user AS VARCHAR) ILIKE ? OR
				users.nama ILIKE ? OR
				users.email ILIKE ? OR
				users.username ILIKE ? OR
				users.area ILIKE ? OR
				users.mesin ILIKE ? OR
				users.account_status ILIKE ? OR
				CAST(users.user_role_id AS VARCHAR) ILIKE ? OR
				users.pict ILIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "users/search.php";
		}
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("users.id_user", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
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
		if($db->getLastError()){
			$this->set_page_error();
		}
		$page_title = $this->view->page_title = "Users";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		$this->render_view("users/list.php", $data); //render the full page
	}
	/**
     * View record detail 
	 * @param $rec_id (select record by table primary key) 
     * @param $value value (select record by value of field name(rec_id))
     * @return BaseView
     */
	function view($rec_id = null, $value = null){
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = urldecode($rec_id);
		$tablename = $this->tablename;
		$fields = array("id_user", 
			"nama", 
			"email", 
			"username", 
			"area", 
			"mesin", 
			"account_status", 
			"user_role_id", 
			"pict");
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("users.id_user", $rec_id); //select record based on primary key
		}
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Users";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		}
		else{
			if($db->getLastError()){
				$this->set_page_error();
			}
			else{
				$this->set_page_error("No record found");
			}
		}
		return $this->render_view("users/view.php", $record);
	}
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function add($formdata = null){
		if($formdata){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
			$fields = $this->fields = array("nama","email","username","area","mesin","password","account_status","user_role_id","pict");
			$postdata = $this->format_request_data($formdata);
			$cpassword = $postdata['confirm_password'];
			$password = $postdata['password'];
			if($cpassword != $password){
				$this->view->page_error[] = "Your password confirmation is not consistent";
			}
			$this->rules_array = array(
				'nama' => 'required',
				'email' => 'required|valid_email',
				'username' => 'required',
				'area' => 'required',
				'mesin' => 'required',
				'password' => 'required',
				'account_status' => 'required',
				'user_role_id' => 'required',
				'pict' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'email' => 'sanitize_string',
				'username' => 'sanitize_string',
				'area' => 'sanitize_string',
				'mesin' => 'sanitize_string',
				'account_status' => 'sanitize_string',
				'user_role_id' => 'sanitize_string',
				'pict' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$password_text = $modeldata['password'];
			//NIK di-uppercase biar gak ada 2 akun beda cuma gara-gara huruf besar/kecil.
			if (!empty($modeldata['username'])) {
				$modeldata['username'] = $this->modeldata['username'] = strtoupper($modeldata['username']);
			}
			//URS: username akun baru wajib format NIK, password wajib kompleksitas tertentu
			if (!empty($modeldata['username']) && !is_valid_nik_username($modeldata['username'])) {
				$this->view->page_error[] = "Username harus berupa NIK (Nomor Induk Karyawan): huruf dan/atau angka, maksimal 11 karakter.";
			}
			if (!empty($password_text) && !is_valid_password_complexity($password_text)) {
				$this->view->page_error[] = "Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.";
			}
			//update modeldata with the password hash
			$modeldata['password'] = $this->modeldata['password'] = password_hash($password_text , PASSWORD_DEFAULT);
			//Check if Duplicate Record Already Exit In The Database
			$db->where("email", $modeldata['email']);
			if($db->has($tablename)){
				$this->view->page_error[] = $modeldata['email']." Already exist!";
			}
			//Check if Duplicate Record Already Exit In The Database
			$db->where("username", $modeldata['username']);
			if($db->has($tablename)){
				$this->view->page_error[] = $modeldata['username']." Already exist!";
			}
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					$this->write_to_log("add", "true");
					$this->set_flash_msg("Record added successfully", "success");
					return	$this->redirect("users");
				}
				else{
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Add New Users";
		$this->render_view("users/add.php");
	}
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function edit($rec_id = null, $formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//Super Admin cuma boleh diedit diri sendiri -- Administrator lain (termasuk
		//sesama role_id=1) ditolak ubah apapun ke akun ini (URS-adjacent, disetujui
		//mentor: satu Super Admin gak bisa "dijatuhkan" admin lain).
		if (is_super_admin_user($rec_id) && intval($rec_id) !== intval(USER_ID)) {
			http_response_code(403);
			return $this->render_view('errors/forbidden.php', null, 'info_layout.php');
		}
		 //editable fields
		$fields = $this->fields = array("id_user","nama","username","area","mesin","account_status","user_role_id","pict");
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nama' => 'required',
				'username' => 'required',
				'area' => 'required',
				'mesin' => 'required',
				'account_status' => 'required',
				'user_role_id' => 'required',
				'pict' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'username' => 'sanitize_string',
				'area' => 'sanitize_string',
				'mesin' => 'sanitize_string',
				'account_status' => 'sanitize_string',
				'user_role_id' => 'sanitize_string',
				'pict' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			//Check if Duplicate Record Already Exit In The Database
			if(isset($modeldata['username'])){
				$db->where("username", $modeldata['username'])->where("id_user", $rec_id, "!=");
				if($db->has($tablename)){
					$this->view->page_error[] = $modeldata['username']." Already exist!";
				}
			} 
			if($this->validated()){
				$db->where("users.id_user", $rec_id);
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if($bool && $numRows){
					$this->write_to_log("edit", "true");
					$this->set_flash_msg("Record updated successfully", "success");
					return $this->redirect("users");
				}
				else{
					if($db->getLastError()){
						$this->set_page_error();
					}
					elseif(!$numRows){
						//not an error, but no record was updated
						$page_error = "No record updated";
						$this->set_page_error($page_error);
						$this->set_flash_msg($page_error, "warning");
						return	$this->redirect("users");
					}
				}
			}
		}
		$db->where("users.id_user", $rec_id);
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "Edit  Users";
		if(!$data){
			$this->set_page_error();
		}
		return $this->render_view("users/edit.php", $data);
	}
	/**
     * Update single field
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editfield($rec_id = null, $formdata = null){
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//Sama kayak edit() -- Super Admin cuma boleh diedit diri sendiri.
		if (is_super_admin_user($rec_id) && intval($rec_id) !== intval(USER_ID)) {
			render_error('Akun ini dilindungi -- cuma Super Admin sendiri yang boleh mengubahnya.');
			return null;
		}
		//editable fields
		$fields = $this->fields = array("id_user","nama","username","area","mesin","account_status","user_role_id","pict");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'nama' => 'required',
				'username' => 'required',
				'area' => 'required',
				'mesin' => 'required',
				'account_status' => 'required',
				'user_role_id' => 'required',
				'pict' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'username' => 'sanitize_string',
				'area' => 'sanitize_string',
				'mesin' => 'sanitize_string',
				'account_status' => 'sanitize_string',
				'user_role_id' => 'sanitize_string',
				'pict' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			//Check if Duplicate Record Already Exit In The Database
			if(isset($modeldata['username'])){
				$db->where("username", $modeldata['username'])->where("id_user", $rec_id, "!=");
				if($db->has($tablename)){
					$this->view->page_error[] = $modeldata['username']." Already exist!";
				}
			} 
			if($this->validated()){
				$db->where("users.id_user", $rec_id);
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if($bool && $numRows){
					$this->write_to_log("edit", "true");
					return render_json(
						array(
							'num_rows' =>$numRows,
							'rec_id' =>$rec_id,
						)
					);
				}
				else{
					if($db->getLastError()){
						$page_error = $db->getLastError();
					}
					elseif(!$numRows){
						$page_error = "No record updated";
					}
					render_error($page_error);
				}
			}
			else{
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
	function delete($rec_id = null){
		Csrf::cross_check();
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
		//form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		//Super Admin gak boleh dihapus sama sekali (bahkan oleh dirinya sendiri) --
		//akun ini permanen, satu-satunya "root" sistem.
		foreach ($arr_rec_id as $one_id) {
			if (is_super_admin_user($one_id)) {
				$this->set_flash_msg('Akun Super Admin gak bisa dihapus.', 'danger');
				return $this->redirect('users');
			}
		}
		$db->where("users.id_user", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
		if($bool){
			$this->write_to_log("delete", "true");
			$this->set_flash_msg("Record deleted successfully", "success");
		}
		elseif($db->getLastError()){
			$page_error = $db->getLastError();
			$this->set_flash_msg($page_error, "danger");
		}
		return	$this->redirect("users");
	}
}
