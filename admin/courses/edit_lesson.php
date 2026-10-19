<?php

require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../includes/functions.php';

$lesson_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$lesson_id) {
    die('Invalid lesson');
}

$stmt = $conn->prepare("
    SELECT
        lessons.*,
        courses.course_name,
        courses.category,
        courses.image
    FROM lessons
    INNER JOIN courses
        ON lessons.course_id = courses.id
    WHERE lessons.id = ?
    LIMIT 1
");

$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die('Lesson not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isAjaxUpload =
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $respond = function (
        bool $success,
        string $message = '',
        string $redirect = ''
    ) use ($isAjaxUpload) {

        if ($isAjaxUpload) {
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'redirect' => $redirect
            ]);

            exit;
        }

        if (!$success) {
            die($message);
        }

        header('Location: ' . $redirect);
        exit;
    };

    $lesson_name = trim($_POST['lesson_name'] ?? '');
    $lesson_order = trim($_POST['lesson_order'] ?? '');

    if ($lesson_name === '' || $lesson_order === '') {
        $respond(false, 'Please complete the lesson information');
    }

    if (!ctype_digit($lesson_order) || (int)$lesson_order < 1) {
        $respond(false, 'Please enter a valid lesson order');
    }

    $videoName = $lesson['video'] ?? '';
    $duration = (int)($lesson['duration'] ?? 0);
    $newVideoPath = null;

    if (
        isset($_FILES['video']) &&
        $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
            $respond(false, 'The selected video could not be uploaded');
        }

        $video = $_FILES['video'];

        $allowedExtensions = [
            'mp4',
            'webm',
            'ogg'
        ];

        $extension = strtolower(
            pathinfo($video['name'], PATHINFO_EXTENSION)
        );

        $maxVideoSize = 1024 * 1024 * 1024;

        if (!in_array($extension, $allowedExtensions, true)) {
            $respond(
                false,
                'Only MP4, WebM and OGG videos are allowed'
            );
        }

        if (
            $video['size'] <= 0 ||
            $video['size'] > $maxVideoSize
        ) {
            $respond(
                false,
                'The video size is invalid or exceeds 1 GB'
            );
        }

        $videoDirectory = $videoUploadPath;

        if (
            !is_dir($videoDirectory) &&
            !mkdir($videoDirectory, 0755, true)
        ) {
            $respond(
                false,
                'The video directory could not be created'
            );
        }

        if (!is_writable($videoDirectory)) {
            $respond(
                false,
                'The video directory is not writable'
            );
        }

        $videoName =
            bin2hex(random_bytes(12)) .
            '.' .
            $extension;

        $newVideoPath =
            $videoDirectory .
            $videoName;

        if (
            !move_uploaded_file(
                $video['tmp_name'],
                $newVideoPath
            )
        ) {
            $respond(
                false,
                'The video upload could not be completed'
            );
        }

        $duration = (int)getVideoDuration($newVideoPath);
    }

    try {

        $sql = "
            UPDATE lessons
            SET
                lesson_name = ?,
                lesson_order = ?,
                video = ?,
                duration = ?
            WHERE id = ?
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            $lesson_name,
            (int)$lesson_order,
            $videoName,
            $duration,
            $lesson_id
        ]);

        if (
            $newVideoPath &&
            !empty($lesson['video'])
        ) {

            $oldVideo =
                $videoUploadPath .
                basename($lesson['video']);

            if (
                is_file($oldVideo) &&
                realpath($oldVideo) !== realpath($newVideoPath)
            ) {
                @unlink($oldVideo);
            }
        }

    } catch (Throwable $e) {

        if (
            $newVideoPath &&
            is_file($newVideoPath)
        ) {
            @unlink($newVideoPath);
        }

        $respond(
            false,
            'The lesson could not be saved'
        );
    }

    $redirect =
        $coursesUrl .
        'manage_course.php?id=' .
        $lesson['course_id'];

    $respond(
        true,
        'Lesson updated successfully.',
        $redirect
    );
}

$videoExtension = strtolower(
    pathinfo(
        $lesson['video'] ?? '',
        PATHINFO_EXTENSION
    )
);

$videoMime = match ($videoExtension) {
    'webm' => 'video/webm',
    'ogg'  => 'video/ogg',
    'mov'  => 'video/quicktime',
    'm4v'  => 'video/mp4',
    default => 'video/mp4'
};

$videoAccessUrl =
    'video_access.php?id=' .
    urlencode((string)$lesson_id);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <title>تعديل الدرس</title>

    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/edit_lesson.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">


    <script src="../assets/js/theme.js"></script>

</head>

