<?php

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/response.php';
require_once SRC_PATH . '/file/f_upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $user = \USER\current_user($mysqli);
    if (!$user)
    {
        header('Location: login.php');
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $type = $_POST['type'] ?? '';
    $is_short = ($type === 'short') ? 1 : 0;
    $content = trim($_POST['content'] ?? '');

    if (!\FILE\check_title($mysqli, $title, $user['email'], $is_short))
    {
        header("Location: index.php");
        exit;
    }
    if ($type === 'short')
    {
        if (!\FILE\check_short_content($content) ||
            !\FILE\new_short_novel($mysqli, $title, $user['email'], $content, $is_premium))
        {
            $response = \RESP\get_and_reset();
            $_SESSION['flash'] = $response;
            header("Location: {$response['redirect']}");
            exit;
        }

        log_info("UPLOAD - Short story uploaded by {$user['email']}");
        header('Location: index.php?upload=success');
        exit;
    } elseif ($type === 'file')
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK)
        {
            die('File upload error');
        }

        $tmp_path = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_original = basename($_FILES['file']['name']);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $hash = hash_file('sha256', $tmp_path);

        $ext = '.pdf';
        $stored_name = uniqid('novel_', true) . $ext;
        $dest = UPLOAD_DIR . DIRECTORY_SEPARATOR . $stored_name;

        if (!\FILE\check_size($file_size) ||
            !\FILE\check_mime($finfo, $tmp_path, UPLOAD_ALLOWED_MIME) ||
            !\FILE\check_hash($mysqli, $hash, $user['email']) ||
            !\FILE\move_file($tmp_path, $dest))
        {
            header("Location: index.php");
            exit;
        }

        $rv = \FILE\insertNewFile(
            $mysqli,
            $title,
            $user['email'],
            $is_premium,
            $file_original,
            $stored_name,
            $file_size,
            $hash
        );
        if (!$rv)
        {
            unlink($dest); // cleanup
            header("Location: index.php");
            exit;
        }

        log_info("UPLOAD - File {$title} uploaded by {$user['email']}");
        header('Location: index.php?upload=success');
        exit;
    } else
    {
        die('Invalid upload type');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Novel - SNH Bookshop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="../assets/js/upload-form.js"></script>
</head>
<body>
    <div id="container">

        <!-- HEADER -->
        <div id="header">
            <div id="header-left"><h1 id="logo">SNH Bookshop</h1></div>
            <div id="header-right">
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- <?php if (isset($_SESSION['flash'])):
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            $is_success = ($flash['code'] == 200);
            ?>
        <div class="flash-message <?= $is_success ? 'success' : 'error' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?> -->

        <!-- MAIN -->
        <div id="main">
            <div class="catalog-container upload-container">
                <h2>Upload new novel</h2>

                <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <textarea id="title" name="title" rows="1" maxlength="50" required></textarea>
                        <div class="char-counter" id="charCounterTitle">0 / 50</div>
                    </div>

                    <div class="form-group-inline">
                        <label><input type="checkbox" name="is_premium"> Premium only</label>
                    </div>

                    <div class="form-group-inline">
                        <label><input type="radio" name="type" value="short" checked> Short story</label>
                        <label><input type="radio" name="type" value="file"> Full novel (PDF)</label>
                    </div>

                    <!-- Short story textarea -->
                    <div id="shortStoryBox" class="upload-section hidden">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="6" maxlength="200" required></textarea>
                        <div class="char-counter" id="charCounter">0 / 200</div>
                    </div>

                    <!-- File upload area -->
                    <div id="fileUploadBox" class="form-group hidden">
                        <label>Upload your PDF</label>
                        <div id="dropArea" class="drop-area">
                            <p>Drag & drop your file here or click to select</p>
                            <input type="file" name="file" id="file" accept="application/pdf">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button-primary">Upload</button>
                        <a href="index.php" class="button-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- FOOTER -->
        <div id="footer"><p>&copy; 2025 SNH Bookshop</p></div>
    </div>
</body>
</html>
