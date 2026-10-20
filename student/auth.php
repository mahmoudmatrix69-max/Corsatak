<?php
require_once __DIR__ . '/paths.php';
require_once $config;
require_once __DIR__ . '/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_SESSION['id'])) {
  if ((int)($_SESSION['role'] ?? 0) === 1) {
    header("Location: " . $adminUrl);
    exit();
  }
  header("Location: " . $indexUrl);
  exit();
}

$accountStatus = '';
$error         = '';
$success       = '';
$step          = 'auth';

if (!empty($_SESSION['google_error'])) {
    $error = $_SESSION['google_error'];
    unset($_SESSION['google_error']);
}

if (isset($_GET['step']) && in_array($_GET['step'], ['verify', 'forgot', 'reset_verify', 'reset'], true)) {
  $step = $_GET['step'];
}

if ($step === 'verify' && empty($_SESSION['pending_verify_id'])) $step = 'auth';
if (in_array($step, ['reset_verify', 'reset'], true) && empty($_SESSION['reset_uid'])) $step = 'auth';
if ($step === 'reset' && empty($_SESSION['reset_verified'])) $step = 'reset_verify';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $action = $_POST['action'] ?? '';

  if ($action === 'login') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
      $error = "من فضلك أدخل البريد الإلكتروني وكلمة المرور";
    } else {
      try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
          $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
        } elseif (($user['auth_provider'] ?? 'email') === 'google') {
          $error = "هذا الحساب مسجل باستخدام Google. استخدم تسجيل الدخول باستخدام Google";
        } elseif (!password_verify($password, $user['password'])) {
          $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
        } elseif ((int)$user['status'] === 2) {
          $accountStatus = 'banned';
        } elseif ((int)$user['status'] === 0 || (int)$user['email_verified'] === 0) {
          $verifyCode    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $verifyExpires = date('Y-m-d H:i:s', time() + 900);

          $conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?")
            ->execute([$verifyCode, $verifyExpires, $user['id']]);

          sendMail(
            $user['email'],
            'كود تفعيل حسابك - منصة كورساتك',
            otpTemplate($user['name'], $verifyCode, 'تفعيل الحساب')
          );

          $_SESSION['pending_verify_id']    = $user['id'];
          $_SESSION['pending_verify_email'] = $user['email'];
          $_SESSION['verify_resend_at'] = time() + 60;
          header("Location: ?step=verify");
          exit();
        } elseif ((int)$user['status'] === 0) {
          $accountStatus = 'inactive';
        } else {
          session_regenerate_id(true);
          $_SESSION['id']    = $user['id'];
          $_SESSION['name']  = $user['name'];
          $_SESSION['email'] = $user['email'];
          $_SESSION['role']  = (int)$user['role'];

          if ((int)$user['role'] === 1) {
            header("Location: " . $adminUrl);
            exit();
          }
          header("Location: " . $indexUrl);
          exit();
        }
      } catch (PDOException $e) {
        error_log("Login DB error: " . $e->getMessage());
        $error = "فشل تسجيل الدخول حاول لاحقاً";
      }
    }
  }

  if ($action === 'register') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$phone || !$password || !$confirm) {
      $error = "من فضلك املأ جميع الحقول";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = "أدخل بريداً إلكترونياً صحيحاً";
    } elseif ($password !== $confirm) {
      $error = "كلمتا المرور غير متطابقتين";
    } else {
      try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
          $error = "البريد الإلكتروني مستخدم بالفعل";
        } else {
          $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
          $stmt->execute([$phone]);

          if ($stmt->fetch()) {
            $error = "رقم الموبايل مستخدم بالفعل";
          } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
              INSERT INTO users (name, email, phone, password, role, status, email_verified)
              VALUES (?, ?, ?, ?, 0, 0, 0)
            ");
            $stmt->execute([$name, $email, $phone, $hash]);
            $newId = $conn->lastInsertId();

            $code    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 900);

            $conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?")
              ->execute([$code, $expires, $newId]);

            sendMail(
              $email,
              'تفعيل حسابك - منصة كورساتك',
              otpTemplate($name, $code, 'تفعيل الحساب')
            );

            $_SESSION['pending_verify_id']    = $newId;
            $_SESSION['pending_verify_email'] = $email;
            $_SESSION['verify_resend_at'] = time() + 60;

            header("Location: ?step=verify");
            exit();
          }
        }
      } catch (PDOException $e) {
        error_log("Register DB error: " . $e->getMessage());
        $error = "تعذّر إنشاء الحساب  حاول للاحقا;
      }
    }
  }

  if ($action === 'verify') {

    $step  = 'verify';
    $code  = trim($_POST['code'] ?? '');
    $uid   = (int)($_SESSION['pending_verify_id'] ?? 0);
    $vmail = $_SESSION['pending_verify_email'] ?? '';

    if (!$uid) {
      $step  = 'auth';
      $error = "انتهت الجلسة سجّل من جديد";
    } else {
      try {
        $stmt = $conn->prepare("SELECT verification_code, verification_expires
                                FROM users WHERE id=? AND email=? LIMIT 1");
        $stmt->execute([$uid, $vmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
          $error = "الحساب غير موجود";
        } elseif ($row['verification_expires'] < date('Y-m-d H:i:s')) {
          $error = "انتهت صلاحية الكود اطلب كود جديد";
        } elseif (!hash_equals((string)$row['verification_code'], $code)) {
          $error = "الكود غير صحيح";
        } else {
          $conn->prepare("UPDATE users SET email_verified=1,
                          status=1,
                          verification_code=NULL, verification_expires=NULL
                          WHERE id=?")->execute([$uid]);

          unset($_SESSION['pending_verify_id'], $_SESSION['pending_verify_email']);
          $success = "تم تفعيل حسابك بنجاح يمكنك تسجيل الدخول الآن";
          $step    = 'auth';
        }
      } catch (PDOException $e) {
        error_log("Verify error: " . $e->getMessage());
        $error = "حصل خطأ حاول لاحقاً";
      }
    }
  }

  if ($action === 'resend_verify') {

    $step  = 'verify';
    $uid   = (int)($_SESSION['pending_verify_id'] ?? 0);
    $vmail = $_SESSION['pending_verify_email'] ?? '';

    if ($uid && $vmail) {
      $remaining = max(0, (int)($_SESSION['verify_resend_at'] ?? 0) - time());

      if ($remaining > 0) {
        $error = "يمكنك طلب كود جديد بعد {$remaining} ثانية";
      } else {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$uid]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u) {
          $code    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expires = date('Y-m-d H:i:s', time() + 900);

          $conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?")
            ->execute([$code, $expires, $uid]);

          sendMail(
            $vmail,
            'كود تفعيل جديد - منصة كورساتك',
            otpTemplate($u['name'], $code, 'تفعيل الحساب')
          );

          $success = "بعتنالك كود جديد على الإيميل";
          $_SESSION['verify_resend_at'] = time() + 60;
        }
      }
    }
  }

  if ($action === 'forgot') {

    $step  = 'forgot';
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = "أدخل بريداً إلكترونياً صحيحاً";
    } else {
      try {
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u) {
          $code    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expires = date('Y-m-d H:i:s', time() + 900);

          $conn->prepare("UPDATE users SET reset_code=?, reset_expires=? WHERE id=?")
            ->execute([$code, $expires, $u['id']]);

          sendMail(
            $email,
            'استعادة كلمة المرور - منصة كورساتك',
            otpTemplate($u['name'], $code, 'استعادة كلمة المرور')
          );

          $_SESSION['reset_uid']   = $u['id'];
          $_SESSION['reset_email'] = $email;
          $_SESSION['reset_resend_at'] = time() + 60;
          unset($_SESSION['reset_verified'], $_SESSION['reset_verified_at'], $_SESSION['reset_verified_until']);

          header("Location: ?step=reset_verify");
          exit();
        }

        $success = "لو البريد مسجّل عندنا، هيوصلك كود خلال دقائق";
      } catch (PDOException $e) {
        error_log("Forgot error: " . $e->getMessage());
        $error = "حصل خطأ، حاول لاحقاً";
      }
    }
  }

  if ($action === 'reset_verify') {

    $step  = 'reset_verify';
    $code  = trim($_POST['code'] ?? '');
    $uid   = (int)($_SESSION['reset_uid'] ?? 0);
    $rmail = $_SESSION['reset_email'] ?? '';

    if (!$uid || !$rmail) {
      $step  = 'auth';
      $error = "انتهت الجلسة ابدأ من جديد";
    } else {
      try {
        $stmt = $conn->prepare("SELECT reset_code, reset_expires FROM users WHERE id=? AND email=? LIMIT 1");
        $stmt->execute([$uid, $rmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !$row['reset_code']) {
          $error = "اطلب كود جديد";
        } elseif (!$row['reset_expires'] || strtotime($row['reset_expires']) <= time()) {
          $error = "انتهت صلاحية الكود اطلب كود جديد";
        } elseif (strlen($code) !== 6 || !hash_equals((string)$row['reset_code'], $code)) {
          $error = "الكود غير صحيح";
        } else {
          $_SESSION['reset_verified'] = true;
          $_SESSION['reset_verified_at'] = time();
          $_SESSION['reset_verified_until'] = time() + 900;
          unset($_SESSION['reset_resend_at']);

          $conn->prepare("UPDATE users SET reset_code=NULL, reset_expires=NULL WHERE id=?")
            ->execute([$uid]);

          header("Location: ?step=reset");
          exit();
        }
      } catch (PDOException $e) {
        error_log("Reset verify error: " . $e->getMessage());
        $error = "حصل خطأ حاول لاحقاً";
      }
    }
  }

  if ($action === 'resend_reset') {

    $step  = 'reset_verify';
    $uid   = (int)($_SESSION['reset_uid'] ?? 0);
    $rmail = $_SESSION['reset_email'] ?? '';
    $remaining = max(0, (int)($_SESSION['reset_resend_at'] ?? 0) - time());

    if (!$uid || !$rmail) {
      $step  = 'auth';
      $error = "انتهت الجلسة ابدأ من جديد";
    } elseif ($remaining > 0) {
      $error = "يمكنك طلب كود جديد بعد {$remaining} ثانية";
    } else {
      try {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id=? AND email=? LIMIT 1");
        $stmt->execute([$uid, $rmail]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u) {
          $code    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expires = date('Y-m-d H:i:s', time() + 900);

          $conn->prepare("UPDATE users SET reset_code=?, reset_expires=? WHERE id=?")
            ->execute([$code, $expires, $uid]);

          sendMail(
            $rmail,
            'كود استعادة كلمة المرور الجديد - منصة كورساتك',
            otpTemplate($u['name'], $code, 'استعادة كلمة المرور')
          );

          $_SESSION['reset_resend_at'] = time() + 60;
          $success = "بعتنالك كود جديد على الإيميل";
        } else {
          $error = "الحساب غير موجود";
        }
      } catch (PDOException $e) {
        error_log("Resend reset error: " . $e->getMessage());
        $error = "حصل خطأ حاول لاحقاً";
      }
    }
  }

  if ($action === 'reset') {

    $step  = 'reset';
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';
    $uid   = (int)($_SESSION['reset_uid'] ?? 0);
    $verifiedUntil = (int)($_SESSION['reset_verified_until'] ?? 0);

    if (!$uid || empty($_SESSION['reset_verified']) || $verifiedUntil <= time()) {
      unset($_SESSION['reset_verified'], $_SESSION['reset_verified_at'], $_SESSION['reset_verified_until']);
      $step  = 'reset_verify';
      $error = "انتهت صلاحية التحقق اطلب كود جديد";
    } elseif (strlen($pass) < 8) {
      $error = "كلمة المرور لازم 8 أحرف على الأقل";
    } elseif ($pass !== $pass2) {
      $error = "كلمتا المرور غير متطابقتين";
    } else {
      try {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $conn->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $uid]);

        unset(
          $_SESSION['reset_uid'],
          $_SESSION['reset_email'],
          $_SESSION['reset_verified'],
          $_SESSION['reset_verified_at'],
          $_SESSION['reset_verified_until'],
          $_SESSION['reset_resend_at']
        );

        $success = "تم تغيير كلمة المرور بنجاح";
        $step    = 'auth';
      } catch (PDOException $e) {
        error_log("Reset error: " . $e->getMessage());
        $error = "حصل خطأ حاول لاحقاً";
      }
    }
  }
}

