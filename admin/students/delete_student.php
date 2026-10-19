<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
$id=$_GET['id'];
$stmt=$conn->prepare("DELETE FROM users WHERE id=? AND role=0");
$stmt->execute([$id]);
header("Location: ".$studentsUrl ."students.php");
exit;
?>
