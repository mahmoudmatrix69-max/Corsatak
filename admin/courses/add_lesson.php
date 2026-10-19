<?php

require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../includes/functions.php';

$course_id = $_GET['course_id'] ?? '';

$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);

$course = $stmt->fetch();

if (!$course) {
    die("Course not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isAjaxUpload = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $respond = function (bool $success, string $message = '', string $redirect = '') use ($isAjaxUpload) {

        if ($isAjaxUpload) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success'   => $success,
                'message'   => $message,
                'redirect'  => $redirect
            ]);

            exit;
        }

        if (!$success) {
            die($message);
        }

        header('Location: ' . $redirect);
        exit;
    };

    $lesson_name    = trim($_POST['lesson_name'] ?? '');
    $lesson_order   = trim($_POST['lesson_order'] ?? '');

    if ($lesson_name === '' || $lesson_order === '') {
        $respond(false, 'Please complete the lesson information');
    }

    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $respond(false, 'Please select a valid video file');
    }

    $video = $_FILES['video'];
    $allowedExtensions = ['mp4', 'webm', 'ogg'];

    $extension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
    $maxVideoSize = 1024 * 1024 * 1024;

    if (!in_array($extension, $allowedExtensions, true)) {
        $respond(false, 'Only MP4 , WebM and OGG videos are allowed');
    }

    if ($video['size'] <= 0 || $video['size'] > $maxVideoSize) {
        $respond(false, 'The video size is invalid or exceeds 1 GB');
    }

    $videoDirectory = $videoUploadPath;
    if (!is_dir($videoDirectory) && !mkdir($videoDirectory, 0755, true)) {
        $respond(false, 'The video directory could not be created');
    }

    if (!is_writable($videoDirectory)) {
        $respond(false, 'The video directory is not writable');
    }
    $videoName = bin2hex(random_bytes(12)) . '.' . $extension;
    $videoPath = $videoDirectory . $videoName;

    if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
        $respond(false, 'The video upload could not be completed');
    }

    $duration = getVideoDuration($videoPath);

    try {
        $sql = "INSERT INTO lessons (course_id, lesson_name, lesson_order, video , duration) VALUES (?, ?, ?, ? , ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$course_id, $lesson_name, $lesson_order, $videoName, $duration]);
    } catch (Throwable $e) {
        if (file_exists($videoPath)) {
            unlink($videoPath);
        }

        $respond(false, 'The video was uploaded, but the lesson could not be saved');
    }

    $redirect = $coursesUrl . 'manage_course.php?id=' . $course_id;
    $respond(true, 'Lesson uploaded successfully', $redirect);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>إضافة درس جديد</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/add_lesson.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="add-lesson-page">
        <header class="add-lesson-header">
            <div class="add-lesson-title">
                <span>محتوى الكورس</span>
                <h1>إضافة درس جديد</h1>
                <p>أضف درسًا جديدًا إلى الكورس وارفع فيديو الدرس بسهولة</p>
            </div>
            <a href="<?= $coursesUrl . 'manage_course.php?id=' . $course['id'] ?>" class="add-lesson-back">
                <span>→</span>
                <span>العودة إلى الكورس</span>
            </a>
        </header>
        <div class="add-lesson-layout">
            <section class="add-lesson-main">
                <div class="add-lesson-card">
                    <div class="add-lesson-card-header">
                        <h2>بيانات الدرس</h2>
                        <p>أدخل بيانات الدرس ثم اختر ملف الفيديو الخاص به</p>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="add-lesson-form">
                        <div class="add-lesson-field">
                            <label for="lesson_name">اسم الدرس</label>
                            <input type="text" id="lesson_name" name="lesson_name" placeholder="اكتب اسم الدرس..." required>
                            <small>استخدم اسمًا واضحًا يوضح محتوى الدرس</small>
                        </div>
                        <div class="add-lesson-field">
                            <label for="lesson_order">ترتيب الدرس</label>
                            <input type="number" id="lesson_order" name="lesson_order" placeholder="1" min="1" required>
                            <small>حدد مكان الدرس داخل ترتيب محتوى الكورس</small>
                        </div>
                        <div class="add-lesson-field">
                            <label for="video">فيديو الدرس</label>
                            <div class="add-video-box">
                                <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/ogg" required>
                                <div class="add-video-icon">▶</div>
                                <strong>اختر فيديو الدرس</strong>
                                <span>MP4 أو WebM أو OGG</span>
                            </div>
                            <div class="video-selected-name" id="video-selected-name"></div>
                            <div class="lesson-video-preview" id="lesson-video-preview">
                                <video id="lesson-video-player" controls preload="metadata"></video>
                            </div>
                        </div>
                        <div class="add-lesson-actions">
                            <a href="<?= $coursesUrl . 'manage_course.php?id=' . $course['id'] ?>" class="add-lesson-cancel">إلغاء</a>
                            <button type="submit" class="add-lesson-save">
                                <span>إضافة الدرس</span>
                                <b>←</b>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            <aside class="add-lesson-sidebar">
                <div class="add-lesson-card add-lesson-course-card">
                    <div class="add-lesson-card-header">
                        <h2>الكورس الحالية</h2>
                        <p>سيتم إضافة الدرس إلى</p>
                    </div>
                    <div class="add-lesson-course">
                        <div class="add-lesson-course-image">
                            <img src="../assets/images/<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['course_name']) ?>">
                        </div>
                        <div class="add-lesson-course-info">
                            <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                            <span><?= htmlspecialchars($course['category']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="add-lesson-card add-lesson-tip-card">
                    <div class="add-lesson-tip-icon">✓</div>
                    <div>
                        <h3>تنظيم محتوى الكورس</h3>
                        <p>ضع الدرس في ترتيبه الصحيح، ثم ارفع الفيديو الذي سيشاهده الطلاب</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    <script>
        const videoInput = document.getElementById('video');
        const videoPreview = document.getElementById('lesson-video-preview');
        const videoPlayer = document.getElementById('lesson-video-player');
        const videoSelectedName = document.getElementById('video-selected-name');
        const lessonForm = document.querySelector('.add-lesson-form');
        const videoBox = document.querySelector('.add-video-box');
        let currentVideoUrl = null;
        const uploadStatus = document.createElement('div');
        uploadStatus.className = 'ui-upload-status';
        uploadStatus.hidden = true;
        uploadStatus.innerHTML = `
    <div class="ui-upload-status__top">
        <span class="ui-upload-status__label"></span>
        <span class="ui-upload-status__percent">0%</span>
    </div>
    <div class="ui-upload-status__bar"><div class="ui-upload-status__fill"></div></div>
    <div class="ui-upload-status__meta">
        <span class="ui-upload-status__state">جاهز</span>
        <span class="ui-upload-status__details">0 ميجابايت · 0 كيلوبايت/ث · --</span>
    </div>
    <div class="ui-upload-actions">
        <button type="button" class="ui-upload-remove">إزالة الفيديو</button>
    </div>
`;
        videoPreview.parentNode.insertBefore(uploadStatus, videoPreview);
        const statusLabel = uploadStatus.querySelector('.ui-upload-status__label');
        const statusPercent = uploadStatus.querySelector('.ui-upload-status__percent');
        const statusFill = uploadStatus.querySelector('.ui-upload-status__fill');
        const statusState = uploadStatus.querySelector('.ui-upload-status__state');
        const statusDetails = uploadStatus.querySelector('.ui-upload-status__details');
        const removeButton = uploadStatus.querySelector('.ui-upload-remove');

        function formatBytes(bytes) {
            if (!bytes) return '0 ميجابايت';
            const units = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];
            const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            return `${(bytes / Math.pow(1024, index)).toFixed(index === 0 ? 0 : 2)} ${units[index]}`;
        }

        function formatSpeed(bytesPerSecond) {
            return `${formatBytes(bytesPerSecond)}/ث`;
        }

        function resetVideoSelection() {
            if (currentVideoUrl) {
                URL.revokeObjectURL(currentVideoUrl);
                currentVideoUrl = null;
            }
            videoInput.value = '';
            videoPreview.style.display = 'none';
            videoSelectedName.style.display = 'none';
            videoPlayer.removeAttribute('src');
            videoPlayer.load();
            uploadStatus.hidden = true;
            uploadStatus.classList.remove('is-success', 'is-error');
            statusFill.style.width = '0%';
            statusPercent.textContent = '0%';
            statusState.textContent = 'جاهز';
            statusDetails.textContent = '0 ميجابايت · 0 كيلوبايت/ث · --';
            videoBox.hidden = false;
        }
        removeButton.addEventListener('click', resetVideoSelection);
        videoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                resetVideoSelection();
                return;
            }
            const allowed = ['video/mp4', 'video/webm', 'video/ogg'];
            if (file.type && !allowed.includes(file.type)) {
                resetVideoSelection();
                alert('يرجى اختيار فيديو بصيغة MP4 أو WebM أو OGG.');
                return;
            }
            if (currentVideoUrl) URL.revokeObjectURL(currentVideoUrl);
            currentVideoUrl = URL.createObjectURL(file);
            videoPlayer.src = currentVideoUrl;
            videoPreview.style.display = 'block';
            videoSelectedName.textContent = `${file.name} · ${formatBytes(file.size)}`;
            videoSelectedName.style.display = 'block';
            videoBox.hidden = true;
            uploadStatus.hidden = false;
            statusLabel.textContent = file.name;
            statusState.textContent = 'جاهز للرفع';
            statusDetails.textContent = `${formatBytes(file.size)} · 0 كيلوبايت/ث · --`;
        });
        lessonForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const file = videoInput.files[0];
            if (!file) {
                alert('يرجى اختيار فيديو للدرس.');
                return;
            }
            const submitButton = lessonForm.querySelector('.add-lesson-save');
            const formData = new FormData(lessonForm);
            const xhr = new XMLHttpRequest();
            const startedAt = Date.now();
            uploadStatus.hidden = false;
            uploadStatus.classList.remove('is-success', 'is-error');
            statusLabel.textContent = file.name;
            statusState.textContent = 'جارٍ رفع الفيديو...';
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.addEventListener('progress', function(event) {
                if (!event.lengthComputable) return;
                const percent = Math.min(100, Math.round((event.loaded / event.total) * 100));
                const elapsed = Math.max((Date.now() - startedAt) / 1000, 0.1);
                const speed = event.loaded / elapsed;
                const remaining = speed > 0 ? Math.max(0, (event.total - event.loaded) / speed) : 0;
                const minutes = Math.floor(remaining / 60);
                const seconds = Math.floor(remaining % 60);
                statusPercent.textContent = `${percent}%`;
                statusFill.style.width = `${percent}%`;
                statusDetails.textContent =
                    `${formatBytes(event.loaded)} / ${formatBytes(event.total)} · ${formatSpeed(speed)} · ${minutes}:${String(seconds).padStart(2, '0')} متبقي`;
                if (percent >= 100) {
                    statusState.textContent = 'جارٍ معالجة الفيديو...';
                    statusDetails.textContent = `${formatBytes(event.total)} · اكتمل الرفع · جارٍ المعالجة...`;
                }
            });
            xhr.addEventListener('load', function() {
                let response = null;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (error) {
                    response = null;
                }
                if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
                    uploadStatus.classList.add('is-success');
                    statusPercent.textContent = '100%';
                    statusFill.style.width = '100%';
                    statusState.textContent = 'اكتمل بنجاح';
                    statusDetails.textContent = `${formatBytes(file.size)} · تمت معالجة الفيديو بنجاح`;
                    window.setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 350);
                    return;
                }
                uploadStatus.classList.add('is-error');
                statusState.textContent = 'فشل رفع الفيديو';
                statusDetails.textContent = response && response.message ?
                    response.message :
                    'تعذر معالجة الفيديو.';
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
            });
            xhr.addEventListener('error', function() {
                uploadStatus.classList.add('is-error');
                statusState.textContent = 'فشل رفع الفيديو';
                statusDetails.textContent = 'حدث خطأ في الاتصال. حاول مرة أخرى';
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
            });
            xhr.addEventListener('abort', function() {
                uploadStatus.classList.add('is-error');
                statusState.textContent = 'تم إلغاء الرفع';
                statusDetails.textContent = 'تم إلغاء عملية رفع الفيديو';
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
            });
            xhr.send(formData);
        });
    </script>
</body>

</html>