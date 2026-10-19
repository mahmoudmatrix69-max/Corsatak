<?php

require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
    http_response_code(403);
    exit('Access denied');
}

$lessonId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$lessonId) {
    http_response_code(400);
    exit('Invalid lesson');
}

$stmt = $conn->prepare("
    SELECT video
    FROM lessons
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$lessonId]);

$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson || empty($lesson['video'])) {
    http_response_code(404);
    exit('Video not found');
}

$file = $videoUploadPath . basename($lesson['video']);

if (!is_file($file) || !is_readable($file)) {
    http_response_code(404);
    exit('Video file not found');
}

$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

$mimeTypes = [
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'ogg'  => 'video/ogg',
    'mov'  => 'video/quicktime',
    'm4v'  => 'video/mp4'
];

$mime = $mimeTypes[$extension] ?? 'application/octet-stream';
$size = filesize($file);

if ($size === false || $size < 1) {
    http_response_code(404);
    exit('Empty video');
}


if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$start = 0;
$end = $size - 1;
$status = 200;

$range = $_SERVER['HTTP_RANGE'] ?? '';

if ($range !== '') {

    if (!preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $matches)) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    $rangeStart = $matches[1];
    $rangeEnd   = $matches[2];

    if ($rangeStart === '' && $rangeEnd === '') {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    if ($rangeStart === '') {
        $suffixLength = (int)$rangeEnd;

        if ($suffixLength <= 0) {
            header('Content-Range: bytes */' . $size);
            http_response_code(416);
            exit;
        }

        $start = max(0, $size - $suffixLength);
        $end = $size - 1;
    } else {
        $start = (int)$rangeStart;

        if ($start >= $size) {
            header('Content-Range: bytes */' . $size);
            http_response_code(416);
            exit;
        }

        if ($rangeEnd !== '') {
            $end = min((int)$rangeEnd, $size - 1);
        }
    }

    if ($start > $end) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    $status = 206;
}

$length = $end - $start + 1;

http_response_code($status);

header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);

if ($status === 206) {
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
}

header('Cache-Control: private, no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

while (ob_get_level() > 0) {
    ob_end_clean();
}

$handle = fopen($file, 'rb');

if ($handle === false) {
    http_response_code(500);
    exit('Unable to open video');
}

if ($start > 0 && fseek($handle, $start, SEEK_SET) !== 0) {
    fclose($handle);
    http_response_code(500);
    exit('Unable to seek video');
}


$bufferSize = 1024 * 1024;
$remaining = $length;

while ($remaining > 0 && !feof($handle)) {
    $readLength = min($bufferSize, $remaining);
    $chunk = fread($handle, $readLength);

    if ($chunk === false || $chunk === '') {
        break;
    }

    echo $chunk;
    $remaining -= strlen($chunk);

    if (function_exists('ob_flush')) {
        @ob_flush();
    }

    flush();

    if (connection_status() !== CONNECTION_NORMAL) {
        break;
    }
}

fclose($handle);
exit;
