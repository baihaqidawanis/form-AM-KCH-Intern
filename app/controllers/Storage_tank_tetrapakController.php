<?php
/** Autonomous Maintenance Storage Tank Tetrapak (Compounding). Clone dari Storage_tankController -- unit fisik beda (Tetrapak vs Silverson), part/SOP sama. */
class Storage_tank_tetrapakController extends BaseMachineController
{
	protected $machineKey = 'storage_tank_tetrapak';
	protected $displayName = 'Storage Tank Tetrapak';
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
