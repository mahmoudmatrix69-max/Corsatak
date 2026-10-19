<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../includes/functions.php';

$course_id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("لم يتم العثور على الكورس");
}

$stmt = $conn->prepare("
    SELECT *
    FROM lessons
    WHERE course_id = ?
    ORDER BY lesson_order ASC, id ASC
");

$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();


$courseDurations = [];

foreach ($lessons as $lesson) {
    $courseId = (int)$lesson['course_id'];
    $duration = (int)($lesson['duration'] ?? 0);

    if (!isset($courseDurations[$courseId])) {
        $courseDurations[$courseId] = 0;
    }

    $courseDurations[$courseId] += $duration;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>إدارة الكورس</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/manage_course.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="manage-page">
        <header class="manage-header">
            <div class="manage-title">
                <span>إدارة المحتوى التعليمي</span>
                <h1>إدارة الكورس</h1>
                <p>
                    إدارة دروس الكورس وفيديوهاتها وترتيب المحتوى من مكان واحد
                </p>
            </div>
            <div class="manage-actions">
                <a
                    href="<?= $coursesUrl . 'view_courses.php?id=' . $course['id'] ?>"
                    class="header-btn back-btn">
                    <span><i class="fa-solid fa-arrow-left"></i></span>
                    <span>عرض الكورس</span>
                </a>
                <a
                    href="<?= $coursesUrl . 'add_lesson.php?course_id=' . $course['id'] ?>"
                    class="manage-course-add-btn">
                    <span class="plus"><i class="fa-solid fa-plus"></i></span>
                    <span>إضافة درس</span>
                </a>
            </div>
        </header>
        <section class="course-summary">
            <div class="summary-card course-identity">
                <div class="course-cover">
                    <img
                        src="../assets/images/<?= htmlspecialchars($course['image']) ?>"
                        alt="<?= htmlspecialchars($course['course_name']) ?>">
                </div>
                <div class="course-identity-content">
                    <span class="badge">
                        <?= htmlspecialchars($course['category']) ?>
                    </span>
                    <h2>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </h2>
                    <p>
                        المستوى:
                        <?= htmlspecialchars($course['level']) ?>
                    </p>
                </div>
            </div>
            <div class="summary-card stats-card">
                <div class="stat">
                    <span class="stat-icon">▶</span>
                    <div class="stat-content">
                        <strong><?= count($lessons) ?></strong>
                        <span>درس</span>
                    </div>
                </div>
                <div class="stat">
                    <span class="stat-icon">◷</span>
                    <div class="stat-content">
                        <strong><?= formatDuration($courseDurations[$course_id] ?? 0) ?></strong>
                    </div>
                </div>
                <div class="stat">
                    <span class="stat-icon">ج</span>
                    <div class="stat-content">
                        <strong><?= htmlspecialchars($course['price']) ?></strong>
                        <span>جنيه</span>
                    </div>
                </div>
            </div>
        </section>
        <section class="lessons-section">
            <header class="lessons-header">
                <div>
                    <h2>دروس الكورس</h2>
                    <p>
                        إدارة جميع الدروس والفيديوهات الموجودة داخل هذه الكورس
                    </p>
                </div>
                <span class="lessons-count">
                    <?= count($lessons) ?>
                </span>
            </header>

            <?php if ($lessons): ?>
                <div class="lesson-list">

                    <?php foreach ($lessons as $lesson): ?>

                        <article class="lesson">
                            
                            <div class="lesson-number">
                                <?= htmlspecialchars($lesson['lesson_order']) ?>
                            </div>
                            <div class="lesson-content">

                                <h3>
                                    <?= htmlspecialchars($lesson['lesson_name']) ?>
                                </h3>

                                <p>
                                    الدرس <?= htmlspecialchars($lesson['lesson_order']) ?>
                                    <?php if (!empty($lesson['video'])): ?>
                                        <span class="video-status video-available">
                                            الفيديو متاح
                                        </span>
                                    <?php else: ?>
                                        <span class="video-status video-missing">
                                            لا يوجد فيديو
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="lesson-actions">
                                <?php if (!empty($lesson['video'])): ?>
                                    <a
                                        href="<?= $coursesUrl . 'watch_lesson.php?id=' . $lesson['id'] ?>"
                                        class="lesson-btn lesson-view">
                                        مشاهدة
                                    </a>
                                <?php endif; ?>
                                <a
                                    href="<?= $coursesUrl . 'edit_lesson.php?id=' . $lesson['id'] ?>"
                                    class="lesson-btn lesson-edit">
                                    تعديل
                                </a>
                                <a
                                    href="<?= $coursesUrl . 'delete_lesson.php?id=' . $lesson['id'] ?>"
                                    class="lesson-btn lesson-delete"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا الدرس ؟');">
                                    حذف
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-lessons">
                    <div class="empty-icon"><i class="fa-solid fa-plus"></i></div>
                    <h3>لا توجد دروس حتى الآن</h3>
                    <p>
                        ابدأ في بناء محتوى الكورس بإضافة أول درس إليها
                    </p>
                    <a
                        href="<?= $coursesUrl . 'add_lesson.php?course_id=' . $course['id'] ?>"
                        class="empty-add">
                        إضافة أول درس
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
