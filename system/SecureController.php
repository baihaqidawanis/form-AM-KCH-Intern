<?php
/**
* Extends to Application Base Controller.
* Page Controllers which need page authentication and authorization can extend to this class 
*/
class SecureController extends BaseController{
	function __construct(){
		parent::__construct();
		// Page actions which do not require authentication.
		$exclude_pages = array();
		$url = Router :: $page_url;
		$url = str_ireplace("/index", "/list", $url);
		
		if(!empty($url)){
			$url_segment =$url_segment = explode("/" , rtrim($url , "/")) ;
			$controller = strtolower(!empty($url_segment[0]) ? $url_segment[0] : null);
			$action = strtolower((!empty($url_segment[1]) ? $url_segment[1] : "list"));
			$page = "$controller/$action";
			if(!in_array($page , $exclude_pages)){
				if($this->authenticate_user()){
					// Role-based page access control sesuai matrix akses URS (libs/ACL.php)
					$access = ACL::GetPageAccess($page);
					$this->status = ($access == AUTHORIZED) ? AUTHORIZED : $access; // FORBIDDEN atau NOROLE kalau role tidak diizinkan

				}
				else{
					$this->status = UNAUTHORIZED;
				}
			}
		}
	}

	/**
	 * Authenticate And Check User Page Access Eligibility
	 * @return  Redirect to Login Page Or Displays Error Message When user access control authorization Fails
	 */
	private function authenticate_user()
	{
		if (user_login_status() == false) {
			//check if user has a login cookie
			$session_key = get_cookie("login_session_key");
			if (!empty($session_key)) {
				$db = $this->GetModel();
				$db->where("login_session_key", hash_value($session_key));
				$user = $db->getOne("users");
				if (!empty($user)) {
					set_session("user_data", $user);
				}
			}
		}
		//URS 1.3: session timeout 30 menit idle. Ini backstop server-side --
		//pengecekan utama (deteksi idle + peringatan) jalan di sisi client lewat
		//assets/js/idle-timeout.js, ini cuma jaga-jaga kalau JS gak jalan/nonaktif.
		if (user_login_status() == true) {
			$last_activity = get_session("last_activity");
			if (!empty($last_activity) && (time() - $last_activity) > SESSION_TIMEOUT_SECONDS) {
				clear_session("user_data");
				clear_session("last_activity");
				clear_cookie("login_session_key");
				set_session("session_timed_out", true);
				return false;
			}
			set_session("last_activity", time());
		}
		return user_login_status();
	}
}