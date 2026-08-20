<?php
	// Pengerasan cookie sesi -- WAJIB di-set SEBELUM session_start().
	// httponly : cookie sesi gak bisa dibaca JavaScript, jadi kalau suatu saat
	//            ada celah XSS yang lolos, sesi login gak ikut kebajak.
	// strict   : PHP nolak session id karangan dari luar (anti session fixation).
	// samesite : cookie gak ikut terkirim di request lintas-situs (lapis tambahan CSRF).
	// secure   : cuma diaktifkan kalau diakses lewat HTTPS -- kalau dipaksa ON di
	//            server HTTP (kondisi sekarang), user malah gak akan bisa login.
	ini_set('session.use_strict_mode', 1);
	session_set_cookie_params(array(
		'httponly' => true,
		'samesite' => 'Lax',
		'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
	));
	session_start(); // Start or Resume Session

	require('config.php');
	
	//composer auto load libraries
	require ('vendor/autoload.php');

	//Error reporting for debugging during development
	if(DEVELOPMENT_MODE == true){
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL); 
	} 
	else {
		//errors will not be displayed on the pages but log to files
		error_reporting(E_ALL);
		ini_set('log_errors', 'On');
		ini_set('error_log', 'error.log');
		ini_set('display_errors','Off');
	}
	//
	if(!empty(DEFAULT_TIMEZONE)){
		date_default_timezone_set(DEFAULT_TIMEZONE);
	}
	
	// Application configurations Settings

	/**
     * Initialize Model Class From Model Dir
     * @return null
     */
	function autoloadModel($className) {
		$filename = MODELS_DIR . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}

	/**
     * Initialize Controller Classes From Controller Dir
     * @return null
     */
	function autoloadController($className) {
		$filename = CONTROLLERS_DIR . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}
	
	/**
     * Initialize Libraries Classes From Libs Dir
     * @return boolean
     */
	function autoloadLibrary($className) {
		$filename = LIBS_DIR . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}
	
	/**
     * Initialize Helper Classes From helper Dir
     * @return null
     */
	function autoloadHelper($className) {
		$filename = HELPERS_DIR . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}
	
	// Register Autoloaders
	spl_autoload_register("autoloadModel");
	spl_autoload_register("autoloadController");
	spl_autoload_register("autoloadLibrary");
	spl_autoload_register("autoloadHelper");
	
	
	
	//Initialize Global Functions Helpers
	require(HELPERS_DIR . 'Functions.php');

	$lang = new Lang;// Initialize language class and load default language phrases
	$csrf = new Csrf;// Initialize Csrf class and generate new application token
	$csrf_token = $csrf::$token; 
	

	// Application Core Files
	require(SYSTEM_DIR . 'BaseController.php');
	require(SYSTEM_DIR . 'SecureController.php');
	require(SYSTEM_DIR . 'BaseMachineController.php');
	require(SYSTEM_DIR . 'BaseView.php');
	require(SYSTEM_DIR . 'Router.php');
	
	//display page with the exceptions
	function exception_handler($exception){
		$view = new BaseView();
		// BaseView::__construct baca ulang $_GET['format'] dari request yang lagi
		// crash (pdf/word/csv/excel/dst) -- kalau dibiarkan, render() di bawah bakal
		// nyoba nge-export $exception (bukan data beneran) pakai format itu, jadi
		// crash kedua yang lebih membingungkan daripada error aslinya. Halaman error
		// harus SELALU html apapun format request aslinya.
		$view->format = 'html';
		$view->render("errors/error_server.php", $exception, "info_layout.php");
		exit;
	}

	//Display application exception in a custom page
	set_exception_handler('exception_handler');

	$page = new Router;
	$page->init(); // Bootstrap Page with the Current URL
	
	