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
    $username    = $_POST['email']    ?? '';
    $password    = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember']) ? true : false;

    $rv = USER\login($mysqli, $username, $password, $remember_me);

    if ($rv) {
        header('Location: index.php');
        exit;
    } else {
        $error_message = 'Invalid email or password.';
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
                    <p>
                        <label for="email">Email</label>
                        <input id="email" tabindex="1" autofocus="autofocus" type="email" name="email" required>
                    </p>
                    <p>
                        <label for="password">Password</label>
                        <input id="password" tabindex="2" type="password" name="password" required>
                    </p>
                    <p class="remember">
                        <input type="checkbox" id="remember" name="remember" value="1">
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
</body>
</html>