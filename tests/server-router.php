<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . '/../' . ltrim($path, '/');
if ($path !== '/' && is_file($file)) {
    if (strpos($path, '/uploads/') === 0 || strpos($path, 'uploads/') === 0) {
        header('X-Content-Type-Options: nosniff');
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
        ];
        if (isset($mimes[$ext])) {
            header('Content-Type: ' . $mimes[$ext]);
        }
        readfile($file);
        return true;
    }
    return false;
}
require __DIR__ . '/../index.php';
