<?php
/** Autonomous Maintenance Temach (Packaging). */
class TemachController extends BaseMachineController
{
	protected $machineKey = 'temach';
	protected $displayName = 'Temach';
	protected $parts = array(

		'conveyor_produk' => 'Conveyor Produk',
		'pusher_pendorong_pack' => 'Pusher Pendorong Pack',
		'turet' => 'Turet',
		'cam' => 'Cam',
		'lubrikasi_bearing_konveyor' => 'Lubrikasi Bearing Konveyor',
		'jalur_compressed_air' => 'Jalur Compressed Air',
		'air_regulator' => 'Air Regulator',
		'heater_a_f' => 'Heater (A-F)',
		'baut_turet' => 'Baut Turet',
	);
}
