<?php
require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';

if (empty($_SESSION['__forgot_confirmed']))
{
    http_response_code(500);
    $error_message = 'Unknown error occurred.';
    \RESP\redirect_with_message($error_message, false, "login.php");
    exit;
}

unset($_SESSION['__forgot_confirmed']);

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
    <title>Forgot password - SNH YourNovel</title>
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
                <h2>Forgot password</h2>
                <p>
                    One last step. Please check your email and click on the link to reset your password.<br>
                </p>
            </div>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>
</body>
</html>
