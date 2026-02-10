<?php

require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/file/f_upload.php';
require_once SRC_PATH . '/utils/log.php';

$user = \USER\current_user($mysqli);
if (!$user)
{
    header('Location: login.php');
    exit;
}

$novel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($novel_id <= 0)
{
    log_warning("FILE ACCESS - Invalid novel ID: {$novel_id} by user: {$user['email']}");
    http_response_code(400);
    $error_message = 'Bad request.';
    \RESP\redirect_with_message($error_message, false, "index.php");
    exit;
}

$novel = \FILE\getPDF($mysqli, $novel_id);

if (!$novel)
{
    log_warning("FILE ACCESS - Novel not found (or is short story): {$novel_id} by user: {$user['email']}");
    http_response_code(404);
    $error_message = 'Novel not found.';
    \RESP\redirect_with_message($error_message, false, "index.php");
    exit;
}

$is_premium_novel = (bool)$novel['is_premium'];
$user_is_premium = ($user['role'] === 'Premium');

if ($is_premium_novel && !$user_is_premium)
{
    log_warning("FILE ACCESS - User is not premium but tried to access premium novel: {$novel_id} by user: {$user['email']}");
    http_response_code(403);
    $error_message = 'Premium pass required.';
    \RESP\redirect_with_message($error_message, false, "index.php");
    exit;
}

$stored_name = $novel['file_stored_name'];
$full_path = UPLOAD_DIR . DIRECTORY_SEPARATOR . $stored_name;

if (file_exists($full_path))
{
    // todo Pulizia dell'output buffer per evitare corruzioni del PDF
    if (ob_get_level())
    {
        ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . addslashes($novel['file_original_name']) . '"');
    header('Content-Length: ' . filesize($full_path));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    readfile($full_path);
    exit;
} else
{
    log_error("FILE ACCESS - File {$stored_name} not found");

    // todo
    header_remove('Content-Type');
    header_remove('Content-Disposition');

    http_response_code(404);
    $error_message = 'File not found.';
    \RESP\redirect_with_message($error_message, false, "index.php");
    exit;
}
