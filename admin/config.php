<?php
require "paths.php";
$host = "localhost";
$dbname = "lms_db";
$username = "root";
$password = "";
try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
} catch (PDOException $e) {
    echo "Failed: " . $e->getMessage();
}
