<?php
/** Autonomous Maintenance FBD Glatt (Compounding). */
class Fbd_glattController extends BaseMachineController
{
	protected $machineKey = 'fbd_glatt';
	protected $displayName = 'FBD Glatt';
	protected $parts = array(

		'body_mesin' => 'Body Mesin',
		'panel_fbd' => 'Panel FBD',
		'hmi_panel_fbd' => 'HMI Panel FBD',
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
