<?php
/** Autonomous Maintenance Illapak 1 - 2 (Filling). */
class Illapak_1_2Controller extends BaseMachineController
{
	protected $machineKey = 'illapak_1_2';
	protected $displayName = 'Illapak 1 - 2';
	protected $parts = array(

		'sealing_horizontal' => 'Sealing Horizontal',
		'sealing_vertikal' => 'Sealing Vertikal',
		'body_mesin' => 'Body Mesin dan Conveyor',
		'roller_foil_film' => 'Roller Foil (Setelah Inkjet)',
		'position_indicator_sealing_vertical' => 'Position Indicator Sealing Vertical',
		'vacum_sliter' => 'Vacum Sliter',
		'piston_pengisian' => 'Piston Pengisian',
		'pneumatic_valves_pengisian' => 'Pneumatic Valves Pengisian',
		'baut_sealing_vertikal' => 'Baut Sealing Vertikal',
		'rubber_penarik_foil' => 'Rubber Penarik Foil',
		'sensor_eyemark_dan_sambungan_foil' => 'Sensor Eyemark dan Sambungan Foil',
		'guarding_mesin' => 'Guarding Mesin',
		'pressure_blow_sealing_vertical' => 'Pressure Blow Sealing Vertical',
		'inkjet' => 'Inkjet',
		'pengunci_nozzle_pengisian' => 'Pengunci Nozzle Pengisian',
	);
}
