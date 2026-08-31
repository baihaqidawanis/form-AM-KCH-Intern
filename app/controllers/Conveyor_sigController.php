<?php
/** Autonomous Maintenance Conveyor SIG (Wrapping dan Pack Cartoning). */
class Conveyor_sigController extends BaseMachineController
{
	protected $machineKey = 'conveyor_sig';
	protected $displayName = 'Conveyor SIG';
	protected $parts = array(
		'meja' => 'Meja',
		'konveyor_belt_flexible_konveyor' => 'Konveyor belt, Flexible Konveyor',
		'badan_konveyor' => 'Badan Konveyor',
		'sensor_untuk_batch' => 'Sensor untuk batch',
		'roller' => 'Roller',
		'rantai_penggerak_utama' => 'Rantai Penggerak Utama',
		'pengecekan_konveyor_belt_flexible_konveyor' => 'Konveyor belt, Flexible Konveyor (Pengecekan)',
		'pengecekan_roller' => 'Roller (Pengecekan)',
		'sensor' => 'Sensor',
		'control_panel' => 'Control Panel',
	);
}
