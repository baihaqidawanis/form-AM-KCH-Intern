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

	private function selectedUnitName($mesin_id)
	{
		if (!$mesin_id) { return ''; }
		$row = $this->GetModel()->where('id', intval($mesin_id))->getOne('mesin', array('nama_mesin'));
		return trim((string) ($row['nama_mesin'] ?? ''));
	}

	private function isBestPackUnit($name)
	{
		return strpos($name, 'Kemas Best Pack - ') === 0
			|| strpos($name, 'Best Pack (non Inkjet) - ') === 0;
	}

	private function isNonInkjetUnit($name)
	{
		return strpos($name, 'Best Pack (non Inkjet) - ') === 0;
	}

	protected function partsForAdd($formdata = null)
	{
		$parts = parent::partsForAdd($formdata);
		$mesin_id = is_array($formdata) ? intval($formdata['mesin'] ?? 0) : intval($this->request->mesin ?? 0);
		if ($mesin_id && $this->isNonInkjetUnit($this->selectedUnitName($mesin_id))) {
			unset($parts['print_head_inkjet']);
		}
		return $parts;
	}

	protected function addContextError($formdata)
	{
		$error = parent::addContextError($formdata);
		if ($error) { return $error; }
		$name = $this->selectedUnitName(intval($formdata['mesin'] ?? 0));
		return $this->isBestPackUnit($name) ? null : 'Pilih unit mesin Best Pack yang valid.';
	}
}
