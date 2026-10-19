<?php

$config = __DIR__ . '/config.php';
$session = __DIR__ . '/session.php';
$paths = __DIR__ . '/paths.php';

$studentUrl = '/ischool/student/';

$adminUrl = '/ischool/admin/';



$assetsUrl = $studentUrl . 'assets/';
$cssUrl = $assetsUrl . 'css/';
$imageUrl = $studentUrl . 'image_courses/';
$videoUrl = $studentUrl . 'videos/';
$includesUrl = $studentUrl . 'includes/';

$loginCssUrl = $cssUrl . 'login.css';
$styleCssUrl = $cssUrl . 'style.css';
$headerCss = $includesUrl . 'header.css';

$indexUrl = $studentUrl . 'index.php';
$loginUrl = $studentUrl . 'auth.php';
$logoutUrl = $studentUrl . 'logout.php';

$studentsUrl = $studentUrl . 'students/';
$coursesUrl = $studentUrl . 'courses/';

