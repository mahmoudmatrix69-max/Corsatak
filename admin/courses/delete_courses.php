<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
$stmt->execute([$id]);
header("Location: " . $coursesUrl . "courses.php");
exit;
