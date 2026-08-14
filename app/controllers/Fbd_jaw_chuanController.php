<?php
/** Autonomous Maintenance FBD Jaw Chuan (Compounding). */
class Fbd_jaw_chuanController extends BaseMachineController
{
	protected $machineKey = 'fbd_jaw_chuan';
	protected $displayName = 'FBD Jaw Chuan';
	protected $parts = array(

		'body_mesin' => 'Body Mesin',
		'panel_fbd' => 'Panel FBD',
		'tombol_tombol_pada_panel_fbd' => 'Tombol-tombol pada panel FBD (Power, Timer, Heater)',
		'seal_bagtight' => 'Seal Bagtight',
		'container_up_down' => 'Container Up-Down',
		'shaking' => 'Shaking',
		'pressure_gauge_damper' => 'Pressure Gauge Damper',
		'seal_container' => 'Seal Container',
		'guarding_pengunci_kontainer' => 'Guarding Pengunci Kontainer',
		'container_mesh_dan_roda' => 'Container (Mesh dan Roda)',
		'filter_dan_bag_tight' => 'Filter dan Bag Tight',
	);
}
