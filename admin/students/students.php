<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';

$search = trim($_GET['search'] ?? '');
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [];

// فلتر نوع الحساب
if ($type === 'student') {
    $where[] = 'role = ?';
    $params[] = 0;
} elseif ($type === 'admin') {
    $where[] = 'role = ?';
    $params[] = 1;
}

// فلتر حالة الحساب
if ($status === 'active') {
    $where[] = 'status = ?';
    $params[] = 1;
} elseif ($status === 'inactive') {
    $where[] = 'status = ?';
    $params[] = 0;
} elseif ($status === 'blocked') {
    $where[] = 'status = ?';
    $params[] = 2;
}

// البحث بالاسم أو البريد أو رقم الحساب
if ($search !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR id LIKE ?)';
    $value = '%' . $search . '%';
    $params[] = $value;
    $params[] = $value;
    $params[] = $value;
}

// تجهيز الاستعلام
$sql = 'SELECT * FROM users';

if (count($where) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY id DESC';

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStudents = (int) $conn->query("SELECT COUNT(*) FROM users WHERE role=0")->fetchColumn();
$activeStudents = (int) $conn->query("SELECT COUNT(*) FROM users WHERE status = 1 AND role = 0")->fetchColumn();
$inactiveStudents = (int) $conn->query("SELECT COUNT(*) FROM users WHERE status = 0 AND role = 0")->fetchColumn();
$subscribedStudents = (int) $conn->query("SELECT COUNT(*) FROM users WHERE status = 2 AND role = 0")->fetchColumn();
$activePercent = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100) : 0;
$inactivePercent = $totalStudents > 0 ? round(($inactiveStudents / $totalStudents) * 100) : 0;
$suspendedPercent = $totalStudents > 0 ? round(($subscribedStudents / $totalStudents) * 100) : 0;
$remainingStudents = max(0, $totalStudents - $activeStudents - $inactiveStudents - $subscribedStudents);
$remainingPercent = $totalStudents > 0 ? round(($remainingStudents / $totalStudents) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلاب</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/students.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="students-page">
        <header class="page-header">
            <div class="heading">
                <span class="heading-label">
                    إدارة الطلاب
                </span>
                <h1>
                    الطلاب
                </h1>
                <p>
                    إدارة حسابات الطلاب ومتابعة حالاتهم وبياناتهم
                </p>
            </div>
            <a
                href="<?= $studentsUrl . 'add_student.php' ?>"
                class="add-button">
                <i class="fas fa-plus"></i>
                <span>إضافة طالب</span>
            </a>
        </header>
        <section class="stats" aria-label="إحصائيات الطلاب">
            <article class="stat-card stat-total">
                <div class="stat-card-content">
                    <div class="stat-card-top">
                        <div class="stat-card-title">
                            <span>قاعدة الطلاب</span>
                            <h2>إجمالي الطلاب</h2>
                        </div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>

                    <div class="stat-value-row">
                        <strong><?= number_format($totalStudents) ?></strong>
                        <span>طالب</span>
                    </div>

                    <div class="stat-description">
                        <i class="fas fa-database"></i>
                        العدد الكلي المسجل فعليًا
                    </div>
                </div>

                <div class="stat-visual total-visual" aria-hidden="true">
                    <svg viewBox="0 0 220 82" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="totalAreaFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="currentColor" stop-opacity=".20" />
                                <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        <path class="visual-grid" d="M0 67H220 M0 45H220 M0 23H220" />
                        <path class="visual-area"
                            fill="url(#totalAreaFill)"
                            d="M0 64 C18 61 25 50 43 54 S68 40 84 47 S108 28 124 37 S148 22 165 29 S188 14 204 22 S215 10 220 13 L220 82 L0 82Z" />
                        <path id="totalStatPath"
                            class="visual-line"
                            d="M0 64 C18 61 25 50 43 54 S68 40 84 47 S108 28 124 37 S148 22 165 29 S188 14 204 22 S215 10 220 13" />

                        <circle class="moving-glow" r="7">
                            <animateMotion dur="5.5s" repeatCount="indefinite" rotate="auto">
                                <mpath href="#totalStatPath" />
                            </animateMotion>
                        </circle>
                        <circle class="moving-dot" r="3.2">
                            <animateMotion dur="5.5s" repeatCount="indefinite" rotate="auto">
                                <mpath href="#totalStatPath" />
                            </animateMotion>
                        </circle>
                    </svg>
                </div>
            </article>

            <article class="stat-card stat-active">
                <div class="stat-card-content">
                    <div class="stat-card-top">
                        <div class="stat-card-title">
                            <span>الحالة الحالية</span>
                            <h2>الطلاب النشطون</h2>
                        </div>
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                    </div>

                    <div class="stat-value-row">
                        <strong><?= number_format($activeStudents) ?></strong>
                        <span><?= $activePercent ?>%</span>
                    </div>

                    <div class="stat-progress">
                        <span style="width:<?= min(100, $activePercent) ?>%"></span>
                    </div>

                    <div class="stat-description">
                        <i class="fas fa-arrow-trend-up"></i>
                        <?= $activePercent ?>% من إجمالي الطلاب
                    </div>
                </div>

                <div class="stat-visual active-visual" aria-hidden="true">
                    <svg viewBox="0 0 100 100">
                        <circle class="ring-bg" cx="50" cy="50" r="36" />
                        <circle class="ring-value"
                            cx="50" cy="50" r="36"
                            pathLength="100"
                            stroke-dasharray="<?= $activePercent ?> 100" />
                        <text x="50" y="54" text-anchor="middle"><?= $activePercent ?>%</text>
                    </svg>
                </div>
            </article>

            <article class="stat-card stat-suspended">
                <div class="stat-card-content">
                    <div class="stat-card-top">
                        <div class="stat-card-title">
                            <span>الحالة الحالية</span>
                            <h2>الطلاب المحظورون</h2>
                        </div>
                        <div class="stat-icon"><i class="fas fa-user-lock"></i></div>
                    </div>

                    <div class="stat-value-row">
                        <strong><?= number_format($subscribedStudents) ?></strong>
                        <span><?= $suspendedPercent ?>%</span>
                    </div>

                    <div class="stat-progress">
                        <span style="width:<?= min(100, $suspendedPercent) ?>%"></span>
                    </div>

                    <div class="stat-description">
                        <i class="fas fa-shield-halved"></i>
                        <?= $suspendedPercent ?>% من إجمالي الطلاب
                    </div>
                </div>

                <div class="stat-visual bars-visual" aria-hidden="true">
                    <div class="bars">
                        <i style="height:34%"></i>
                        <i style="height:51%"></i>
                        <i style="height:43%"></i>
                        <i style="height:<?= max(12, min(100, $suspendedPercent)) ?>%"></i>
                        <i style="height:70%"></i>
                        <i style="height:57%"></i>
                        <i style="height:80%"></i>
                    </div>
                </div>
            </article>

            <article class="stat-card stat-inactive">
                <div class="stat-card-content">
                    <div class="stat-card-top">
                        <div class="stat-card-title">
                            <span>الحالة الحالية</span>
                            <h2>الطلاب غير النشطين</h2>
                        </div>
                        <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
                    </div>

                    <div class="stat-value-row">
                        <strong><?= number_format($inactiveStudents) ?></strong>
                        <span><?= $inactivePercent ?>%</span>
                    </div>

                    <div class="stat-progress">
                        <span style="width:<?= min(100, $inactivePercent) ?>%"></span>
                    </div>

                    <div class="stat-description">
                        <i class="fas fa-chart-pie"></i>
                        <?= $inactivePercent ?>% من إجمالي الطلاب
                    </div>
                </div>

                <div class="stat-visual inactive-visual" aria-hidden="true">
                    <svg viewBox="0 0 220 82" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="inactiveAreaFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="currentColor" stop-opacity=".16" />
                                <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        <path class="visual-grid" d="M0 67H220 M0 45H220 M0 23H220" />
                        <path class="visual-area"
                            fill="url(#inactiveAreaFill)"
                            d="M0 58 C19 57 28 46 44 52 S68 35 84 44 S106 31 124 38 S147 25 164 32 S188 18 204 26 S215 15 220 18 L220 82 L0 82Z" />
                        <path id="inactiveStatPath"
                            class="visual-line"
                            d="M0 58 C19 57 28 46 44 52 S68 35 84 44 S106 31 124 38 S147 25 164 32 S188 18 204 26 S215 15 220 18" />

                        <circle class="moving-glow" r="7">
                            <animateMotion dur="6s" repeatCount="indefinite" rotate="auto">
                                <mpath href="#inactiveStatPath" />
                            </animateMotion>
                        </circle>
                        <circle class="moving-dot" r="3.2">
                            <animateMotion dur="6s" repeatCount="indefinite" rotate="auto">
                                <mpath href="#inactiveStatPath" />
                            </animateMotion>
                        </circle>
                    </svg>
                </div>
            </article>
        </section>
        <section class="search-box">
            <form method="GET">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="ابحث بالاسم أو البريد الإلكتروني أو رقم الطالب">
                <?php if ($search): ?>
                    <a
                        href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
                        class="clear-button"
                        title="مسح البحث">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
                <button
                    type="submit"
                    class="search-button">
                    بحث
                </button>
            </form>
        </section>

        <section class="account-filters" aria-label="فلاتر الحسابات">
            <form method="GET" class="account-filters-form">
                <div class="account-filter">
                    <label for="accountType">نوع الحساب</label>
                    <select id="accountType" name="type" onchange="this.form.submit()">
                        <option value="" <?= $type === '' ? 'selected' : '' ?>>كل الحسابات</option>
                        <option value="student" <?= $type === 'student' ? 'selected' : '' ?>>الطلاب</option>
                        <option value="admin" <?= $type === 'admin' ? 'selected' : '' ?>>الأدمن</option>
                    </select>
                </div>
                <div class="account-filter">
                    <label for="accountStatus">حالة الحساب</label>
                    <select id="accountStatus" name="status" onchange="this.form.submit()">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>كل الحالات</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>نشط</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>غير نشط</option>
                        <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>محظور</option>
                    </select>
                </div>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($type !== '' || $status !== '' || $search !== ''): ?>
                    <a class="account-filter-reset" href="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-rotate-left"></i>
                        إعادة التعيين
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <section class="students-box">
            <div class="table-scroll">
                <table>
                    <colgroup>
                        <col style="width:28%">
                        <col style="width:14%">
                        <col style="width:14%">
                        <col style="width:24%">
                        <col style="width:9%">
                        <col style="width:11%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>الحساب</th>
                            <th>نوع الحساب</th>
                            <th>طريقة التسجيل</th>
                            <th>البريد الإلكتروني</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students): ?>
                            <?php foreach ($students as $student): ?>
                                <?php
                                $safeStudent = $student;
                                foreach (
                                    [
                                        'password',
                                        'password_hash',
                                        'token',
                                        'access_token',
                                        'refresh_token',
                                        'remember_token',
                                        'otp',
                                        'otp_code',
                                        'verification_code',
                                        'reset_token',
                                        'reset_token_hash',
                                        'verification_token',
                                        'secret'
                                    ] as $privateKey
                                ) {
                                    unset($safeStudent[$privateKey]);
                                }
                                $provider = strtolower(trim((string)($student['auth_provider'] ?? $student['registration_method'] ?? 'email')));
                                $isGoogle = in_array($provider, ['google', 'google_oauth', 'google-oauth'], true);
                                $safeStudent['courses'] = 0;
                                $safeStudent['auth_provider'] = $isGoogle ? 'google' : 'email';
                                $detailsJson = htmlspecialchars(
                                    json_encode($safeStudent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                $isAdmin = (int)$student['role'] === 1;
                                ?>
                                <tr>
                                    <td>
                                        <div class="student">
                                            <div class="avatar <?= $isAdmin ? 'avatar-admin' : 'avatar-student' ?>">
                                                <i class="<?= $isAdmin ? 'fas fa-shield-halved' : 'fas fa-user' ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <small><?= $isAdmin ? 'حساب أدمن' : 'حساب طالب' ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="account-type <?= $isAdmin ? 'admin' : 'student' ?>">
                                            <i class="<?= $isAdmin ? 'fas fa-shield-halved' : 'fas fa-graduation-cap' ?>"></i>
                                            <?= $isAdmin ? 'أدمن' : 'طالب' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="registration-method <?= $isGoogle ? 'google' : 'email' ?>">
                                            <i class="<?= $isGoogle ? 'fab fa-google' : 'fas fa-envelope' ?>"></i>
                                            <?= $isGoogle ? 'Google' : 'إيميل' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>" class="email">
                                            <?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ((int)$student['status'] === 1): ?>
                                            <span class="status active"><span></span>نشط</span>
                                        <?php elseif ((int)$student['status'] === 2): ?>
                                            <span class="status suspended"><span></span>محظور</span>
                                        <?php else: ?>
                                            <span class="status inactive"><span></span>غير نشط</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="view-account" data-account="<?= $detailsJson ?>" title="عرض كل البيانات" aria-label="عرض كل البيانات">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit_student.php?id=<?= (int)$student['id'] ?>" class="edit" title="تعديل" aria-label="تعديل">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="delete_student.php?id=<?= (int)$student['id'] ?>" class="delete" title="حذف" aria-label="حذف" onclick="return confirm('هل تريد حذف هذا الحساب؟')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty">
                                        <i class="fas fa-users"></i>
                                        <strong><?= $search ? 'لم يتم العثور على حسابات' : 'لا توجد حسابات حاليًا' ?></strong>
                                        <span><?= $search ? 'جرّب استخدام كلمات بحث مختلفة.' : 'ستظهر الحسابات هنا عند إضافتها.' ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mobile-students">
                <?php if ($students): ?>
                    <?php foreach ($students as $student): ?>
                        <?php
                        $safeMobile = $student;
                        foreach (
                            [
                                'password',
                                'password_hash',
                                'token',
                                'access_token',
                                'refresh_token',
                                'remember_token',
                                'otp',
                                'otp_code',
                                'verification_code',
                                'reset_token',
                                'reset_token_hash',
                                'verification_token',
                                'secret'
                            ] as $privateKey
                        ) {
                            unset($safeMobile[$privateKey]);
                        }
                        $provider = strtolower(trim((string)($student['auth_provider'] ?? $student['registration_method'] ?? 'email')));
                        $isGoogle = in_array($provider, ['google', 'google_oauth', 'google-oauth'], true);
                        $safeMobile['courses'] = 0;
                        $safeMobile['auth_provider'] = $isGoogle ? 'google' : 'email';
                        $mobileJson = htmlspecialchars(
                            json_encode($safeMobile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        $isAdmin = (int)$student['role'] === 1;
                        ?>
                        <article class="mobile-card">
                            <div class="mobile-top">
                                <div class="student">
                                    <div class="avatar <?= $isAdmin ? 'avatar-admin' : 'avatar-student' ?>">
                                        <i class="<?= $isAdmin ? 'fas fa-shield-halved' : 'fas fa-user' ?>"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <small>#<?= htmlspecialchars((string)$student['id'], ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                </div>
                                <?php if ((int)$student['status'] === 1): ?>
                                    <span class="status active"><span></span>نشط</span>
                                <?php elseif ((int)$student['status'] === 2): ?>
                                    <span class="status suspended"><span></span>محظور</span>
                                <?php else: ?>
                                    <span class="status inactive"><span></span>غير نشط</span>
                                <?php endif; ?>
                            </div>

                            <div class="mobile-email">
                                <i class="fas fa-envelope"></i>
                                <?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                            <div class="mobile-info">
                                <div>
                                    <small>نوع الحساب</small>
                                    <strong><?= $isAdmin ? 'أدمن' : 'طالب' ?></strong>
                                </div>
                                <div>
                                    <small>الحالة</small>
                                    <strong><?= (int)$student['status'] === 1 ? 'نشط' : ((int)$student['status'] === 2 ? 'محظور' : 'غير نشط') ?></strong>
                                </div>
                                <div>
                                    <small>طريقة التسجيل</small>
                                    <strong class="<?= $isGoogle ? 'google-text' : 'email-text' ?>">
                                        <i class="<?= $isGoogle ? 'fab fa-google' : 'fas fa-envelope' ?>"></i>
                                        <?= $isGoogle ? 'Google' : 'إيميل' ?>
                                    </strong>
                                </div>
                            </div>

                            <div class="mobile-actions">
                                <button type="button" class="view-account" data-account="<?= $mobileJson ?>">
                                    <i class="fas fa-eye"></i>
                                    عرض
                                </button>
                                <a href="edit_student.php?id=<?= (int)$student['id'] ?>" class="edit">
                                    <i class="fas fa-pen"></i>
                                    تعديل
                                </a>
                                <a href="delete_student.php?id=<?= (int)$student['id'] ?>" class="delete" onclick="return confirm('هل تريد حذف هذا الحساب؟')">
                                    <i class="fas fa-trash"></i>
                                    حذف
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty">
                        <i class="fas fa-users"></i>
                        <strong><?= $search ? 'لم يتم العثور على حسابات' : 'لا توجد حسابات حاليًا' ?></strong>
                        <span><?= $search ? 'جرّب استخدام كلمات بحث مختلفة.' : 'ستظهر الحسابات هنا عند إضافتها.' ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="account-modal" id="accountDetailsModal" aria-hidden="true">
            <div class="account-modal-backdrop" data-close-account></div>
            <div class="account-modal-box" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
                <div class="account-modal-head">
                    <div>
                        <h3 id="accountModalTitle">بيانات الحساب</h3>
                        <p id="accountModalSubtitle"></p>
                    </div>
                    <button type="button" class="account-modal-close" data-close-account aria-label="إغلاق">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
                <div class="account-modal-grid" id="accountModalGrid"></div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('accountDetailsModal');
            const grid = document.getElementById('accountModalGrid');
            const title = document.getElementById('accountModalTitle');
            const subtitle = document.getElementById('accountModalSubtitle');
            if (!modal || !grid) return;

            const labels = {
                id: 'رقم الحساب',
                name: 'الاسم',
                email: 'البريد الإلكتروني',
                role: 'نوع الحساب',
                auth_provider: 'طريقة التسجيل',
                status: 'حالة الحساب',
                created_at: 'تاريخ الإنشاء',
                updated_at: 'آخر تحديث',
                phone: 'رقم الهاتف',
                username: 'اسم المستخدم',
                gender: 'النوع',
                birth_date: 'تاريخ الميلاد',
                parent_phone: 'هاتف ولي الأمر',
                school: 'المدرسة',
                grade: 'الصف',
                address: 'العنوان',
                last_login: 'آخر تسجيل دخول',
                ip_address: 'عنوان IP',
                avatar: 'الصورة',
                courses: 'الكورسات'
            };

            const hidden = new Set([
                'password', 'password_hash', 'token', 'access_token', 'refresh_token',
                'remember_token', 'otp', 'otp_code', 'verification_code', 'reset_token',
                'reset_token_hash', 'verification_token', 'secret'
            ]);

            function textValue(key, value) {
                if (key === 'role') return String(value) === '1' ? 'أدمن' : 'طالب';
                if (key === 'auth_provider') return ['google', 'google_oauth', 'google-oauth'].includes(String(value).toLowerCase()) ? 'Google' : 'إيميل';
                if (key === 'status') {
                    return String(value) === '1' ? 'نشط' : (String(value) === '2' ? 'محظور' : 'غير نشط');
                }
                if (key === 'courses') return '0';
                return String(value);
            }

            function openModal(button) {
                let data = {};
                try {
                    data = JSON.parse(button.getAttribute('data-account') || '{}');
                } catch (e) {
                    return;
                }

                grid.innerHTML = '';
                title.textContent = data.name || 'بيانات الحساب';
                subtitle.textContent = data.email || '';

                Object.keys(data).forEach(function(key) {
                    if (hidden.has(key) || data[key] === null || data[key] === '') return;
                    if (typeof data[key] === 'object') return;

                    const field = document.createElement('div');
                    field.className = 'account-modal-field';

                    const label = document.createElement('span');
                    label.textContent = labels[key] || key.replaceAll('_', ' ');

                    const value = document.createElement('strong');
                    value.textContent = textValue(key, data[key]);

                    field.append(label, value);
                    grid.appendChild(field);
                });

                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('account-modal-open');
            }

            function closeModal() {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('account-modal-open');
            }

            document.querySelectorAll('.view-account').forEach(function(button) {
                button.addEventListener('click', function() {
                    openModal(this);
                });
            });

            modal.querySelectorAll('[data-close-account]').forEach(function(element) {
                element.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('show')) closeModal();
            });
        });
    </script>

</body>

</html>