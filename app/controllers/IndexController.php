<?php
/**
 * Index Page Controller
 * @category  Controller
 */
class IndexController extends BaseController
{
	function __construct()
	{
		parent::__construct();
		$this->tablename = "users";
	}
	/**
	 * Index Action 
	 * @return null
	 */
	function index()
	{
		if (user_login_status() == true) {
			$this->redirect(HOME_PAGE);
		} else {
			$this->render_view("index/index.php");
		}
	}
	private function login_user($username, $password_text, $rememberme = false)
	{
		$db = $this->GetModel();
		$username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$db->where("username", $username);
		$tablename = $this->tablename;
		$user = $db->getOne($tablename);
		if (!empty($user)) {
			//Akun yang sudah diblokir (3x gagal login) ditolak duluan sebelum cek password,
			//sesuai URS: "silakan menghubungi administrator" -- gak ada auto-unblock berbasis waktu,
			//cuma Administrator/Supervisor yang bisa buka lewat halaman Users.
			$user_status = strtolower($user['account_status']);
			if ($user_status == "blocked") {
				return $this->login_fail("Akun Anda telah terkunci karena 3 kali salah memasukkan password. Silakan hubungi administrator.");
			}
			//Verify User Password Text With DB Password Hash Value.
			//Uses PHP password_verify() function with default options
			$password_hash = $user['password'];
			$this->modeldata['password'] = $password_hash; //update the modeldata with the password hash
			if (password_verify($password_text, $password_hash)) {
				//check if user account has been activated by administrator
				if ($user_status != "active") {
					return $this->login_fail("Your account is not active. Please contact system administrator for more information");
				}
				//Password benar -- reset counter gagal login
				if (!empty($user['failed_login_attempts'])) {
					$db->where("id_user", $user['id_user']);
					$db->update($tablename, array("failed_login_attempts" => 0));
				}
				unset($user['password']); //Remove user password. No need to store it in the session
				set_session("user_data", $user); // Set active user data in a sessions
				$this->write_to_log("userlogin", "true");
				//if Remeber Me, Set Cookie
				if ($rememberme == true) {
					$sessionkey = time() . random_str(20); // Generate a session key for the user
					//Update user session info in database with the session key
					$db->where("id_user", $user['id_user']);
					$res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));
					if (!empty($res)) {
						set_cookie("login_session_key", $sessionkey); // save user login_session_key in a Cookie
					}
				} else {
					clear_cookie("login_session_key");// Clear any previous set cookie
				}
				$redirect_url = get_session("login_redirect_url");// Redirect to user active page
				if (!empty($redirect_url)) {
					clear_session("login_redirect_url");
					return $this->redirect($redirect_url);
				} else {
					return $this->redirect(HOME_PAGE);
				}
			} else {
				//password salah -- naikkan counter, blokir akun kalau udah 3x
				$attempts = intval($user['failed_login_attempts']) + 1;
				$update = array("failed_login_attempts" => $attempts);
				if ($attempts >= 3) {
					$update["account_status"] = "Blocked";
				}
				$db->where("id_user", $user['id_user']);
				$db->update($tablename, $update);
				if ($attempts >= 3) {
					return $this->login_fail("Akun Anda telah terkunci karena 3 kali salah memasukkan password. Silakan hubungi administrator.");
				}
				return $this->login_fail("Username or password not correct");
			}
		} else {
			//user is not registered
			return $this->login_fail("Username or password not correct");
		}
	}
	/**
	 * Display login page with custom message when login fails
	 * @return BaseView
	 */
	private function login_fail($page_error = null)
	{
		$this->set_page_error($page_error);
		$this->render_view("index/login.php");
	}
	/**
	 * Login Action
	 * If Not $_POST Request, Display Login Form View
	 * @return View
	 */
	function login($formdata = null)
	{
		if ($formdata) {
			$modeldata = $this->modeldata = $formdata;
			$username = trim($modeldata['username']);
			$password = $modeldata['password'];
			$rememberme = (!empty($modeldata['rememberme']) ? $modeldata['rememberme'] : false);
			$this->login_user($username, $password, $rememberme);
		} else {
			$this->set_page_error("Invalid request");
			$this->render_view("index/login.php");
		}
	}
	/**
	 * Insert new record into the user table
	 * @param $formdata array from $_POST
	 * @return BaseView
	 */
	function register($formdata = null)
	{
		if ($formdata) {
			$request = $this->request;
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$fields = $this->fields = array("nama", "email", "username", "area", "mesin", "password", "account_status", "user_role_id", "pict"); //registration fields
			$postdata = $this->format_request_data($formdata);
			$cpassword = $postdata['confirm_password'];
			$password = $postdata['password'];
			if ($cpassword != $password) {
				$this->view->page_error[] = "Your password confirmation is not consistent";
			}
			$this->rules_array = array(
				'nama' => 'required',
				'email' => 'required|valid_email',
				'username' => 'required',
				'area' => 'required',
				'mesin' => 'required',
				'password' => 'required',
				'user_role_id' => 'required',
				'pict' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'email' => 'sanitize_string',
				'username' => 'sanitize_string',
				'area' => 'sanitize_string',
				'mesin' => 'sanitize_string',
				'user_role_id' => 'sanitize_string',
				'pict' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$password_text = $modeldata['password'];
			//NIK di-uppercase biar gak ada 2 akun beda cuma gara-gara huruf besar/kecil
			//(NIK alfanumerik sekarang, beda sama waktu masih digit-murni yang gak ada isu case).
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
			$modeldata['password'] = $this->modeldata['password'] = password_hash($password_text, PASSWORD_DEFAULT);
			$modeldata['account_status'] = "Pending";
			//Self-register selalu jadi Staff/Operator (role_id 4) -- paksa di server,
			//jangan percaya nilai user_role_id dari form (hidden input bisa dimanipulasi
			//lewat devtools/curl). Naikkan role dilakukan superadmin manual lewat Users.
			$modeldata['user_role_id'] = $this->modeldata['user_role_id'] = 4;
			//Check if Duplicate Record Already Exit In The Database
			$db->where("email", $modeldata['email']);
			if ($db->has($tablename)) {
				$this->view->page_error[] = $modeldata['email'] . " Already exist!";
			}
			//Check if Duplicate Record Already Exit In The Database
			$db->where("username", $modeldata['username']);
			if ($db->has($tablename)) {
				$this->view->page_error[] = $modeldata['username'] . " Already exist!";
			}
			if ($this->validated()) {
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if ($rec_id) {
					$this->write_to_log("add", "true");
					$this->login_user($modeldata['username'], $password_text);
					return;
				} else {
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Add New Users";
		return $this->render_view("index/register.php");
	}
	/**
	 * Logout Action
	 * Destroy All Sessions And Cookies
	 * @return View
	 */
	function logout($arg = null)
	{
		Csrf::cross_check();
		$this->write_to_log("userlogout", "true");
		session_destroy();
		clear_cookie("login_session_key");
		$this->redirect("");
	}
}
