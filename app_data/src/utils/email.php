<?php

namespace EMAIL;

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/log.php';

use PHPMailer\PHPMailer\PHPMailer;

// ===== funzione base =====
function send_email(string $subject, string $send_to, string $msg): bool
{
    try
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('noreply@snhn.com', 'SNH Project');
        $mail->addAddress($send_to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $msg;
        $mail->AltBody = strip_tags($msg);

        $mail->send();
        log_info("Email sent to {$send_to} (subject: {$subject})");
        return true;
    } catch (\Exception $e)
    {
        log_error("Failed to send email to {$send_to}: " . $e->getMessage());
        return false;
    }
}

function send_confirm_email(string $email, string $token): bool
{
    $link = SITE_BASE . "/confirm.php?email=" . urlencode($email) . "&token=" . urlencode($token);
    $subject = 'Confirm your SNH account';
    $message = <<<HTML
<p>Welcome to <strong>SNH YourNovel Project</strong>!</p>
<p>Your subscription is almost complete.</p>
<p>
To confirm your email address, please click the link below:
</p>
<p>
<a href="{$link}">Confirm your account</a>
</p>
<p>
If you did not create an account, you can safely ignore this email.
</p>
<p>
Regards,<br>
SNH Bookshop - DDC
</p>
HTML;

    return send_email($subject, $email, $message);
}


function send_reset_password(string $email, string $token): bool
{
    $link = SITE_BASE . "/reset_password.php?email=" . urlencode($email) . "&token=" . urlencode($token);

    $subject = 'Reset your SNH password';

    $message = <<<HTML
<p>Hello,</p>

<p>
We received a request to reset the password for your
<strong>SNH YourNovel</strong> account.
</p>

<p>
To choose a new password, click the link below:
</p>

<p>
<a href="{$link}">Reset your password</a>
</p>

<p>
If you did not request a password reset, you can safely ignore this email.
</p>

<p>
Regards,<br>
SNH Bookshop - DDC
</p>
HTML;

    return send_email($subject, $email, $message);
}


function send_unlock_email(string $email, string $token): bool
{
    $unlock_link = SITE_BASE . "/unlock.php?email=" . urlencode($email) . "&token=" . urlencode($token);

    $subject = 'Unlock your SNH account';

    $message = <<<HTML
<p>Hello,</p>

<p>
We detected multiple failed login attempts on the account associated with
<strong>{$email}</strong>.
</p>

<p>
If this was you and you want to immediately unlock your account,
click the link below:
</p>

<p>
<a href="{$unlock_link}">Unlock your account</a>
</p>

<p>
This link is single-use and temporary.
</p>

<p>
If this wasn't you, we recommend changing your password as soon as possible.
</p>

<p>
Regards,<br>
SNH Bookshop - DDC
</p>
HTML;

    return send_email($subject, $email, $message);
}
