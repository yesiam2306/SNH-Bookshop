<?php
require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';

$user = \USER\current_user($mysqli);

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

            <div id="header-bottom">
                <div id="search">
                <form action="catalog.php" method="get" class="search-container">
                    <input type="text" name="q" placeholder="Search...">
                    <button type="submit">
                    <img src="../assets/img/search.svg" 
                        alt="Search">
                    </button>
                </form>
                </div>


                <div id="account">
                    <ul>
                        <?php if ($user): ?>
                            <li><a href="profile.php">Profile</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="signup.php">Sign up</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- MAIN -->
        <div id="main" style="--bg-image: url('<?php echo $bg; ?>');">
            <?php if ($user): ?>
                <h2>Hello, <?php echo htmlspecialchars($user['email']); ?>!</h2>
                <p>Welcome to your personal area.</p>
            <?php else: ?>
                <h2>Hello, Guest!</h2>
                <p>Please log in or sign up to access all features.</p>
            <?php endif; ?>
        </div>

        <!-- FOOTER -->
        <div id="footer">
            <p>&copy; 2025 SNH Bookshop</p>
        </div>

    </div>
</body>
</html>
