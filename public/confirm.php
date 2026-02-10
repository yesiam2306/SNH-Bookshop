<?php

require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/validator.php';

// cose per css
$backgrounds = [];
for ($i = 0; $i < 5; $i++)
{
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];

if ($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    http_response_code(405);
    $error_message = 'Not allowed.';
    \RESP\redirect_with_message($error_message, false, "login.php");
}

$email_raw = $_GET['email'] ?? '';
$token_raw = $_GET['token'] ?? '';

$email = \VALIDATOR\sanitize_email($email_raw);
$token = is_string($token_raw) ? trim($token_raw) : '';

if (!$email || $token === '')
{
    http_response_code(400);
    $error_message = 'Bad request.';
    \RESP\redirect_with_message($error_message, false, "login.php");
}

if (!\USER\check_token($mysqli, $email, $token))
{
    log_warning("CONFIRM - Invalid or expired token for {$email}");
    http_response_code(401);
    $error_message = 'Invalid or expired token.';
    \RESP\redirect_with_message($error_message, false, "login.php");
    exit;
}

if (!\USER\confirm($mysqli, $email, 'User'))
{
    log_error("CONFIRM - Failed to confirm user {$email}");
    http_response_code(500);
    $error_message = 'Internal servererror.';
    \RESP\redirect_with_message($error_message, false, "login.php");
    exit;
}

$_SESSION['__email_confirmed'] = true;

header('Location: confirm_success.php');
exit;
