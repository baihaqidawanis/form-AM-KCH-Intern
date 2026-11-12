<?php 
class PasswordmanagerController extends BaseController{
	function __construct(){
		parent::__construct();
		$this->tablename = "users";
	}
	private function reset_rate_allowed($identifier){
		$db = $this->GetModel();
		$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		if (!filter_var($ip, FILTER_VALIDATE_IP)) { $ip = '0.0.0.0'; }
		$identifier_hash = hash('sha256', strtolower(trim($identifier)));
		try {
			$db->startTransaction();
			$db->rawQuery('SELECT pg_advisory_xact_lock(hashtext(?))', array($ip . ':' . $identifier_hash));
			$db->rawQuery("DELETE FROM \"password_reset_attempts\" WHERE \"attempt_time\" < CURRENT_TIMESTAMP - INTERVAL '1 day'");
			$since = date('Y-m-d H:i:s', time() - 900);
			$ip_count = $db->rawQueryOne('SELECT COUNT(*) AS total FROM "password_reset_attempts" WHERE "ip_address"=? AND "attempt_time">=?', array($ip, $since));
			$id_count = $db->rawQueryOne('SELECT COUNT(*) AS total FROM "password_reset_attempts" WHERE "identifier_hash"=? AND "attempt_time">=?', array($identifier_hash, $since));
			if (intval($ip_count['total'] ?? 0) >= 5 || intval($id_count['total'] ?? 0) >= 5) { $db->commit(); return false; }
			$db->insert('password_reset_attempts', array('ip_address'=>$ip, 'identifier_hash'=>$identifier_hash)); $db->commit(); return true;
		} catch (Exception $e) { if (!empty($db->transaction)) { $db->rollback(); } return false; }
	}

	function index(){
		$this->render_view("passwordmanager/index.php", null, "info_layout.php");
	}
	function postresetlink(){
		if (!is_post_request()) { http_response_code(405); return $this->redirect('passwordmanager'); }
		$nik = strtoupper(trim((string)($this->post->nik ?? '')));
		$password = (string)($this->post->password ?? '');
		$confirmation = (string)($this->post->confirm_password ?? '');
		if (!is_valid_nik_username($nik) || $password !== $confirmation || !is_valid_password_complexity($password)) {
			$this->set_page_error('Isi NIK yang valid, password minimal 8 karakter (huruf besar, huruf kecil, angka, dan simbol), serta konfirmasi yang sama.');
			return $this->render_view('passwordmanager/index.php', null, 'info_layout.php');
		}
		if (!$this->reset_rate_allowed($nik)) {
			http_response_code(429); $this->set_page_error('Terlalu banyak percobaan. Silakan tunggu 15 menit.');
			return $this->render_view('passwordmanager/index.php', null, 'info_layout.php');
		}
		$db = $this->GetModel();
		$user = $db->where('username', $nik)->getOne($this->tablename, array('id_user', 'is_super_admin'));
		// The root administrator cannot be deactivated through a public self-service request.
		if ($user && empty($user['is_super_admin'])) {
			$changed = $db->where('id_user', $user['id_user'])->update($this->tablename, array(
				'password'=>password_hash($password, PASSWORD_DEFAULT), 'account_status'=>'pending_activation',
				'password_reset_key'=>null, 'password_expire_date'=>null, 'failed_login_attempts'=>0
			));
			if (!$changed) { $this->set_page_error('Permintaan belum dapat diproses. Silakan coba lagi.'); return $this->render_view('passwordmanager/index.php', null, 'info_layout.php'); }
			$this->rec_id = $user['id_user'];
			$this->write_to_log('password_reset_pending_activation', 'true');
		}
		// Same response for unknown NIKs; account existence is not exposed.
		$this->set_flash_msg('Jika NIK terdaftar, password baru disimpan dan akun menunggu aktivasi. Hubungi Administrator.', 'info');
		return $this->render_view('passwordmanager/index.php', null, 'info_layout.php');
	}

	function updatepassword(){
		header('Referrer-Policy: no-referrer');
		$password_key = trim((string)($_POST['key'] ?? get_value("key")));
		if(!empty($password_key)){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$hashed_key = hash_value($password_key);
			$db->where ("password_reset_key", $hashed_key);
			$date_to_expire = $db->getValue($tablename, "password_expire_date");
			if(!empty($date_to_expire)){
				$password_has_not_expired =  new DateTime($date_to_expire) > new DateTime();
				if($password_has_not_expired){
					if(!empty($_POST['password'])){
						$password = $_POST["password"]; 
						$cpassword = $_POST["cpassword"];
						if($password != $cpassword){
							$this->set_page_error("Your password confirmation is not consistent");
							$this->render_view("passwordmanager/password_reset_form.php", array('key' => $password_key), "info_layout.php");
						}
						else if(!is_valid_password_complexity($password)){
							$this->set_page_error("Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.");
							$this->render_view("passwordmanager/password_reset_form.php", array('key' => $password_key), "info_layout.php");
						}
						else{
							$new_password_hash = password_hash($password , PASSWORD_DEFAULT);
							$new_password_data = array(
								"password" => $new_password_hash,
								"password_reset_key" => null,
								"password_expire_date" => null,
								// Password baru belum boleh dipakai sebelum admin menyetujui.
								"account_status" => "pending_activation",
								"failed_login_attempts" => 0
							);
							$db->where ("password_reset_key", $hashed_key);
							$db->update($tablename, $new_password_data);
							if($db->getRowCount()){
								$this->write_to_log('password_reset_pending_activation', 'true');
								$this->render_view("passwordmanager/password_reset_completed.php", null, "info_layout.php");
							}
							else{
								$this->render_view("passwordmanager/password_reset_error.php", null, "info_layout.php");
							}
						}
					}
					else{
						$this->render_view("passwordmanager/password_reset_form.php", array('key' => $password_key), "info_layout.php");
					}
				}
				else{
					$this->set_page_error("Password reset key has expired. Please start a new password request");
					$this->render_view("passwordmanager/index.php", null, "info_layout.php");
				}
			}
			else{
				$this->render_view("errors/error_general.php", "Invalid Password Reset Key", "info_layout.php");
			}	
		}
		else{
			$this->redirect("passwordmanager");
		}
	}
}
