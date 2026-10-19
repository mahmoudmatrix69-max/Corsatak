<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $title          = trim($_POST["title"]);
    $price          = trim($_POST["price"]);
    $description    = trim($_POST["description"]);
    $category       = trim($_POST["category"] ?? "");
    $level          = trim($_POST["level"] ?? "");
    $status         = isset($_POST["status"]) ? 1 : 0;
    $image          = $_FILES["image"];
    $message = "";

    if (empty($image["tmp_name"])) {
        $sql = "INSERT INTO courses ( course_name , category , level , price , description , status )
        VALUES ( ? , ? , ? , ? , ? , ? )";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $title,
            $category,
            $level,
            $price,
            $description,
            $status
        ]);
    } else {

        $imageName = rand(100000000000000, 999999999999999) . "_" . $image["name"];
        $error_image = "";
        $imagePath = $imageUploadPath;
        if (!is_dir($imagePath) && !mkdir($imagePath, 0755, true)) {
            $error_image = "Image upload directory could not be created";
        }

        if (!is_writable($imagePath)) {
            $error_image = "Image upload directory is not writable";
        }

        if (!move_uploaded_file($image["tmp_name"], $imagePath . $imageName)) {
            $error_image = "Course image upload failed";
        }

        $sql = "INSERT INTO courses (
            course_name,
            category,
            level,
            price,
            description,
            image,
            status
        )

        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $title,
            $category,
            $level,
            $price,
            $description,
            $imageName,
            $status
        ]);
    }
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
    <title>إضافة كورس جديدة</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/add_courses.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="page">
        <header class="page-header">
            <div class="header-content">
                <span class="eyebrow">إدارة المحتوى التعليمي</span>
                <h1>إضافة كورس جديدة</h1>
                <p>أدخل بيانات الكورس وارفع صورتها ثم قم بنشرها للطلاب.</p>
            </div>
            <a href="<?= $coursesUrl . "courses.php" ?>" class="back-button">
                <span>→</span>
                <span>العودة إلى الكورسات</span>
            </a>
        </header>
        <form class="course-form" method="POST" enctype="multipart/form-data">
            <div class="form-content">
                <div class="fields">
                    <div class="field full">
                        <label for="course-title">
                            اسم الكورس <span>*</span>
                        </label>
                        <input
                            type="text"
                            id="course-title"
                            name="title"
                            required
                            placeholder="اكتب اسم الكورس...">
                    </div>
                    <div class="fields-row two">
                        <div class="field">
                            <label for="category">
                                التصنيف <span>*</span>
                            </label>
                            <select id="category" name="category">
                                <option value="" selected disabled>اختر التصنيف</option>
                                <option value="programming">البرمجة</option>
                                <option value="web-development">تطوير الويب</option>
                                <option value="web-design">تصميم الويب</option>
                                <option value="database">قواعد البيانات</option>
                                <option value="data-analysis">تحليل البيانات</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="level">
                                مستوى الكورس <span>*</span>
                            </label>
                            <select id="level" name="level">
                                <option value="" selected disabled>اختر المستوى</option>
                                <option value="beginner">مبتدئ</option>
                                <option value="intermediate">متوسط</option>
                                <option value="advanced">متقدم</option>
                            </select>
                        </div>
                    </div>
                    <div class="fields-row three">
                        <div class="field">
                            <label for="price">
                                سعر الكورس <span>*</span>
                            </label>
                            <div class="unit-input">
                                <input type="number" id="price" name="price" placeholder="0.00" min="0" step="0.01" required>
                                <span>جنيه</span>
                            </div>
                        </div>
                    </div>
                    <div class="field description-field">
                        <label for="description">وصف الكورس</label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="اكتب وصفًا واضحًا ومختصرًا عن محتوى الكورس..."></textarea>
                    </div>
                </div>
                <div class="image-section">
                    <label class="image-upload">
                        <input
                            type="file"
                            id="course-image"
                            name="image"
                            required
                            accept="image/png,image/jpeg,image/webp,image/gif">
                        <img id="preview" alt="معاينة صورة الكورس">
                        <div class="upload-content">
                            <div class="upload-icon">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 16V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <path d="M7.5 8.5L12 4L16.5 8.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M5 20H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </div>
                            <strong>رفع صورة الكورس</strong>
                            <span>اضغط هنا لاختيار صورة</span>
                            <small>PNG أو JPG أو WEBP أو GIF</small>
                            <small>المقاس المقترح: 1280 × 720 بكسل</small>
                        </div>
                        <div id="file-info">
                            <span id="file-name"></span>
                            <button type="button" id="remove-image">إزالة الصورة</button>
                        </div>
                    </label>
                </div>
            </div>
            <div class="publish-section">
                <div class="publish-content">
                    <div class="publish-icon">✓</div>
                    <div>
                        <strong>نشر الكورس مباشرة</strong>
                        <span>عند التفعيل ستصبح الكورس متاحة للطلاب فور إنشائها.</span>
                    </div>
                </div>
                <label class="switch">
                    <input type="checkbox" name="status">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="form-footer">
                <a href="<?= $coursesUrl . "courses.php" ?>" class="cancel-button">
                    إلغاء
                </a>
                <button type="submit" class="create-button">
                    <span>إنشاء الكورس</span>
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 12H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        <path d="M13 6L19 12L13 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </form>
    </main>
    <script>
        const input = document.getElementById("course-image");
        const preview = document.getElementById("preview");
        const fileName = document.getElementById("file-name");
        const remove = document.getElementById("remove-image");
        const uploadContent = document.querySelector(".upload-content");
        const fileInfo = document.getElementById("file-info");
        input.addEventListener("change", function() {
            const file = input.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.style.display = "block";
            uploadContent.style.display = "none";
            fileName.textContent = file.name;
            fileInfo.style.display = "flex";
            remove.style.display = "block";
        });
        remove.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            input.value = "";
            preview.src = "";
            preview.style.display = "none";
            uploadContent.style.display = "flex";
            fileName.textContent = "";
            fileInfo.style.display = "none";
            remove.style.display = "none";
        });
    </script>
</body>

</html>