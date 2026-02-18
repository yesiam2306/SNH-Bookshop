<!-- quando l'utente usa il link ricevuto via email, accede alla pagina tramite una GET passando email e token.
 la pagina verifica che email e token siano validi e se lo sono mostra il form per inserire la nuova password. 
 a questo punto la pagina viene chiamata tramite una POST -->

<?php
require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/validator.php';
require_once SRC_PATH . '/utils/response.php';

$email = \VALIDATOR\sanitize_email($_POST['email'] ?? $_GET['email'] ?? '');
$token = $_POST['token'] ?? $_GET['token'] ?? '';

if (!$email || $token === '')
{
    log_warning("RESET - Invalid request parameters.");
    http_response_code(400);
    $error_message = 'Bad request.';
    \RESP\redirect_with_message($error_message, false, "login.php");
    exit;
}

if (!\USER\check_token($mysqli, $email, $token))
{
    log_warning("Invalid reset token for email: $email");
    http_response_code(401);
    $error_message = 'Invalid or expired token.';
    \RESP\redirect_with_message($error_message, false, "login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (empty($_POST['password']) ||
        empty($_POST['password_confirmation']) ||
        empty($_POST['email']))
    {
        http_response_code(400);
        $error_message = 'Empty fields.';
        \RESP\redirect_with_message($error_message, false, "reset_password.php");
        exit;
    } elseif (!hash_equals($_SESSION['__csrf'] ?? '', $_POST['csrf_token'] ?? ''))
    {
        log_error("CSRF - Invalid token on role update.");
        header('Location: logout.php');
        exit();
    } else
    {
        $error_message = \VALIDATOR\validate_password($_POST['password'] ?? '', $_POST['email'] ?? '');
        if (!empty($error_message))
        {
            $password_error_message = $error_message[0];
            \RESP\redirect_with_message($password_error_message, false, "reset_password.php");
            exit;
        } else
        {
            $email = \VALIDATOR\sanitize_email($_POST['email'] ?? '');
            if (!$email)
            {
                $user_error_message = 'Invalid email format.';
                http_response_code(400);
                \RESP\redirect_with_message($user_error_message, false, "reset_password.php");
                exit;
            } else
            {
                $password              = $_POST['password'];
                $password_confirmation = $_POST['password_confirmation'];

                if ($password !== $password_confirmation)
                {
                    $password_error_message = 'Password and confirmation do not match.';
                    \RESP\redirect_with_message($password_error_message, false, "reset_password.php");
                    exit;
                } else
                {
                    $rv = USER\reset_password($mysqli, $email, $password);
                    if (!$rv)
                    {
                        $user_error_message = 'Internal error. Please try again later.';
                        http_response_code(500);
                        \RESP\redirect_with_message($user_error_message, false, "reset_password.php");
                        exit;
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
            <?php \RESP\render_flash(); ?>
            <div class="login-container">
                <h2 class="login-title">Choose a new password</h2>
                <form action="reset_password.php" method="post" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf'] ?? '') ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <p>
                        <label for="password">New password</label>
                        <div class="input-wrapper">
                            <input id="password" type="password" name="password" required>
                            <button type="button" class="toggle-password">
                                <img src="assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                    </p>

                    <p>
                        <label for="password_confirmation">Confirm password</label>
                        <div class="input-wrapper">
                            <input id="password_confirmation" type="password" name="password_confirmation" required>
                            <button type="button" class="toggle-password">
                                <img src="assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                    </p>

                    
                    <ul id="password-checklist" class="password-rules">
                        Please choose a password with:
                        <li id="rule-length">At least 12 characters</li>
                        <li id="rule-lower">At least one lowercase letter</li>
                        <li id="rule-upper">At least one uppercase letter</li>
                        <li id="rule-number">At least one number</li>
                        <li id="rule-symbol">At least one symbol in <br>
                            ! @ # $ % ( ) [ ] { } _ + - * = ; : , . ? \</li>
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

    <script src="assets/js/signup-password-tools.js"></script>
</body>
</html>
