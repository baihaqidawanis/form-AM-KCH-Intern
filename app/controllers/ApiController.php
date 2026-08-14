<?php

/**
 * Info Contoller Class
 * @category  Controller
 */

class ApiController extends BaseController
{
	/**
	 * Method SharedController yang boleh dipanggil lewat endpoint ini.
	 * Whitelist wajib: sebelumnya $action diambil mentah dari URL tanpa
	 * validasi apapun, jadi endpoint publik ini bisa manggil method PUBLIC
	 * apapun di SharedController -- termasuk method yang diwarisi dari
	 * BaseController seperti write_to_log(), yang kalau dipanggil bebas bisa
	 * dipakai nyuntik entri palsu ke audit_log tanpa login sama sekali.
	 * @var array
	 */
	private $allowed_actions = array(
		'sig_Line_option_list',
		'sig_kategori_tag_option_list',
		'sig_korelasi_tag_option_list',
		'sig_klasifikasi_tag_option_list',
		'sig_kategori_ketidaksesuaian_option_list',
		'users_user_role_id_option_list',
		'users_email_value_exist',
		'users_username_value_exist',
	);

	/**
	 * call model action to retrieve data
	 * @return json data
	 */
	function json($action, $arg1 = null, $arg2 = null)
	{
		if (!in_array($action, $this->allowed_actions, true)) {
			render_json(null);
			return;
		}
		$model = new SharedController;
		$args = array($arg1, $arg2);
		$data = call_user_func_array(array($model, $action), $args);
		render_json($data);
	}
}
