<?php
/**
 * Page Access Control
 * @category  RBAC Helper
 */
defined('ROOT') or exit('No direct script access allowed');
class ACL
{
	/**
	 * Pemetaan area penugasan user ke modul mesin AM.
	 * Key disimpan dalam format normal agar pencocokan case-insensitive dan
	 * toleran terhadap spasi berlebih.
	 * @var array<string,array<int,string>>
	 */
	public static $area_machines = array(
		'compounding' => array(
			'cosmec', 'fbd_jaw_chuan', 'fbd_glatt', 'supermixer', 'granulator',
			'storage_tank', 'storage_tank_tetrapak', 'mixing_tank'
		),
		'filling' => array(
			'sig', 'joeya', 'illapak_1_2', 'illapak_3_12', 'unifill_b'
		),
		'kemas' => array(
			'jihcheng', 'jinsung_1_4', 'jinsung_5'
		),
		'wrapping dan pack cartoning' => array(
			'chimei', 'temach', 'best_pack', 'check_weigher', 'conveyor_sig'
		),
	);

	/**
	 * Array of user roles (role_id) and page access, sesuai matrix akses URS
	 * (Tabel 4 — Administrator, Manager, Supervisor, Staff/Operator).
	 * Key = role_id: 1=Administrator, 2=Manager, 3=Supervisor, 4=Staff, 5=Operator.
	 * Value "*" = akses semua halaman & aksi untuk role tsb.
	 * Value array asosiatif per halaman: "*" = semua aksi, atau array aksi spesifik (list/list2/view/add/edit/...).
	 * Halaman yang tidak didaftarkan untuk suatu role otomatis FORBIDDEN (default-deny).
	 * @var array
	 */
	public static $role_pages = array(
		// 1 = Administrator: akses seluruh fitur & menu (URS 2.2)
		1 => '*',

		// 3 = Supervisor: akses Home, AM (full CRUD), Users, Approval, Panduan (URS 2.2) — TANPA Audit Trail
		3 => array(
			'master_mesin' => '*',
			'sig' => '*', 'joeya' => '*', 'illapak_1_2' => '*', 'illapak_3_12' => '*', 'unifill_b' => '*',
			'chimei' => '*', 'temach' => '*', 'check_weigher' => '*', 'conveyor_sig' => '*', 'jihcheng' => '*', 'jinsung_1_4' => '*', 'jinsung_5' => '*', 'best_pack' => '*',
			'cosmec' => '*', 'fbd_jaw_chuan' => '*', 'fbd_glatt' => '*', 'supermixer' => '*', 'granulator' => '*', 'storage_tank' => '*', 'storage_tank_tetrapak' => '*', 'mixing_tank' => '*',
			'approval' => '*',
			'roles' => '*', 'tag' => '*',
			'users' => array('export_specimen'),
		),

		// 2 = Manager: akses Home, AM (view saja, tidak bisa tambah form), Approval, Panduan (URS 2.2 & 4.2)
		2 => array(
			'master_mesin' => '*',
			'sig' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'joeya' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'illapak_1_2' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'illapak_3_12' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'unifill_b' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'chimei' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'temach' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'check_weigher' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'conveyor_sig' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jihcheng' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jinsung_1_4' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jinsung_5' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'best_pack' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'cosmec' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'fbd_jaw_chuan' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'fbd_glatt' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'supermixer' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'granulator' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'storage_tank' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'storage_tank_tetrapak' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'mixing_tank' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'approval' => '*',
			'users' => array('export_specimen'),
		),

		// 4 = Staff: akses Home, AM (view + isi form + edit_data record sendiri), Panduan (URS 2.2 & 3.1)
		4 => array(
			'sig' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'joeya' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'illapak_1_2' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'illapak_3_12' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'unifill_b' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'chimei' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'temach' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'check_weigher' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'conveyor_sig' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jihcheng' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jinsung_1_4' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jinsung_5' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'best_pack' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'cosmec' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'fbd_jaw_chuan' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'fbd_glatt' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'supermixer' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'granulator' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'storage_tank' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'storage_tank_tetrapak' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'mixing_tank' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'users' => array('export_specimen'),
		),

		// 5 = Operator: akses operasional AM + TTD Digital Operator
		5 => array(
			'sig' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'joeya' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'illapak_1_2' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'illapak_3_12' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'unifill_b' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'chimei' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'temach' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'check_weigher' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'conveyor_sig' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jihcheng' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jinsung_1_4' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'jinsung_5' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'best_pack' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'cosmec' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'fbd_jaw_chuan' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'fbd_glatt' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'supermixer' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'granulator' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'storage_tank' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'storage_tank_tetrapak' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'mixing_tank' => array('list', 'list2', 'view', 'add', 'edit_data', 'daily_report', 'period_report', 'sign_period', 'cancel_period_signature'),
			'users' => array('export_specimen'),
		),
	);

