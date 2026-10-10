<?php
require __DIR__ . '/../includes/functions.php';

$u = require_user();
$filename = $u['profile_image'];

if (!preg_match('/^[a-f0-9]{32}$/', $filename)) {
    http_response_code(404);
    exit;
}

$path = __DIR__ . '/../var/avatars/' . $filename;
if (!is_file($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
header('Cache-Control: private, no-store');
readfile($path);