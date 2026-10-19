<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../includes/functions.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");

$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found");
}

$lessons = $conn->query("
    SELECT course_id, duration
    FROM lessons

")->fetchAll();

$courseDurations = [];

foreach ($lessons as $lesson) {
    $courseId = (int)$lesson['course_id'];
    $duration = (int)($lesson['duration'] ?? 0);

    if (!isset($courseDurations[$courseId])) {
        $courseDurations[$courseId] = 0;
    }

    $courseDurations[$courseId] += $duration;
}


$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM lessons 
    WHERE course_id = ?
");

$stmt->execute([$course['id']]);

$totalLessons = $stmt->fetchColumn();


?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>تفاصيل الكورس - <?= htmlspecialchars($course['course_name']) ?></title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/view_courses.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="view-page">
        <header class="view-header">
            <div class="view-heading">
                <span>تفاصيل المحتوى التعليمي</span>
                <h1>تفاصيل الكورس</h1>
                <p>
                    عرض شامل لمعلومات الكورس وإعداداتها الحالية
                </p>
            </div>
            <a
                href="<?= $coursesUrl . 'courses.php' ?>"
                class="back-button">
                <span>→</span>
                <span>العودة إلى الكورسات</span>
            </a>
        </header>
        <div class="view-layout">
            <section class="view-main">
                <article class="view-card course-main-card">
                    <div class="course-image">
                        <img
                            src="../assets/images/<?= htmlspecialchars($course['image']) ?>"
                            alt="<?= htmlspecialchars($course['course_name']) ?>">
                    </div>
                    <div class="course-content">
                        <div class="course-tags">
                            <span class="tag tag-category">
                                <?= htmlspecialchars($course['category']) ?>
                            </span>
                            <span class="tag tag-level">
                                <?= htmlspecialchars($course['level']) ?>
                            </span>
                        </div>
                        <h2 class="course-title">
                            <?= htmlspecialchars($course['course_name']) ?>
                        </h2>
                        <p class="course-description">
                            <?= nl2br(htmlspecialchars($course['description'])) ?>
                        </p>
                        <div class="stats">
                            <div class="stat">
                                <div class="stat-top">
                                    <span class="stat-icon">◷</span>
                                    <div>
                                        <strong><?= formatDuration($courseDurations[$course['id']] ?? 0) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="stat">
                                <div class="stat-top">
                                    <span class="stat-icon">▶</span>
                                    <div>
                                        <strong><?= $totalLessons ?></strong>
                                        <small>درس</small>
                                    </div>
                                </div>
                            </div>
                            <div class="stat">
                                <div class="stat-top">
                                    <span class="stat-icon">ج</span>
                                    <div>
                                        <strong><?= htmlspecialchars($course['price']) ?></strong>
                                        <small>جنيه</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                <article class="view-card">
                    <header class="information-header">
                        <div>
                            <h2>معلومات الكورس</h2>
                        </div>
                        <span>نظرة عامة</span>
                    </header>
                    <div class="information-grid">
                        <div class="information-item">
                            <span>اسم الكورس</span>
                            <strong><?= htmlspecialchars($course['course_name']) ?></strong>
                        </div>
                        <div class="information-item">
                            <span>التصنيف</span>
                            <strong><?= htmlspecialchars($course['category']) ?></strong>
                        </div>
                        <div class="information-item">
                            <span>المستوى</span>
                            <strong><?= htmlspecialchars($course['level']) ?></strong>
                        </div>
                        <div class="information-item">
                            <span>المدة</span>
                            <strong><?= formatDurationTotal($courseDurations[$course['id']] ?? 0) ?></strong>
                        </div>
                        <div class="information-item">
                            <span>عدد الدروس</span>
                            <strong><?= $totalLessons?></strong>
                        </div>
                        <div class="information-item">
                            <span>السعر</span>
                            <strong><?= htmlspecialchars($course['price']) ?> جنيه</strong>
                        </div>
                    </div>
                </article>
            </section>
            <aside class="view-sidebar">
                <article class="view-card price-card">
                    <span class="price-label">سعر الكورس</span>
                    <div class="price">
                        <strong><?= htmlspecialchars($course['price']) ?></strong>
                        <small>جنيه</small>
                    </div>
                    <div class="divider"></div>
                    <div class="details">
                        <div class="detail-row">
                            <span>الكورس</span>
                            <strong><?= htmlspecialchars($course['course_name']) ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>التصنيف</span>
                            <strong><?= htmlspecialchars($course['category']) ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>المستوى</span>
                            <strong><?= htmlspecialchars($course['level']) ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>المدة</span>
                            <strong><?= formatDurationTotal($courseDurations[$course['id']] ?? 0) ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>الدروس</span>
                            <strong><?= $totalLessons ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>الحالة</span>
                            <strong>
                                <span class="status <?= $course['status'] ? 'published' : 'draft' ?>">
                                    <?= $course['status'] ? 'منشورة' : 'مسودة' ?>
                                </span>
                            </strong>
                        </div>
                    </div>
                    <div class="up-to-date">
                        <span>✓</span>
                        <div>
                            بيانات الكورس محدثة حاليًا
                        </div>
                    </div>
                </article>
                <div class="view-card action-card">
                    <a
                        href="<?= $coursesUrl . 'manage_course.php?id=' . $course['id'] ?>"
                        class="action manage-action">
                        إدارة محتوى الكورس
                    </a>
                    <a
                        href="<?= $coursesUrl . 'courses.php' ?>"
                        class="action return-action">
                        العودة إلى الكورسات
                    </a>
                </div>
            </aside>
        </div>
    </main>
</body>

</html>