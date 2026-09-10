<?php 
/**
 * Account Page Controller
 * @category  Controller
 */
class AccountController extends SecureController{
	function __construct(){
		parent::__construct(); 
		$this->tablename = "users";
	}
	/**
		* Index Action
		* @return null
		*/
	function index(){
		$db = $this->GetModel();
		$rec_id = $this->rec_id = USER_ID; //get current user id from session
		$db->where ("id_user", $rec_id);
		$tablename = $this->tablename;
		$fields = array("id_user", 
			"nama", 
			"email", 
			"username", 
			"area", 
			"mesin", 
			"account_status", 
			"user_role_id", 
			"pict",
			"paraf_image",
			"user_initials");
		$user = $db->getOne($tablename , $fields);
		if(!empty($user)){
			$page_title = $this->view->page_title = "My Account";
			$this->render_view("account/view.php", $user);
		}
		else{
			$this->set_page_error();
			$this->render_view("account/view.php");
		}
	}

	/** Render the digital-initial specimen tab in My Account. */
	function paraf(){
		$db = $this->GetModel();
		$db->where('id_user', USER_ID);
		$user = $db->getOne($this->tablename, array('id_user', 'username', 'nama', 'paraf_image', 'user_initials'));
		if (!$user && $db->getLastError()) {
			$this->set_page_error();
		}
		return $this->render_view('account/paraf.php', $user ?: array());
	}
	/**
     * Update user account record with formdata
	 * @param $formdata array() from $_POST
     * @return array
     */
	function edit($formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = USER_ID;
		$tablename = $this->tablename;
		$area_assignment_locked = in_array(intval(get_active_user('user_role_id')), array(4, 5), true);
		 //editable fields -- account_status & user_role_id SENGAJA tidak termasuk:
		 //user gak boleh naikkan role/aktivasi akun sendiri, itu wewenang
		 //Administrator lewat menu Users (UsersController::edit()).
		$fields = $this->fields = array("id_user","nama","username","area","mesin","pict");
		if ($area_assignment_locked) {
			$this->fields = array("id_user", "nama", "username", "mesin", "pict");
		}
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nama' => 'required',
				'username' => 'required',
				'mesin' => 'required',
			);
			if (!$area_assignment_locked) { $this->rules_array['area'] = 'required'; }
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'username' => 'sanitize_string',
				'mesin' => 'sanitize_string',
				'pict' => 'sanitize_string',
			);
			if (!$area_assignment_locked) { $this->sanitize_array['area'] = 'sanitize_string'; }
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if (isset($modeldata['pict']) && trim($modeldata['pict']) !== '') {
				$pict = ltrim(str_replace('\\', '/', trim($modeldata['pict'])), '/');
				if (!preg_match('#^uploads/(files|photos)/[^/]+\.(jpe?g|png|webp)$#i', $pict)) {
					$this->view->page_error[] = 'File foto profil tidak valid.';
				} else {
					$uploads_root = realpath(ROOT . 'uploads');
					$file = realpath(ROOT . $pict);
					if (!$uploads_root || !$file || strpos($file, $uploads_root . DIRECTORY_SEPARATOR) !== 0) {
						$this->view->page_error[] = 'File foto profil tidak ditemukan.';
					} else { $modeldata['pict'] = $pict; }
				}
			} else { unset($modeldata['pict']); }

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
				if($bool){
					$this->write_to_log("edit", "true");
					$this->set_flash_msg("Record updated successfully", "success");
					$db->where ("id_user", $rec_id);
					$user = $db->getOne($tablename , "*");
					set_session("user_data", $user);// update session with new user data
					return $this->redirect("account");
				}
				else{
					if($db->getLastError()){
						$this->set_page_error();
					}
					return $this->redirect("account");
				}
			}
		}
		$db->where("users.id_user", $rec_id);
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "My Account";
		if(!$data){
			$this->set_page_error();
		}
		return $this->render_view("account/edit.php", $data);
	}
	/**
     * Change account email
     * @return BaseView
     */
	function change_email($formdata = null){
		if($formdata){
			$email = trim((string)($formdata['email'] ?? ''));
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->set_page_error('Format email tidak valid.');
			} else {
				$db = $this->GetModel();
				$db->where('email', $email)->where('id_user', USER_ID, '!=');
				if ($db->has($this->tablename)) {
					$this->set_page_error('Email sudah digunakan.');
				} else {
					$db->where('id_user', USER_ID);
					if($db->update($this->tablename, array('email' => $email))){
						$user = get_session('user_data'); $user['email'] = $email; set_session('user_data', $user);
						$this->write_to_log('change_email', 'true');
						$this->set_flash_msg('Email berhasil diperbarui.', 'success');
						return $this->redirect('account');
					}
					$this->set_page_error($db->getLastError() ?: 'Email tidak dapat diperbarui.');
				}
			}
		}
		return $this->render_view('account/change_email.php');
	}

	function change_password($formdata = null){
		if ($formdata) {
			$old_password = (string)($formdata['old_password'] ?? '');
			$new_password = (string)($formdata['new_password'] ?? '');
			$confirm_password = (string)($formdata['confirm_password'] ?? '');
			$db = $this->GetModel(); $db->where('id_user', USER_ID);
			$user = $db->getOne($this->tablename, array('password'));
			if (!$user || !password_verify($old_password, $user['password'])) { $this->set_page_error('Password saat ini tidak sesuai.'); }
			elseif ($new_password !== $confirm_password) { $this->set_page_error('Konfirmasi password baru tidak sesuai.'); }
			elseif (!is_valid_password_complexity($new_password)) { $this->set_page_error('Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.'); }
			elseif (password_verify($new_password, $user['password'])) { $this->set_page_error('Password baru tidak boleh sama dengan password saat ini.'); }
			else {
				$db->where('id_user', USER_ID);
				$data = array('password' => password_hash($new_password, PASSWORD_DEFAULT), 'password_reset_key' => null, 'password_expire_date' => null, 'login_session_key' => null);
				if ($db->update($this->tablename, $data)) {
					clear_cookie('login_session_key'); session_regenerate_id(true);
					$this->write_to_log('change_password', 'true'); $this->set_flash_msg('Password berhasil diubah.', 'success');
					return $this->redirect('account');
				}
				$this->set_page_error($db->getLastError() ?: 'Password tidak dapat diperbarui.');
			}
		}
		return $this->render_view('account/change_password.php');
	}

	/**
	 * Save digital signature / paraf
	 * @return JSON
	 */
	function save_paraf() {
		$db = $this->GetModel();
		$userId = USER_ID;
		$request = $this->post ?? new stdClass;

		if (is_post_request()) {
			$parafImage = trim((string)($request->paraf_image ?? ''));
			$hasParafPayload = property_exists($request, 'paraf_image');
			$userInitials = strtoupper(trim((string)($request->user_initials ?? '')));
			$userInitials = substr(preg_replace('/[^A-Z0-9]/', '', $userInitials), 0, 10);

			if (!empty($parafImage)) {
				if (strlen($parafImage) > 500 * 1024) {
					render_json(array('success' => false, 'message' => 'Ukuran gambar paraf terlalu besar (maks 500KB).'));
					return;
				}
				if (!is_valid_base64_png_data_uri($parafImage)) {
					render_json(array('success' => false, 'message' => 'Format gambar paraf tidak valid (harus Base64 PNG).'));
					return;
				}
			}

			$updateData = array('user_initials' => !empty($userInitials) ? $userInitials : null);
			if ($hasParafPayload) {
				$updateData['paraf_image'] = !empty($parafImage) ? $parafImage : null;
			}

			$db->where('id_user', $userId);
			$res = $db->update($this->tablename, $updateData);
			if ($res) {
				// Audit hanya menyimpan jenis aksi, tidak pernah payload Base64 paraf.
				$this->rec_id = (string)$userId;
				$this->modeldata = array(
					'action_detail' => !empty($parafImage) ? 'update_canvas_signature' : 'clear_canvas_signature'
				);
				$this->write_to_log('update_paraf_specimen', 'true');
				$this->modeldata = null;

				$user = get_session('user_data');
				if (array_key_exists('paraf_image', $updateData)) {
					$user['paraf_image'] = $updateData['paraf_image'];
				}
				$user['user_initials'] = $updateData['user_initials'];
				set_session('user_data', $user);
				render_json(array('success' => true, 'message' => 'Paraf digital berhasil disimpan!'));
				return;
			}
			render_json(array('success' => false, 'message' => 'Gagal menyimpan ke database.'));
			return;
		}
		render_json(array('success' => false, 'message' => 'Metode request tidak diizinkan.'));
	}

}
