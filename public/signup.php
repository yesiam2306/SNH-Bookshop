<?php
session_start();

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/user/u_auth.php';

/* roba per css*/
$backgrounds = [];
for ($i = 0; $i < 5; $i++) {
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];
/*--------------*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username              = $_POST['email']                 ?? '';
    $password              = $_POST['password']              ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';

    if ($password !== $password_confirmation) {
        $password_error_message = 'Passwords do not match.';
    } else {
        $rv = USER\signup($mysqli, $username, $password);

        if ($rv) {
            header('Location: index.php');
            exit;
        } else {
            $user_error_message = 'User already exists. Try to log in.';
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
                    <p>
                        <label for="email">Email</label>
                        <input id="email" tabindex="1" autofocus="autofocus" type="email" name="email" required>
                        <?php if (!empty($user_error_message)): ?>
                            <span id="user-error" class="error_message">
                                <?= htmlspecialchars($user_error_message) ?>
                            </span>
                        <?php endif; ?>
                    </p>
                    <p>
                        <label for="password">Password</label>
                        <input id="password" tabindex="2" type="password" name="password" required>
                    </p>
                    <p>
                        <label for="password_confirmation">Password Confirmation</label>
                        <input id="password_confirmation" tabindex="3" type="password" name="password_confirmation" required>
                        <span id="password-error" style="color: red; font-size: 14px; display: none;">
                            Passwords do not match
                        </span>
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
    <script>
    const form = document.querySelector(".login-form");
    const password = document.getElementById("password");
    const confirm = document.getElementById("password_confirmation");
    const error = document.getElementById("password-error");

    form.addEventListener("submit", function (e) {
        if (password.value !== confirm.value) {
            e.preventDefault(); 
            error.style.display = "block"; 
        } else {
            error.style.display = "none";
        }
    });
    </script>
</body>
</html>