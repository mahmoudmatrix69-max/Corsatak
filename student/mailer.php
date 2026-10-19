<?php

function sendMail(string $to, string $subject, string $htmlBody): bool
{
    $host     = '#';
    $port     = 465;
    $username = '#';
    $password = '#';
    $fromName = 'Coursatk Support';

    $timeout = 20;

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

    if (!$fp) {
        error_log("SMTP connect failed: $errstr ($errno)");
        return false;
    }

    stream_set_timeout($fp, $timeout);

    $read = function () use ($fp) {

        $data = '';

        while ($line = fgets($fp, 515)) {

            $data .= $line;

            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        return $data;
    };

    $cmd = function ($command) use ($fp, $read) {

        fwrite($fp, $command . "\r\n");

        return $read();
    };

    // قراءة الاتصال
    $read();

    // تعريف السيرفر
    $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';

    $cmd('EHLO ' . $serverName);

    // تسجيل الدخول
    $cmd('AUTH LOGIN');

    $cmd(base64_encode($username));

    $authResp = $cmd(base64_encode($password));

    if (strpos($authResp, '235') === false) {

        error_log("SMTP auth failed: $authResp");

        fclose($fp);

        return false;
    }

    // المرسل
    $mailFromResp = $cmd(
        'MAIL FROM: <' . $username . '>'
    );

    if (strpos($mailFromResp, '250') === false) {

        fclose($fp);

        return false;
    }

    // المستلم
    $rcptResp = $cmd(
        'RCPT TO: <' . $to . '>'
    );

    if (
        strpos($rcptResp, '250') === false &&
        strpos($rcptResp, '251') === false
    ) {

        fclose($fp);

        return false;
    }

    // DATA
    $dataResp = $cmd('DATA');

    if (strpos($dataResp, '354') === false) {

        fclose($fp);

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    */

    $encodedFromName = base64_encode($fromName);

    $encodedSubject = base64_encode($subject);

    $headers  = "From: =?UTF-8?B?{$encodedFromName}?= <{$username}>\r\n";
    $headers .= "To: <{$to}>\r\n";
    $headers .= "Subject: =?UTF-8?B?{$encodedSubject}?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $headers .= "Date: " . date('r') . "\r\n";

    $messageId = md5(
        uniqid((string) time(), true)
    );

    $headers .= "Message-ID: <{$messageId}@gmail.com>\r\n";

    /*
    |--------------------------------------------------------------------------
    | Body
    |--------------------------------------------------------------------------
    */

    $body = chunk_split(
        base64_encode($htmlBody)
    );

    $message = $headers . "\r\n" . $body;

    fwrite(
        $fp,
        $message . "\r\n.\r\n"
    );

    $sendResp = $read();

    $cmd('QUIT');

    fclose($fp);

    return strpos($sendResp, '250') !== false;
}


/*
|--------------------------------------------------------------------------
| قالب OTP
|--------------------------------------------------------------------------
*/

function otpTemplate(
    string $name,
    string $code,
    string $title
): string {

    $year = date('Y');

    $name = htmlspecialchars(
        $name,
        ENT_QUOTES,
        'UTF-8'
    );

    $code = htmlspecialchars(
        $code,
        ENT_QUOTES,
        'UTF-8'
    );

    $title = htmlspecialchars(
        $title,
        ENT_QUOTES,
        'UTF-8'
    );

    return "
<!DOCTYPE html>

<html lang='ar' dir='rtl'>

<head>

    <meta charset='UTF-8'>

    <meta
        name='viewport'
        content='width=device-width, initial-scale=1.0'
    >

    <meta
        name='color-scheme'
        content='light'
    >

    <title>{$title}</title>

</head>


<body
    style='
        margin:0;
        padding:0;
        width:100%;
        background:#ffffff;
        font-family:Arial,Tahoma,sans-serif;
        color:#111827;
        -webkit-text-size-adjust:100%;
    '
>


<!-- الصفحة -->

<table
    width='100%'
    cellpadding='0'
    cellspacing='0'
    border='0'
    style='
        width:100%;
        background:#ffffff;
    '
>

<tr>

<td align='center'>


<!-- شريط الهوية -->

<table
    width='100%'
    cellpadding='0'
    cellspacing='0'
    border='0'
    style='
        width:100%;
        background:#2563eb;
    '
>

<tr>

<td
    align='center'
    style='
        padding:26px 20px;
    '
>

    <table
        cellpadding='0'
        cellspacing='0'
        border='0'
    >

    <tr>

        <td
            align='center'
            valign='middle'
            style='
                width:42px;
                height:42px;
                background:#ffffff;
                border-radius:12px;
                font-size:21px;
                line-height:42px;
            '
        >
            🎓
        </td>

    </tr>

    </table>


    <div
        style='
            margin-top:10px;
            color:#ffffff;
            font-size:21px;
            line-height:28px;
            font-weight:bold;
        '
    >
        كورساتك
    </div>


</td>

</tr>

</table>


<!-- المحتوى -->

<table
    width='100%'
    cellpadding='0'
    cellspacing='0'
    border='0'
>

<tr>

<td
    align='center'
    style='
        padding:55px 20px 20px;
    '
>


    <!-- العنوان -->

    <div
        style='
            max-width:460px;
            margin:auto;
            text-align:center;
        '
    >

        <div
            style='
                color:#111827;
                font-size:26px;
                line-height:36px;
                font-weight:bold;
            '
        >
            {$title}
        </div>


        <div
            style='
                margin-top:15px;
                color:#475569;
                font-size:15px;
                line-height:26px;
            '
        >
            مرحباً {$name} 👋
        </div>


        <div
            style='
                margin-top:3px;
                color:#64748b;
                font-size:14px;
                line-height:25px;
            '
        >
            استخدم رمز التحقق لإكمال العملية
        </div>

    </div>


    <!-- OTP -->

    <table
        width='100%'
        cellpadding='0'
        cellspacing='0'
        border='0'
        style='
            max-width:460px;
            margin-top:38px;
        '
    >

    <tr>

    <td align='center'>


        <div
            style='
                color:#94a3b8;
                font-size:12px;
                line-height:18px;
                margin-bottom:12px;
            '
        >
            رمز التحقق
        </div>


        <!-- الرقم -->

        <div
            dir='ltr'
            style='
                color:#2563eb;
                font-size:40px;
                line-height:50px;
                font-weight:bold;
                letter-spacing:11px;
                padding-left:11px;
            '
        >
            {$code}
        </div>


        <!-- خط -->

        <div
            style='
                width:70px;
                height:3px;
                background:#2563eb;
                border-radius:10px;
                margin:18px auto 0;
            '
        ></div>


    </td>

    </tr>

    </table>


    <!-- الصلاحية -->

    <div
        style='
            max-width:460px;
            margin:30px auto 0;
            text-align:center;
        '
    >

        <div
            style='
                color:#64748b;
                font-size:13px;
                line-height:22px;
            '
        >
            الرمز صالح لمدة
            <strong style='color:#111827;'>
                15 دقيقة
            </strong>
        </div>


        <div
            style='
                margin-top:8px;
                color:#94a3b8;
                font-size:12px;
                line-height:20px;
            '
        >
            إذا لم تطلب هذا الرمز، يمكنك تجاهل الرسالة
        </div>

    </div>


</td>

</tr>

</table>


<!-- مساحة -->

<table
    width='100%'
    cellpadding='0'
    cellspacing='0'
    border='0'
>

<tr>

<td
    align='center'
    style='
        padding:45px 20px 30px;
    '
>

    <div
        style='
            width:100%;
            max-width:460px;
            height:1px;
            background:#eef2f7;
        '
    ></div>


    <div
        style='
            margin-top:20px;
            color:#64748b;
            font-size:12px;
            line-height:20px;
        '
    >
        كورساتك
    </div>


    <div
        style='
            margin-top:4px;
            color:#a1a9b5;
            font-size:11px;
            line-height:18px;
        '
    >
        © {$year} جميع الحقوق محفوظة
    </div>


</td>

</tr>

</table>


</td>

</tr>

</table>


</body>

</html>
";
}