$verifyResendAt = (int)($_SESSION['verify_resend_at'] ?? 0);
$verifyResendRemaining = max(0, $verifyResendAt - time());

$verifyCodeRemaining = 0;
if ($step === 'verify') {
  $vuid = (int)($_SESSION['pending_verify_id'] ?? 0);
  if ($vuid) {
    $stmt = $conn->prepare("SELECT verification_expires FROM users WHERE id=? LIMIT 1");
    $stmt->execute([$vuid]);
    $vrow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($vrow && $vrow['verification_expires']) {
      $verifyCodeRemaining = max(0, strtotime($vrow['verification_expires']) - time());
    }
  }
}

$resetResendAt = (int)($_SESSION['reset_resend_at'] ?? 0);
$resetResendRemaining = max(0, $resetResendAt - time());

$resetCodeRemaining = 0;
if ($step === 'reset_verify') {
  $ruid = (int)($_SESSION['reset_uid'] ?? 0);
  if ($ruid) {
    $stmt = $conn->prepare("SELECT reset_expires FROM users WHERE id=? LIMIT 1");
    $stmt->execute([$ruid]);
    $rrow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rrow && $rrow['reset_expires']) {
      $resetCodeRemaining = max(0, strtotime($rrow['reset_expires']) - time());
    }
  }
}

