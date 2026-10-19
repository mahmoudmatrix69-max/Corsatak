<?php

require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';

$lesson_id = $_GET['id'] ?? '';

if (!is_numeric($lesson_id)) {
    die('رقم الدرس غير صالح');
}


$stmt = $conn->prepare("
    SELECT
        lessons.*,
        courses.course_name,
        courses.id AS course_id
    FROM lessons
    INNER JOIN courses ON lessons.course_id = courses.id
    WHERE lessons.id = ?
    LIMIT 1
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    die('لم يتم العثور على الدرس');
}


$stmt = $conn->prepare("
    SELECT *
    FROM lessons
    WHERE course_id = ?
    ORDER BY lesson_order ASC, id ASC
");
$stmt->execute([$lesson['course_id']]);
$lessons = $stmt->fetchAll();


$previousLesson = null;
$nextLesson = null;
foreach ($lessons as $index => $item) {
    if ((string)$item['id'] === (string)$lesson['id']) {
        $previousLesson = $lessons[$index - 1] ?? null;
        $nextLesson = $lessons[$index + 1] ?? null;
        break;
    }
}


$videoPath = 'video_access.php?id=' . (int)$lesson['id'];
$videoMime = 'video/mp4';

if (!empty($lesson['video'])) {
    $ext = strtolower(pathinfo($lesson['video'], PATHINFO_EXTENSION));
    $videoMime = match ($ext) {
        'webm' => 'video/webm',
        'ogg'  => 'video/ogg',
        'mov'  => 'video/quicktime',
        'm4v'  => 'video/mp4',
        default => 'video/mp4'
    };
}

$courseName = htmlspecialchars($lesson['course_name'], ENT_QUOTES, 'UTF-8');
$lessonName = htmlspecialchars($lesson['lesson_name'], ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lessonName ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/watch_lesson.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>

    <style>
        .video-player:focus {
            outline: none;
        }

        .lessons-list {
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
        }

        .video-player {
            outline: none;
        }

        .progress-area {
            position: relative;
        }

        .video-preview {
            position: absolute;
            left: 0;
            bottom: 22px;
            width: 220px;
            height: 148px;
            padding: 4px;
            box-sizing: border-box;
            border-radius: 10px;
            background: rgba(15, 23, 42, .97);
            border: 1px solid rgba(255, 255, 255, .14);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .42);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(5px);
            transition: opacity .12s ease, transform .12s ease, visibility .12s ease;
            z-index: 100;
            overflow: hidden;
        }

        .video-preview.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .video-preview canvas {
            display: block;
            width: 100%;
            height: 124px;
            border-radius: 7px;
            background: #000;
            object-fit: cover;
        }

        .preview-time {
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            direction: ltr;
        }

        body.light-mode .video-preview {
            background: rgba(255, 255, 255, .98);
            border-color: rgba(15, 23, 42, .14);
            box-shadow: 0 12px 32px rgba(15, 23, 42, .20);
        }

        body.light-mode .preview-time {
            color: #0f172a;
        }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="watch-page">
        <div class="watch-layout">


            <section class="video-column">


                <div class="video-player" id="player" tabindex="0">
                    <?php if (!empty($lesson['video'])): ?>
                        <video id="video" class="watch-video" preload="auto" playsinline>
                            <source src="<?= htmlspecialchars($videoPath, ENT_QUOTES, 'UTF-8') ?>" type="<?= $videoMime ?>">
                        </video>


                        <div class="video-loading" id="videoLoading">
                            <div class="loading-spinner"></div>
                            <span>جاري تحميل الفيديو...</span>
                        </div>


                        <button type="button" class="center-play" id="centerPlay" aria-label="تشغيل">
                            <i class="fa-solid fa-play"></i>
                        </button>


                        <div class="seek-feedback" id="seekFeedback">
                            <i></i>
                            <strong>10</strong>
                            <span>ثانية</span>
                        </div>


                        <div class="video-controls" id="controls">
                            <div class="progress-area" id="progressArea">
                                <div class="video-preview" id="videoPreview">
                                    <canvas id="previewCanvas" width="220" height="124"></canvas>
                                    <div class="preview-time" id="previewTime">00:00</div>
                                </div>

                                <div class="buffer-progress" id="bufferBar"></div>
                                <div class="played-progress" id="playedBar"></div>

                                <input type="range"
                                    id="seekBar"
                                    min="0"
                                    max="100"
                                    value="0"
                                    step="0.1"
                                    aria-label="تقدم الفيديو">
                            </div>
                            <div class="controls-row">
                                <div class="controls-group controls-left">
                                    <button type="button" class="control-button" id="fullscreenBtn" title="ملء الشاشة">
                                        <i class="fa-solid fa-expand"></i>
                                    </button>
                                    <button type="button" class="control-button" id="pipBtn" title="صورة داخل صورة">
                                        <i class="fa-regular fa-clone"></i>
                                    </button>
                                    <button type="button" class="control-button speed-button" id="speedBtn">1x</button>
                                </div>
                                <div class="time-display">
                                    <span id="currentTime">00:00</span>
                                    <b>/</b>
                                    <span id="totalTime">00:00</span>
                                </div>
                                <div class="controls-group controls-right">
                                    <button type="button" class="control-button" id="playBtn" title="تشغيل">
                                        <i class="fa-solid fa-play"></i>
                                    </button>
                                    <button type="button" class="control-button" id="muteBtn" title="الصوت">
                                        <i class="fa-solid fa-volume-high"></i>
                                    </button>
                                    <input type="range" id="volumeBar" class="volume-range" min="0" max="1" step="0.05" value="1">
                                </div>
                            </div>
                        </div>


                        <div class="speed-menu" id="speedMenu">
                            <span>سرعة التشغيل</span>
                            <button data-speed="0.75">0.75x</button>
                            <button data-speed="1" class="active">1x</button>
                            <button data-speed="1.25">1.25x</button>
                            <button data-speed="1.5">1.5x</button>
                            <button data-speed="1.75">1.75x</button>
                            <button data-speed="2">2x</button>
                        </div>

                    <?php else: ?>
                        <div class="empty-video">
                            <div class="empty-video-icon"><i class="fa-solid fa-video-slash"></i></div>
                            <h2>لا يوجد فيديو</h2>
                            <p>لم يتم رفع فيديو لهذا الدرس حتى الآن</p>
                        </div>
                    <?php endif; ?>
                </div>


                <div class="current-lesson">
                    <div class="current-number">
                        <span>الدرس</span>
                        <strong><?= htmlspecialchars($lesson['lesson_order'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="current-title">
                        <span>الدرس الحالي</span>
                        <h1><?= $lessonName ?></h1>
                    </div>
                </div>


                <div class="lesson-navigation">
                    <?php if ($previousLesson): ?>
                        <a href="?id=<?= (int)$previousLesson['id'] ?>" class="navigation-card previous"
                            aria-label="الدرس السابق" title="الدرس السابق">
                            <span class="navigation-icon"><i class="fa-solid fa-chevron-right"></i></span>
                            <span class="navigation-text">
                                <small>الدرس السابق</small>
                                <strong><?= htmlspecialchars($previousLesson['lesson_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </span>
                        </a>
                    <?php else: ?>
                        <span class="navigation-card previous disabled" aria-disabled="true" title="لا يوجد درس سابق">
                            <span class="navigation-icon"><i class="fa-solid fa-chevron-right"></i></span>
                            <span class="navigation-text">
                                <small>الدرس السابق</small>
                                <strong>لا يوجد</strong>
                            </span>
                        </span>
                    <?php endif; ?>

                    <?php if ($nextLesson): ?>
                        <a href="?id=<?= (int)$nextLesson['id'] ?>" class="navigation-card next"
                            aria-label="الدرس التالي" title="الدرس التالي">
                            <span class="navigation-text">
                                <small>الدرس التالي</small>
                                <strong><?= htmlspecialchars($nextLesson['lesson_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </span>
                            <span class="navigation-icon"><i class="fa-solid fa-chevron-left"></i></span>
                        </a>
                    <?php else: ?>
                        <span class="navigation-card next disabled" aria-disabled="true" title="لا يوجد درس تالي">
                            <span class="navigation-text">
                                <small>الدرس التالي</small>
                                <strong>لا يوجد</strong>
                            </span>
                            <span class="navigation-icon"><i class="fa-solid fa-chevron-left"></i></span>
                        </span>
                    <?php endif; ?>
                </div>
            </section>


            <aside class="course-content">


                <div class="content-header">
                    <div class="content-title">
                        <span>محتوى الكورس</span>
                        <h2><?= $courseName ?></h2>
                    </div>
                    <div class="content-actions">
                        <div class="content-count" title="عدد الدروس"><?= count($lessons) ?></div>
                        <a href="<?= $coursesUrl . 'manage_course.php?id=' . (int)$lesson['course_id'] ?>" class="content-back" title="العودة للكورس">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>


                <div class="course-stats">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-regular fa-clock"></i></div>
                        <div class="stat-text">
                            <span>المدة</span>
                            <strong id="lessonDuration">--:--</strong>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-chart-simple"></i></div>
                        <div class="stat-text">
                            <span>المشاهدة</span>
                            <strong id="lessonPercentage">0%</strong>
                        </div>
                    </div>
                    <div class="stat-status" id="lessonStatus">
                        <i class="fa-regular fa-circle"></i>
                        <span>لم تبدأ</span>
                    </div>
                </div>


                <div class="lessons-list">
                    <?php foreach ($lessons as $item):
                        $isActive = (string)$item['id'] === (string)$lesson['id'];
                        $itemVideo = !empty($item['video']) ? 'video_access.php?id=' . (int)$item['id'] : '';
                    ?>
                        <a href="?id=<?= (int)$item['id'] ?>" class="lesson-card <?= $isActive ? 'active' : '' ?>" data-id="<?= (int)$item['id'] ?>" data-video="<?= htmlspecialchars($itemVideo, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="lesson-thumbnail">
                                <div class="thumbnail-background">
                                    <span class="shape shape-1"></span>
                                    <span class="shape shape-2"></span>
                                    <span class="shape shape-3"></span>
                                    <div class="thumbnail-glow"></div>
                                    <div class="thumbnail-center"><i class="fa-solid fa-play"></i></div>
                                </div>
                                <span class="lesson-number"><?= htmlspecialchars($item['lesson_order'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="lesson-duration">
                                    <i class="fa-regular fa-clock"></i>
                                    <b class="duration">--:--</b>
                                </span>
                                <span class="lesson-completed"><i class="fa-solid fa-check"></i></span>
                            </div>


                            <div class="lesson-card-content">
                                <span class="lesson-label">الدرس <?= htmlspecialchars($item['lesson_order'], ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars($item['lesson_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="progress-information">
                                    <span>التقدم</span>
                                    <strong>0%</strong>
                                </div>
                                <div class="lesson-progress"><span></span></div>
                            </div>

                            <i class="fa-solid fa-chevron-left lesson-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </div>

            </aside>

        </div>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const video = document.getElementById('video');
            const player = document.getElementById('player');
            if (!video || !player) return;

            const controls = document.getElementById('controls');
            const playBtn = document.getElementById('playBtn');
            const centerPlay = document.getElementById('centerPlay');
            const muteBtn = document.getElementById('muteBtn');
            const volumeBar = document.getElementById('volumeBar');
            const seekBar = document.getElementById('seekBar');
            const progressArea = document.getElementById('progressArea');
            const videoPreview = document.getElementById('videoPreview');
            const previewCanvas = document.getElementById('previewCanvas');
            const previewTime = document.getElementById('previewTime');
            const previewContext = previewCanvas?.getContext('2d');
            const playedBar = document.getElementById('playedBar');
            const bufferBar = document.getElementById('bufferBar');
            const currentTime = document.getElementById('currentTime');
            const totalTime = document.getElementById('totalTime');
            const lessonDuration = document.getElementById('lessonDuration');
            const lessonPercentage = document.getElementById('lessonPercentage');
            const lessonStatus = document.getElementById('lessonStatus');
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            const pipBtn = document.getElementById('pipBtn');
            const speedBtn = document.getElementById('speedBtn');
            const speedMenu = document.getElementById('speedMenu');
            const videoLoading = document.getElementById('videoLoading');
            const seekFeedback = document.getElementById('seekFeedback');

            const lessonId = <?= (int)$lesson['id'] ?>;
            const positionKey = 'ischool_position_' + lessonId;
            const progressKey = 'ischool_progress_' + lessonId;
            const completedKey = 'ischool_completed_' + lessonId;

            let hideTimer = 0;
            let loadingTimer = 0;
            let clickTimer = 0;
            let feedbackTimer = 0;
            let pointerInside = false;
            let draggingSeek = false;
            let lastUiSecond = -1;
            let lastSavedSecond = -1;
            let lastBufferPercent = -1;
            const HIDE_DELAY = 700;

            function formatTime(value) {
                if (!Number.isFinite(value) || value < 0) return '00:00';
                value = Math.floor(value);
                const h = Math.floor(value / 3600);
                const m = Math.floor((value % 3600) / 60);
                const s = value % 60;
                return h ?
                    String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0') :
                    String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }

            function revealControls() {
                clearTimeout(hideTimer);
                player.classList.remove('controls-hidden');
                if (!video.paused && pointerInside) {
                    hideTimer = setTimeout(() => {
                        if (pointerInside && !video.paused && !draggingSeek && !speedMenu.classList.contains('show')) {
                            player.classList.add('controls-hidden');
                        }
                    }, HIDE_DELAY);
                }
            }

            function hideControls() {
                clearTimeout(hideTimer);
                if (!video.paused && !draggingSeek && !speedMenu.classList.contains('show')) {
                    player.classList.add('controls-hidden');
                }
            }

            player.addEventListener('mouseenter', () => {
                pointerInside = true;
                revealControls();
            });

            player.addEventListener('mousemove', () => {
                pointerInside = true;
                if (player.classList.contains('controls-hidden')) {
                    revealControls();
                } else {
                    clearTimeout(hideTimer);
                    if (!video.paused) {
                        hideTimer = setTimeout(() => {
                            if (pointerInside && !video.paused && !draggingSeek) {
                                player.classList.add('controls-hidden');
                            }
                        }, HIDE_DELAY);
                    }
                }
            }, {
                passive: true
            });

            player.addEventListener('mouseleave', () => {
                pointerInside = false;
                hideControls();
            });

            function updatePlayIcon() {
                const icon = playBtn?.querySelector('i');
                if (!icon) return;

                if (video.paused) {
                    icon.className = 'fa-solid fa-play';
                    centerPlay?.classList.remove('hidden');
                    player.classList.remove('controls-hidden');
                } else {
                    icon.className = 'fa-solid fa-pause';
                    centerPlay?.classList.add('hidden');
                    if (!pointerInside) hideControls();
                }
            }

            function togglePlay() {
                if (video.paused) {
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            }

            playBtn?.addEventListener('click', e => {
                e.stopPropagation();
                togglePlay();
                revealControls();
            });

            centerPlay?.addEventListener('click', e => {
                e.stopPropagation();
                togglePlay();
                revealControls();
            });

            video.addEventListener('play', updatePlayIcon);
            video.addEventListener('pause', () => {
                savePosition(true);
                updatePlayIcon();
            });

            function showLoading() {
                clearTimeout(loadingTimer);
                loadingTimer = setTimeout(() => {
                    if (!video.paused && video.readyState < 3) {
                        videoLoading?.classList.add('show');
                    }
                }, 250);
            }

            function hideLoading() {
                clearTimeout(loadingTimer);
                videoLoading?.classList.remove('show');
            }

            video.addEventListener('waiting', showLoading);
            video.addEventListener('stalled', showLoading);
            video.addEventListener('seeking', () => {
                clearTimeout(loadingTimer);
            });
            video.addEventListener('playing', hideLoading);
            video.addEventListener('canplay', hideLoading);
            video.addEventListener('canplaythrough', hideLoading);
            video.addEventListener('error', hideLoading);

            function savePosition(force = false) {
                if (!Number.isFinite(video.currentTime)) return;
                const sec = Math.floor(video.currentTime);
                if (!force) {
                    if (sec === lastSavedSecond) return;
                    if (lastSavedSecond >= 0 && sec - lastSavedSecond < 5) return;
                }
                lastSavedSecond = sec;
                localStorage.setItem(positionKey, String(video.currentTime));
            }

            function updateStatus() {
                if (!Number.isFinite(video.duration) || video.duration <= 0) return;

                const percentage = Math.max(
                    0,
                    Math.min(100, (video.currentTime / video.duration) * 100)
                );

                lessonPercentage.textContent = Math.round(percentage) + '%';

                if (percentage >= 100) {
                    localStorage.setItem(progressKey, '100');
                    localStorage.setItem(completedKey, '1');

                    lessonStatus.className = 'stat-status completed';
                    lessonStatus.innerHTML =
                        '<i class="fa-solid fa-circle-check"></i><span>تمت المشاهدة</span>';
                } else if (percentage > 0) {
                    localStorage.removeItem(completedKey);
                    localStorage.setItem(progressKey, String(Math.round(percentage)));

                    lessonStatus.className = 'stat-status watching';
                    lessonStatus.innerHTML =
                        '<i class="fa-solid fa-circle-play"></i><span>جاري المشاهدة ' +
                        Math.round(percentage) + '%</span>';
                } else {
                    lessonStatus.className = 'stat-status';
                    lessonStatus.innerHTML =
                        '<i class="fa-regular fa-circle"></i><span>لم تبدأ</span>';
                }
            }

            function updateProgress(force = false) {
                if (!Number.isFinite(video.duration) || video.duration <= 0) return;

                const pct = Math.max(0, Math.min(100, (video.currentTime / video.duration) * 100));

                seekBar.value = pct;
                playedBar.style.width = pct + '%';
                currentTime.textContent = formatTime(video.currentTime);
                totalTime.textContent = formatTime(video.duration);
                lessonDuration.textContent = formatTime(video.duration);

                if (force || Math.floor(video.currentTime) !== lastSavedSecond) {
                    savePosition(false);
                }

                const second = Math.floor(video.currentTime);
                if (force || second !== lastUiSecond) {
                    lastUiSecond = second;

                    const progress = Math.max(
                        0,
                        Math.min(100, (video.currentTime / video.duration) * 100)
                    );

                    if (progress >= 100) {
                        localStorage.setItem(progressKey, '100');
                        localStorage.setItem(completedKey, '1');
                    } else {
                        localStorage.setItem(progressKey, String(Math.round(progress)));
                        localStorage.removeItem(completedKey);
                    }

                    updateStatus();
                    updateLessonCards();
                }
            }
            video.addEventListener('progress', () => {
                if (!Number.isFinite(video.duration) || !video.buffered.length) return;

                try {
                    const end = video.buffered.end(video.buffered.length - 1);
                    const pct = Math.round(Math.min(100, (end / video.duration) * 100));

                    if (pct !== lastBufferPercent) {
                        lastBufferPercent = pct;
                        bufferBar.style.width = pct + '%';
                    }
                } catch (_) {}
            });
            video.addEventListener('timeupdate', () => {
                updateProgress(false);
                updateStatus();
            });

            video.addEventListener('seeking', () => {
                updateProgress(true);
            });

            video.addEventListener('seeked', () => {
                updateProgress(true);
                updateStatus();
            });

            video.addEventListener('loadedmetadata', () => {
                lessonDuration.textContent = formatTime(video.duration);
                totalTime.textContent = formatTime(video.duration);

                const saved = parseFloat(localStorage.getItem(positionKey) || '');
                if (Number.isFinite(saved) && saved > 0 && saved < video.duration - 3) {
                    try {
                        video.currentTime = saved;
                    } catch (_) {}
                }

                updateProgress(true);
                updateStatus();
            });

            function updateVolume() {
                const value = video.muted ? 0 : Math.max(0, Math.min(1, video.volume));
                const percent = Math.round(value * 100);

                volumeBar.value = value;
                volumeBar.style.setProperty('--volume-percent', percent + '%');

                const icon = muteBtn.querySelector('i');
                if (video.muted || value === 0) icon.className = 'fa-solid fa-volume-xmark';
                else if (value < .5) icon.className = 'fa-solid fa-volume-low';
                else icon.className = 'fa-solid fa-volume-high';
            }

            muteBtn?.addEventListener('click', e => {
                e.stopPropagation();
                video.muted = !video.muted;

                if (!video.muted && video.volume === 0) {
                    video.volume = .7;
                }

                updateVolume();
                revealControls();
            });

            volumeBar?.addEventListener('input', e => {
                e.stopPropagation();
                video.volume = Number(volumeBar.value);
                video.muted = video.volume === 0;
                updateVolume();
            });
            let previewVideo = null;
            let previewRequest = 0;
            let previewLastTime = -1;

            function createPreviewVideo() {
                if (previewVideo || !video.currentSrc) return;

                previewVideo = document.createElement('video');
                previewVideo.preload = 'metadata';
                previewVideo.muted = true;
                previewVideo.playsInline = true;
                previewVideo.src = video.currentSrc;

                previewVideo.addEventListener('loadedmetadata', () => {
                    previewLastTime = -1;
                });
            }

            function showPreview() {
                videoPreview?.classList.add('show');
            }

            function hidePreview() {
                videoPreview?.classList.remove('show');
            }

            function updatePreview(clientX) {
                if (!progressArea || !videoPreview) return;
                if (!Number.isFinite(video.duration) || video.duration <= 0) return;

                const rect = progressArea.getBoundingClientRect();
                if (rect.width <= 0) return;

                let x = clientX - rect.left;
                x = Math.max(0, Math.min(rect.width, x));

                const percentage = x / rect.width;
                const time = percentage * video.duration;

                const previewWidth = videoPreview.offsetWidth || 220;

                let left = x - previewWidth / 2;
                left = Math.max(0, Math.min(rect.width - previewWidth, left));

                videoPreview.style.left = left + 'px';
                previewTime.textContent = formatTime(time);

                createPreviewVideo();

                if (!previewVideo || !Number.isFinite(previewVideo.duration)) return;
                if (Math.abs(time - previewLastTime) < 0.15) return;

                previewLastTime = time;
                const requestId = ++previewRequest;

                const drawFrame = () => {
                    if (requestId !== previewRequest) return;

                    if (
                        previewContext &&
                        previewCanvas &&
                        previewVideo.readyState >= 2
                    ) {
                        previewContext.clearRect(
                            0,
                            0,
                            previewCanvas.width,
                            previewCanvas.height
                        );

                        try {
                            previewContext.drawImage(
                                previewVideo,
                                0,
                                0,
                                previewCanvas.width,
                                previewCanvas.height
                            );
                        } catch (_) {}
                    }
                };

                const onSeeked = () => {
                    previewVideo.removeEventListener('seeked', onSeeked);
                    drawFrame();
                };

                previewVideo.addEventListener('seeked', onSeeked);

                try {
                    previewVideo.currentTime = Math.max(
                        0,
                        Math.min(previewVideo.duration, time)
                    );
                } catch (_) {
                    previewVideo.removeEventListener('seeked', onSeeked);
                }

                showPreview();
            }

            progressArea?.addEventListener('pointerenter', e => {
                updatePreview(e.clientX);
            });

            progressArea?.addEventListener('pointermove', e => {
                updatePreview(e.clientX);
            }, {
                passive: true
            });

            progressArea?.addEventListener('pointerleave', hidePreview);
            seekBar?.addEventListener('pointerdown', e => {
                draggingSeek = true;
                revealControls();
                updatePreview(e.clientX);
            });

            window.addEventListener('pointerup', () => {
                if (draggingSeek) {
                    draggingSeek = false;
                    savePosition(true);
                    if (!pointerInside) hideControls();
                }
            }, {
                passive: true
            });

            seekBar?.addEventListener('input', e => {
                if (!Number.isFinite(video.duration) || video.duration <= 0) return;

                const percentage = Number(seekBar.value) / 100;
                video.currentTime = percentage * video.duration;

                playedBar.style.width = seekBar.value + '%';
                currentTime.textContent = formatTime(video.currentTime);

                updateStatus();

                const rect = progressArea?.getBoundingClientRect();
                if (rect && rect.width > 0) {
                    const clientX = rect.left + percentage * rect.width;
                    updatePreview(clientX);
                }
            });

            fullscreenBtn?.addEventListener('click', e => {
                e.stopPropagation();
                if (document.fullscreenElement) {
                    document.exitFullscreen().catch(() => {});
                } else {
                    player.requestFullscreen().catch(() => {});
                }
                revealControls();
            });

            document.addEventListener('fullscreenchange', () => {
                const icon = fullscreenBtn?.querySelector('i');
                if (icon) icon.className = document.fullscreenElement ?
                    'fa-solid fa-compress' :
                    'fa-solid fa-expand';
            });

            pipBtn?.addEventListener('click', async e => {
                e.stopPropagation();
                try {
                    if (document.pictureInPictureElement) await document.exitPictureInPicture();
                    else if (document.pictureInPictureEnabled) await video.requestPictureInPicture();
                } catch (_) {}
                revealControls();
            });

            speedBtn?.addEventListener('click', e => {
                e.stopPropagation();
                speedMenu.classList.toggle('show');
                revealControls();
            });

            speedMenu?.querySelectorAll('[data-speed]').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.stopPropagation();

                    video.playbackRate = Number(btn.dataset.speed);
                    speedBtn.textContent = btn.dataset.speed + 'x';

                    speedMenu.querySelectorAll('[data-speed]').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    speedMenu.classList.remove('show');

                    updateStatus();
                    revealControls();
                });
            });

            document.addEventListener('click', e => {
                if (speedMenu && !speedMenu.contains(e.target) && e.target !== speedBtn) {
                    speedMenu.classList.remove('show');
                }
            });
            video.addEventListener('click', e => {
                if (e.detail > 1) return;

                clearTimeout(clickTimer);
                clickTimer = setTimeout(() => {
                    togglePlay();
                    revealControls();
                }, 150);
            });

            video.addEventListener('dblclick', e => {
                e.preventDefault();
                clearTimeout(clickTimer);

                const rect = video.getBoundingClientRect();
                const ratio = (e.clientX - rect.left) / rect.width;

                if (ratio < .34) {
                    video.currentTime = Math.max(0, video.currentTime - 10);
                    showSeekFeedback('backward');
                } else if (ratio > .66) {
                    video.currentTime = Math.min(video.duration || Infinity, video.currentTime + 10);
                    showSeekFeedback('forward');
                } else {
                    fullscreenBtn?.click();
                }

                revealControls();
            });

            function showSeekFeedback(type) {
                if (!seekFeedback) return;

                const icon = seekFeedback.querySelector('i');
                const value = seekFeedback.querySelector('strong');

                if (type === 'forward') {
                    seekFeedback.className = 'seek-feedback forward show';
                    icon.className = 'fa-solid fa-rotate-right';
                    value.textContent = '+10';
                } else {
                    seekFeedback.className = 'seek-feedback backward show';
                    icon.className = 'fa-solid fa-rotate-left';
                    value.textContent = '-10';
                }

                clearTimeout(feedbackTimer);
                feedbackTimer = setTimeout(() => seekFeedback.classList.remove('show'), 550);
            }


            function videoHasFocus() {
                return document.activeElement === video ||
                    document.activeElement === player ||
                    player.matches(':hover');
            }

            document.addEventListener('keydown', function(e) {
                if (!videoHasFocus()) return;

                const target = e.target;
                const tag = target?.tagName?.toLowerCase();

                if (
                    tag === 'input' ||
                    tag === 'textarea' ||
                    tag === 'select' ||
                    target?.isContentEditable
                ) return;

                const key = e.key;
                const lower = key.toLowerCase();

                if (key === ' ') {
                    e.preventDefault();
                    e.stopPropagation();
                    togglePlay();
                    revealControls();
                    return;
                }

                if (lower === 'k') {
                    e.preventDefault();
                    togglePlay();
                    revealControls();
                    return;
                }

                if (key === 'ArrowLeft' || lower === 'j') {
                    e.preventDefault();
                    e.stopPropagation();
                    video.currentTime = Math.max(0, video.currentTime - 10);
                    showSeekFeedback('backward');
                    revealControls();
                    return;
                }

                if (key === 'ArrowRight' || lower === 'l') {
                    e.preventDefault();
                    e.stopPropagation();
                    video.currentTime = Math.min(
                        Number.isFinite(video.duration) ? video.duration : video.currentTime + 10,
                        video.currentTime + 10
                    );
                    showSeekFeedback('forward');
                    revealControls();
                    return;
                }

                if (key === 'ArrowUp') {
                    e.preventDefault();
                    e.stopPropagation();
                    video.muted = false;
                    video.volume = Math.min(1, video.volume + 0.1);
                    updateVolume();
                    revealControls();
                    return;
                }

                if (key === 'ArrowDown') {
                    e.preventDefault();
                    e.stopPropagation();
                    video.volume = Math.max(0, video.volume - 0.1);
                    video.muted = video.volume === 0;
                    updateVolume();
                    revealControls();
                    return;
                }

                if (lower === 'm') {
                    e.preventDefault();
                    video.muted = !video.muted;
                    updateVolume();
                    revealControls();
                    return;
                }

                if (lower === 'f') {
                    e.preventDefault();
                    if (document.fullscreenElement) {
                        document.exitFullscreen().catch(() => {});
                    } else if (player.requestFullscreen) {
                        player.requestFullscreen().catch(() => {});
                    }
                    return;
                }

                if (key === 'Escape') {
                    if (document.fullscreenElement) {
                        e.preventDefault();
                        document.exitFullscreen().catch(() => {});
                    }
                    speedMenu?.classList.remove('show');
                    return;
                }

                if (key === 'Home' || key === '0') {
                    e.preventDefault();
                    video.currentTime = 0;
                    revealControls();
                    return;
                }

                if (key === 'End') {
                    e.preventDefault();
                    if (Number.isFinite(video.duration)) {
                        video.currentTime = video.duration;
                    }
                    revealControls();
                    return;
                }

                if (/^[1-9]$/.test(key) && Number.isFinite(video.duration) && video.duration > 0) {
                    e.preventDefault();
                    video.currentTime = (Number(key) / 10) * video.duration;
                    revealControls();
                }
            });

            player.addEventListener('click', function() {
                player.focus({
                    preventScroll: true
                });
            });

            video.addEventListener('play', function() {
                player.focus({
                    preventScroll: true
                });
            });

            video.addEventListener('ended', () => {
                if (Number.isFinite(video.duration) && video.duration > 0) {
                    localStorage.setItem(progressKey, '100');
                    localStorage.setItem(completedKey, '1');
                    localStorage.removeItem(positionKey);

                    updateProgress(true);
                    updateStatus();
                    updateLessonCards();
                }
            });
            updateVolume();
            updatePlayIcon();
            loadLessonDurations();
            updateLessonCards();
        });

        function updateLessonCards() {
            document.querySelectorAll('.lesson-card').forEach(card => {
                const id = card.dataset.id;
                const progress = parseFloat(localStorage.getItem('ischool_progress_' + id) || '0');
                const safe = Math.max(0, Math.min(100, progress));
                const completed = localStorage.getItem('ischool_completed_' + id) === '1';

                const bar = card.querySelector('.lesson-progress span');
                const pct = card.querySelector('.progress-information strong');
                const check = card.querySelector('.lesson-completed');

                if (bar) bar.style.width = safe + '%';
                if (pct) pct.textContent = Math.round(safe) + '%';

                card.classList.toggle('completed', completed);
                if (check) check.classList.toggle('show', completed);
            });
        }

        function loadLessonDurations() {
            document.querySelectorAll('.lesson-card').forEach(card => {
                const duration = card.querySelector('.duration');
                if (duration && duration.textContent.trim() === '') {
                    duration.textContent = '--:--';
                }
            });
        }

        function formatCardDuration(seconds) {
            if (!Number.isFinite(seconds) || seconds < 0) return '--:--';

            seconds = Math.floor(seconds);
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;

            if (h > 0) {
                return String(h).padStart(2, '0') + ':' +
                    String(m).padStart(2, '0') + ':' +
                    String(s).padStart(2, '0');
            }

            return String(m).padStart(2, '0') + ':' +
                String(s).padStart(2, '0');
        }
    </script>

</body>

</html>