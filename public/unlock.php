<?php

// public/unlock.php

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    http_response_code(405);
    exit('Not allowed.');
}

// Expecting: unlock.php?email=...&token=...
$email_raw = $_GET['email'] ?? '';
$token_raw = $_GET['token'] ?? '';

$email = \VALIDATOR\sanitize_email($email_raw);
$token = is_string($token_raw) ? trim($token_raw) : '';

if (!$email || $token === '')
{
    log_warning("UNLOCK - Invalid request parameters. Email or token missing.");
    header('Location: index.php');
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$token_hash = hash('sha256', $token);

$rv = \USER\unlock_token($mysqli, $ip, $email, $token_hash);
if ($rv)
{
    header('Location: login.php?unlock=1');
} else
{
    header('Location: login.php?unlock=0');
}