$postedAction = $_POST['action'] ?? '';
$initialTab   = $postedAction === 'register' ? 'register' : 'login';

$old = [
  'login_email' => htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'),
  'reg_name'    => htmlspecialchars($_POST['name']  ?? '', ENT_QUOTES, 'UTF-8'),
  'reg_email'   => htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'),
  'reg_phone'   => htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'),
];

$verifyEmail = $_SESSION['pending_verify_email'] ?? '';
$resetEmail  = $_SESSION['reset_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>منصة كورساتك | تسجيل الدخول</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssUrl . "login.css" ?>">

  <style>
    .otp-timer {
      width: 100%;
      margin: 0 0 14px;
      padding: 10px 14px;
      border-radius: 12px;
      background: rgba(59, 130, 246, .08);
      color: #475569;
      text-align: center;
      font-size: 13px;
      line-height: 1.6;
      box-sizing: border-box;
    }

    .otp-timer strong {
      direction: ltr;
      display: inline-block;
      font-variant-numeric: tabular-nums;
      color: #2563eb;
      min-width: 44px;
    }

    button:disabled {
      opacity: .55;
      cursor: not-allowed;
    }
  </style>
</head>

<body>

  <main class="auth">

    <a href="../index.php" class="back-btn" aria-label="رجوع">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14" />
        <path d="m12 5 7 7-7 7" />
      </svg>
      <span>رجوع</span>
    </a>

    <section class="panel-form">
      <div class="card" id="authCard">

        <div class="mobile-brand">
          <div class="logo-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2 2 7l10 5 10-5-10-5Z" />
              <path d="m2 17 10 5 10-5" />
              <path d="m2 12 10 5 10-5" />
            </svg>
          </div>
          <span>منصة كورساتك</span>
        </div>

        <?php if ($step === 'auth'): ?>

          <div class="tabs" id="tabs" data-active="<?= $initialTab ?>">
            <span class="tab-ind"></span>
            <button class="tab <?= $initialTab === 'login' ? 'active' : '' ?>" data-form="login" type="button">تسجيل الدخول</button>
            <button class="tab <?= $initialTab === 'register' ? 'active' : '' ?>" data-form="register" type="button">حساب جديد</button>
          </div>

          <div class="forms" id="formsWrap">

            <form class="form <?= $initialTab === 'login' ? '' : 'hidden' ?>" id="form-login" method="post" novalidate>
              <input type="hidden" name="action" value="login">

              <div class="field">
                <div class="input-wrap">
                  <input type="email" name="email" id="login-email" placeholder=" " autocomplete="email" value="<?= $postedAction === 'login' ? $old['login_email'] : '' ?>" required>
                  <label for="login-email">البريد الإلكتروني</label>
                  <span class="field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="2" y="4" width="20" height="16" rx="3" />
                      <path d="m3 7 8.4 5.6a1 1 0 0 0 1.2 0L21 7" />
                    </svg>
                  </span>
                </div>
                <span class="err-msg">أدخل بريداً إلكترونياً صحيحاً</span>
              </div>

              <div class="field">
                <div class="input-wrap">
                  <input type="password" name="password" id="login-pass" placeholder=" " autocomplete="current-password" required>
                  <label for="login-pass">كلمة المرور</label>
                  <span class="field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="4" y="10" width="16" height="11" rx="3" />
                      <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                      <circle cx="12" cy="15.5" r="1.4" />
                    </svg>
                  </span>
                  <button type="button" class="eye" aria-label="إظهار كلمة المرور">
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4.2-.9" />
                      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                      <path d="m2 2 20 20" />
                    </svg>
                  </button>
                </div>
                <span class="err-msg">كلمة المرور لا تقل عن 6 أحرف على الأقل</span>
              </div>

              <div class="row-between">
                <label class="check">
                  <input type="checkbox" checked>
                  <span class="box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="m4 12.5 5.2 5.2L20 7" />
                    </svg>
                  </span>
                  تذكرني
                </label>
                <a href="?step=forgot" class="link">نسيت كلمة المرور ؟</a>
              </div>

              <button type="submit" class="btn-primary">
                <span class="btn-text">
                  تسجيل الدخول
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5" />
                    <path d="m12 19-7-7 7-7" />
                  </svg>
                </span>
                <span class="spinner"><i></i></span>
              </button>

              <div class="divider">أو المتابعة عبر</div>
              <a href="google.php" class="social">
                <svg viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.45a5.52 5.52 0 0 1-2.39 3.62v3h3.86c2.26-2.08 3.58-5.15 3.58-8.81Z" />
                  <path fill="#34A853" d="M12 24c3.24 0 5.96-1.08 7.95-2.91l-3.87-3a7.2 7.2 0 0 1-10.72-3.78H1.35v3.1A12 12 0 0 0 12 24Z" />
                  <path fill="#FBBC05" d="M5.36 14.3a7.2 7.2 0 0 1 0-4.6V6.6H1.35a12 12 0 0 0 0 10.8l4.01-3.1Z" />
                  <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0A12 12 0 0 0 1.35 6.6l4.01 3.1A7.2 7.2 0 0 1 12 4.77Z" />
                </svg>

                المتابعة بحساب جوجل
              </a>
            </form>

            <form class="form <?= $initialTab === 'register' ? '' : 'hidden' ?>" id="form-register" method="post" novalidate>
              <input type="hidden" name="action" value="register">

              <div class="field">
                <div class="input-wrap">
                  <input type="text" name="name" id="reg-name" placeholder=" " autocomplete="name" value="<?= $postedAction === 'register' ? $old['reg_name'] : '' ?>" required>
                  <label for="reg-name">الاسم الكامل</label>
                  <span class="field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="8" r="3.6" />
                      <path d="M4.5 20a7.5 7.5 0 0 1 15 0" />
                    </svg>
                  </span>
                </div>
                <span class="err-msg">من فضلك أدخل اسمك الكامل</span>
              </div>

              <div class="field">
                <div class="input-wrap">
                  <input type="email" name="email" id="reg-email" placeholder=" " autocomplete="email" value="<?= $postedAction === 'register' ? $old['reg_email'] : '' ?>" required>
                  <label for="reg-email">البريد الإلكتروني</label>
                  <span class="field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="2" y="4" width="20" height="16" rx="3" />
                      <path d="m3 7 8.4 5.6a1 1 0 0 0 1.2 0L21 7" />
                    </svg>
                  </span>
                </div>
                <span class="err-msg">أدخل بريداً إلكترونياً صحيحاً</span>
              </div>

              <div class="field phone">
                <div class="input-wrap">
                  <input type="tel" name="phone" id="reg-phone" placeholder=" " autocomplete="tel" inputmode="numeric" maxlength="11" value="<?= $postedAction === 'register' ? $old['reg_phone'] : '' ?>" required>
                  <label for="reg-phone">رقم الموبايل</label>
                  <span class="phone-prefix">
                    <span class="flag">🇪🇬</span>
                    <span class="code" dir="ltr">+20</span>
                  </span>
                </div>
                <span class="err-msg">أدخل رقم موبايل مصري صحيح</span>
              </div>

              <div class="field">
                <div class="input-wrap">
                  <input type="password" name="password" id="reg-pass" placeholder=" " autocomplete="new-password" minlength="8" required>
                  <label for="reg-pass">كلمة المرور</label>
                  <span class="field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="4" y="10" width="16" height="11" rx="3" />
                      <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                      <circle cx="12" cy="15.5" r="1.4" />
                    </svg>
                  </span>
                  <button type="button" class="eye" aria-label="إظهار كلمة المرور">
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4.2-.9" />
                      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                      <path d="m2 2 20 20" />
                    </svg>
                  </button>
                </div>
                <span class="err-msg">كلمة المرور لازم 8 أحرف على الأقل</span>
              </div>

              <div class="strength" id="strength" data-level="0">
                <div class="strength-bars"><i></i><i></i><i></i><i></i></div>
                <span class="strength-text">قوة كلمة المرور</span>
              </div>

              <div class="field">
                <div class="input-wrap">
                  <input type="password" name="confirm_password" id="reg-pass2" placeholder=" " autocomplete="new-password" minlength="8" required>
                  <label for="reg-pass2">تأكيد كلمة المرور</label>
                  <span class="field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="4" y="10" width="16" height="11" rx="3" />
                      <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                      <path d="m9.5 15.5 1.7 1.7 3.3-3.4" />
                    </svg>
                  </span>
                  <button type="button" class="eye" aria-label="إظهار تأكيد كلمة المرور">
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4.2-.9" />
                      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                      <path d="m2 2 20 20" />
                    </svg>
                  </button>
                </div>
                <span class="err-msg">كلمتا المرور غير متطابقتين</span>
              </div>

              <div class="row-between">
                <label class="check" id="termsCheck">
                  <input type="checkbox">
                  <span class="box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="m4 12.5 5.2 5.2L20 7" />
                    </svg>
                  </span>
                  اوافق علي الشروط
                </label>
                <button type="button" class="link" data-modal="terms">اقرا الشروط</button>
              </div>

              <button type="submit" class="btn-primary">
                <span class="btn-text">
                  إنشاء الحساب
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5" />
                    <path d="m12 19-7-7 7-7" />
                  </svg>
                </span>
                <span class="spinner"><i></i></span>
              </button>

              <div class="divider">أو التسجيل عبر</div>
              <a href="google.php" class="social">
                <svg viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.45a5.52 5.52 0 0 1-2.39 3.62v3h3.86c2.26-2.08 3.58-5.15 3.58-8.81Z" />
                  <path fill="#34A853" d="M12 24c3.24 0 5.96-1.08 7.95-2.91l-3.87-3a7.2 7.2 0 0 1-10.72-3.78H1.35v3.1A12 12 0 0 0 12 24Z" />
                  <path fill="#FBBC05" d="M5.36 14.3a7.2 7.2 0 0 1 0-4.6V6.6H1.35a12 12 0 0 0 0 10.8l4.01-3.1Z" />
                  <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0A12 12 0 0 0 1.35 6.6l4.01 3.1A7.2 7.2 0 0 1 12 4.77Z" />
                </svg>

                المتابعة بحساب جوجل
              </a>
            </form>
          </div>

        <?php elseif ($step === 'verify'): ?>

          <div class="step-icon">
            <div style="background: linear-gradient(140deg,#3b82f6,#1d4ed8)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="3" />
                <path d="m3 7 8.4 5.6a1 1 0 0 0 1.2 0L21 7" />
              </svg>
            </div>
          </div>

          <h2 class="step-title">تفعيل الحساب</h2>
          <p class="step-desc">
            بعتنالك كود مكوّن من 6 أرقام على<br>
            <strong><?= htmlspecialchars($verifyEmail) ?></strong>
          </p>

          <div class="otp-timer" id="verifyCodeTimer" data-seconds="<?= (int)$verifyCodeRemaining ?>">
            صلاحية الكود: <strong id="verifyCodeTimerText">--:--</strong>
          </div>

          <form method="post" novalidate id="verifyForm">
            <input type="hidden" name="action" value="verify">
            <div class="otp-box" id="otpVerify">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>
            <input type="hidden" name="code" id="verifyCode" value="">

            <button type="submit" class="btn-primary">
              <span class="btn-text">
                تأكيد الكود
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M19 12H5" />
                  <path d="m12 19-7-7 7-7" />
                </svg>
              </span>
              <span class="spinner"><i></i></span>
            </button>
          </form>

          <form method="post" style="margin-top:10px" id="resendVerifyForm">
            <input type="hidden" name="action" value="resend_verify">
            <button type="submit" class="btn-secondary" id="resendVerifyBtn" <?= $verifyResendRemaining > 0 ? 'disabled' : '' ?>>
              <span id="resendVerifyText">
                <?= $verifyResendRemaining > 0 ? 'إعادة إرسال الكود بعد ' . $verifyResendRemaining . ' ثانية' : 'إعادة إرسال الكود' ?>
              </span>
            </button>
          </form>

          <a href="?" class="step-back">↩ رجوع لتسجيل الدخول</a>

        <?php elseif ($step === 'forgot'): ?>

          <div class="step-icon">
            <div style="background: linear-gradient(140deg,#f59e0b,#d97706)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="10" width="16" height="11" rx="3" />
                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                <circle cx="12" cy="15.5" r="1.4" />
              </svg>
            </div>
          </div>

          <h2 class="step-title">نسيت كلمة المرور؟</h2>
          <p class="step-desc">أدخل بريدك الإلكتروني وهنبعتلك كود لإعادة التعيين</p>

          <form method="post" novalidate id="forgotForm">
            <input type="hidden" name="action" value="forgot">

            <div class="field">
              <div class="input-wrap">
                <input type="email" name="email" id="forgot-email" placeholder=" " autocomplete="email" required>
                <label for="forgot-email">البريد الإلكتروني</label>
                <span class="field-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="3" />
                    <path d="m3 7 8.4 5.6a1 1 0 0 0 1.2 0L21 7" />
                  </svg>
                </span>
              </div>
              <span class="err-msg">أدخل بريداً إلكترونياً صحيحاً</span>
            </div>

            <button type="submit" class="btn-primary">
              <span class="btn-text">
                إرسال الكود
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M19 12H5" />
                  <path d="m12 19-7-7 7-7" />
                </svg>
              </span>
              <span class="spinner"><i></i></span>
            </button>
          </form>

          <a href="?" class="step-back">↩ رجوع لتسجيل الدخول</a>

        <?php elseif ($step === 'reset_verify'): ?>

          <div class="step-icon">
            <div style="background: linear-gradient(140deg,#10b981,#059669)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="10" width="16" height="11" rx="3" />
                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                <circle cx="12" cy="15.5" r="1.4" />
              </svg>
            </div>
          </div>

          <h2 class="step-title">تأكيد البريد الإلكتروني</h2>
          <p class="step-desc">أدخل كود التحقق المكوّن من 6 أرقام على<br><strong><?= htmlspecialchars($resetEmail) ?></strong></p>

          <div class="otp-timer" id="resetCodeTimer" data-seconds="<?= (int)$resetCodeRemaining ?>">
            صلاحية الكود: <strong id="resetCodeTimerText">--:--</strong>
          </div>

          <form method="post" novalidate id="resetVerifyForm">
            <input type="hidden" name="action" value="reset_verify">
            <div class="otp-box" id="otpResetVerify">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>
            <input type="hidden" name="code" id="resetVerifyCode" value="">
            <button type="submit" class="btn-primary"><span class="btn-text">تأكيد الكود</span><span class="spinner"><i></i></span></button>
          </form>

          <form method="post" style="margin-top:10px" id="resendResetForm">
            <input type="hidden" name="action" value="resend_reset">
            <button type="submit" class="btn-secondary" id="resendResetBtn" <?= $resetResendRemaining > 0 ? 'disabled' : '' ?>>
              <span id="resendResetText"><?= $resetResendRemaining > 0 ? 'إعادة إرسال الكود بعد ' . $resetResendRemaining . ' ثانية' : 'إعادة إرسال الكود' ?></span>
            </button>
          </form>

          <a href="?" class="step-back">↩ رجوع لتسجيل الدخول</a>

        <?php elseif ($step === 'reset'): ?>

          <div class="step-icon">
            <div style="background: linear-gradient(140deg,#10b981,#059669)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                <path d="m9 12 2 2 4-4" />
              </svg>
            </div>
          </div>

          <h2 class="step-title">تغيير كلمة المرور</h2>
          <p class="step-desc">تم التحقق من بريدك الإلكتروني، أدخل كلمة المرور الجديدة</p>

          <div class="otp-timer" id="resetVerifiedTimer" data-seconds="<?= max(0, (int)($_SESSION['reset_verified_until'] ?? 0) - time()) ?>">
            الوقت المتاح لتغيير كلمة المرور: <strong id="resetVerifiedTimerText">--:--</strong>
          </div>

          <form method="post" novalidate id="resetForm">
            <input type="hidden" name="action" value="reset">
            <div class="field">
              <div class="input-wrap">
                <input type="password" name="password" id="reset-pass" placeholder=" " autocomplete="new-password" minlength="8" required>
                <label for="reset-pass">كلمة المرور الجديدة</label>
                <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="10" width="16" height="11" rx="3" />
                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                    <circle cx="12" cy="15.5" r="1.4" />
                  </svg></span>
              </div>
              <span class="err-msg">كلمة المرور لازم 8 أحرف على الأقل</span>
            </div>
            <div class="field">
              <div class="input-wrap">
                <input type="password" name="confirm_password" id="reset-pass2" placeholder=" " autocomplete="new-password" minlength="8" required>
                <label for="reset-pass2">تأكيد كلمة المرور</label>
                <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="10" width="16" height="11" rx="3" />
                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                    <path d="m9.5 15.5 1.7 1.7 3.3-3.4" />
                  </svg></span>
              </div>
              <span class="err-msg">كلمتا المرور غير متطابقتين</span>
            </div>
            <button type="submit" class="btn-primary"><span class="btn-text">تغيير كلمة المرور</span><span class="spinner"><i></i></span></button>
          </form>

          <a href="?" class="step-back">↩ رجوع لتسجيل الدخول</a>

        <?php endif; ?>

      </div>
    </section>

    <section class="panel-visual">
      <div class="grid-overlay"></div>
      <span class="ring r1"></span>
      <span class="ring r2"></span>
      <div class="blob b1"></div>
      <div class="blob b2"></div>
      <div class="blob b3"></div>

      <div class="visual-content">
        <div class="v-logo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2 2 7l10 5 10-5-10-5Z" />
            <path d="m2 17 10 5 10-5" />
            <path d="m2 12 10 5 10-5" />
          </svg>
        </div>
        <div class="v-brand"><i></i> منصة كورساتك</div>
        <h2>كورساتك <span>في مكان واحد</span></h2>
        <p>تعلّم ، تابع تقدّمك ، في منصة كورساتك</p>

        <div class="chips">
          <span class="chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z" />
            </svg>
            سريع
          </span>
          <span class="chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
              <path d="m9 12 2 2 4-4" />
            </svg>
            آمن
          </span>
          <span class="chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="6" />
              <path d="m8.2 13.9-1.2 7.1 5-3 5 3-1.2-7.1" />
            </svg>
            موثوق
          </span>
        </div>

        <div class="trust">
          <div class="trust-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="10" width="16" height="11" rx="3" />
              <path d="M8 10V7a4 4 0 0 1 8 0v3" />
            </svg>
            تشفير آمن
          </div>
          <div class="trust-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            </svg>
            حماية البيانات
          </div>
          <div class="trust-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <path d="M12 6v6l4 2" />
            </svg>
            دعم متواصل
          </div>
        </div>
      </div>
    </section>

  </main>

  <div class="toast-wrap" id="toastWrap"></div>

  <div class="modal" id="modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true">
      <div class="modal-head">
        <h3 id="modalTitle">العنوان</h3>
        <button class="modal-close" type="button" aria-label="إغلاق" id="modalClose">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
          </svg>
        </button>
      </div>
      <div class="modal-body" id="modalBody"></div>
    </div>
  </div>

  <script>
    window.__AUTH__ = {
      error: <?= json_encode($error,         JSON_UNESCAPED_UNICODE) ?>,
      success: <?= json_encode($success,       JSON_UNESCAPED_UNICODE) ?>,
      accountStatus: <?= json_encode($accountStatus, JSON_UNESCAPED_UNICODE) ?>,
      step: <?= json_encode($step) ?>
    };

    (function() {
      var $ = function(s, c) {
        return (c || document).querySelector(s);
      };
      var $$ = function(s, c) {
        return Array.prototype.slice.call((c || document).querySelectorAll(s));
      };

      var tabs = $('#tabs');
      var tabBtns = $$('.tab');
      var formLogin = $('#form-login');
      var formReg = $('#form-register');
      var toastWrap = $('#toastWrap');

      if (tabs && formLogin && formReg) {
        tabBtns.forEach(function(btn) {
          btn.addEventListener('click', function() {
            var target = btn.dataset.form;
            if (tabs.dataset.active === target) return;
            tabs.dataset.active = target;
            tabBtns.forEach(function(b) {
              b.classList.toggle('active', b.dataset.form === target);
            });
            formLogin.classList.toggle('hidden', target !== 'login');
            formReg.classList.toggle('hidden', target !== 'register');
          });
        });
      }

      $$('.eye').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var wrap = btn.closest('.input-wrap');
          var input = wrap ? wrap.querySelector('input') : null;
          if (!input) return;
          var show = input.type === 'password';
          input.type = show ? 'text' : 'password';
          btn.classList.toggle('on', show);
        });
      });

      function setupOTP(containerId, hiddenId) {
        var wrap = document.getElementById(containerId);
        var hidden = document.getElementById(hiddenId);
        if (!wrap || !hidden) return;

        var inputs = $$('input', wrap);

        function sync() {
          hidden.value = inputs.map(function(i) {
            return i.value || '';
          }).join('');
        }

        inputs.forEach(function(inp, idx) {
          inp.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 1);
            if (this.value && idx < inputs.length - 1) inputs[idx + 1].focus();
            sync();
          });

          inp.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
              inputs[idx - 1].focus();
            }
          });

          inp.addEventListener('paste', function(e) {
            e.preventDefault();
            var txt = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            for (var i = 0; i < inputs.length; i++) inputs[i].value = txt[i] || '';
            sync();
            if (txt.length) inputs[Math.min(txt.length, inputs.length) - 1].focus();
          });
        });

        if (inputs[0]) inputs[0].focus();
      }

      setupOTP('otpVerify', 'verifyCode');
      setupOTP('otpReset', 'resetCode');
      setupOTP('otpResetVerify', 'resetVerifyCode');

      function isEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test((v || '').trim());
      }

      function isEgyptPhone(v) {
        return /^01[0125][0-9]{8}$/.test((v || '').replace(/\D/g, ''));
      }

      function setError(field, on) {
        if (field) field.classList.toggle('error', on);
      }

      $$('.field').forEach(function(field) {
        var input = field.querySelector('input');
        if (!input) return;
        input.addEventListener('input', function() {
          field.classList.remove('error');
        });
      });

      var regPhone = $('#reg-phone');
      if (regPhone) regPhone.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
      });

      var regPass = $('#reg-pass');
      var regPass2 = $('#reg-pass2');
      var pass2Field = regPass2 ? regPass2.closest('.field') : null;
      var strengthEl = $('#strength');
      var strengthTxt = strengthEl ? strengthEl.querySelector('.strength-text') : null;
      var LABELS = ['قوة كلمة المرور', 'ضعيفة', 'متوسطة', 'جيدة', 'قوية'];

      function scorePassword(p) {
        if (!p) return 0;
        var s = 0;
        if (p.length >= 8) s++;
        if (p.length >= 12) s++;
        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
        if (/\d/.test(p)) s++;
        if (/[^A-Za-z0-9]/.test(p)) s++;
        return Math.min(4, Math.max(1, s));
      }

      if (regPass && strengthEl) {
        regPass.addEventListener('input', function() {
          var s = scorePassword(regPass.value);
          if (regPass.value) {
            strengthEl.classList.add('show');
            strengthEl.dataset.level = s;
            strengthTxt.textContent = LABELS[s];
          } else {
            strengthEl.classList.remove('show');
            strengthEl.dataset.level = 0;
            strengthTxt.textContent = LABELS[0];
          }
          if (regPass2 && regPass2.value) setError(pass2Field, regPass2.value !== regPass.value);
        });
      }

      if (regPass2 && pass2Field) {
        regPass2.addEventListener('input', function() {
          setError(pass2Field, regPass2.value && regPass2.value !== regPass.value);
        });
      }

      var ICONS = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m8 12.5 3 3 5-6"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5"/><circle cx="12" cy="16.5" r=".6" fill="currentColor"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-5"/><circle cx="12" cy="7.5" r=".6" fill="currentColor"/></svg>',
        warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><circle cx="12" cy="17" r=".6" fill="currentColor"/></svg>'
      };

      function showToast(msg, type, ms) {
        type = type || 'info';
        ms = ms || 3600;

        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = ICONS[type] + '<span>' + msg + '</span>';
        toastWrap.appendChild(t);

        requestAnimationFrame(function() {
          t.classList.add('show');
        });

        setTimeout(function() {
          t.classList.add('leaving');
          t.addEventListener('transitionend', function() {
            t.remove();
          }, {
            once: true
          });
        }, ms);
      }

      (function() {
        var A = window.__AUTH__ || {};
        if (A.error) showToast(A.error, 'error', 4500);
        if (A.accountStatus === 'inactive') showToast('حسابك غير مُفعَّل بعد، تواصل مع الدعم', 'warn', 5000);
        else if (A.accountStatus === 'banned') showToast('تم حظر حسابك، تواصل مع الدعم', 'error', 5000);
        if (A.success) showToast(A.success, 'success', 5000);
      })();

      (function() {
        var btn = $('#resendVerifyBtn');
        var text = $('#resendVerifyText');
        var remaining = <?= (int)$verifyResendRemaining ?>;

        if (!btn || !text || remaining <= 0) return;

        var timer = setInterval(function() {
          remaining--;

          if (remaining <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            text.textContent = 'إعادة إرسال الكود';
            return;
          }

          text.textContent = 'إعادة إرسال الكود بعد ' + remaining + ' ثانية';
        }, 1000);
      })();

      (function() {
        var btn = $('#resendResetBtn');
        var text = $('#resendResetText');
        var remaining = <?= (int)$resetResendRemaining ?>;
        if (!btn || !text || remaining <= 0) return;
        var timer = setInterval(function() {
          remaining--;
          if (remaining <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            text.textContent = 'إعادة إرسال الكود';
            return;
          }
          text.textContent = 'إعادة إرسال الكود بعد ' + remaining + ' ثانية';
        }, 1000);
      })();

      function startCountdown(elId, textId, onEnd) {
        var el = document.getElementById(elId);
        var text = document.getElementById(textId);
        if (!el || !text) return;
        var remaining = parseInt(el.getAttribute('data-seconds') || '0', 10);

        function render() {
          var min = Math.floor(Math.max(0, remaining) / 60);
          var sec = Math.max(0, remaining) % 60;
          text.textContent = String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
        }
        render();
        if (remaining <= 0) {
          if (onEnd) onEnd();
          return;
        }
        var timer = setInterval(function() {
          remaining--;
          render();
          if (remaining <= 0) {
            clearInterval(timer);
            if (onEnd) onEnd();
          }
        }, 1000);
      }

      startCountdown('verifyCodeTimer', 'verifyCodeTimerText');
      startCountdown('resetCodeTimer', 'resetCodeTimerText');
      startCountdown('resetVerifiedTimer', 'resetVerifiedTimerText');

      var modal = $('#modal');
      var modalTitle = $('#modalTitle');
      var modalBody = $('#modalBody');
      var modalClose = $('#modalClose');

      var MODAL_CONTENT = {
        terms: {
          title: 'الشروط والأحكام',
          body: '<p>باستخدامك لمنصة كورساتك، فإنك توافق على الالتزام بالشروط التالية:</p>' +
            '<h4>1. الحساب الشخصي</h4><ul><li>أنت مسؤول عن سرية بيانات الدخول</li><li>يُمنع مشاركة الحساب مع الغير</li></ul>' +
            '<h4>2. المحتوى التعليمي</h4><ul><li>جميع الكورسات محمية بحقوق الملكية</li><li>يُمنع إعادة نشر المحتوى بأي شكل</li></ul>' +
            '<h4>3. الدفع والاسترداد</h4><ul><li>يمكن طلب الاسترداد خلال 7 أيام من الشراء</li></ul>'
        }
      };

      function openModal(key) {
        var d = MODAL_CONTENT[key];
        if (!d) return;
        modalTitle.textContent = d.title;
        modalBody.innerHTML = d.body;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }

      function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }

      $$('[data-modal]').forEach(function(el) {
        el.addEventListener('click', function(e) {
          e.preventDefault();
          openModal(el.dataset.modal);
        });
      });

      if (modalClose) modalClose.addEventListener('click', closeModal);
      if (modal) modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
      });
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
      });

      $$('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
          var valid = true;
          var firstErr = null;

          function mark(field, cond) {
            if (!field) return;
            if (!cond) {
              setError(field, true);
              if (!firstErr) firstErr = field.querySelector('input');
              valid = false;
            } else setError(field, false);
          }

          if (form.id === 'form-login') {
            mark($('#login-email').closest('.field'), isEmail($('#login-email').value));
            mark($('#login-pass').closest('.field'), $('#login-pass').value.length >= 6);
          } else if (form.id === 'form-register') {
            mark($('#reg-name').closest('.field'), $('#reg-name').value.trim().length >= 3);
            mark($('#reg-email').closest('.field'), isEmail($('#reg-email').value));
            mark($('#reg-phone').closest('.field'), isEgyptPhone($('#reg-phone').value));
            mark(regPass.closest('.field'), regPass.value.length >= 8);
            mark(pass2Field, regPass.value === regPass2.value && regPass2.value.length >= 8);

            var terms = $('#termsCheck');
            if (!terms.querySelector('input').checked) {
              terms.classList.add('error');
              valid = false;
              if (!firstErr) firstErr = terms.querySelector('input');
            } else terms.classList.remove('error');
          } else if (form.id === 'verifyForm') {
            if ($('#verifyCode').value.length !== 6) {
              showToast('أدخل الكود المكوّن من 6 أرقام', 'error');
              valid = false;
            }
          } else if (form.id === 'resetVerifyForm') {
            if ($('#resetVerifyCode').value.length !== 6) {
              showToast('أدخل الكود المكوّن من 6 أرقام', 'error');
              valid = false;
            }
          } else if (form.id === 'resetForm') {
            mark($('#reset-pass').closest('.field'), $('#reset-pass').value.length >= 8);
            mark($('#reset-pass2').closest('.field'), $('#reset-pass').value === $('#reset-pass2').value && $('#reset-pass2').value.length >= 8);
          } else if (form.id === 'forgotForm') {
            mark($('#forgot-email').closest('.field'), isEmail($('#forgot-email').value));
          }

          if (!valid) {
            e.preventDefault();
            if (firstErr) firstErr.focus({
              preventScroll: true
            });
            return;
          }

          var btn = form.querySelector('.btn-primary');
          if (btn) btn.classList.add('loading');
        });
      });

      var termsCheck = $('#termsCheck');
      if (termsCheck) {
        termsCheck.querySelector('input').addEventListener('change', function() {
          this.closest('.check').classList.remove('error');
        });
      }

    })();
  </script>

</body>

</html>