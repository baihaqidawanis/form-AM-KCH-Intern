<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/config.php';

$dsn = DB_TYPE === 'pgsql'
    ? 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME
    : DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$sql = file_get_contents(dirname(__DIR__) . '/database/migrations/20261002_rtwt_sync_outbox.sql');
if ($sql === false) throw new RuntimeException('Migration outbox RTWT tidak ditemukan.');
$pdo->exec($sql);
fwrite(STDOUT, "Form AM RTWT outbox migration applied.\n");
