<?php

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'secure'   => $secure,
  'httponly' => true,
  'samesite' => 'Lax'
]);

session_start();
session_regenerate_id(true);

$TTL = 10*60;
if (isset($_SESSION['__last_activity']) && time() - $_SESSION['__last_activity'] > $TTL) {
    session_unset();
    session_destroy();
}
$_SESSION['__last_activity'] = time();
