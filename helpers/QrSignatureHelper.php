<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;

class QrSignatureHelper
{
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
    public static function canonicalJson(array $payload)
    {
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    public static function computeDocumentHash($mesinSlug, $mesinId, $bulan, $tahun, $periode, array $document = array())
    {
        $payload = array('machine' => (string)$mesinSlug, 'machine_id' => (int)$mesinId,
            'month' => (int)$bulan, 'year' => (int)$tahun, 'period' => (int)$periode,
            'document' => $document);
        return hash('sha256', self::canonicalJson($payload));
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
    public static function generateToken($mesinSlug = null, $mesinId = null, $bulan = null, $tahun = null, $periode = null, $role = null, $userId = null)
    {
        return bin2hex(random_bytes(32));
    }

    private static function hmacKey()
    {
        $key = defined('SIGNATURE_HMAC_KEY') ? (string)SIGNATURE_HMAC_KEY : '';
        if (strlen($key) < 32) { throw new RuntimeException('SIGNATURE_HMAC_KEY belum dikonfigurasi dengan aman.'); }
        return $key;
    }

    private static function signatureMac(array $row, $role)
    {
        $payload = array(
            'version' => (int)($row['signature_version'] ?? 2),
            'machine' => (string)$row['mesin_slug'], 'machine_id' => (int)$row['mesin_id'],
            'month' => (int)$row['bulan'], 'year' => (int)$row['tahun'], 'period' => (int)$row['periode'],
            'role' => $role, 'user_id' => (int)$row[$role . '_id'],
            'username' => (string)$row[$role . '_username'], 'name' => (string)$row[$role . '_name'],
            'role_id' => (int)$row[$role . '_role_id'], 'signed_at' => (string)$row[$role . '_signed_at'],
            'document_hash' => (string)$row['document_hash'], 'token' => (string)$row[$role . '_token'],
        );
        return hash_hmac('sha256', self::canonicalJson($payload), self::hmacKey());
    }

    public static function verifyStoredSignature(array $row, $role)
    {
        if (!in_array($role, array('operator', 'spv'), true)) { return false; }
        $stored = (string)($row[$role . '_signature_mac'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $stored)) { return false; }
        try { return hash_equals($stored, self::signatureMac($row, $role)); }
        catch (Throwable $e) { return false; }
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
            // Tampilan memakai snapshot signer; perubahan profil akun tidak boleh
            // mengubah identitas yang melekat pada tanda tangan lama.
            if (!empty($row['operator_id'])) {
                $row['operator_user'] = array('id_user' => $row['operator_id'], 'nama' => $row['operator_name'],
                    'username' => $row['operator_username'], 'user_role_id' => $row['operator_role_id']);
            }
            if (!empty($row['spv_id'])) {
                $row['spv_user'] = array('id_user' => $row['spv_id'], 'nama' => $row['spv_name'],
                    'username' => $row['spv_username'], 'user_role_id' => $row['spv_role_id']);
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
    public static function savePeriodSignature($mesinSlug, $mesinId, $bulan, $tahun, $periode, $userId, $role, $docHash, array $documentPayload = array())
    {
        if (!in_array($role, array('operator', 'spv'), true) || !preg_match('/^[a-f0-9]{64}$/', (string)$docHash)) {
            return array('success' => false, 'error' => 'Parameter tanda tangan tidak valid.');
        }
        $db = self::getDb();
        try { self::hmacKey(); } catch (Throwable $e) { return array('success' => false, 'error' => $e->getMessage()); }
        $token = self::generateToken();
        $now = date('Y-m-d H:i:s');
        try {
            $db->where('id_user', (int)$userId);
            $signer = $db->getOne('users', array('id_user', 'nama', 'username', 'user_role_id'));
            if (!$signer) { throw new RuntimeException('Akun penandatangan tidak ditemukan.'); }
            $db->startTransaction();
			$lockKey = 'form-am:' . $mesinSlug . ':' . intval($mesinId) . ':' . sprintf('%04d-%02d', intval($tahun), intval($bulan)) . ':p' . intval($periode);
			$db->rawQuery('SELECT pg_advisory_xact_lock(hashtext(?))', array($lockKey));
            $db->rawQuery(
                'INSERT INTO am_period_signatures (mesin_slug, mesin_id, bulan, tahun, periode, document_hash, document_payload, signature_version, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?::jsonb, 2, ?, ?) ON CONFLICT (mesin_slug, mesin_id, bulan, tahun, periode) DO NOTHING',
                array($mesinSlug, (int)$mesinId, (int)$bulan, (int)$tahun, (int)$periode, $docHash, self::canonicalJson($documentPayload), 'draft', $now)
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

            $updateData = array('document_hash' => $docHash, 'document_payload' => self::canonicalJson($documentPayload), 'signature_version' => 2, 'updated_at' => $now);
            $prefix = $role . '_';
            $updateData += array($prefix . 'id' => (int)$userId, $prefix . 'signed_at' => $now,
                $prefix . 'token' => $token, $prefix . 'name' => (string)$signer['nama'],
                $prefix . 'username' => (string)$signer['username'], $prefix . 'role_id' => (int)$signer['user_role_id']);
            $macRow = array_merge($existing, $updateData, array('mesin_slug' => $mesinSlug, 'mesin_id' => (int)$mesinId,
                'bulan' => (int)$bulan, 'tahun' => (int)$tahun, 'periode' => (int)$periode));
            $updateData[$prefix . 'signature_mac'] = self::signatureMac($macRow, $role);
            if ($role === 'operator') {
                $updateData['status'] = 'signed_operator';
            } else {
                $updateData['status'] = 'approved';
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
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $db = self::getDb();
        $rows = $db->rawQuery(
            "SELECT * FROM am_period_signatures WHERE operator_token = ? OR spv_token = ? LIMIT 2",
            array($token, $token)
        );

        // Prefix pendek hanya sah jika menunjuk tepat ke satu token dan satu role.
        // Ini mencegah auditor memvalidasi dokumen yang salah ketika prefix bertabrakan.
        $matches = array();
        foreach ($rows as $candidate) {
            foreach (array('operator', 'spv') as $role) {
                $candidateToken = strtolower((string)($candidate[$role . '_token'] ?? ''));
                $matched = hash_equals($candidateToken, $token);
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
            $row['verified_role'] = $isOperator ? 'Operator Produksi' : 'Supervisor';
            $signedAt = $isOperator ? $row['operator_signed_at'] : $row['spv_signed_at'];
            $row['signer_user'] = array('id_user' => $row[$verifiedRole . '_id'], 'nama' => $row[$verifiedRole . '_name'],
                'username' => $row[$verifiedRole . '_username'], 'user_role_id' => $row[$verifiedRole . '_role_id']);
            $row['verified_signed_at'] = $signedAt;
            $row['signature_authentic'] = self::verifyStoredSignature($row, $verifiedRole);

            // Machine name
            $db->where('id', $row['mesin_id']);
            $mesin = $db->getOne('mesin', array('nama_mesin'));
            $row['machine_name'] = $mesin['nama_mesin'] ?? ('Mesin ID ' . $row['mesin_id']);
        }
        return $row ?: null;
    }
}
