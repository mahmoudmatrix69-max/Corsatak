<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) {
    die("Course not found");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $course_name    = trim($_POST["course_name"]);
    $price          = trim($_POST["price"]);
    $description    = trim($_POST["description"]);
    $category       = trim($_POST["category"] ?? "");
    $level          = trim($_POST["level"] ?? "");
    $status         = isset($_POST["status"]) ? 1 : 0;
    $imageName      = $course['image'];

    if (!empty($_FILES["image"]["name"])) {
        $image = $_FILES["image"];
        $imageName = rand(100000000000000, 999999999999999) . "_" . $image["name"];
        $imagePath = $imageUploadPath;
        if (!is_dir($imagePath)) {
            mkdir($imagePath, 0755, true);
        }

        move_uploaded_file(
            $image["tmp_name"],
            $imagePath . $imageName
        );

    }

    $sql = "UPDATE courses SET
        course_name = ?,
        category = ?,
        level = ?,
        price = ?,
        description = ?,
        image = ?,
        status = ?
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $course_name,
        $category,
        $level,
        $price,
        $description,
        $imageName,
        $status,
        $id
    ]);

    header("Location: " . $coursesUrl . "courses.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>تعديل الكورس</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/edit_courses.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="edit-course-page">
        <header class="edit-course-header">
            <div class="edit-course-title">
                <span>إدارة المحتوى التعليمي</span>
                <h1>تعديل الكورس</h1>
                <p>حدّث بيانات الكورس وإعداداتها وصورتها من مكان واحد.</p>
            </div>
            <a href="<?= $coursesUrl . 'courses.php' ?>" class="edit-back-btn">
                <span>→</span>
                <span>العودة إلى الكورسات</span>
            </a>
        </header>
        <form method="POST" enctype="multipart/form-data" class="edit-course-form">
            <div class="edit-course-layout">
                <section class="edit-course-main">
                    <div class="edit-course-card">
                        <div class="edit-card-header">
                            <h2>بيانات الكورس</h2>
                            <p>عدّل المعلومات الأساسية الخاصة بهذه الكورس</p>
                        </div>
                        <div class="edit-form-grid">
                            <div class="edit-form-group edit-form-full">
                                <label for="course_name">اسم الكورس</label>
                                <input
                                    type="text"
                                    id="course_name"
                                    name="course_name"
                                    value="<?= htmlspecialchars($course['course_name']) ?>"
                                    placeholder="اكتب اسم الكورس..."
                                    required>
                            </div>
                            <div class="edit-form-group">
                                <label for="category">التصنيف</label>
                                <select id="category" name="category">
                                    <option value="programming" <?= $course['category'] == 'programming' ? 'selected' : '' ?>>البرمجة</option>
                                    <option value="web-development" <?= $course['category'] == 'web-development' ? 'selected' : '' ?>>تطوير الويب</option>
                                    <option value="web-design" <?= $course['category'] == 'web-design' ? 'selected' : '' ?>>تصميم الويب</option>
                                    <option value="database" <?= $course['category'] == 'database' ? 'selected' : '' ?>>قواعد البيانات</option>
                                    <option value="data-analysis" <?= $course['category'] == 'data-analysis' ? 'selected' : '' ?>>تحليل البيانات</option>
                                </select>
                            </div>
                            <div class="edit-form-group">
                                <label for="level">مستوى الكورس</label>
                                <select id="level" name="level">
                                    <option value="beginner" <?= $course['level'] == 'beginner' ? 'selected' : '' ?>>مبتدئ</option>
                                    <option value="intermediate" <?= $course['level'] == 'intermediate' ? 'selected' : '' ?>>متوسط</option>
                                    <option value="advanced" <?= $course['level'] == 'advanced' ? 'selected' : '' ?>>متقدم</option>
                                </select>
                            </div>
                            <div class="edit-form-group">
                                <label for="price">سعر الكورس</label>
                                <div class="edit-input-unit">
                                    <input type="number" id="price" name="price" value="<?= htmlspecialchars($course['price']) ?>" min="0" step="0.01" placeholder="0.00" required>
                                    <span>جنيه</span>
                                </div>
                            </div>
                            <div class="edit-form-group edit-form-full">
                                <label for="description">وصف الكورس</label>
                                <textarea id="description" name="description" placeholder="اكتب وصفًا واضحًا عن الكورس..."><?= htmlspecialchars($course['description']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="edit-course-card">
                        <div class="edit-card-header">
                            <h2>حالة الكورس</h2>
                            <p>تحكم في ظهور الكورس وإتاحتها للطلاب.</p>
                        </div>
                        <div class="edit-status-options">
                            <label class="edit-status-option">
                                <input
                                    type="checkbox"
                                    name="status"
                                    value="1"
                                    <?= $course['status'] ? 'checked' : '' ?>>
                                <span class="edit-radio">
                                    <span></span>
                                </span>
                                <div>
                                    <strong>الكورس منشورة</strong>
                                    <small>الكورس ظاهرة ومتاحة للطلاب.</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </section>
                <aside class="edit-course-sidebar">
                    <div class="edit-course-card edit-image-card">
                        <div class="edit-card-header">
                            <h2>صورة الكورس</h2>
                            <p>حدّث صورة الغلاف الخاصة بالكورس</p>
                        </div>
                        <div class="edit-current-image">
                            <img
                                id="current-image"
                                src="../assets/images/<?= htmlspecialchars($course['image']) ?>"
                                alt="<?= htmlspecialchars($course['course_name']) ?>">
                            <img id="image-preview" alt="معاينة الصورة الجديدة">
                        </div>
                        <label for="image" class="edit-image-btn">
                            <span><i class="fa-solid fa-plus"></i></span>
                            <span>تغيير الصورة</span>
                        </label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/png,image/jpeg,image/webp,image/gif"
                            hidden>
                        <p class="edit-image-note">PNG أو JPG أو WEBP أو GIF</p>
                    </div>
                    <div class="edit-course-card edit-summary-card">
                        <div class="edit-card-header">
                            <h2>ملخص الكورس</h2>
                        </div>
                        <div class="edit-summary-list">
                            <div>
                                <span>الكورس</span>
                                <strong><?= htmlspecialchars($course['course_name']) ?></strong>
                            </div>
                            <div>
                                <span>السعر</span>
                                <strong><?= htmlspecialchars($course['price']) ?> جنيه</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
            <div class="edit-course-actions">
                <a href="<?= $coursesUrl . 'courses.php' ?>" class="edit-cancel-btn">
                    إلغاء
                </a>
                <button type="submit" class="edit-save-btn">
                    <span>حفظ التعديلات</span>
                    <b>←</b>
                </button>
            </div>
        </form>
    </main>
    <script>
        const imageInput = document.getElementById('image');
        const currentImage = document.getElementById('current-image');
        const imagePreview = document.getElementById('image-preview');
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                imagePreview.style.display = 'none';
                currentImage.style.display = 'block';
                return;
            }
            if (!file.type.startsWith('image/')) {
                this.value = '';
                alert('يرجى اختيار ملف صورة صالح.');
                return;
            }
            const url = URL.createObjectURL(file);
            imagePreview.src = url;
            imagePreview.style.display = 'block';
            currentImage.style.display = 'none';
        });
    </script>
</body>

</html>