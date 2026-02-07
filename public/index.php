<?php
require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/user/u_novels.php';

$user = \USER\current_user($mysqli);
if (!$user)
{
    header('Location: login.php');
    exit;
}

// require_once DBM_PATH . '/users.php';
// $rv = \DBM\updateUserUser($mysqli, $user['email']);
// \USER\edit_session("User");
// $new_catalog = \USER\create_catalog($mysqli);

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
    $novels = \USER\search_catalog($mysqli, $user['email'], $query);
} else
{
    $novels = \USER\show_catalog($mysqli, $user['email']);
}

// PAGINATION
$novels_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_novels = count($novels);
$total_pages = max(1, ceil($total_novels / $novels_per_page));
$start_index = ($page - 1) * $novels_per_page;
$current_novels = array_slice($novels, $start_index, $novels_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SNH YourNovel - Home</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div id="container">

        <!-- HEADER -->
        <div id="header">
            <div id="header-left">
                <h1 id="logo">SNH YourNovel</h1>
            </div>

                    <div id="header-center">
                <form action="index.php" method="get" class="search-container">
                    <input type="text" name="search" placeholder="Search...">
                    <button type="submit">
                        <img src="../assets/img/search.svg" alt="Search">
                    </button>
                </form>
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
            
            <div class="catalog-header">
                <h2>Novel Catalog</h2>
                <a href="upload.php" class="button-primary upload-btn">Upload new novel</a>
            </div>

                <?php if (!empty($current_novels)): ?>
                    <table class="catalog">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th></th>
                                <th>Author</th>
                                <th>Premium</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($current_novels as $n): ?>
                                <tr class="novel-row"
                                    data-title="<?= htmlspecialchars($n['title']) ?>"
                                    data-email="<?= htmlspecialchars($n['email']) ?>"
                                    data-content="<?= htmlspecialchars($n['content'] ?? '') ?>"
                                    data-link="<?= htmlspecialchars($n['file_stored_name'] ?? '') ?>"
                                    data-premium="<?= htmlspecialchars($n['is_premium']) ?>">
                                    <td><?= htmlspecialchars($n['title']) ?></td>
                                    <td>
                                        <?php if ($n['is_short']): ?>
                                            <img src="../assets/img/short-story.png" alt="short story">          
                                        <?php else: ?>
                                            <img src="../assets/img/pdf.png" alt="pdf">    
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($n['email']) ?></td>
                                    <td>
                                        <?php if ($n['is_premium']): ?>
                                            <img src="../assets/img/premium.png" alt="premium">
                                        <?php endif; ?>
                                    </td>
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
                    <p style="text-align:center; color:#555;">No novels available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- MODAL -->
        <div id="novel-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3 id="modal-title"></h3>
                <p><strong>Author:</strong> <span id="modal-author"></span></p>
                <div id="modal-body"></div>
            </div>
        </div>

        <!-- FOOTER -->
        <div id="footer">
            <p>&copy; 2025 SNH YourNovel</p>
        </div>
    </div>

    <script>
    window.userIsPremium = <?= ($user['role'] === 'Premium') ? 1 : 0 ?>;
    </script>
    <script src="../assets/js/show-content.js"></script>
</body>
</html>
