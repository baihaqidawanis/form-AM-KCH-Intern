<?php
/** Autonomous Maintenance Unifill (Filling). */
class Unifill_bController extends BaseMachineController
{
	protected $machineKey = 'unifill_b';
	protected $displayName = 'Unifill';
	protected $parts = array(

		'conveyor' => 'Conveyor',
		'cutting_unit' => 'Cutting Unit',
		'neck_sealing_unit' => 'Neck Sealing Unit',
		'nozzle' => 'Nozzle',
		'tekanan_angin' => 'Tekanan Angin',
		'temperature_air_pendingin' => 'Temperature Air Pendingin',
		'piston_valves_dan_selang_pengisian' => 'Piston, Valves, dan Selang Pengisian',
		'buffer_roller_dispenser' => 'Buffer Roller / Dispenser',
		'sensory_eyemark_sensor_redaksi' => 'Sensory Eyemark, Sensor Redaksi',
		'cylinder_grip' => 'Cylinder Grip',
		'timing_belt_pengisian' => 'Timing Belt Pengisian',
		'filter_airfan' => 'Filter Airfan',
		'guide_nozzle' => 'Guide Nozzle',
	);
}
