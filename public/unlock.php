<?php

require_once __DIR__ . '/../config/config.php';
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
    exit('Not allowed.');
}

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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account unlocked - SNH YourNovel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta http-equiv="refresh" content="5;url=login.php">
</head>
<body>
    <div id="container">
        <div id="header">
            <div id="logo">
                <h1>SNH YourNovel</h1>
            </div>
        </div>

        <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
            <div class="login-container" style="text-align:center;">
                <h2>Account unlocked</h2>
                <p>Your account is now unlocked.<br>
                   You’ll be redirected to the login page in a few seconds.</p>
                <a href="login.php" class="button-primary">Go to login</a>
            </div>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>
</body>
</html>
