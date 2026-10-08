<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/config.php';

function postRtwtOutbox(string $url, array $payload, array $headers, ?string $photoPath = null): array
{
    if (function_exists('curl_init') && ($photoPath === null || class_exists('CURLFile'))) {
        $body = $payload;
        if ($photoPath !== null) {
            $body['foto_before'] = new CURLFile($photoPath, mime_content_type($photoPath) ?: 'image/jpeg', basename($photoPath));
        }
        $ch = curl_init($url);
        if ($ch === false) return [false, 0, 'cURL tidak dapat memulai koneksi.'];
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_NOSIGNAL => true]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        return [$response, $status, $error];
    }

    if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        return [false, 0, 'PHP cURL dan HTTP stream wrapper tidak tersedia di server.'];
    }

    if ($photoPath !== null) {
        $contents = @file_get_contents($photoPath);
        if ($contents === false) return [false, 0, 'Foto Before antrean tidak dapat dibaca.'];
        $boundary = '----FormAmRtwt' . bin2hex(random_bytes(12));
        $body = '';
        foreach ($payload as $name => $value) {
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="' . str_replace('"', '', (string) $name) . "\"\r\n\r\n" . (string) $value . "\r\n";
        }
        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Disposition: form-data; name="foto_before"; filename="' . str_replace('"', '', basename($photoPath)) . "\"\r\n";
        $body .= 'Content-Type: ' . (mime_content_type($photoPath) ?: 'image/jpeg') . "\r\n\r\n" . $contents . "\r\n--" . $boundary . "--\r\n";
        $headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
    } else {
        $body = http_build_query($payload, '', '&');
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $headers[] = 'Content-Length: ' . strlen($body);
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $body, 'timeout' => 15, 'ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $context);
    $status = 0;
    foreach (($http_response_header ?? []) as $header) {
        if (preg_match('#^HTTP/\\S+\\s+(\\d{3})#', $header, $matches)) { $status = (int) $matches[1]; break; }
    }
    $error = '';
    if ($response === false) { $lastError = error_get_last(); $error = (string) ($lastError['message'] ?? 'HTTP stream gagal terhubung.'); }
    return [$response, $status, $error];
}

if (RTWT_API_MODE !== 'modular' || RTWT_API_TOKEN === '') {
    fwrite(STDERR, "RTWT modular API belum dikonfigurasi.\n");
    exit(1);
}

$dsn = DB_TYPE === 'pgsql'
    ? 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME
    : DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
if (DB_TYPE === 'pgsql' && !$pdo->query("SELECT pg_try_advisory_lock(hashtext('form_am_rtwt_sync_outbox_worker'))")->fetchColumn()) {
    fwrite(STDOUT, "Another RTWT sync worker is still running.\n");
    exit(0);
}
$jobs = $pdo->query("SELECT * FROM rtwt_sync_outbox WHERE status IN ('PENDING','RETRY','REVIEW') AND next_attempt_at<=NOW() ORDER BY next_attempt_at,id LIMIT 50")->fetchAll();
$processed = 0;

