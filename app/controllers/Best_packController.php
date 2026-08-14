<?php
/** Autonomous Maintenance Best Pack (Packaging). */
class Best_packController extends BaseMachineController
{
	protected $machineKey = 'best_pack';
	protected $displayName = 'Best Pack';
	protected $parts = array(

		'body_best_pack' => 'Body Best Pack',
		'konveyor_best_pack' => 'Konveyor Best Pack',
		'print_head_inkjet' => 'Print Head Inkjet',
		'belt_conveyor_best_pack' => 'Belt Conveyor Best Pack',
		'pisau_best_pack' => 'Pisau Best Pack',
		'selang_angin_best_pack' => 'Selang Angin Best pack',
	);
}
