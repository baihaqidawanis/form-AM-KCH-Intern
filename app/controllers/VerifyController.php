<?php

/**
 * Public Verification Controller for Digital Signatures & QR Code Scans
 * @category Controller
 */
class VerifyController extends BaseController
{
	private const MACHINE_CONTROLLERS = array(
		'best_pack' => 'Best_packController',
		'check_weigher' => 'Check_weigherController',
		'chimei' => 'ChimeiController',
		'conveyor_sig' => 'Conveyor_sigController',
		'cosmec' => 'CosmecController',
		'fbd_glatt' => 'Fbd_glattController',
		'fbd_jaw_chuan' => 'Fbd_jaw_chuanController',
		'granulator' => 'GranulatorController',
		'illapak_1_2' => 'Illapak_1_2Controller',
		'illapak_3_12' => 'Illapak_3_12Controller',
		'jihcheng' => 'JihchengController',
		'jinsung_1_4' => 'Jinsung_1_4Controller',
		'jinsung_5' => 'Jinsung_5Controller',
		'joeya' => 'JoeyaController',
		'mixing_tank' => 'Mixing_tankController',
		'sig' => 'SigController',
		'storage_tank' => 'Storage_tankController',
		'storage_tank_tetrapak' => 'Storage_tank_tetrapakController',
		'supermixer' => 'SupermixerController',
		'temach' => 'TemachController',
		'unifill_b' => 'Unifill_bController',
	);

    /**
     * Verify document digital signature via token
     * @param string $token
     */
    function signature($token = null)
    {
        $token = trim((string)$token);
        if (empty($token)) {
            $token = trim((string)($this->request->token ?? ''));
        }

        $signature = null;
        if (!empty($token)) {
            $signature = QrSignatureHelper::getSignatureByToken($token);
        }

		$integrity_valid = false;
		$current_document_hash = null;
		if ($signature) {
			$controller_class = self::MACHINE_CONTROLLERS[$signature['mesin_slug']] ?? null;
			if ($controller_class && class_exists($controller_class)) {
				try {
					$machine_controller = new $controller_class;
					$state = $machine_controller->periodDocumentState(
						$signature['mesin_id'],
						$signature['bulan'],
						$signature['tahun'],
						$signature['periode']
					);
					$current_document_hash = $state['document_hash'];
					$integrity_valid = !empty($signature['signature_authentic'])
						&& hash_equals((string)$signature['document_hash'], $current_document_hash);
				} catch (Throwable $e) {
					error_log('QR signature re-hash failed: ' . $e->getMessage());
				}
			}
		}

        $this->view->page_title = "Verifikasi Tanda Tangan Digital - PT Bintang Toedjoe";
        $data = array(
            'token' => $token,
            'signature' => $signature,
			'is_registered' => !empty($signature),
			'is_valid' => !empty($signature) && $integrity_valid,
			'integrity_valid' => $integrity_valid,
			'current_document_hash' => $current_document_hash,
        );

        $this->render_view("verify/signature.php", $data, "info_layout.php");
    }

    /**
     * Default index redirects to signature verification
     */
    function index()
    {
        $token = trim((string)($this->request->token ?? ''));
        if (!empty($token)) {
            return $this->signature($token);
        }
        return $this->signature('');
    }
}
