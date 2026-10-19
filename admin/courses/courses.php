<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['search'] ?? '');

if ($search) {
    $stmt = $conn->prepare("
        SELECT *
        FROM courses
        WHERE course_name LIKE ?
        ORDER BY id DESC
    ");

    $stmt->execute(["%" . $search . "%"]);
    $courses = $stmt->fetchAll();
} else {
    $courses = $conn->query("
        SELECT *
        FROM courses
        ORDER BY id DESC
    ")->fetchAll();
}

$lessons = $conn->query("
    SELECT course_id, duration
    FROM lessons
")->fetchAll();

$courseDurations = [];
$courseLessons = [];

foreach ($lessons as $lesson) {
    $courseId = (int)$lesson['course_id'];
    $duration = (int)($lesson['duration'] ?? 0);

    if (!isset($courseDurations[$courseId])) {
        $courseDurations[$courseId] = 0;
    }

    if (!isset($courseLessons[$courseId])) {
        $courseLessons[$courseId] = 0;
    }

    $courseDurations[$courseId] += $duration;
    $courseLessons[$courseId]++;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <title>إدارة الكورسات</title>

    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/courses.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">

    <script src="../assets/js/theme.js"></script>
</head>

<body>

    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="courses-page">

        <div class="courses-shell">

            <header class="courses-header">

                <div class="courses-title-area">

                    <div class="page-kicker">
                        لوحة إدارة المحتوى
                    </div>

                    <h1>
                    الكورسات التعليمية
                    </h1>

                    <p>
                        إدارة الكورسات ومراجعة تفاصيلها ومحتواها من مكان واحد
                    </p>

                </div>

                <a
                    href="<?= $coursesUrl . 'add_courses.php' ?>"
                    class="add-course-btn">

                    <span class="plus">
                        <i class="fa-solid fa-plus"></i>
                    </span>

                    <span>
                        إضافة كورس جديدة
                    </span>

                </a>

            </header>


            <section class="courses-toolbar">

                <form method="GET" class="courses-search-form">

                    <div class="search-box">

                        <span
                            class="search-icon"
                            aria-hidden="true">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">

                                <circle
                                    cx="11"
                                    cy="11"
                                    r="7">
                                </circle>

                                <path
                                    d="m20 20-4-4">
                                </path>

                            </svg>

                        </span>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="ابحث عن كورس بالاسم..."
                            autocomplete="off">

                    </div>

                    <button
                        type="submit"
                        class="search-btn">

                        بحث

                    </button>

                </form>

            </section>


            <section class="courses-grid">

                <?php if ($courses): ?>

                    <?php foreach ($courses as $course): ?>

                        <article class="course-card">

                            <div class="course-image">

                                <img
                                    src="../assets/images/<?= htmlspecialchars($course['image']) ?>"
                                    alt="<?= htmlspecialchars($course['course_name']) ?>"
                                    loading="lazy">

                            </div>


                            <div class="course-content">

                                <div class="course-top-info">

                                    <span class="course-category">

                                        <?= htmlspecialchars($course['category']) ?>

                                    </span>

                                    <span class="course-level">

                                        <?= htmlspecialchars($course['level']) ?>

                                    </span>

                                </div>


                                <h2 class="course-name">

                                    <?= htmlspecialchars($course['course_name']) ?>

                                </h2>


                                <p class="course-description">

                                    <?= htmlspecialchars($course['description']) ?>

                                </p>


                                <div class="course-meta">


                                    <div class="course-meta-item">

                                        <span
                                            class="meta-icon"
                                            aria-hidden="true">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2">

                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9">
                                                </circle>

                                                <path
                                                    d="M12 7v5l3 2">
                                                </path>

                                            </svg>

                                        </span>


                                        <div>

                                            <strong>
                                                <?= formatDuration($courseDurations[$course['id']] ?? 0) ?>
                                            </strong>

                                        </div>

                                    </div>


                                    <div class="course-meta-item">

                                        <span
                                            class="meta-icon"
                                            aria-hidden="true">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2">

                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9">
                                                </circle>

                                                <path
                                                    d="m10 8 6 4-6 4V8z">
                                                </path>

                                            </svg>

                                        </span>


                                        <div>

                                            <strong>
                                                <?= $courseLessons[$course['id']] ?? 0 ?>
                                            </strong>

                                            <span>
                                             من الدروس
                                            </span>

                                        </div>

                                    </div>


                                    <div class="course-meta-item">

                                        <span
                                            class="meta-icon"
                                            aria-hidden="true">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2">

                                                <path
                                                    d="M20 6 9 17l-5-5">
                                                </path>

                                            </svg>

                                        </span>


                                        <div>

                                            <strong>
                                                <?= htmlspecialchars($course['access']) ?>
                                            </strong>

                                        </div>

                                    </div>


                                </div>


                                <div class="course-footer">

                                    <div class="course-price">

                                        <span>
                                            السعر
                                        </span>

                                        <strong>

                                            <?= htmlspecialchars($course['price']) ?>
                                            جنيه

                                        </strong>

                                    </div>

                                </div>


                                <div class="course-actions">

                                    <a
                                        href="<?= $coursesUrl . 'view_courses.php?id=' . $course['id'] ?>"
                                        class="course-action view-action">

                                        <span>
                                            عرض التفاصيل
                                        </span>

                                        <b>
                                            ←
                                        </b>

                                    </a>


                                    <a
                                        href="<?= $coursesUrl . 'edit_courses.php?id=' . $course['id'] ?>"
                                        class="course-action edit-action">

                                        <span>
                                            تعديل
                                        </span>

                                    </a>


                                    <a
                                        href="<?= $coursesUrl . 'delete_courses.php?id=' . $course['id'] ?>"
                                        class="course-action delete-action"
                                        onclick="return confirm('هل أنت متأكد من حذف هذه الكورس ؟');">

                                        <span>
                                            حذف
                                        </span>

                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="no-courses">

                        <div class="no-courses-icon">
                            <i class="fa-solid fa-plus"></i>
                        </div>

                        <h2>
                            لا توجد دورات
                        </h2>

                        <p>

                            <?php if ($search): ?>

                                لم يتم العثور على أي كورس تطابق عبارة البحث
                                «<?= htmlspecialchars($search) ?>».

                            <?php else: ?>

                                لم تتم إضافة أي دورات حتى الآن
                                يمكنك البدء بإضافة أول كورس من الزر أدناه

                            <?php endif; ?>

                        </p>


                        <a
                            href="<?= $coursesUrl . 'add_courses.php' ?>"
                            class="no-courses-btn">

                            إضافة كورس جديدة

                        </a>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>

</body>

</html>