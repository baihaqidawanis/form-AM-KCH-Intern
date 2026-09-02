<?php
/** Autonomous Maintenance Illapak 1 - 2 (Filling). */
class Illapak_1_2Controller extends BaseMachineController
{
	protected $machineKey = 'illapak_1_2';
	protected $displayName = 'Illapak 1 - 2';
	// Disimpan di header AM supaya riwayat pemeriksaan tetap bisa diaudit per shift.
	protected $extraFields = array('shift');
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

	/** Shift yang dipilih harus menjadi bagian dari data POST, bukan sekadar filter UI. */
	protected function selectedShift($formdata = null)
	{
		$value = is_array($formdata) && isset($formdata['shift']) ? $formdata['shift'] : (isset($this->request->shift) ? $this->request->shift : null);
		return in_array((string) $value, array('1', '2', '3'), true) ? (string) $value : null;
	}

	protected function addContextError($formdata)
	{
		return $this->selectedShift($formdata) ? null : 'Shift wajib dipilih (1, 2, atau 3).';
	}

	/**
	 * Filter dilakukan sebelum field dibangun untuk validasi/INSERT. Jadi part
	 * shift lain tidak hanya tersembunyi di browser, tetapi juga tidak diterima
	 * jika dikirim manual lewat request yang dimodifikasi.
	 */
	protected function partsForAdd($formdata = null)
	{
		$shift = $this->selectedShift($formdata);
		$this->view->selected_shift = $shift;
		if (!$shift) { return array(); }

		$db = $this->GetModel();
		$rows = $db->where('machine_key', $this->machineKey)->where('taken_out_at', null, 'IS')->orderBy('urutan', 'ASC')->get('master_part', null, array('field_name', 'label', 'shift_schedule'));
		$parts = array();
		foreach ($rows as $row) {
			$allowed_shifts = array_filter(array_map('trim', explode(',', (string) $row['shift_schedule'])));
			if (in_array($shift, $allowed_shifts, true)) { $parts[$row['field_name']] = $row['label']; }
		}
		return $parts;
	}
}
