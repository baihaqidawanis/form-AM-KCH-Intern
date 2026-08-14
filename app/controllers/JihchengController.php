<?php
/** Autonomous Maintenance Jihcheng (Packaging). */
class JihchengController extends BaseMachineController
{
	protected $machineKey = 'jihcheng';
	protected $displayName = 'Jihcheng';
	protected $parts = array(

		'konveyor_belt' => 'Konveyor Belt',
		'flexible_konveyor_u' => 'Flexible Konveyor U',
		'suction_cup' => 'Suction Cup',
		'pocket_pembawa_tube_dan_pack' => 'Pocket Pembawa Tube dan Pack',
		'shaft_dan_bushing_pusher' => 'Shaft dan Bushing Pusher',
		'bearing_rantai_tube_cam_pusher' => 'Bearing Rantai Tube, Cam Pusher',
		'rantai_penggerak_utama_pocket_tube_pack' => 'Rantai Penggerak Utama, Rantai Pocket Tube, Rantai Pocket Pack',
		'regulator_angin_utama' => 'Regulator Angin Utama',
		'regulator_angin_chamber_hot_melt' => 'Regulator Angin Chamber Hot Melt',
		'sensor_tube_pack_nozzle_lem' => 'Sensor : Tube, Pack, Nozzle Lem',
		'pengecekan_tombol_emergency' => 'Pengecekan Tombol Emergency',
	);
}
