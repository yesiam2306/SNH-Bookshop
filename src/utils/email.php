<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/log.php';

// function send_unlock_email(string $email, string $plain_token): bool
// {
//     if (!$email)
//     {
//         log_error("send_unlock_email: invalid email provided: $email");
//         return false;
//     }

//     // Build unlock link
//     // Assumes config defines SITE_BASE (e.g. https://localhost or https://your-domain.tld)
//     // If not present, fallback to relative path
//     $siteBase = defined('SITE_BASE') ? SITE_BASE : '';
//     $unlock_link = rtrim($siteBase, '/') . '/public/unlock.php?email=' . urlencode($email) . '&token=' . urlencode($plain_token);

//     $subject = 'SNH Bookshop — Account unlock';
//     $message = <<<EOT
// Hello,

// We have detected multiple failed login attempts for the account associated with this email address.
// If you want to immediately unlock the account, click the link below:

// $unlock_link

// This link is single-use and temporary.

// Regards,
// SNH Bookshop - DDC
// EOT;

//     $from = defined('MAIL_FROM') ? MAIL_FROM : 'no-reply@localhost';
//     $headers  = "From: {$from}\r\n";
//     $headers .= "MIME-Version: 1.0\r\n";
//     $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

//     // Try to use PHPMailer if available for better deliverability
//     if (class_exists('PHPMailer\PHPMailer\PHPMailer'))
//     {
//         try
//         {
//             $mail = new PHPMailer\PHPMailer\PHPMailer(true);
//             // If config provides SMTP settings, use them
//             if (defined('SMTP_HOST') && SMTP_HOST)
//             {
//                 $mail->isSMTP();
//                 $mail->Host       = SMTP_HOST;
//                 $mail->SMTPAuth   = !empty(SMTP_USER);
//                 if (!empty(SMTP_USER))
//                 {
//                     $mail->Username = SMTP_USER;
//                     $mail->Password = SMTP_PASS;
//                 }
//                 $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
//                 $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
//             }
//             $mail->setFrom($from);
//             $mail->addAddress($email);
//             $mail->Subject = $subject;
//             $mail->Body    = $message;
//             $mail->send();
//             log_info("Unlock token email (PHPMailer) sent to {$email}");
//             return true;
//         } catch (Exception $e)
//         {
//             log_error("PHPMailer failed to send unlock email to {$email}: " . $e->getMessage());
//             // fallback to mail()
//         }
//     }

//     // Fallback to PHP mail()
//     $sent = @mail($email, $subject, $message, $headers);
//     if ($sent)
//     {
//         log_info("Unlock token email (mail()) sent to {$email}");
//         return true;
//     } else
//     {
//         log_error("Failed to send unlock email (mail()) to {$email}");
//         return false;
//     }
// }
