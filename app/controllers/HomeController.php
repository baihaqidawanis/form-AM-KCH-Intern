<?php 

/**
 * Home Page Controller
 * @category  Controller
 */
class HomeController extends SecureController{
	/**
     * Index Action
     * @return View
     */
	function index(){

		$this->render_view("home/index.php" , null , "main_layout.php");

	}

	/**
	 * Endpoint ringan buat idle-timer session timeout (URS 1.3) -- dipanggil AJAX
	 * pas user klik "Saya Masih Di Sini", cukup buat bikin request baru yang
	 * lewat SecureController::authenticate_user() sehingga last_activity ke-update.
	 * @return null
	 */
	function ping(){
		render_json(array('ok' => true));
	}
}
