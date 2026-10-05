<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/config.php';

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
        $payload['foto_before'] = new CURLFile($absolute, mime_content_type($absolute) ?: 'image/jpeg', basename($absolute));
    }

    $headers = ['Authorization: Bearer ' . RTWT_API_TOKEN, 'X-RTWT-Token: ' . RTWT_API_TOKEN];
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_NOSIGNAL => true]);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = $response === false ? curl_error($ch) : '';
    curl_close($ch);
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

fwrite(STDOUT, "Processed {$processed} RTWT sync job(s).\n");
