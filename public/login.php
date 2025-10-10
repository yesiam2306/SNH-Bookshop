<?php

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/validator.php';
require_once SRC_PATH . '/utils/log.php';

/* roba per css*/
$backgrounds = [];
for ($i = 0; $i < 5; $i++)
{
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];
/*--------------*/


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (empty($_POST['email']) || empty($_POST['password']))
    {
        $error_message = 'Please enter both email and password.';
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
        if (!\USER\check_ip_attempts($mysqli, $_SERVER['REMOTE_ADDR']) ||
            !\USER\check_email_attempts($mysqli, $email))
        {
            $error_message = 'Too many attempts. Retry in few minutes.';
        } else
        {
            $password    = $_POST['password'] ?? '';

            $remember_me = isset($_POST['remember']) ? true : false;

            $rv = \USER\login($mysqli, $email, $password, $remember_me);

            if ($rv)
            {
                \USER\reset_quarantine($mysqli, $_SERVER['REMOTE_ADDR'], $email);
                header('Location: index.php');
                exit;
            } else
            {
                $error_message = 'Invalid credentials.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SNH Bookshop - Home</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div id="container">

        <!-- HEADER -->
        <div id="header">
            <div id="logo">
                <h1>SNH Bookshop</h1>
            </div>
        </div>

        <!-- MAIN -->
        <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
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
                                <img src="../assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                    </p>
                    <?php if (!empty($error_message)): ?>
                        <span id="password-error" class="error-message">
                            <?= htmlspecialchars($error_message) ?><br>
                        </span>
                    <?php endif; ?>
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
            <p>&copy; 2025 SNH Bookshop</p>
        </div>

    </div>
    <script src="../assets/js/login-password-tools.js"></script>
</body>
</html>