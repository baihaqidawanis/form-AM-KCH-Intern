<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;

class QrSignatureHelper
{
    private static $salt = 'KalbeConsumerHealth_AM_Form_2026_Security_Key';

    /**
     * Generate a QR Code with the Kalbe logo centered and return as a Base64 data URI (PNG).
     *
     * @param string $data
     * @param int $scale
     * @param bool $includeLogo
     * @return string data:image/png;base64,...
     */
    public static function generateQrBase64($data, $scale = 8, $includeLogo = true)
    {
        $options = new QROptions();
        $options->version = -1; // Auto version
        $options->eccLevel = EccLevel::H; // High error correction (~30%) to allow logo
        $options->scale = $scale;
        $options->outputBase64 = false;
        $options->outputInterface = QRGdImagePNG::class;

        $qrcode = new QRCode($options);
        $qrPngString = $qrcode->render($data);

        $qrImg = @imagecreatefromstring($qrPngString);
        if (!$qrImg) {
            return 'data:image/png;base64,' . base64_encode($qrPngString);
        }

        if ($includeLogo) {
            $logoPath = ROOT . 'assets/images/kalbe-QR.png';
            if (!file_exists($logoPath)) {
                $logoPath = ROOT . 'kalbe-QR.png';
            }

            if (file_exists($logoPath)) {
                $logo = @imagecreatefrompng($logoPath);
                if ($logo) {
                    $qrW = imagesx($qrImg);
                    $qrH = imagesy($qrImg);
                    $logoW = imagesx($logo);
                    $logoH = imagesy($logo);

                    // Logo takes ~22% of QR width
                    $targetLogoW = (int)($qrW * 0.22);
                    $targetLogoH = (int)($logoH * ($targetLogoW / max(1, $logoW)));

                    $centerX = (int)(($qrW - $targetLogoW) / 2);
                    $centerY = (int)(($qrH - $targetLogoH) / 2);

                    // Solid white circular backing with compact padding so logo does not touch QR matrix dots
                    $white = imagecolorallocate($qrImg, 255, 255, 255);
                    $centerQrX = (int)($qrW / 2);
                    $centerQrY = (int)($qrH / 2);
                    $circleDiameter = (int)(max($targetLogoW, $targetLogoH) * 1.08);
                    imagefilledellipse($qrImg, $centerQrX, $centerQrY, $circleDiameter, $circleDiameter, $white);

                    imagecopyresampled(
                        $qrImg,
                        $logo,
                        $centerX,
                        $centerY,
                        0,
                        0,
                        $targetLogoW,
                        $targetLogoH,
                        $logoW,
                        $logoH
                    );
                    imagedestroy($logo);
                }
            }
        }

        ob_start();
        imagepng($qrImg);
        $finalPng = ob_get_clean();
        imagedestroy($qrImg);

        return 'data:image/png;base64,' . base64_encode($finalPng);
    }

    /**
     * Compute a SHA-256 hash of a period report check dataset.
     *
     * @param string $mesinSlug
     * @param int $mesinId
     * @param int $bulan
     * @param int $tahun
     * @param int $periode
     * @param array $checks
     * @return string
     */
    public static function computeDocumentHash($mesinSlug, $mesinId, $bulan, $tahun, $periode, array $checks = array())
    {
        $payload = array(
            'machine' => $mesinSlug,
            'machine_id' => (int)$mesinId,
            'month' => (int)$bulan,
            'year' => (int)$tahun,
            'period' => (int)$periode,
            'checks' => $checks
        );
        return hash('sha256', json_encode($payload));
    }

    /**
     * Generate a cryptographic token for QR verification.
     *
     * @param string $mesinSlug
     * @param int $mesinId
     * @param int $bulan
     * @param int $tahun
     * @param int $periode
     * @param string $role 'operator'|'spv'
     * @param int $userId
     * @return string
     */
    public static function generateToken($mesinSlug, $mesinId, $bulan, $tahun, $periode, $role, $userId)
    {
        $raw = sprintf('%s|%d|%d|%d|%d|%s|%d|%s|%s',
            $mesinSlug,
            $mesinId,
            $bulan,
            $tahun,
            $periode,
            $role,
            $userId,
            microtime(true),
            self::$salt
        );
        return hash('sha256', $raw);
    }

