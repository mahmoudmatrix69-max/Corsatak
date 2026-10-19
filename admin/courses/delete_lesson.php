<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
$lesson_id = $_GET['id'] ?? '';
$stmt = $conn->prepare("
    SELECT course_id, video
    FROM lessons
    WHERE id = ?
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();
if (!$lesson) {
    die("Lesson not found");
}
if (!empty($lesson['video'])) {
    $videoPath = $videoUploadPath . $lesson['video'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}
$stmt = $conn->prepare("
    DELETE FROM lessons
    WHERE id = ?
");
$stmt->execute([$lesson_id]);
header(
    "Location: " .
        $coursesUrl .
        "manage_course.php?id=" .
        $lesson['course_id']
);
exit;
