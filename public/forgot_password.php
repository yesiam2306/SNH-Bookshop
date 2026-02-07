<?php
require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/validator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (empty($_POST['email']))
    {
        $user_error_message = ('Input fields cannot be empty.');
    } elseif (!hash_equals($_SESSION['__csrf'] ?? '', $_POST['csrf_token'] ?? ''))
    {
        log_error("Invalid request (CSRF check failed).");
        session_unset();
        session_destroy();
        http_response_code(403);
        exit("Invalid request.");
    } else
    {
        $email = \VALIDATOR\sanitize_email($_POST['email'] ?? '');
        if (!$email)
        {
            $user_error_message = 'Invalid email format.';
        } else
        {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);

            $rv = USER\tokenReset($mysqli, $email, $token_hash);
            if (!$rv)
            {
                // TODO in realtà non è l'unico motivo possibile
                $user_error_message = 'User already exists. Try to log in.';
            } else
            {
                $rv = \EMAIL\send_reset_password($email, $token);
                $_SESSION['__forgot_confirmed'] = true;
                header('Location: forgot_success.php');
                exit;
            }
        }

    }
}

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
    <title>Forgot Password - SNH YourNovel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <form action="forgot_password.php" method="post" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                <h2>Forgot password</h2>
                <p>
                    Please insert your email address to reset your password.<br>
                    <p>
                        <label for="email">Email</label>
                        <input id="email" tabindex="1" autofocus="autofocus" type="email" name="email" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </p>
                    <input type="submit" value="Send Reset Link" class="button-primary">
                </p>
            </div>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>
</body>
</html>
