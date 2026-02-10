<?php

require_once __DIR__ . '/../app_data/config/config.php';
require_once SRC_PATH . '/session_boot.php';
require_once SRC_PATH . '/user/u_auth.php';

\USER\logout($mysqli);

header('Location: login.php');
exit;
