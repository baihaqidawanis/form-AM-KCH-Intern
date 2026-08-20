<?php
/**
 * Page Access Control
 * @category  RBAC Helper
 */
defined('ROOT') or exit('No direct script access allowed');
class ACL
{
	

	/**
	 * Array of user roles (role_id) and page access, sesuai matrix akses URS
	 * (Tabel 4 — Administrator, Manager, Supervisor, Staff/Operator).
	 * Key = role_id (lihat tabel `roles`: 1=Administrator, 2=Manager, 3=Supervisor, 4=Staff/Operator).
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
			'sig' => '*', 'joeya' => '*', 'illapak_1_2' => '*', 'illapak_3_12' => '*', 'unifill_b' => '*',
			'chimei' => '*', 'temach' => '*', 'jihcheng' => '*', 'jinsung_1_4' => '*', 'jinsung_5' => '*', 'best_pack' => '*',
			'cosmec' => '*', 'fbd_jaw_chuan' => '*', 'fbd_glatt' => '*', 'supermixer' => '*', 'storage_tank' => '*', 'storage_tank_tetrapak' => '*', 'mixing_tank' => '*',
			'approval' => '*',
			'users' => '*',
			'roles' => '*', 'tag' => '*',
		),

		// 2 = Manager: akses Home, AM (view saja, tidak bisa tambah form), Approval, Panduan (URS 2.2 & 4.2)
		2 => array(
			'sig' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'joeya' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'illapak_1_2' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'illapak_3_12' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'unifill_b' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'chimei' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'temach' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'jihcheng' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'jinsung_1_4' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'jinsung_5' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'best_pack' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'cosmec' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'fbd_jaw_chuan' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'fbd_glatt' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'supermixer' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'storage_tank' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'storage_tank_tetrapak' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'mixing_tank' => array('list', 'list2', 'view', 'edit', 'editfield', 'edit_data', 'delete'), // view + approval (URS 4.2) + edit_data (dibatasi ke record sendiri, dicek di controller) + delete (URS 3.1), tidak add
			'approval' => '*',
		),

		// 4 = Staff/Operator: akses Home, AM (view + isi form + edit_data record sendiri), Panduan (URS 2.2 & 3.1) — tidak approval/delete
		4 => array(
			'sig' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'joeya' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'illapak_1_2' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'illapak_3_12' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'unifill_b' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'chimei' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'temach' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'jihcheng' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'jinsung_1_4' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'jinsung_5' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'best_pack' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'cosmec' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'fbd_jaw_chuan' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'fbd_glatt' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'supermixer' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'storage_tank' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'storage_tank_tetrapak' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
			'mixing_tank' => array('list', 'list2', 'view', 'add', 'edit_data'), // + edit_data (dibatasi ke record sendiri, dicek di controller)
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
	public static $exclude_page_check = array("", "index", "home", "account", "info", "masterdetail", "panduan_pengisian_am");

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

			//If user is accessing excluded access contrl pages
			if (in_array($page, self::$exclude_page_check)) {
				return AUTHORIZED;
			}

			$user_role = USER_ROLE; // Get user defined role_id (int) from session value
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

	/**
	 * Check if user role has access to a page
	 * @return Bool
	 */
	public static function is_allowed($path)
	{
		return (self::GetPageAccess($path) == AUTHORIZED);
	}

}
