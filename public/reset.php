<!-- TODO cancellare. spostato tutto in reset_password.php -->

<?php

/**
require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    http_response_code(405);
    exit('Not allowed.');
}

$email_raw = $_GET['email'] ?? '';
$token_raw = $_GET['token'] ?? '';

$email = \VALIDATOR\sanitize_email($email_raw);
$token = is_string($token_raw) ? trim($token_raw) : '';

if (!$email || $token === '')
{
    log_warning("RESET - Invalid request parameters.");
    header('Location: error.php');
    exit;
}

if (!\USER\check_token($mysqli, $email, $token))
{
    log_warning("RESET - Invalid or expired token for {$email}");
    header('Location: error.php');
    exit;
}

$_SESSION['__forgot_confirmed'] = true;

header('Location: reset_password.php?email=' . urlencode($email));
exit;
*/
