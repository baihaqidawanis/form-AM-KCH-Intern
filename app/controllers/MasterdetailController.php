<?php
/**
 * Display master detail pages
 * @return View
 */
class MasterdetailController extends SecureController
{
	/**
	 * Kombinasi master_page/detail_page yang boleh di-render lewat controller
	 * ini. $master_page dan $detail_page sebelumnya masuk mentah ke include()
	 * tanpa whitelist -- rawan disalahgunakan buat include file lain di luar
	 * yang dimaksud (path traversal), walau dampaknya kebatasi karena selalu
	 * butuh suffix "-pages.php" yang cuma dipakai 1 file di app ini.
	 * @var array
	 */
	private $allowed_pages = array(
		'audit_log/users',
	);

	function index($master_page, $detail_page, $field_name = null, $field_value = null)
	{
		if (!in_array("$master_page/$detail_page", $this->allowed_pages, true)) {
			return $this->render_view("errors/error_404.php", "Master Detail Page Was Not Found", "info_layout.php");
		}
		$view_data = array(
			"master_page" => $master_page,
			"detail_page" => $detail_page,
			"field_name" => $field_name,
			"field_value" => $field_value
		);
		$this->render_view("$master_page/$detail_page-pages.php", $view_data);
	}
}