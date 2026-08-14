<?php
/** Autonomous Maintenance Cosmec (Compounding). */
class CosmecController extends BaseMachineController
{
	protected $machineKey = 'cosmec';
	protected $displayName = 'Cosmec';
	protected $parts = array(

		'body_panel_hmi' => 'Body Panel HMI',
		'body_mesin' => 'Body Mesin',
		'pengunci_bin' => 'Pengunci Bin',
		'switch_rantai' => 'Switch Rantai',
		'as_dan_flange_tumbler' => 'As dan Flange Tumbler',
		'baut_dan_mur_pada_flange_shaft' => 'Baut dan Mur pada Flange Shaft',
		'panel_pompa_hidrolik_mesin' => 'Panel Pompa Hidrolik Mesin',
	);
}