foreach ($jobs as $job) {
    $payload = json_decode((string) $job['payload'], true);
    if (!is_array($payload)) $payload = [];
    $url = RTWT_API_URL;
    $photoPath = null;
    if ($job['operation'] === 'CANCEL') {
        $url = preg_replace('#/sync/?$#', '/sync/cancel', $url) ?: $url;
    } else {
        $relative = ltrim((string) ($job['photo_path'] ?? ''), '/\\');
        $absolute = ROOT . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if ($relative === '' || !is_file($absolute)) {
            $error = 'Foto Before antrean tidak ditemukan.';
            $pdo->prepare("UPDATE rtwt_sync_outbox SET status='RETRY',attempts=attempts+1,last_error=?,next_attempt_at=NOW()+INTERVAL '1 hour',updated_at=NOW() WHERE id=?")->execute([$error, $job['id']]);
            continue;
        }
        $photoPath = $absolute;
    }

    $headers = ['Authorization: Bearer ' . RTWT_API_TOKEN, 'X-RTWT-Token: ' . RTWT_API_TOKEN];
    [$response, $status, $error] = postRtwtOutbox($url, $payload, $headers, $photoPath);
    $decoded = is_string($response) ? json_decode($response, true) : null;
    $review = is_array($decoded) && !empty($decoded['data']['review_required']);

    if ($status >= 200 && $status < 300 && !$review) {
        $pdo->prepare("UPDATE rtwt_sync_outbox SET status='SYNCED',attempts=attempts+1,last_error=NULL,last_response=CAST(? AS jsonb),synced_at=NOW(),updated_at=NOW() WHERE id=?")
            ->execute([json_encode($decoded ?: ['raw' => (string) $response], JSON_UNESCAPED_UNICODE), $job['id']]);
    } else {
        $attempts = (int) $job['attempts'] + 1;
        $delayMinutes = min(60, max(1, 2 ** min($attempts - 1, 6)));
        $nextStatus = $review ? 'REVIEW' : 'RETRY';
        $message = $review ? 'Menunggu review Admin Teknik/Assigner di RTWT.' : ('HTTP ' . $status . ': ' . ($error !== '' ? $error : (string) $response));
        $pdo->prepare("UPDATE rtwt_sync_outbox SET status=?,attempts=?,last_error=?,last_response=CAST(? AS jsonb),next_attempt_at=NOW()+(? * INTERVAL '1 minute'),updated_at=NOW() WHERE id=?")
            ->execute([$nextStatus, $attempts, substr($message, 0, 2000), json_encode($decoded ?: ['raw' => (string) $response], JSON_UNESCAPED_UNICODE), $delayMinutes, $job['id']]);
    }
    $processed++;
}

// Master Part uses a separate outbox, so it cannot delay or alter ticket sync.
$masterJobs = $pdo->query("SELECT * FROM rtwt_master_part_sync_outbox WHERE status IN ('PENDING','RETRY') AND next_attempt_at<=NOW() ORDER BY next_attempt_at,id LIMIT 50")->fetchAll();
foreach ($masterJobs as $job) {
    $payload = json_decode((string)$job['payload'], true);
    if (!is_array($payload)) $payload = [];
    $url = preg_replace('#/sync/?$#', '/master-parts/sync', RTWT_API_URL) ?: RTWT_API_URL;
    $headers = ['Authorization: Bearer ' . RTWT_API_TOKEN, 'X-RTWT-Token: ' . RTWT_API_TOKEN];
    [$response, $status, $error] = postRtwtOutbox($url, $payload, $headers);
    $decoded = is_string($response) ? json_decode($response, true) : null;
    if ($status >= 200 && $status < 300) {
        $pdo->prepare("UPDATE rtwt_master_part_sync_outbox SET status='SYNCED',attempts=attempts+1,last_error=NULL,last_response=CAST(? AS jsonb),synced_at=NOW(),updated_at=NOW() WHERE id=?")
            ->execute([json_encode($decoded ?: ['raw' => (string)$response], JSON_UNESCAPED_UNICODE), $job['id']]);
    } else {
        $attempts = (int)$job['attempts'] + 1;
        $delayMinutes = min(60, max(1, 2 ** min($attempts - 1, 6)));
        $message = 'HTTP ' . $status . ': ' . ($error !== '' ? $error : (string)$response);
        $pdo->prepare("UPDATE rtwt_master_part_sync_outbox SET status='RETRY',attempts=?,last_error=?,last_response=CAST(? AS jsonb),next_attempt_at=NOW()+(? * INTERVAL '1 minute'),updated_at=NOW() WHERE id=?")
            ->execute([$attempts, substr($message, 0, 2000), json_encode($decoded ?: ['raw' => (string)$response], JSON_UNESCAPED_UNICODE), $delayMinutes, $job['id']]);
    }
    $processed++;
}

fwrite(STDOUT, "Processed {$processed} RTWT sync job(s).\n");
