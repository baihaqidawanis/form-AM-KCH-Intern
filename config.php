<?php

// Load .env (kalau ada) ke environment variable. Nilai yang udah di-set duluan
// di environment asli (misal lewat docker-compose `environment:`) gak ditimpa.
$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
	foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
			continue;
		}
		list($envKey, $envValue) = explode('=', $line, 2);
		$envKey = trim($envKey);
		$envValue = trim($envValue);
		if (getenv($envKey) === false) {
			putenv("$envKey=$envValue");
		}
	}
}
function env($key, $default = null){
	$value = getenv($key);
	return $value === false ? $default : $value;
}

define("DEFAULT_TIMEZONE", "Asia/Jakarta"); // set php date functions timezone
define("DEVELOPMENT_MODE" , true);// set to false when in production

// return full path of application directory
define("ROOT", str_replace("\\", "/", dirname(__FILE__)) . "/");

// return the application directory name.
define("ROOT_DIR_NAME", basename(ROOT));

define("SITE_NAME", "Form AM Site Pulogadung");


// Get Site Address Dynamically
$site_addr = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["SCRIPT_NAME"]);

//Must end with /
$site_addr = rtrim($site_addr, "/\\") . "/";

// Can Be Set Manually Like "http://localhost/mysite/".
define("SITE_ADDR", $site_addr);

define("APP_ID", "381475a47dd3816b7c91a81a80c98734");

// Application Default Color (Mostly Used By Mobile)
define("META_THEME_COLOR", "#000000");

//Application resource access status
define("AUTHORIZED", 200);
define("UNAUTHORIZED", 401);
define("NOROLE", 404);
define("FORBIDDEN", 403);

// Application Files and Directories 
define("IMG_DIR",  "assets/images/");
define("FONTS_DIR",  "assets/fonts/");
define("SITE_FAVICON", IMG_DIR . "favicon.png");
define("SITE_LOGO", IMG_DIR . "logo.png");

define("CSS_DIR", SITE_ADDR . "assets/css/");
define("JS_DIR", SITE_ADDR . "assets/js/");

define("APP_DIR", "app/");
define("SYSTEM_DIR", "system/");
define("HELPERS_DIR", "helpers/");
define("LIBS_DIR", "libs/");
define("LANGS_DIR", "languages/");
define("MODELS_DIR", APP_DIR . "models/");
define("CONTROLLERS_DIR", APP_DIR . "controllers/");
define("VIEWS_DIR", APP_DIR . "views/");
define("LAYOUTS_DIR", VIEWS_DIR . "layouts/");
define("PAGES_DIR", VIEWS_DIR . "partials/");
define("AUDIT_LOGS_DIR", "logs/");

// File Upload Directories 
define("UPLOAD_DIR", "uploads/");
define("UPLOAD_FILE_DIR", UPLOAD_DIR . "files/");
define("UPLOAD_IMG_DIR", UPLOAD_DIR . "photos/");
define("MAX_UPLOAD_FILESIZE", trim(ini_get("upload_max_filesize")));

// First page to see after user login 
define("HOME_PAGE", "Home");
define("DEFAULT_PAGE", "index"); //Default Controller Class
define("DEFAULT_PAGE_ACTION", "index"); //Default Controller Action
define("DEFAULT_LAYOUT", LAYOUTS_DIR . "main_layout.php");
define("DEFAULT_LANGUAGE", "english"); //Default Language

// Page Meta Information
define("META_AUTHOR", "");
define("META_DESCRIPTION", "");
define("META_KEYWORDS", "");
define("META_VIEWPORT", "width=device-width, initial-scale=1.0");
define("PAGE_CHARSET", "UTF-8");

// Email Configuration Default Settings
define("USE_SMTP",false);
define("SMTP_USERNAME", "");
define("SMTP_PASSWORD", "");
define("SMTP_HOST", "");
define("SMTP_PORT", "");

//Default Email Sender Details. Please set this even if you are not using SMTP
define("DEFAULT_EMAIL", "");
define("DEFAULT_EMAIL_ACCOUNT_NAME", "");

// Database Configuration Settings
define("DB_HOST", env("DB_HOST", "localhost"));
define("DB_USERNAME", env("DB_USERNAME", "root"));
define("DB_PASSWORD", env("DB_PASSWORD", ""));
define("DB_NAME", env("DB_NAME", "form_am_plg"));
define("DB_TYPE", env("DB_TYPE", "mysql"));
define("DB_PORT", env("DB_PORT", "3306"));
define("DB_CHARSET", env("DB_CHARSET", "utf8"));

define("MAX_RECORD_COUNT", 20); //Default Max Records to Retrieve  per Page
define("ORDER_TYPE", "DESC");  //Default Order Type

// Active User Profile Details
define('USER_ID',(isset($_SESSION[APP_ID.'user_data']) ? $_SESSION[APP_ID.'user_data']['id_user'] : null ));
define('USER_NAME',(isset($_SESSION[APP_ID.'user_data']) ? $_SESSION[APP_ID.'user_data']['username'] : null ));
define('USER_EMAIL',(isset($_SESSION[APP_ID.'user_data']) ? $_SESSION[APP_ID.'user_data']['email'] : null ));