    /**
     * Get Database Model instance
     * @return PDODb
     */
    private static function getDb()
    {
        return new PDODb(DB_TYPE, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, DB_CHARSET);
    }

    /**
     * Retrieve signature record for a specific machine period.
     *
     * @param string $mesinSlug
     * @param int $mesinId
     * @param int $bulan
     * @param int $tahun
     * @param int $periode
     * @return array|null
     */
    public static function getPeriodSignature($mesinSlug, $mesinId, $bulan, $tahun, $periode)
    {
        $db = self::getDb();
        $db->where('mesin_slug', $mesinSlug)
           ->where('mesin_id', (int)$mesinId)
           ->where('bulan', (int)$bulan)
           ->where('tahun', (int)$tahun)
           ->where('periode', (int)$periode);

        $row = $db->getOne('am_period_signatures');
        if ($row) {
            // Enrich with user info
            if (!empty($row['operator_id'])) {
                $db->where('id_user', $row['operator_id']);
                $opUser = $db->getOne('users', array('id_user', 'nama', 'username', 'user_role_id', 'paraf_image', 'user_initials'));
                $row['operator_user'] = $opUser;
            }
            if (!empty($row['spv_id'])) {
                $db->where('id_user', $row['spv_id']);
                $spvUser = $db->getOne('users', array('id_user', 'nama', 'username', 'user_role_id', 'paraf_image', 'user_initials'));
                $row['spv_user'] = $spvUser;
            }
        }
        return $row ?: null;
    }

    /**
     * Save a signature (operator or spv).
     *
     * @param string $mesinSlug
     * @param int $mesinId
     * @param int $bulan
     * @param int $tahun
     * @param int $periode
     * @param int $userId
     * @param string $role 'operator'|'spv'
     * @param string $docHash
     * @return array ['success' => bool, 'token' => string, 'error' => string]
     */
    public static function savePeriodSignature($mesinSlug, $mesinId, $bulan, $tahun, $periode, $userId, $role, $docHash)
    {
        if (!in_array($role, array('operator', 'spv'), true) || !preg_match('/^[a-f0-9]{64}$/', (string)$docHash)) {
            return array('success' => false, 'error' => 'Parameter tanda tangan tidak valid.');
        }
        $db = self::getDb();
        $token = self::generateToken($mesinSlug, $mesinId, $bulan, $tahun, $periode, $role, $userId);
        $now = date('Y-m-d H:i:s');
        try {
            $db->startTransaction();
            $db->rawQuery(
                'INSERT INTO am_period_signatures (mesin_slug, mesin_id, bulan, tahun, periode, document_hash, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (mesin_slug, mesin_id, bulan, tahun, periode) DO NOTHING',
                array($mesinSlug, (int)$mesinId, (int)$bulan, (int)$tahun, (int)$periode, $docHash, 'draft', $now)
            );
            $existing = $db->rawQueryOne(
                'SELECT * FROM am_period_signatures WHERE mesin_slug = ? AND mesin_id = ? AND bulan = ? AND tahun = ? AND periode = ? FOR UPDATE',
                array($mesinSlug, (int)$mesinId, (int)$bulan, (int)$tahun, (int)$periode)
            );
            if (!$existing) { throw new RuntimeException('Record tanda tangan tidak dapat dikunci.'); }

            $existingToken = $role === 'operator' ? ($existing['operator_token'] ?? null) : ($existing['spv_token'] ?? null);
            $existingUser = $role === 'operator' ? ($existing['operator_id'] ?? null) : ($existing['spv_id'] ?? null);
            if ($existingToken) {
                if (intval($existingUser) === intval($userId) && hash_equals((string)$existing['document_hash'], (string)$docHash)) {
                    $db->commit();
                    return array('success' => true, 'token' => $existingToken);
                }
                $db->rollback();
                return array('success' => false, 'error' => 'Periode ini telah ditandatangani. Batalkan tanda tangan lama sebelum menggantinya.');
            }
            if (($existing['operator_token'] ?? null) || ($existing['spv_token'] ?? null)) {
                if (!hash_equals((string)$existing['document_hash'], (string)$docHash)) {
                    $db->rollback();
                    return array('success' => false, 'error' => 'Isi dokumen berubah setelah penandatanganan. Batalkan tanda tangan lama sebelum melanjutkan.');
                }
            }
            if ($role === 'spv' && empty($existing['operator_token'])) {
                $db->rollback();
                return array('success' => false, 'error' => 'Operator harus menandatangani dokumen terlebih dahulu.');
            }

            $updateData = array('document_hash' => $docHash, 'updated_at' => $now);
            if ($role === 'operator') {
                $updateData += array('operator_id' => (int)$userId, 'operator_signed_at' => $now, 'operator_token' => $token, 'status' => 'signed_operator');
            } else {
                $updateData += array('spv_id' => (int)$userId, 'spv_signed_at' => $now, 'spv_token' => $token, 'status' => 'approved');
            }
            $db->where('id', $existing['id']);
            if (!$db->update('am_period_signatures', $updateData) || !$db->getRowCount()) {
                throw new RuntimeException($db->getLastError() ?: 'Gagal menyimpan tanda tangan.');
            }
            if (!$db->commit()) { throw new RuntimeException('Commit tanda tangan gagal.'); }
            return array('success' => true, 'token' => $token);
        } catch (Throwable $e) {
            try { $db->rollback(); } catch (Throwable $ignored) {}
            error_log('savePeriodSignature failed: ' . $e->getMessage());
            return array('success' => false, 'error' => 'Gagal menyimpan tanda tangan digital.');
        }
    }

