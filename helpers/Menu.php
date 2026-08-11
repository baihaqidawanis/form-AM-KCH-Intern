<?php
/**
 * Menu Items
 * All Project Menu
 * @category  Menu List
 */

class Menu
{


	public static $navbarsideleft = array(
		array(
			'path' => 'home',
			'label' => 'Home',
			'icon' => '<i class="fa fa-home "></i>'
		),

		array(
			'path' => '/',
			'label' => 'Compounding',
			'icon' => '<i class="fa fa-gears "></i>',
			'submenu' => array(
				// Belum ada mesin pabrik kita yang dikerjain di kategori ini.
				// Isi array ini pas mulai bikin mesin Compounding (ngikutin pola SIG,
				// lihat DOCS_MD/FINAL_IMPROVEMENT.md bagian "FOKUS SAAT INI").
			)
		),

		array(
			'path' => '/',
			'label' => 'Filling',
			'icon' => '<i class="fa fa-gears "></i>',
			'submenu' => array(
				array(
					'path' => 'sig',
					'label' => 'SIG',
					'icon' => ''
				),
				array(
					'path' => 'joeya',
					'label' => 'Joeya',
					'icon' => ''
				),
				array('path' => 'illapak_1_2',  'label' => 'Illapak 1 - 2',  'icon' => ''),
				array('path' => 'illapak_3_12', 'label' => 'Illapak 3 - 12', 'icon' => ''),
				array('path' => 'unifill_b',     'label' => 'Unifill B',     'icon' => '')
			)
		),

		array(
			'path' => '/',
			'label' => 'Packaging',
			'icon' => '<i class="fa fa-gears "></i>',
			'submenu' => array(
				array(
					'path' => 'chimei',
					'label' => 'Chimei',
					'icon' => ''
				),
				array(
					'path' => 'temach',
					'label' => 'Temach',
					'icon' => ''
				),
				array(
					'path' => 'jihcheng',
					'label' => 'Jihcheng',
					'icon' => ''
				),
				array(
					'path' => 'jinsung_1_4',
					'label' => 'Jinsung 1 - 4',
					'icon' => ''
				),
				array(
					'path' => 'jinsung_5',
					'label' => 'Jinsung 5',
					'icon' => ''
				),
				array(
					'path' => 'best_pack',
					'label' => 'Best Pack',
					'icon' => ''
				),
			)
		),

		array(
			'path' => 'approval',
			'label' => 'Approval',
			'icon' => '<i class="fa fa-check-square "></i>'
		),

		array(
			'path' => 'users',
			'label' => 'Users',
			'icon' => '<i class="fa fa-users"></i>'
		),

		array(
			'path' => 'panduan_pengisian_am',
			'label' => 'Panduan Pengisian AM',
			'icon' => '<i class="fa fa-book"></i>'
		)
	);



	public static $approval = array(
		array(
			"value" => "Approved",
			"label" => "Approved",
		),
		array(
			"value" => "Not Approved",
			"label" => "Not Approved",
		),
	);
// =================TES_SIG==================TES_SIG===================TES_SIG====================TES_SIG===================
	public static $Kondisi_Harian = array(
		array(
			"value" => "OK",
			"label" => "Kondisi Baik",
		),
		array(
			"value" => "NOK",
			"label" => "Kondisi Tidak Baik",
		),
	);

	public static $antistatic = array(
		array(
			"value" => "OK",
			"label" => "Kondisi Baik",
		),
		array(
			"value" => "NOK",
			"label" => "Kondisi Tidak Baik",
		),
		array(
			"value" => "N/A",
			"label" => "Tidak Dilakukan"
		),
	);
	
	public static $Harmonika_Pick_n_Place = array(
		array(
			"value" => "OK",
			"label" => "Kondisi Baik",
		),
		array(
			"value" => "NOK",
			"label" => "Kondisi Tidak Baik",
		),
		array(
			"value" => "N/A",
			"label" => "Tidak ada Pick n Place",
		),
	);

	public static $Nozzle_dan_Auger = array(
		array(
			"value" => "OK",
			"label" => "Kondisi Baik",
		),
		array(
			"value" => "NOK",
			"label" => "Kondisi Tidak Baik",
		),
		array(
			"value" => "N/A",
			"label" => "Tidak Dilakukan",
		),
	);

	public static $Vacuum_Pad = array(
		array(
			"value" => "OK",
			"label" => "Kondisi Baik",
		),
		array(
			"value" => "NOK",
			"label" => "Kondisi Tidak Baik",
		),
		array(
			"value" => "N/A",
			"label" => "Tidak ada Vacuum Pad",
		),
	);

	public static $approval2 = array(
		array(
			"value" => "approved",
			"label" => "Approved",
		),
		array(
			"value" => "not approved",
			"label" => "Not Approved",
		),
	);

	public static $status = array(
		array(
			"value" => "Open",
			"label" => "Open",
		),
		array(
			"value" => "Closed",
			"label" => "Closed",
		),
		array(
			"value" => "Pending",
			"label" => "Pending",
		),
	);

	public static $konveyor = array(
		array(
			"value" => "OK",
			"label" => "Kondisi Part Baik",
		),
		array(
			"value" => "NOK",
			"label" => "Kondisi Part Tidak Baik",
		),
		array(
			"value" => "N/A",
			"label" => "Tidak Dilakukan AM",
		),
	);

