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
			"pict");
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
		 //editable fields -- account_status & user_role_id SENGAJA tidak termasuk:
		 //user gak boleh naikkan role/aktivasi akun sendiri, itu wewenang
		 //Administrator lewat menu Users (UsersController::edit()).
		$fields = $this->fields = array("id_user","nama","username","area","mesin","pict");
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nama' => 'required',
				'username' => 'required',
				'area' => 'required',
				'mesin' => 'required',
				'pict' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'username' => 'sanitize_string',
				'area' => 'sanitize_string',
				'mesin' => 'sanitize_string',
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
			$email = trim($formdata['email']);
			$db = $this->GetModel();
			$rec_id = $this->rec_id = USER_ID; //get current user id from session
			$tablename = $this->tablename;
			$db->where ("id_user", $rec_id);
			$result = $db->update($tablename, array('email' => $email ));
			if($result){
				$this->write_to_log("emailchange", "true");
				$this->set_flash_msg("Email address changed successfully", "success");
				$this->redirect("account");
			}
			else{
				$this->set_page_error("Email not changed");
			}
		}
		return $this->render_view("account/change_email.php");
	}
}
