<?php

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'secure'   => true,
  'httponly' => true,
  'samesite' => 'Strict'
]);

session_start();
if (!isset($_SESSION['__initialized']))
{
    session_regenerate_id(true);
    $_SESSION['__initialized'] = true;
}


$TTL = 10 * 60;
if (isset($_SESSION['__last_activity']) && time() - $_SESSION['__last_activity'] > $TTL)
{
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['__csrf']))
{
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

$_SESSION['__last_activity'] = time();
