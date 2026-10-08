<?php

/**
 * One-way Form AM master-part sync. Failures only affect this outbox and never
 * block a Master Part save/takeout flow.
 */
class RtwtMasterPartSync
{
    public static function queueAndSend($db, array $payload)
    {
        try {
            $reference = 'form_am_master:' . trim((string) $payload['machine_key']) . ':' . trim((string) $payload['field_name']) . ':' . intval($payload['source_machine_id'] ?? 0);
            $payload['source_reference'] = $reference;
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $db->rawQuery("INSERT INTO rtwt_master_part_sync_outbox(source_reference,payload,status,attempts,next_attempt_at) VALUES(?,CAST(? AS jsonb),'PENDING',0,NOW()) ON CONFLICT(source_reference) DO UPDATE SET payload=EXCLUDED.payload,status='PENDING',attempts=0,next_attempt_at=NOW(),last_error=NULL,last_response=NULL,synced_at=NULL,updated_at=NOW()", array($reference, $encoded));

            if (!defined('RTWT_API_MODE') || RTWT_API_MODE !== 'modular' || !defined('RTWT_API_TOKEN') || RTWT_API_TOKEN === '') return;
            $url = self::endpoint();
            if ($url === '') return;
            list($response, $status, $error) = self::send($url, $payload);
            $decoded = is_string($response) ? json_decode($response, true) : null;
            if ($status >= 200 && $status < 300) {
                $db->rawQuery("UPDATE rtwt_master_part_sync_outbox SET status='SYNCED',attempts=attempts+1,last_error=NULL,last_response=CAST(? AS jsonb),synced_at=NOW(),updated_at=NOW() WHERE source_reference=?", array(json_encode($decoded ?: array('raw' => (string)$response), JSON_UNESCAPED_UNICODE), $reference));
                return;
            }
            self::markRetry($db, $reference, 1, 'HTTP ' . $status . ': ' . ($error !== '' ? $error : (string)$response), $decoded);
        } catch (Throwable $e) {
            error_log('RTWT master part sync tidak dapat diantrikan: ' . $e->getMessage());
        }
    }

    public static function endpoint()
    {
        $base = defined('RTWT_API_URL') ? trim((string)RTWT_API_URL) : '';
        return preg_replace('#/sync/?$#', '/master-parts/sync', $base) ?: '';
    }

    public static function send($url, array $payload)
    {
        $headers = array('Authorization: Bearer ' . RTWT_API_TOKEN, 'X-RTWT-Token: ' . RTWT_API_TOKEN);
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) return array(false, 0, 'cURL tidak dapat memulai koneksi.');
            curl_setopt_array($ch, array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($payload, '', '&'), CURLOPT_HTTPHEADER => array_merge($headers, array('Content-Type: application/x-www-form-urlencoded')), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_NOSIGNAL => true));
            $response = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
            return array($response, $status, $error);
        }
        if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) return array(false, 0, 'PHP cURL dan HTTP stream wrapper tidak tersedia di server.');
        $body = http_build_query($payload, '', '&');
        $context = stream_context_create(array('http' => array('method' => 'POST', 'header' => implode("\r\n", array_merge($headers, array('Content-Type: application/x-www-form-urlencoded', 'Content-Length: ' . strlen($body)))), 'content' => $body, 'timeout' => 15, 'ignore_errors' => true)));
        $response = @file_get_contents($url, false, $context); $status = 0;
        foreach (($http_response_header ?? array()) as $header) if (preg_match('#^HTTP/\\S+\\s+(\\d{3})#', $header, $matches)) { $status = (int)$matches[1]; break; }
        $error = $response === false ? (string)((error_get_last()['message'] ?? 'HTTP stream gagal terhubung.')) : '';
        return array($response, $status, $error);
    }

    public static function markRetry($db, $reference, $attempts, $error, $response = null)
    {
        $delay = min(60, max(1, 2 ** min(max(0, (int)$attempts - 1), 6)));
        $db->rawQuery("UPDATE rtwt_master_part_sync_outbox SET status='RETRY',attempts=?,last_error=?,last_response=CAST(? AS jsonb),next_attempt_at=NOW()+(? * INTERVAL '1 minute'),updated_at=NOW() WHERE source_reference=?", array((int)$attempts, substr((string)$error, 0, 2000), json_encode($response ?: array(), JSON_UNESCAPED_UNICODE), $delay, $reference));
    }
}
