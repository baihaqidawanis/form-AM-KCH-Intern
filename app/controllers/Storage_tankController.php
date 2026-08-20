<?php
/** Autonomous Maintenance Storage Tank (Compounding). */
class Storage_tankController extends BaseMachineController
{
	protected $machineKey = 'storage_tank';
	protected $displayName = 'Storage Tank Silverson';
	protected $parts = array(

		'body_storage_tank' => 'Body Storage Tank',
		'jalur_pipa_storage_tank' => 'Jalur Pipa Storage Tank',
		'motor_dan_gearbox' => 'Motor dan gearbox',
		'baling_baling_agitator' => 'Baling-baling Agitator',
		'seal_mainhole' => 'Seal mainhole',
		'pengunci_tutup_mainhole' => 'Pengunci tutup mainhole',
		'clamp_ferrule' => 'Clamp Ferrule',
	);
}
