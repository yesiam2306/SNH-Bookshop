<?php
require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/admin/a_control.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/response.php';

$user = \USER\current_user($mysqli);
if (!$user)
{
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'Admin')
{
    log_warning("ADMIN - Access denied to admin.php for user {$_SESSION['email']}");
    header('Location: index.php');
    exit;
}

// todo sicuri che sia da mettere?
if (empty($_SESSION['__csrf']))
{
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// cose per css
$backgrounds = [];
for ($i = 0; $i < 5; $i++)
{
    $backgrounds[] = '../img/background-' . ($i + 1) . '.jpg';
}
$bg = $backgrounds[array_rand($backgrounds)];

// funzionalità search
if (isset($_GET['search']) && !empty(trim($_GET['search'])))
{
    $query = trim($_GET['search']);
    $users = \ADMIN\search_user($mysqli, $_SESSION['email'], $query);
} else
{
    $users = \ADMIN\show_users($mysqli, $_SESSION['email']);
}

// PAGINATION
$users_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_users = count($users);
$total_pages = max(1, ceil($total_users / $users_per_page));
$start_index = ($page - 1) * $users_per_page;
$current_users = array_slice($users, $start_index, $users_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SNH YourNovel - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
    <div id="container">

        <!-- HEADER -->
        <div id="header">
            <div id="header-left">
                <h1 id="logo">SNH YourNovel</h1>
            </div>

                    <div id="header-center">
                <form action="admin.php" method="get" class="search-container">
                    <input type="text" name="search" placeholder="Search...">
                    <button type="submit">
                        <img src="assets/img/search.svg" alt="Search">
                    </button>
                </form>
            </div>

            <div id="header-right">
                <ul>
                    <?php if ($user): ?>
                        <li><a href="admin.php"><?= htmlspecialchars($user['email']) ?></a></li>
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
            <?php \RESP\render_flash(); ?>
        
            <div class="userlist-header">
                <h2>Users</h2>
            </div>

                <?php if ($total_users > 0): ?>
                    <table class="userlist">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($current_users as $n): ?>
                                <tr class="user-row"
                                    data-user-id="<?= htmlspecialchars($n['user_id']) ?>"
                                    data-email="<?= htmlspecialchars($n['email']) ?>"
                                    data-role="<?= htmlspecialchars($n['role']) ?>">
                                    <td><?= htmlspecialchars($n['user_id']) ?></td>
                                    <td><?= htmlspecialchars($n['email']) ?></td>
                                    <td><?= htmlspecialchars($n['role']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- PAGINATION -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="page-btn">&laquo; Prev</a>
                            <?php endif; ?>

                            <?php
                            $visible = 3;
                        $start = max(1, $page - 1);
                        $end = min($total_pages, $start + $visible - 1);
                        if ($end - $start < $visible - 1)
                        {
                            $start = max(1, $end - $visible + 1);
                        }

                        if ($start > 1)
                        {
                            echo '<a href="?page=1" class="page-btn">1</a>';
                            if ($start > 2)
                            {
                                echo '<span class="dots">...</span>';
                            }
                        }

                        for ($i = $start; $i <= $end; $i++)
                        {
                            $active = ($i === $page) ? 'active' : '';
                            echo "<a href=\"?page=$i\" class=\"page-btn $active\">$i</a>";
                        }

                        if ($end < $total_pages)
                        {
                            if ($end < $total_pages - 1)
                            {
                                echo '<span class="dots">...</span>';
                            }
                            echo "<a href=\"?page=$total_pages\" class=\"page-btn\">$total_pages</a>";
                        }
                        ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="page-btn">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#555;">No users available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- MODAL -->
        <div id="user-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3 id="modal-title"></h3>
                <p><strong>Select New Role:</strong></p>
                <div id="modal-body"></div>
            </div>
        </div>

        <!-- FOOTER -->
        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>
    
    <script src="assets/js/admin-change-role.js"></script>

</body>
</html>