<?php
/** Autonomous Maintenance Granulator (Compounding). */
class GranulatorController extends BaseMachineController
{
	protected $machineKey = 'granulator';
	protected $displayName = 'Granulator';
	protected $parts = array(
		'body_mesin' => 'Body Mesin',
		'perforated_mesh' => 'Perforated Mesh',
		'baling_baling_pisau' => 'Baling-baling Pisau',
		'seal_corong' => 'Seal Corong',
	);
}
