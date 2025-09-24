<?php
session_start();
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

        <div id="header">
            <h1>Welcome to SNH Bookshop</h1>
            <div id="nav">
                <ul>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="signup.php">Sign up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div id="main">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <h2>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p>Welcome to your personal area.</p>
            <?php else: ?>
                <h2>Hello, Guest!</h2>
                <p>Please log in or sign up to access all features.</p>
            <?php endif; ?>
        </div>

        <div id="footer">
            <p>&copy; 2025 SNH Proj</p>
        </div>

    </div>
</body>
</html>
