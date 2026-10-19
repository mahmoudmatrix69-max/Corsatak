<?php

require_once __DIR__ . '/paths.php';
require_once $config;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$stmt = $conn->prepare("SELECT role, status FROM users WHERE id = ?");
$stmt->execute([$_SESSION["id"] ?? 0]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["role"] != 0 || $user["status"] != 1) {
    session_destroy();
    header("Location: $loginUrl");
    exit();
}

?>