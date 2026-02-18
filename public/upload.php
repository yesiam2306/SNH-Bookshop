<?php

require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/response.php';
require_once SRC_PATH . '/file/f_upload.php';

$user = \USER\current_user($mysqli);
if (!$user)
{
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0)
    {
        $max_size = ini_get('post_max_size');
        http_response_code(400);
        $error_message = "Error occurred during file upload. The max file size is {$max_size}.";
        \RESP\redirect_with_message($error_message, false, "upload.php");
        exit;
    }

    if (!hash_equals($_SESSION['__csrf'] ?? '', $_POST['csrf_token'] ?? ''))
    {
        log_error("CSRF - Invalid token on role update.");
        header('Location: logout.php');
        exit();
    }

    $title = trim($_POST['title'] ?? '');
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $type = $_POST['type'] ?? '';
    $is_short = ($type === 'short') ? 1 : 0;
    $content = trim($_POST['content'] ?? '');

    if (!\FILE\check_title($mysqli, $title, $user['email'], $is_short))
    {
        http_response_code(400);
        $error_message = 'Invalid title.';
        \RESP\redirect_with_message($error_message, false, "upload.php");
        exit;
    }
    if ($type === 'short')
    {
        if (!\FILE\check_short_content($content) ||
            !\FILE\new_short_novel($mysqli, $title, $user['email'], $content, $is_premium))
        {
            http_response_code(500);
            $error_message = 'Internal server error.';
            \RESP\redirect_with_message($error_message, false, "upload.php");
            exit;
        }

        log_info("UPLOAD - Short story uploaded by {$user['email']}");
        http_response_code(200);
        $msg = 'Short story uploaded successfully.';
        \RESP\redirect_with_message($msg, true, "index.php");
        exit;
    } elseif ($type === 'file')
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK)
        {
            http_response_code(500);
            $error_message = 'Internal server error.';
            \RESP\redirect_with_message($error_message, false, "upload.php");
            exit;
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
            http_response_code(500);
            $error_message = 'Error occurred during file upload. The max file size is 50MB. Only .pdf allowed.';
            \RESP\redirect_with_message($error_message, false, "upload.php");
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
            unlink($dest); // lo elimina dal disco.
            http_response_code(500);
            $error_message = 'Internal server error.';
            \RESP\redirect_with_message($error_message, false, "upload.php");
            exit;
        }

        log_info("UPLOAD - File {$title} uploaded by {$user['email']}");
        http_response_code(200);
        $msg = 'File uploaded successfully.';
        \RESP\redirect_with_message($msg, true, "index.php");
        exit;
    } else
    {
        http_response_code(400);
        $msg = 'Invalid upload type.';
        \RESP\redirect_with_message($msg, false, "upload.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Novel - SNH Bookshop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/upload-form.js"></script>
</head>
<body>
    <div id="container">

        <!-- HEADER -->
        <div id="header">
            <div id="header-left"><h1 id="logo">SNH Bookshop</h1></div>
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
        <div id="main">
            <?php \RESP\render_flash(); ?>
            <div class="catalog-container upload-container">
                <h2>Upload new novel</h2>

                <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
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