	/**
	 * Current user role_id (integer, lihat tabel `roles`)
	 * @var int|null
	 */
	public static $user_role = null;

	/**
	 * pages to Exclude From Access Validation Check
	 * Halaman ini selalu boleh diakses siapapun yang sudah login, apapun role-nya.
	 * @var array
	 */
	public static $exclude_page_check = array("", "index", "home", "account", "info", "masterdetail", "panduan_pengisian_am", "verify");

	/**
	 * Init page properties
	 */
	public function __construct()
	{	
		if(!empty(USER_ROLE)){
			self::$user_role = USER_ROLE;
		}
	}

	/**
	 * Check page path against user role permissions
	 * if user has access return AUTHORIZED
	 * if user has NO access return UNAUTHORIZED
	 * if user has NO role return NO_ROLE
	 * @return string
	 */
	public static function GetPageAccess($path)
	{
		$rp = self::$role_pages;
		if ($rp == "*") {
			return AUTHORIZED; // Grant access to any user
		} else {
			$path = strtolower(trim($path, '/'));

			$arr_path = explode("/", $path);
			$page = strtolower($arr_path[0]);

			$user_role = intval(USER_ROLE); // Get user defined role_id (int) from session value
			if (!self::is_machine_allowed($page, $user_role, get_active_user('area'))) {
				return FORBIDDEN;
			}

			//If user is accessing excluded access contrl pages
			if (in_array($page, self::$exclude_page_check)) {
				return AUTHORIZED;
			}

			if (array_key_exists($user_role, $rp)) {
				$action = (!empty($arr_path[1]) ? $arr_path[1] : "list");
				if ($action == "index") {
					$action = "list";
				}
				//Check if user have access to all pages or user have access to all page actions
				if ($rp[$user_role] == "*" || (!empty($rp[$user_role][$page]) && $rp[$user_role][$page] == "*")) {
					return AUTHORIZED;
				} else {
					if (!empty($rp[$user_role][$page]) && in_array($action, $rp[$user_role][$page])) {
						return AUTHORIZED;
					}
				}
				return FORBIDDEN;
			} else {
				//User does not have any role.
				return NOROLE;
			}
		}
	}

	/** Normalisasi nama area dari data user. */
	private static function normalize_area($area)
	{
		$clean = strtolower(trim((string)preg_replace('/\s+/', ' ', (string)$area)));
		if ($clean === 'wrapping & pack cartoning') {
			return 'wrapping dan pack cartoning';
		}
		return $clean;
	}

	/**
	 * Defense tunggal untuk ACL route dan controller API.
	 * Role selain Staff/Operator tidak dibatasi area.
	 */
	public static function is_machine_allowed($page, $user_role = null, $user_area = null)
	{
		$role = $user_role === null ? intval(USER_ROLE) : intval($user_role);
		if (!in_array($role, array(4, 5), true)) {
			return true;
		}

		$machine = strtolower(trim((string)$page, '/'));
		$machine = explode('/', $machine)[0];
		$all_machines = array();
		foreach (self::$area_machines as $machines) {
			$all_machines = array_merge($all_machines, $machines);
		}
		if (!in_array($machine, $all_machines, true)) {
			return true;
		}

		$area = self::normalize_area($user_area === null ? get_active_user('area') : $user_area);
		return isset(self::$area_machines[$area]) && in_array($machine, self::$area_machines[$area], true);
	}

	/**
	 * Check if user role has access to a page
	 * @return Bool
	 */
	public static function is_allowed($path)
	{
		return (self::GetPageAccess($path) == AUTHORIZED);
	}

}
