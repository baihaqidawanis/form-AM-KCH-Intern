<?php
/** Autonomous Maintenance SIG (Filling). */
class SigController extends BaseMachineController
{
	protected $machineKey = 'sig';
	protected $displayName = 'SIG';
	protected $extraFields = array('value_tekanan_angin');
	protected $parts = array(
		'sealing_cross_dan_vertikal' => 'Sealing Cross dan Vertikal',
		'guarding_akrilik' => 'Guarding Akrilik',
		'jalur_conveyor' => 'Jalur Conveyor',
		'vacuum_hood' => 'Vacuum Hood',
		'antistatic' => 'Antistatic',
		'tekanan_angin_suplai' => 'Tekanan Angin Suplai',
		'jarak_slider_dengan_nozzle' => 'Jarak Slider dengan Nozzle',
		'rol_penarik_sachet_dan_foil_slitting_shim' => 'Rol Penarik Sachet dan Foil / Slitting Shim',
		'pisau_belah' => 'Pisau Belah',
		'modul_pisau' => 'Modul Pisau',
		'inkjet' => 'Inkjet',
	);
}
