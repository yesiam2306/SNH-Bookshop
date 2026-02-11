<?php
require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/utils/response.php';

if (empty($_SESSION['__reset_confirmed']))
{
    http_response_code(500);
    $error_message = 'Unknown error occurred.';
    \RESP\redirect_with_message($error_message, false, "login.php");
    exit;
}

unset($_SESSION['__reset_confirmed']);

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
    <title>Password Reset Success - SNH YourNovel</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <h2>Password updated successfully!</h2>
                <p>
                    Your password has been successfully updated.<br>
                    You’ll be redirected to the login page in a few seconds.
                </p>
                <a href="login.php" class="button-primary">Go to login</a>
            </div>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>
</body>
</html>