    /**
     * Look up signature by verification token.
     *
     * @param string $token
     * @return array|null
     */
    public static function getSignatureByToken($token)
    {
        $token = strtolower(trim((string)$token));
        $tokenLength = strlen($token);
        if ($tokenLength < 8 || $tokenLength > 64 || !preg_match('/^[a-f0-9]+$/', $token)) {
            return null;
        }

        $db = self::getDb();
        $operatorLookup = $tokenLength === 64 ? $token : $token . '%';
        $comparison = $tokenLength === 64 ? '=' : 'LIKE';
        $rows = $db->rawQuery(
            "SELECT * FROM am_period_signatures WHERE operator_token {$comparison} ? OR spv_token {$comparison} ? LIMIT 2",
            array($operatorLookup, $operatorLookup)
        );

        // Prefix pendek hanya sah jika menunjuk tepat ke satu token dan satu role.
        // Ini mencegah auditor memvalidasi dokumen yang salah ketika prefix bertabrakan.
        $matches = array();
        foreach ($rows as $candidate) {
            foreach (array('operator', 'spv') as $role) {
                $candidateToken = strtolower((string)($candidate[$role . '_token'] ?? ''));
                $matched = $tokenLength === 64
                    ? hash_equals($candidateToken, $token)
                    : strncmp($candidateToken, $token, $tokenLength) === 0;
                if ($candidateToken !== '' && $matched) {
                    $matches[] = array('row' => $candidate, 'role' => $role);
                }
            }
        }

        if (count($matches) !== 1) {
            return null;
        }

        $row = $matches[0]['row'];
        $verifiedRole = $matches[0]['role'];
        if ($row) {
            $isOperator = $verifiedRole === 'operator';
            $row['verified_role'] = $isOperator ? 'Operator Produksi' : 'SPV / Fasilitator';
            $signerId = $isOperator ? $row['operator_id'] : $row['spv_id'];
            $signedAt = $isOperator ? $row['operator_signed_at'] : $row['spv_signed_at'];

            $signerUser = null;
            if ($signerId) {
                $db->where('id_user', $signerId);
                $signerUser = $db->getOne('users', array('id_user', 'nama', 'username', 'email', 'area', 'mesin', 'user_role_id'));
            }
            $row['signer_user'] = $signerUser;
            $row['verified_signed_at'] = $signedAt;

            // Machine name
            $db->where('id', $row['mesin_id']);
            $mesin = $db->getOne('mesin', array('nama_mesin'));
            $row['machine_name'] = $mesin['nama_mesin'] ?? ('Mesin ID ' . $row['mesin_id']);
        }
        return $row ?: null;
    }
}
