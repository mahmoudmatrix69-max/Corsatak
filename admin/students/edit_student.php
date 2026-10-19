<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND role IN (0,1)");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) {
    exit('لم يتم العثور على الطالب');
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $status = $_POST['status'] ?? 1;
    $role = $_POST['role'] ?? ($student['role'] ?? 0);
    $password = $_POST['password'] ?? '';
    if ($name === '' || $email === '') {
        $error = 'يرجى إدخال الاسم والبريد الإلكتروني';
    } elseif (!in_array((int)$role, [0, 1], true)) {
        $error = 'نوع الحساب غير صالح';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'يرجى إدخال بريد إلكتروني صحيح';
    } else {
        if ($password != '') {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE users
                SET name=?, email=?, phone=?, status=?, password=?, role=?
                WHERE id=?
            ");
            $stmt->execute([
                $name,
                $email,
                $phone,
                $status,
                $password,
                (int)$role,
                $id
            ]);
        } else {
            $stmt = $conn->prepare("
                UPDATE users
                SET name=?, email=?, phone=?, status=?, role=?
                WHERE id=?
            ");
            $stmt->execute([
                $name,
                $email,
                $phone,
                $status,
                (int)$role,
                $id
            ]);
        }
        $student['name'] = $name;
        $student['email'] = $email;
        $student['phone'] = $phone;
        $student['status'] = $status;
        $student['role'] = (int)$role;
        $success = 'تم تحديث بيانات الحساب بنجاح';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات الحساب</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/edit_student.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="page">
        <header class="page-header">
            <div class="heading">
                <span class="heading-label">
                    إدارة الطلاب
                </span>
                <h1>
                    تعديل بيانات الحساب
                </h1>
                <p>
                    قم بتحديث بيانات الحساب أو نوعه أو حالته أو كلمة المرور.
                </p>
            </div>
            <a
                href="<?= $studentsUrl . 'students.php' ?>"
                class="back-button">
                <i class="fas fa-arrow-right"></i>
                <span>العودة إلى الحسابات</span>
            </a>
        </header>
        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-circle-exclamation"></i>
                <span>
                    <?= htmlspecialchars($error) ?>
                </span>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success">
                <i class="fas fa-circle-check"></i>
                <span>
                    <?= htmlspecialchars($success) ?>
                </span>
            </div>
        <?php endif; ?>
        <div class="content">
            <section class="card">
                <div class="card-title">
                    <div class="title-icon">
                        <i class="fas fa-user-pen"></i>
                    </div>
                    <div>
                        <h2>
                            بيانات الحساب
                        </h2>
                        <p>
                            عدّل المعلومات التي تريد تحديثها.
                        </p>
                    </div>
                </div>
                <form method="POST">
                    <div class="fields">
                        <div class="field">
                            <label for="name">
                                الاسم الكامل
                            </label>
                            <div class="input-box">
                                <i class="fas fa-user"></i>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="<?= htmlspecialchars($student['name']) ?>"
                                    placeholder="اكتب الاسم الكامل"
                                    autocomplete="name"
                                    required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="email">
                                البريد الإلكتروني
                            </label>
                            <div class="input-box">
                                <i class="fas fa-envelope"></i>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="<?= htmlspecialchars($student['email']) ?>"
                                    placeholder="student@example.com"
                                    autocomplete="email"
                                    required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="phone">
                                رقم الهاتف
                            </label>
                            <div class="input-box">
                                <i class="fas fa-phone"></i>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    value="<?= htmlspecialchars($student['phone']) ?>"
                                    placeholder="اكتب رقم الهاتف"
                                    autocomplete="tel">
                            </div>
                        </div>
                        <div class="field">
                            <label for="role">
                                نوع الحساب
                            </label>
                            <div class="input-box">
                                <i class="fas fa-user-tag"></i>
                                <select name="role" id="role">
                                    <option value="0" <?= (int)$student['role'] === 0 ? 'selected' : '' ?>>
                                        طالب
                                    </option>
                                    <option value="1" <?= (int)$student['role'] === 1 ? 'selected' : '' ?>>
                                        أدمن
                                    </option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        <div class="field">
                            <label for="status">
                                حالة الحساب
                            </label>
                            <div class="input-box">
                                <i class="fas fa-circle-check"></i>
                                <select
                                    name="status"
                                    id="status">
                                    <option
                                        value="1"
                                        <?= $student['status'] == 1 ? 'selected' : '' ?>>
                                        نشط
                                    </option>
                                    <option
                                        value="0"
                                        <?= $student['status'] == 0 ? 'selected' : '' ?>>
                                        غير نشط
                                    </option>
                                    <option
                                        value="2"
                                        <?= $student['status'] == 2 ? 'selected' : '' ?>>
                                        محظور
                                    </option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        <div class="field">
                            <label for="password">
                                كلمة مرور جديدة
                            </label>
                            <div class="input-box">
                                <i class="fas fa-lock"></i>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    placeholder="اتركها فارغة للإبقاء على الحالية"
                                    autocomplete="new-password">
                                <button
                                    type="button"
                                    class="password-button"
                                    id="togglePassword"
                                    aria-label="إظهار كلمة المرور">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="password-note">
                                إذا لم تدخل كلمة مرور جديدة، ستظل كلمة المرور الحالية كما هي.
                            </small>
                        </div>
                    </div>
                    <div class="form-buttons">
                        <a
                            href="<?= $studentsUrl . 'students.php' ?>"
                            class="cancel-button">
                            إلغاء
                        </a>
                        <button
                            type="submit"
                            class="submit-button">
                            <i class="fas fa-save"></i>
                            <span>حفظ التعديلات</span>
                        </button>
                    </div>
                </form>
            </section>
            <aside class="card preview-card">
                <div class="preview-header">
                    <div>
                        <span class="preview-label">
                            معاينة
                        </span>
                        <h2>
                            <?= (int)$student['role'] === 1 ? 'حساب الأدمن' : 'حساب الطالب' ?>
                        </h2>
                    </div>
                    <div class="preview-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <div class="student-profile">
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3 id="previewName">
                            <?= htmlspecialchars($student['name']) ?>
                        </h3>
                        <p id="previewEmail">
                            <?= htmlspecialchars($student['email']) ?>
                        </p>
                    </div>
                </div>
                <div class="preview-status">
                    <span>
                        حالة الحساب
                    </span>
                    <strong
                        id="previewStatus"
                        class="<?= $student['status'] == 1 ? 'active' : ($student['status'] == 2 ? 'suspended' : 'inactive') ?>">
                        <?php if ($student['status'] == 1): ?>
                            نشط
                        <?php elseif ($student['status'] == 2): ?>
                            محظور
                        <?php else: ?>
                            غير نشط
                        <?php endif; ?>
                    </strong>
                </div>
                <div class="preview-details">
                    <div class="preview-item">
                        <div class="item-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <span>
                                رقم الهاتف
                            </span>
                            <strong id="previewPhone">
                                <?= !empty($student['phone']) ? htmlspecialchars($student['phone']) : 'لم تتم الإضافة' ?>
                            </strong>
                        </div>
                    </div>
                    <div class="preview-item">
                        <div class="item-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div>
                            <span>
                                نوع الحساب
                            </span>
                            <strong id="previewRole">
                                <?= (int)$student['role'] === 1 ? 'أدمن' : 'طالب' ?>
                            </strong>
                        </div>
                    </div>
                    <div class="preview-item">
                        <div class="item-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <span>
                                الصلاحية
                            </span>
                            <strong id="previewPermission">
                                <?= (int)$student['role'] === 1 ? 'حساب أدمن' : 'حساب طالب' ?>
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="preview-footer">
                    <i class="fas fa-circle-check"></i>
                    <span>
                        يمكنك حفظ التعديلات بعد الانتهاء
                    </span>
                </div>
            </aside>
        </div>
    </main>
    <script>
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const statusInput = document.getElementById('status');
        const roleInput = document.getElementById('role');
        const previewName = document.getElementById('previewName');
        const previewEmail = document.getElementById('previewEmail');
        const previewPhone = document.getElementById('previewPhone');
        const previewStatus = document.getElementById('previewStatus');
        const previewRole = document.getElementById('previewRole');
        const previewPermission = document.getElementById('previewPermission');
        nameInput.addEventListener('input', function() {
            previewName.textContent =
                this.value.trim() || 'طالب';
        });
        emailInput.addEventListener('input', function() {
            previewEmail.textContent =
                this.value.trim() || 'student@example.com';
        });
        phoneInput.addEventListener('input', function() {
            previewPhone.textContent =
                this.value.trim() || 'لم تتم الإضافة';
        });
        statusInput.addEventListener('change', function() {
            if (this.value == 1) {
                previewStatus.textContent = 'نشط';
                previewStatus.className = 'active';
            } else if (this.value == 2) {
                previewStatus.textContent = 'محظور';
                previewStatus.className = 'suspended';
            } else {
                previewStatus.textContent = 'غير نشط';
                previewStatus.className = 'inactive';
            }
        });

        roleInput.addEventListener('change', function() {
            const isAdmin = this.value == 1;

            previewRole.textContent = isAdmin ? 'أدمن' : 'طالب';
            previewPermission.textContent = isAdmin ?
                'حساب أدمن' :
                'حساب طالب';
        });

        const togglePassword =
            document.getElementById('togglePassword');
        const passwordInput =
            document.getElementById('password');
        togglePassword.addEventListener('click', function() {
            const icon =
                this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute(
                    'aria-label',
                    'إخفاء كلمة المرور'
                );
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute(
                    'aria-label',
                    'إظهار كلمة المرور'
                );
            }
        });
    </script>
</body>

</html>