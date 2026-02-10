<?php
require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';

if (empty($_SESSION['__signup_ok']))
{
    http_response_code(500);
    $error_message = 'Unknown error occurred.';
    \RESP\redirect_with_message($error_message, false, "signup.php");
    exit;
}

unset($_SESSION['__signup_ok']);

$backgrounds = [];
for ($i = 0; $i < 5; $i++)
{
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup completed - SNH YourNovel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="container">
        <div id="header">
            <div id="logo">
                <h1>SNH YourNovel</h1>
            </div>
        </div>

        <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
            <div class="login-container" style="text-align:left;">
                <h2>Signup completed</h2>
                <p>
                    One last step. Please check your email and click on the link to confirm your subscription.<br>
                </p>
                <a href="index.php" class="button-primary">Home</a>
            </div>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>
</body>
</html>
