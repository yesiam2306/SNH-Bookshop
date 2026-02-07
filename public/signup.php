<?php
require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/validator.php';
require_once SRC_PATH . '/session_boot.php';

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
    if (empty($_POST['password']) ||
        empty($_POST['password_confirmation']) ||
        empty($_POST['email']))
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
                    $unlock_token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $unlock_token);
                    $rv = USER\signup($mysqli, $email, $password, 'Pending', $token_hash);
                    if (!$rv)
                    {
                        // TODO in realtà non è l'unico motivo possibile
                        $user_error_message = 'User already exists. Try to log in.';
                    } else
                    {
                        $rv = \EMAIL\send_confirm_email($email, $unlock_token);
                        $_SESSION['__signup_ok'] = true;
                        header('Location: signup_success.php');
                        exit;
                    }
                }
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
                <h2 class="login-title">Create an Account</h2>
                <form action="signup.php" method="post" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                    <p>
                        <label for="email">Email</label>
                        <input id="email" tabindex="1" autofocus="autofocus" type="email" name="email" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <?php if (!empty($user_error_message)): ?>
                            <span id="user-error" class="error-message">
                                <?= htmlspecialchars($user_error_message) ?>
                            </span>
                        <?php endif; ?>
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
                    <p>
                        <button id="generate-password" type="button" class="button-secondary">
                            Suggest a strong password
                        </button>
                    </p>
                    <p>
                        <label for="password_confirmation">Password Confirmation</label>
                        <div class="input-wrapper">
                            <input id="password_confirmation" type="password" name="password_confirmation" required>
                            <button type="button" class="toggle-password">
                                <img src="../assets/img/mostra.png" alt="Show password">
                            </button>
                        </div>
                        <?php if (!empty($password_error_message)): ?>
                            <span id="password-error" class="error-message">
                                <?php foreach ($password_error_message as $msg): ?>
                                    <?= htmlspecialchars($msg) ?><br>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                        <ul id="password-checklist" class="password-rules">
                            Please choose a password with:
                            <li id="rule-length">At least 12 characters</li>
                            <li id="rule-lower">At least one lowercase letter</li>
                            <li id="rule-upper">At least one uppercase letter</li>
                            <li id="rule-number">At least one number</li>
                            <li id="rule-symbol">At least one symbol in <br>
                                ! " # $ % & ' ( ) * + , - . / : ; < = > ? @ [ \ ] ^ _ ` { | } ~</li>
                        </ul>

                    </p>
                    <p>
                        <input type="submit" value="Create" class="button-primary">
                    </p>
                </form>
                <div class="login-links">
                    <a href="login.php">Login as Existing Customer</a> 
                </div>
            </div>  

        </div>

        <!-- FOOTER -->
        <div id="footer">
            <p>&copy; 2025 SNH Bookshop</p>
        </div>

    </div>
    <script src="../assets/js/signup-password-tools.js"></script>
</body>
</html>