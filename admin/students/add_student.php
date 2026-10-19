<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';
$error = '';
$success = '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$status = $_POST['status'] ?? 1;
$role = $_POST['role'] ?? 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if ($name == '' || $email == '' || $password == '' || $confirmPassword == '') {
        $error = 'Please fill in all required fields';
    } elseif (!in_array((int)$role, [0, 1], true)) {
        $error = 'Invalid account type';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password != $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $check = $conn->prepare("SELECT id , email , phone FROM users WHERE email = ? OR phone = ?");
        $check->execute([$email, $phone]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['email'] == $email) {
                $error = 'This email already exists ';
            } elseif ($phone != '' && $existing['phone'] == $phone) {
                $error = 'This phone number already exists';
            } else {
                $error = 'This email or phone number already exists ';
            }
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name , email , phone , password , status , role ) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $password, $status, (int)$role]);
            $success = ((int)$role === 1)
                ? 'Admin account added successfully.'
                : 'Student added successfully.';
            $name = '';
            $email = '';
            $phone = '';
            $status = 1;
            $role = 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة حساب جديد</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/add_student.css">
    <link rel="stylesheet" href="../assets/css/disable_mobile.css">
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="page">
        <header class="page-header">
            <div class="heading">
                <span class="heading-label">إدارة الطلاب</span>
                <h1>إضافة حساب جديد</h1>
                <p>
                    أنشئ حسابًا جديدًا وحدد نوعه وحالته وبياناته الأساسية.
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
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success">
                <i class="fas fa-circle-check"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>
        <div class="content">
            <section class="card">
                <div class="card-header">
                    <div class="title-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h2>بيانات الحساب</h2>
                        <p>أدخل المعلومات المطلوبة لإنشاء الحساب.</p>
                    </div>
                </div>
                <form method="POST" class="form">
                    <div class="fields">
                        <div class="field">
                            <label for="name">الاسم الكامل</label>
                            <div class="input-box">
                                <i class="fas fa-user"></i>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="<?= htmlspecialchars($name) ?>"
                                    placeholder="اكتب الاسم الكامل"
                                    autocomplete="name"
                                    required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="email">البريد الإلكتروني</label>
                            <div class="input-box">
                                <i class="fas fa-envelope"></i>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="<?= htmlspecialchars($email) ?>"
                                    placeholder="student@example.com"
                                    autocomplete="email"
                                    required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="phone">رقم الهاتف</label>
                            <div class="input-box">
                                <i class="fas fa-phone"></i>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    value="<?= htmlspecialchars($phone) ?>"
                                    placeholder="اكتب رقم الهاتف"
                                    autocomplete="tel">
                            </div>
                        </div>
                        <div class="field">
                            <label for="role">نوع الحساب</label>
                            <div class="input-box">
                                <i class="fas fa-user-tag"></i>
                                <select name="role" id="role">
                                    <option value="0" <?= (int)$role === 0 ? 'selected' : '' ?>>
                                        طالب
                                    </option>
                                    <option value="1" <?= (int)$role === 1 ? 'selected' : '' ?>>
                                        أدمن
                                    </option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>

                        <div class="field">
                            <label for="status">حالة الحساب</label>
                            <div class="input-box">
                                <i class="fas fa-circle-check"></i>
                                <select name="status" id="status">
                                    <option value="1" <?= $status == 1 ? 'selected' : '' ?>>
                                        نشط
                                    </option>
                                    <option value="0" <?= $status == 0 ? 'selected' : '' ?>>
                                        غير نشط
                                    </option>
                                    <option value="2" <?= $status == 2 ? 'selected' : '' ?>>
                                        محظور
                                    </option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        <div class="field">
                            <label for="password">كلمة المرور</label>
                            <div class="input-box">
                                <i class="fas fa-lock"></i>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    placeholder="اكتب كلمة المرور"
                                    autocomplete="new-password"
                                    required>
                                <button
                                    type="button"
                                    class="password-button"
                                    data-target="password"
                                    aria-label="إظهار كلمة المرور">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="field">
                            <label for="confirm_password">تأكيد كلمة المرور</label>
                            <div class="input-box">
                                <i class="fas fa-lock"></i>
                                <input
                                    type="password"
                                    name="confirm_password"
                                    id="confirm_password"
                                    placeholder="أعد كتابة كلمة المرور"
                                    autocomplete="new-password"
                                    required>
                                <button
                                    type="button"
                                    class="password-button"
                                    data-target="confirm_password"
                                    aria-label="إظهار تأكيد كلمة المرور">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
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
                            <i class="fas fa-user-plus"></i>
                            <span>إضافة الطالب</span>
                        </button>
                    </div>
                </form>
            </section>
            <aside class="card preview-card">
                <div class="preview-header">
                    <div>
                        <span class="preview-label">معاينة</span>
                        <h2><?= (int)$role === 1 ? 'حساب الأدمن' : 'الحساب الجديد' ?></h2>
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
                            <?= $name != '' ? htmlspecialchars($name) : 'طالب جديد' ?>
                        </h3>
                        <p id="previewEmail">
                            <?= $email != '' ? htmlspecialchars($email) : 'student@example.com' ?>
                        </p>
                    </div>
                </div>
                <div class="preview-status">
                    <span>حالة الحساب</span>
                    <strong
                        id="previewStatus"
                        class="<?= $status == 1 ? 'active' : ($status == 2 ? 'suspended' : 'inactive') ?>">
                        <?php if ($status == 1): ?>
                            نشط
                        <?php elseif ($status == 2): ?>
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
                            <span>رقم الهاتف</span>
                            <strong id="previewPhone">
                                <?= $phone != '' ? htmlspecialchars($phone) : 'لم تتم الإضافة' ?>
                            </strong>
                        </div>
                    </div>
                    <div class="preview-item">
                        <div class="item-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div>
                            <span>نوع الحساب</span>
                            <strong id="previewRole"><?= (int)$role === 1 ? 'أدمن' : 'طالب' ?></strong>
                        </div>
                    </div>
                    <div class="preview-item">
                        <div class="item-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <span>الصلاحية</span>
                            <strong id="previewPermission"><?= (int)$role === 1 ? 'حساب أدمن' : 'حساب طالب' ?></strong>
                        </div>
                    </div>
                </div>
                <div class="preview-footer">
                    <i class="fas fa-circle-check"></i>
                    <span>الحساب جاهز للإنشاء</span>
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
        const previewTitle = document.querySelector('.preview-card h2');
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value.trim() || 'طالب جديد';
        });
        emailInput.addEventListener('input', function() {
            previewEmail.textContent = this.value.trim() || 'student@example.com';
        });
        phoneInput.addEventListener('input', function() {
            previewPhone.textContent = this.value.trim() || 'لم تتم الإضافة';
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
            previewPermission.textContent = isAdmin ? 'حساب أدمن' : 'حساب طالب';

            if (previewTitle) {
                previewTitle.textContent = isAdmin ? 'حساب الأدمن' : 'الحساب الجديد';
            }
        });

        document.querySelectorAll('.password-button').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.setAttribute('aria-label', 'إخفاء كلمة المرور');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.setAttribute('aria-label', 'إظهار كلمة المرور');
                }
            });
        });
    </script>
</body>

</html>