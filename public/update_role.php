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

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    http_response_code(405);
    $error_message = 'Not Allowed';
    \RESP\redirect_with_message($error_message, false, "admin.php");
    exit;
}

if (empty($_POST['userId']) || empty($_POST['newRole']))
{
    log_warning("ADMIN - Missing required parameters for update role.");
    http_response_code(400);
    $error_message = 'Bad request.';
    \RESP\redirect_with_message($error_message, false, "admin.php");
    exit;
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['__csrf'], $_POST['csrf_token']))
{
    log_error("CSRF - Invalid token on role update.");
    header('Location: logout.php');
    exit();
}

$user_id = (int) $_POST['userId'];
$new_role = $_POST['newRole'];

$allowed_roles = ['User', 'Premium', 'Admin'];
if (!in_array($new_role, $allowed_roles))
{
    log_warning("ADMIN - Invalid new role value.");
    http_response_code(400);
    $error_message = 'Invalid new role value.';
    \RESP\redirect_with_message($error_message, false, "admin.php");
    exit;
}

if (!\ADMIN\update_user_role($mysqli, $user_id, $new_role))
{
    http_response_code(500);
    $error_message = 'Internal server error.';
    \RESP\redirect_with_message($error_message, false, "admin.php");
    exit;
}

header('Location: admin.php');
exit;
