<?php
require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/validator.php';

if (empty($_SESSION['__forgot_confirmed']))
{
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (empty($_POST['password']) ||
        empty($_POST['password_confirmation']) ||
        empty($_POST['email']))
    {
        // todo email va gestita diversamente.
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
        $error_message = \VALIDATOR\validate_password($_POST['password'] ?? '', $_POST['email'] ?? '');
        if (!empty($error_message))
        {
            $password_error_message = $error_message;
        } else
        {
            $email = \VALIDATOR\sanitize_email($_POST['email'] ?? '');
            if (!$email)
            {
                $user_error_message = 'Invalid email format.';
            } else
            {
                $password              = $_POST['password'];
                $password_confirmation = $_POST['password_confirmation'];

                if ($password !== $password_confirmation)
                {
                    $password_error_message[] = 'Password and confirmation do not match.';
                } else
                {
                    $rv = USER\reset_password($mysqli, $email, $password);
                    if (!$rv)
                    {
                        // TODO in realtà non è l'unico motivo possibile
                        $user_error_message = 'User already exists. Try to log in.';
                    } else
                    {
                        unset($_SESSION['__forgot_confirmed']);
                        $_SESSION['__reset_confirmed'] = true;
                        header('Location: reset_success.php');
                        exit;
                    }
                }
            }
        }
    }
}

// cose per css
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
    <title>Reset password - SNH YourNovel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta http-equiv="refresh" content="300;url=login.php">
</head>
<body>
    <div id="container">
        <div id="header">
            <div id="logo">
                <h1>SNH YourNovel</h1>
            </div>
        </div>

        <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
            <div class="login-container">
                <h2 class="login-title">Choose a new password</h2>
                <form action="reset_password.php" method="post" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf'] ?? '') ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">

                    <p>
                        <label for="password">New password</label>
                        <div class="input-wrapper">
                            <input id="password" type="password" name="password" required>
                            <button type="button" class="toggle-password">
                                <img src="../assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                    </p>

                    <p>
                        <label for="password_confirmation">Confirm password</label>
                        <div class="input-wrapper">
                            <input id="password_confirmation" type="password" name="password_confirmation" required>
                            <button type="button" class="toggle-password">
                                <img src="../assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                    </p>

                    <ul class="password-rules">
                        <li id="rule-length">At least 12 characters</li>
                        <li id="rule-lower">Contains lowercase letters</li>
                        <li id="rule-upper">Contains uppercase letters</li>
                        <li id="rule-number">Contains digits</li>
                        <li id="rule-symbol">Contains symbols</li>
                    </ul>

                    <p>
                        <button id="generate-password" class="button-secondary">Generate secure password</button>
                    </p>

                    <p>
                        <input type="submit" value="Reset password" class="button-primary">
                    </p>
                </form>
            </div>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>

    <script src="../assets/js/signup-password-tools.js"></script>
</body>
</html>
