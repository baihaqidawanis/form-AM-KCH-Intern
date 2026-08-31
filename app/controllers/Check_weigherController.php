<?php
/** Autonomous Maintenance Check Weigher (Wrapping dan Pack Cartoning). */
class Check_weigherController extends BaseMachineController
{
	protected $machineKey = 'check_weigher';
	protected $displayName = 'Check Weigher';
	protected $parts = array(
		'lengan_rejector' => 'Lengan Rejector',
		'body_mesin_check_weigher' => 'Body Mesin Check Weigher',
		'vanbelt' => 'Vanbelt',
		'belt_check_weigher' => 'Belt Check Weigher',
		'roller_dan_bearing' => 'Roller dan bearing',
		'pelumasan_roller_dan_bearing' => 'Roller dan bearing (Pelumasan)',
		'bearing_roller' => 'Bearing Roller',
		'kaki_kaki' => 'Kaki-Kaki',
		'pengecekan_belt_check_weigher' => 'Belt Check Weigher (Pengecekan)',
	);
}
