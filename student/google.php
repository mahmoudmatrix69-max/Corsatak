<?php

require_once __DIR__ . '/paths.php';
require_once $config;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$client_id = '#';
$client_secret = '#';
$redirect_uri = '#';


$loginUrl = 'auth.php';

function googleError($message)
{
    global $loginUrl;

    $_SESSION['google_error'] = $message;

    header('Location: ' . $loginUrl);
    exit();
}

if (!isset($_GET['code'])) {

    if (isset($_GET['error'])) {

        if ($_GET['error'] === 'access_denied') {
            googleError('تم إلغاء تسجيل الدخول باستخدام Google');
        }

        googleError('تعذر إكمال تسجيل الدخول باستخدام Google');
    }

    $googleUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);

    header('Location: ' . $googleUrl);
    exit();
}

$code = trim($_GET['code']);

if ($code === '') {
    googleError('تعذر الحصول على رمز تسجيل الدخول من Google');
}

$ch = curl_init('https://oauth2.googleapis.com/token');

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$response = curl_exec($ch);

if ($response === false) {
    error_log('Google Token CURL Error: ' . curl_error($ch));
    curl_close($ch);
    googleError('تعذر الاتصال بخدمة Google، حاول مرة أخرى');
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$token = json_decode($response, true);

if (!is_array($token)) {
    error_log('Google Token Invalid Response: ' . $response);
    googleError('تعذر قراءة بيانات تسجيل الدخول من Google');
}

if ($httpCode !== 200 || empty($token['access_token'])) {
    error_log('Google Token Error: ' . $response);
    googleError('تعذر إكمال تسجيل الدخول باستخدام Google');
}

$accessToken = $token['access_token'];

$ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken
    ]
]);

$response = curl_exec($ch);

if ($response === false) {
    error_log('Google UserInfo CURL Error: ' . curl_error($ch));
    curl_close($ch);
    googleError('تعذر الحصول على بيانات حساب Google');
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$google = json_decode($response, true);

if ($httpCode !== 200 || !is_array($google)) {
    error_log('Google UserInfo Error: ' . $response);
    googleError('تعذر الحصول على بيانات حساب Google');
}

$email = strtolower(trim($google['email'] ?? ''));
$name = trim($google['name'] ?? '');
$googleId = trim($google['sub'] ?? '');
$avatar = trim($google['picture'] ?? '');

if ($email === '') {
    googleError('تعذر الحصول على البريد الإلكتروني من حساب Google');
}

if ($googleId === '') {
    googleError('تعذر التحقق من هوية حساب Google');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    googleError('البريد الإلكتروني الذي تم الحصول عليه من Google غير صحيح');
}

if (
    isset($google['email_verified']) &&
    $google['email_verified'] !== true
) {
    googleError('البريد الإلكتروني في حساب Google غير موثق');
}

if ($name === '') {
    $name = 'مستخدم Google';
}

$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE email = ?
    LIMIT 1
");

$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

    if ((int)$user['status'] === 2) {
        googleError('هذا الحساب محظور حاليًا');
    }

    $provider = $user['auth_provider'] ?? 'email';

    if ($provider === 'email') {
        googleError('هذا البريد الإلكتروني مسجل بالفعل باستخدام البريد الإلكتروني وكلمة المرور');
    }

    if ($provider !== 'google') {
        googleError('لا يمكن تسجيل الدخول بهذا الحساب باستخدام Google');
    }

    if (
        !empty($user['google_id']) &&
        $user['google_id'] !== $googleId
    ) {
        googleError('حساب Google المستخدم لا يطابق حساب Google المرتبط بهذا الحساب');
    }

    try {

        $stmt = $conn->prepare("
            UPDATE users
            SET
                google_id = ?,
                avatar = ?,
                email_verified = 1,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $googleId,
            $avatar !== '' ? $avatar : null,
            $user['id']
        ]);
    } catch (PDOException $e) {

        error_log('Google Account Update Error: ' . $e->getMessage());

        googleError('تعذر تحديث بيانات حساب Google ، حاول مرة أخرى');
    }

    session_regenerate_id(true);

    $_SESSION['id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = (int)$user['role'];

    if ((int)$user['role'] === 1) {
        header('Location: ' . $adminUrl);
        exit();
    }

    header('Location: ' . $indexUrl);
    exit();
}

$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE google_id = ?
    LIMIT 1
");

$stmt->execute([$googleId]);

$googleUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($googleUser) {

    if ((int)$googleUser['status'] === 2) {
        googleError('هذا الحساب محظور حاليًا');
    }

    if (($googleUser['auth_provider'] ?? '') !== 'google') {
        googleError('لا يمكن تسجيل الدخول بهذا الحساب باستخدام Google');
    }

    if (
        !empty($googleUser['email']) &&
        strtolower(trim($googleUser['email'])) !== $email
    ) {
        googleError('البريد الإلكتروني لحساب Google لا يطابق الحساب المرتبط به');
    }

    session_regenerate_id(true);

    $_SESSION['id'] = $googleUser['id'];
    $_SESSION['name'] = $googleUser['name'];
    $_SESSION['email'] = $googleUser['email'];
    $_SESSION['role'] = (int)$googleUser['role'];

    if ((int)$googleUser['role'] === 1) {
        header('Location: ' . $adminUrl);
        exit();
    }

    header('Location: ' . $indexUrl);
    exit();
}

$password = password_hash(
    bin2hex(random_bytes(32)),
    PASSWORD_DEFAULT
);

try {

    $stmt = $conn->prepare("
        INSERT INTO users
        (
            name,
            email,
            auth_provider,
            password,
            phone,
            role,
            status,
            email_verified,
            google_id,
            avatar
        )
        VALUES
        (
            ?,
            ?,
            'google',
            ?,
            NULL,
            0,
            1,
            1,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $name,
        $email,
        $password,
        $googleId,
        $avatar !== '' ? $avatar : null
    ]);
} catch (PDOException $e) {

    error_log('Google Register Error: ' . $e->getMessage());

    if (
        isset($e->errorInfo[1]) &&
        (int)$e->errorInfo[1] === 1062
    ) {
        googleError('هذا الحساب موجود بالفعل ، يرجى تسجيل الدخول باستخدام طريقة تسجيل الدخول المرتبطة بالحساب');
    }

    googleError('تعذر إنشاء حساب Google، حاول مرة أخرى');
}

$id = $conn->lastInsertId();

if (!$id) {
    googleError('تعذر إنشاء الحساب، حاول مرة أخرى');
}

session_regenerate_id(true);

$_SESSION['id'] = $id;
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = 0;

header('Location: ' . $indexUrl);
exit();
