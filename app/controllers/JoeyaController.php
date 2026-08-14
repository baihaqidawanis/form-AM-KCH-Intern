<?php
/** Autonomous Maintenance Joeya (Filling). */
class JoeyaController extends BaseMachineController
{
	protected $machineKey = 'joeya';
	protected $displayName = 'Joeya';
	protected $parts = array(

		'sealing_horizontal' => 'Sealing Horizontal',
		'sealing_vertikal' => 'Sealing Vertikal',
		'jalur_konveyor_sachet' => 'Jalur Konveyor Sachet',
		'collecting_plate_seluncuran_sachet' => 'Collecting Plate / Seluncuran Sachet',
		'roller_foil_film' => 'Roller Foil / Film',
		'bearing_sealing' => 'Bearing Sealing',
		'bearing_pisau_sachet_cutting' => 'Bearing Pisau / Sachet Cutting',
		'final_cutting' => 'Final Cutting',
		'per_transmisi_sealing' => 'Per Transmisi Sealing',
		'filling_pump' => 'Filling Pump',
		'bantalan_sealing' => 'Bantalan Sealing',
		'isolasi_tahan_panas' => 'Isolasi Tahan Panas',
	);
}
