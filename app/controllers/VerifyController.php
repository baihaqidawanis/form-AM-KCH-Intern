<?php

/**
 * Public Verification Controller for Digital Signatures & QR Code Scans
 * @category Controller
 */
class VerifyController extends BaseController
{
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

        $this->view->page_title = "Verifikasi Tanda Tangan Digital - PT Bintang Toedjoe";
        $data = array(
            'token' => $token,
            'signature' => $signature,
            'is_valid' => !empty($signature)
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
