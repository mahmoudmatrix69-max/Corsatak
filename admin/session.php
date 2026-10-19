<?php
require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}
if ($_SESSION["role"] != 1) {
    header("Location: ../index.php");
    exit();
}
