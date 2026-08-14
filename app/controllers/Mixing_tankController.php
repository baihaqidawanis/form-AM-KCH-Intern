<?php
/** Autonomous Maintenance Mixing Tank (Compounding). */
class Mixing_tankController extends BaseMachineController
{
	protected $machineKey = 'mixing_tank';
	protected $displayName = 'Mixing Tank';
	protected $parts = array(

		'body_mixing_tank' => 'Body Mixing Tank',
		'jalur_pipa_mixing_tank' => 'Jalur Pipa Mixing Tank',
		'body_panel_hmi' => 'Body Panel HMI',
		'agitator' => 'Agitator',
		'seal_mainhole' => 'Seal mainhole',
	);
}