<body>

    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="page">

        <header class="header">

            <div class="title">

                <span>محتوى الكورس</span>

                <h1>تعديل الدرس</h1>

                <p>
                    حدّث اسم الدرس وترتيبه، واستبدل الفيديو عند الحاجة
                </p>

            </div>

            <a
                href="<?= $coursesUrl . 'manage_course.php?id=' . (int)$lesson['course_id'] ?>"
                class="back">

                <span>→</span>

                <span>العودة إلى الكورس</span>

            </a>

        </header>

        <div class="layout">

            <section class="main">

                <div class="card">

                    <div class="card-head">

                        <h2>بيانات الدرس</h2>

                        <p>
                            عدّل المعلومات الأساسية للدرس والفيديو المرتبط به
                        </p>

                    </div>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        class="form"
                        id="lesson-form">

                        <div class="field">

                            <label for="lesson_name">
                                اسم الدرس
                            </label>

                            <input
                                type="text"
                                id="lesson_name"
                                name="lesson_name"
                                value="<?= htmlspecialchars($lesson['lesson_name'], ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="اكتب اسم الدرس..."
                                required>

                        </div>

                        <div class="field">

                            <label for="lesson_order">
                                ترتيب الدرس
                            </label>

                            <input
                                type="number"
                                id="lesson_order"
                                name="lesson_order"
                                value="<?= htmlspecialchars((string)$lesson['lesson_order'], ENT_QUOTES, 'UTF-8') ?>"
                                min="1"
                                placeholder="1"
                                required>

                        </div>

                        <div class="field">

                            <label>
                                الفيديو الحالي
                            </label>

                            <?php if (!empty($lesson['video'])): ?>

                                <div class="current-video">

                                    <video
                                        id="current-video"
                                        controls
                                        preload="metadata"
                                        playsinline>

                                        <source
                                            src="<?= htmlspecialchars($videoAccessUrl, ENT_QUOTES, 'UTF-8') ?>"
                                            type="<?= htmlspecialchars($videoMime, ENT_QUOTES, 'UTF-8') ?>">

                                        متصفحك لا يدعم تشغيل الفيديو

                                    </video>

                                </div>

                            <?php else: ?>

                                <div class="no-video">
                                    لا يوجد فيديو مرفوع لهذا الدرس
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="field">

                            <label for="video">
                                استبدال الفيديو
                            </label>

                            <div
                                class="video-box"
                                id="video-box">

                                <input
                                    type="file"
                                    id="video"
                                    name="video"
                                    accept="video/mp4,video/webm,video/ogg">

                                <div class="video-icon">
                                    ▶
                                </div>

                                <strong>
                                    اختيار فيديو جديد
                                </strong>

                                <span>
                                    اترك الحقل فارغًا للاحتفاظ بالفيديو الحالي
                                </span>

                            </div>

                            <div
                                class="selected-name"
                                id="selected-name">
                            </div>

                            <div
                                class="video-preview"
                                id="video-preview">

                                <video
                                    id="video-player"
                                    controls
                                    preload="metadata"
                                    playsinline>
                                </video>

                            </div>

                            <div
                                class="upload-status"
                                id="upload-status">

                                <div class="status-top">

                                    <span
                                        class="status-label"
                                        id="status-label">
                                    </span>

                                    <span
                                        class="status-percent"
                                        id="status-percent">
                                        0%
                                    </span>

                                </div>

                                <div class="status-bar">

                                    <div
                                        class="status-fill"
                                        id="status-fill">
                                    </div>

                                </div>

                                <div class="status-meta">

                                    <span
                                        class="status-state"
                                        id="status-state">
                                        جاهز
                                    </span>

                                    <span id="status-details">
                                        0 ميجابايت · 0 كيلوبايت/ث · --
                                    </span>

                                </div>

                                <button
                                    type="button"
                                    class="remove-video"
                                    id="remove-video">

                                    إزالة الفيديو المحدد

                                </button>

                            </div>

                        </div>

                        <div class="actions">

                            <a
                                href="<?= $coursesUrl . 'manage_course.php?id=' . (int)$lesson['course_id'] ?>"
                                class="cancel">

                                إلغاء

                            </a>

                            <button
                                type="submit"
                                class="save"
                                id="save-button">

                                <span>
                                    حفظ التعديلات
                                </span>

                                <b>←</b>

                            </button>

                        </div>

                    </form>

                </div>

            </section>

            <aside class="sidebar">

                <div class="card">

                    <div class="card-head">

                        <h2>الكورس الحالية</h2>

                        <p>
                            هذا الدرس تابع للكورس التالية.
                        </p>

                    </div>

                    <div class="course-info">

                        <div class="course-image">

                            <img
                                src="../assets/images/<?= htmlspecialchars($lesson['image'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($lesson['course_name'], ENT_QUOTES, 'UTF-8') ?>">

                        </div>

                        <div class="course-details">

                            <h3>
                                <?= htmlspecialchars($lesson['course_name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <span>
                                <?= htmlspecialchars($lesson['category'], ENT_QUOTES, 'UTF-8') ?>
                            </span>

                        </div>

                    </div>

                </div>

                <div class="card tip">

                    <div class="tip-icon">
                        ✓
                    </div>

                    <div>

                        <h3>
                            تحديث محتوى الدرس
                        </h3>

                        <p>
                            يمكنك تغيير اسم الدرس أو ترتيبه أو رفع فيديو جديد
                        </p>

                        <p>
                            إذا تركت الفيديو فارغًا فسيظل الفيديو الحالي كما هو
                        </p>

                    </div>

                </div>

            </aside>

        </div>

    </main>

    <script>

        const videoInput =
            document.getElementById('video');

        const videoBox =
            document.getElementById('video-box');

        const videoPreview =
            document.getElementById('video-preview');

        const videoPlayer =
            document.getElementById('video-player');

        const selectedName =
            document.getElementById('selected-name');

        const uploadStatus =
            document.getElementById('upload-status');

        const statusLabel =
            document.getElementById('status-label');

        const statusPercent =
            document.getElementById('status-percent');

        const statusFill =
            document.getElementById('status-fill');

        const statusState =
            document.getElementById('status-state');

        const statusDetails =
            document.getElementById('status-details');

        const removeVideo =
            document.getElementById('remove-video');

        const form =
            document.getElementById('lesson-form');

        const saveButton =
            document.getElementById('save-button');

        let videoUrl = null;

        function formatBytes(bytes) {

            if (!bytes) {
                return '0 ميجابايت';
            }

            const units = [
                'بايت',
                'كيلوبايت',
                'ميجابايت',
                'جيجابايت'
            ];

            const index = Math.min(
                Math.floor(
                    Math.log(bytes) / Math.log(1024)
                ),
                units.length - 1
            );

            return `${(
                bytes / Math.pow(1024, index)
            ).toFixed(index === 0 ? 0 : 2)} ${units[index]}`;
        }

        function formatSpeed(bytesPerSecond) {
            return `${formatBytes(bytesPerSecond)}/ث`;
        }

        function resetVideo() {

            if (videoUrl) {
                URL.revokeObjectURL(videoUrl);
                videoUrl = null;
            }

            videoInput.value = '';

            videoPreview.style.display = 'none';

            selectedName.style.display = 'none';

            videoPlayer.removeAttribute('src');

            videoPlayer.load();

            uploadStatus.classList.remove(
                'show',
                'success',
                'error'
            );

            statusFill.style.width = '0%';

            statusPercent.textContent = '0%';

            statusState.textContent = 'جاهز';

            statusDetails.textContent =
                '0 ميجابايت · 0 كيلوبايت/ث · --';

            videoBox.hidden = false;
        }

        removeVideo.addEventListener(
            'click',
            resetVideo
        );

        videoInput.addEventListener(
            'change',
            function() {

                const file = this.files[0];

                if (!file) {
                    resetVideo();
                    return;
                }

                const allowed = [
                    'video/mp4',
                    'video/webm',
                    'video/ogg'
                ];

                if (
                    file.type &&
                    !allowed.includes(file.type)
                ) {

                    resetVideo();

                    alert(
                        'يرجى اختيار فيديو بصيغة MP4 أو WebM أو OGG'
                    );

                    return;
                }

                if (file.size > 1024 * 1024 * 1024) {

                    resetVideo();

                    alert(
                        'حجم الفيديو يجب ألا يتجاوز 1 جيجابايت'
                    );

                    return;
                }

                if (videoUrl) {
                    URL.revokeObjectURL(videoUrl);
                }

                videoUrl =
                    URL.createObjectURL(file);

                videoPlayer.src = videoUrl;

                videoPreview.style.display = 'block';

                selectedName.style.display = 'block';

                selectedName.textContent =
                    `${file.name} · ${formatBytes(file.size)}`;

                videoBox.hidden = true;

                uploadStatus.classList.add('show');

                uploadStatus.classList.remove(
                    'success',
                    'error'
                );

                statusLabel.textContent =
                    file.name;

                statusPercent.textContent =
                    '0%';

                statusFill.style.width =
                    '0%';

                statusState.textContent =
                    'جاهز للاستبدال';

                statusDetails.textContent =
                    `${formatBytes(file.size)} · 0 كيلوبايت/ث · --`;

                videoPlayer.load();
            }
        );

        form.addEventListener(
            'submit',
            function(event) {

                const file =
                    videoInput.files[0];

                if (!file) {
                    return;
                }

                event.preventDefault();

                const formData =
                    new FormData(form);

                const xhr =
                    new XMLHttpRequest();

                const startedAt =
                    Date.now();

                uploadStatus.classList.add('show');

                uploadStatus.classList.remove(
                    'success',
                    'error'
                );

                statusLabel.textContent =
                    file.name;

                statusState.textContent =
                    'جارٍ رفع الفيديو...';

                saveButton.disabled = true;

                saveButton.setAttribute(
                    'aria-busy',
                    'true'
                );

                xhr.open(
                    'POST',
                    window.location.href,
                    true
                );

                xhr.setRequestHeader(
                    'X-Requested-With',
                    'XMLHttpRequest'
                );

                xhr.upload.addEventListener(
                    'progress',
                    function(event) {

                        if (!event.lengthComputable) {
                            return;
                        }

                        const percent = Math.min(
                            100,
                            Math.round(
                                (event.loaded / event.total) * 100
                            )
                        );

                        const elapsed =
                            Math.max(
                                (Date.now() - startedAt) / 1000,
                                0.1
                            );

                        const speed =
                            event.loaded / elapsed;

                        const remaining =
                            speed > 0
                                ? Math.max(
                                    0,
                                    (event.total - event.loaded) / speed
                                )
                                : 0;

                        const minutes =
                            Math.floor(
                                remaining / 60
                            );

                        const seconds =
                            Math.floor(
                                remaining % 60
                            );

                        statusPercent.textContent =
                            `${percent}%`;

                        statusFill.style.width =
                            `${percent}%`;

                        statusDetails.textContent =
                            `${formatBytes(event.loaded)} / ` +
                            `${formatBytes(event.total)} · ` +
                            `${formatSpeed(speed)} · ` +
                            `${minutes}:${String(seconds).padStart(2, '0')} متبقي`;

                        if (percent >= 100) {

                            statusState.textContent =
                                'جارٍ معالجة الفيديو...';

                            statusDetails.textContent =
                                `${formatBytes(event.total)} · ` +
                                `اكتمل الرفع · جارٍ المعالجة...`;
                        }
                    }
                );

                xhr.addEventListener(
                    'load',
                    function() {

                        let response = null;

                        try {

                            response =
                                JSON.parse(
                                    xhr.responseText
                                );

                        } catch (error) {

                            response = null;
                        }

                        if (
                            xhr.status >= 200 &&
                            xhr.status < 300 &&
                            response &&
                            response.success
                        ) {

                            uploadStatus.classList.add(
                                'success'
                            );

                            statusPercent.textContent =
                                '100%';

                            statusFill.style.width =
                                '100%';

                            statusState.textContent =
                                'اكتمل بنجاح';

                            statusDetails.textContent =
                                `${formatBytes(file.size)} · ` +
                                `تم تحديث الفيديو بنجاح`;

                            window.setTimeout(
                                function() {

                                    window.location.href =
                                        response.redirect;

                                },
                                350
                            );

                            return;
                        }

                        uploadStatus.classList.add(
                            'error'
                        );

                        statusState.textContent =
                            'فشل رفع الفيديو';

                        statusDetails.textContent =
                            response &&
                            response.message
                                ? response.message
                                : 'تعذر تحديث الفيديو.';

                        saveButton.disabled = false;

                        saveButton.removeAttribute(
                            'aria-busy'
                        );
                    }
                );

                xhr.addEventListener(
                    'error',
                    function() {

                        uploadStatus.classList.add(
                            'error'
                        );

                        statusState.textContent =
                            'فشل رفع الفيديو';

                        statusDetails.textContent =
                            'حدث خطأ في الاتصال. حاول مرة أخرى';

                        saveButton.disabled = false;

                        saveButton.removeAttribute(
                            'aria-busy'
                        );
                    }
                );

                xhr.addEventListener(
                    'abort',
                    function() {

                        uploadStatus.classList.add(
                            'error'
                        );

                        statusState.textContent =
                            'تم إلغاء الرفع';

                        statusDetails.textContent =
                            'تم إلغاء عملية رفع الفيديو';

                        saveButton.disabled = false;

                        saveButton.removeAttribute(
                            'aria-busy'
                        );
                    }
                );

                xhr.send(formData);
            }
        );

    </script>

</body>

</html>
