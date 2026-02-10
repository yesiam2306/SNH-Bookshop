<!-- todo: cancellare -->

<?php

require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';

$user = \USER\current_user($mysqli);
if (!$user)
{
    header('Location: login.php');
    exit;
}

$backgrounds = [];
for ($i = 0; $i < 5; $i++)
{
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];

if (strcasecmp($user['role'], 'Premium') === 0)
{
    log_info("PREMIUM - {$user['email']} was already premium");
    header('Location: index.php');
    exit;
}

$rv = \USER\become_premium($mysqli, $user['email']);
if (!$rv)
{
    log_error("PREMIUM - Upgrade failed for {$user['email']}");
    $message = "Oops, something went wrong. Please try again later.";
} else
{
    $message = "This is your lucky day. You've become Premium without paying, enjoy! :)";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SNH Bookshop - Become Premium</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="container">

    <!-- HEADER -->
    <div id="header">
        <div id="header-left">
            <h1 id="logo">SNH Bookshop</h1>
        </div>

        <div id="header-right">
            <ul>
                <?php if ($user): ?>
                    <li><a href="index.php"><?= htmlspecialchars($user['email']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- MAIN -->
    <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
        <div class="catalog-container" style="text-align:center;">
            <h3><?= htmlspecialchars($message) ?></h3>
            <p style="margin-top:2rem;">
                <a href="index.php" class="button-primary">Back to Home</a>
            </p>
        </div>
    </div>

    <!-- FOOTER -->
    <div id="footer">
        <p>&copy; 2025 SNH Bookshop</p>
    </div>
</div>
</body>
</html>