	public static $docking_station = array(
		array(
			"value" => "Station 1",
			"label" => "Station 1",
		),
		array(
			"value" => "Station 2",
			"label" => "Station 2",
		),
		array(
			"value" => "Station 3",
			"label" => "Station 3",
		),
		array(
			"value" => "Station 4",
			"label" => "Station 4",
		),
		array(
			"value" => "Station 5",
			"label" => "Station 5",
		),
		array(
			"value" => "Station 6",
			"label" => "Station 6",
		),
		array(
			"value" => "Station 7",
			"label" => "Station 7",
		),
		array(
			"value" => "Station 8",
			"label" => "Station 8",
		),
		array(
			"value" => "Station 9",
			"label" => "Station 9",
		),
	);

	public static $discharge_station = array(
		array(
			"value" => "DISCHARGE STATION 1",
			"label" => "DISCHARGE STATION 1",
		),
	);

	public static $no_ibc = array(
		array(
			"value" => "K1C1",
			"label" => "K1C1",
		),
		array(
			"value" => "K1C2",
			"label" => "K1C2",
		),
		array(
			"value" => "K1C3",
			"label" => "K1C3",
		),
		array(
			"value" => "K1C4",
			"label" => "K1C4",
		),
		array(
			"value" => "K1C5",
			"label" => "K1C5",
		),
		array(
			"value" => "K1C6",
			"label" => "K1C6",
		),
		array(
			"value" => "K1C7",
			"label" => "K1C7",
		),
		array(
			"value" => "K1C8",
			"label" => "K1C8",
		),
		array(
			"value" => "K1C9",
			"label" => "K1C9",
		),
		array(
			"value" => "K1C10",
			"label" => "K1C10",
		),
		array(
			"value" => "K1C11",
			"label" => "K1C11",
		),
		array(
			"value" => "K1C12",
			"label" => "K1C12",
		),
		array(
			"value" => "K1C13",
			"label" => "K1C13",
		),
		array(
			"value" => "K1C14",
			"label" => "K1C14",
		),
		array(
			"value" => "K1C15",
			"label" => "K1C15",
		),
		array(
			"value" => "K1C16",
			"label" => "K1C16",
		),
		array(
			"value" => "K1C17",
			"label" => "K1C17",
		),
		array(
			"value" => "K1C18",
			"label" => "K1C18",
		),
		array(
			"value" => "K1C19",
			"label" => "K1C19",
		),
		array(
			"value" => "K1C20",
			"label" => "K1C20",
		),
		array(
			"value" => "K1C21",
			"label" => "K1C21",
		),
		array(
			"value" => "K1C22",
			"label" => "K1C22",
		),
		array(
			"value" => "K1C23",
			"label" => "K1C23",
		),
		array(
			"value" => "K1C24",
			"label" => "K1C24",
		),
		array(
			"value" => "K1C25",
			"label" => "K1C25",
		),
		array(
			"value" => "K1C26",
			"label" => "K1C26",
		),
		array(
			"value" => "K1C27",
			"label" => "K1C27",
		),
		array(
			"value" => "K1C28",
			"label" => "K1C28",
		),
		array(
			"value" => "K1C29",
			"label" => "K1C29",
		),
		array(
			"value" => "K1C30",
			"label" => "K1C30",
		),
		array(
			"value" => "K1C31",
			"label" => "K1C31",
		),
		array(
			"value" => "K1C32",
			"label" => "K1C32",
		),
		array(
			"value" => "K1C33",
			"label" => "K1C33",
		),
		array(
			"value" => "K1C34",
			"label" => "K1C34",
		),
		array(
			"value" => "K1C35",
			"label" => "K1C35",
		),
		array(
			"value" => "K1C36",
			"label" => "K1C36",
		),
		array(
			"value" => "K1C37",
			"label" => "K1C37",
		),
		array(
			"value" => "K1C38",
			"label" => "K1C38",
		),
		array(
			"value" => "K1C39",
			"label" => "K1C39",
		),
		array(
			"value" => "K1C40",
			"label" => "K1C40",
		),
		array(
			"value" => "K1C41",
			"label" => "K1C41",
		),
		array(
			"value" => "K1C42",
			"label" => "K1C42",
		),
		array(
			"value" => "K1C43",
			"label" => "K1C43",
		),
		array(
			"value" => "K1C44",
			"label" => "K1C44",
		),
		array(
			"value" => "K1C45",
			"label" => "K1C45",
		),
		array(
			"value" => "K1C46",
			"label" => "K1C46",
		),
		array(
			"value" => "K1C47",
			"label" => "K1C47",
		),
		array(
			"value" => "K1C48",
			"label" => "K1C48",
		),
		array(
			"value" => "K1C49",
			"label" => "K1C49",
		),
		array(
			"value" => "K1C50",
			"label" => "K1C50",
		),
		array(
			"value" => "K1C51",
			"label" => "K1C51",
		),
		array(
			"value" => "K1C52",
			"label" => "K1C52",
		),
		array(
			"value" => "K1C53",
			"label" => "K1C53",
		),
		array(
			"value" => "K1C54",
			"label" => "K1C54",
		),
		array(
			"value" => "K1C55",
			"label" => "K1C55",
		),
	);

	public static $account_status = array(
		array(
			"value" => "Active",
			"label" => "Active",
		),
		array(
			"value" => "Pending",
			"label" => "Pending",
		),
		array(
			"value" => "Blocked",
			"label" => "Blocked",
		),
	);

}
