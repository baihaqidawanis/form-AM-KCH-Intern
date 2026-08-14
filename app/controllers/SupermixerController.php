<?php
/** Autonomous Maintenance Supermixer (Compounding). */
class SupermixerController extends BaseMachineController
{
	protected $machineKey = 'supermixer';
	protected $displayName = 'Supermixer';
	protected $parts = array(

		'body_mesin' => 'Body Mesin',
		'pressure_gauge' => 'Pressure Gauge',
		'timer' => 'Timer',
		'chopper' => 'Chopper',
		'agitator' => 'Agitator',
		'valve_hopper' => 'Valve Hopper',
		'discharge_valve' => 'Discharge Valve',
	);
}
