<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/config.php';
if (DB_TYPE !== 'pgsql') throw new RuntimeException('Migration multi-korelasi Form AM memerlukan PostgreSQL.');

$pdo = new PDO(
    'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
    DB_USERNAME,
    DB_PASSWORD,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$sql = file_get_contents(dirname(__DIR__) . '/database/migrations/20261026_multi_nok_correlation.sql');
if ($sql === false) throw new RuntimeException('Migration multi-korelasi Form AM tidak ditemukan.');
$pdo->exec($sql);
fwrite(STDOUT, "Form AM multi-correlation migration applied.\n");
