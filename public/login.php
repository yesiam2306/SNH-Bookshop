<?php

require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/validator.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/response.php';

/* roba per css*/
$backgrounds = [];
for ($i = 0; $i < 5; $i++)
{
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];
/*--------------*/


$user = \USER\current_user($mysqli);
if ($user)
{
    header('Location: index.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (empty($_POST['email']) || empty($_POST['password']))
    {
        http_response_code(400);
        $msg = 'Email and password are required.';
        \RESP\redirect_with_message($msg, false, "login.php");
    } elseif (!hash_equals($_SESSION['__csrf'] ?? '', $_POST['csrf_token'] ?? ''))
    {
        log_error("CSRF - Invalid token on role update.");
        header('Location: logout.php');
        exit();
    } else
    {
        $email = \VALIDATOR\sanitize_email($_POST['email'] ?? '');
        if (!\USER\check_ip_attempts($mysqli, $_SERVER['REMOTE_ADDR']) ||
            !\USER\check_email_attempts($mysqli, $email))
        {
            http_response_code(401);
            $error_message = 'Too many attempts. Retry in few minutes.';
            \RESP\redirect_with_message($error_message, false, "login.php");
        } else
        {
            $password    = $_POST['password'] ?? '';

            $remember_me = isset($_POST['remember']) ? true : false;

            $rv = \USER\login($mysqli, $email, $password, $remember_me);

            if ($rv)
            {
                \USER\reset_quarantine($mysqli, $_SERVER['REMOTE_ADDR'], $email);
                if ($rv['role'] === 'Admin')
                {
                    header('Location: admin.php');
                    exit;
                } else
                {
                    header('Location: index.php');
                    exit;
                }
            } else
            {
                http_response_code(403);
                $error_message = 'Invalid credentials.';
                \RESP\redirect_with_message($error_message, false, "login.php");
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SNH YourNovel - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="container">

        <!-- HEADER -->
        <div id="header">
            <div id="logo">
                <h1>SNH YourNovel</h1>
            </div>
        </div>

        <!-- MAIN -->
        <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
            <?php \RESP\render_flash(); ?>
            <div class="login-container">
                <h2 class="login-title">Login as Existing Customer</h2>
                <form action="login.php" method="post" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf'] ?? '') ?>">
                    <p>
                        <label for="email">Email</label>
                        <input id="email" tabindex="1" autofocus="autofocus" type="email" name="email" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input id="password" type="password" name="password" required>
                            <button type="button" class="toggle-password">
                                <img src="assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                    </p>
                    <p class="remember">
                        <input type="checkbox" id="remember" name="remember" value="1"
                            <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                        <label for="remember">Remember me</label>
                    </p>
                    <p>
                        <input type="submit" value="Login" class="button-primary">
                    </p>
                </form>
                <div class="login-links">
                    <a href="signup.php">Create a new account</a> | 
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </div>  

        </div>

        <!-- FOOTER -->
        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>

    </div>
    <script src="assets/js/login-password-tools.js"></script>
</body>
</html>