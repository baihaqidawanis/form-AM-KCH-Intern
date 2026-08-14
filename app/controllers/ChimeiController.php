<?php
/** Autonomous Maintenance Chimei (Packaging). */
class ChimeiController extends BaseMachineController
{
	protected $machineKey = 'chimei';
	protected $displayName = 'Chimei';
	protected $parts = array(
		'conveyor_produk' => 'Conveyor Produk',
		'roller_opp' => 'Roller OPP',
		'rantai_opp' => 'Rantai OPP',
		'bearing_break_opp' => 'Bearing Break OPP',
		'rantai_motor_utama_cam' => 'Rantai Motor Utama & Cam',
		'as_pendorong_pack' => 'As Pendorong Pack',
		'jalur_compressed_air' => 'Jalur Compressed Air',
		'air_regulator' => 'Air Regulator',
		'sensor_produk_opp_pack' => 'Sensor : Produk, OPP, Pack',
	);
}
