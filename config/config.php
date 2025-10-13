<?php

define("BASE_PATH", __DIR__ . "/..");
define("SRC_PATH", BASE_PATH . "/src");
define("DBM_PATH", SRC_PATH . "/db_manager");
define("LOG_PATH", BASE_PATH . "/logs");

// === UPLOAD SETTINGS ===
define('UPLOAD_DIR', realpath(__DIR__ . '/../uploads'));
define('UPLOAD_MAX_SIZE', 50 * 1024 * 1024); // 50 MB
define('UPLOAD_ALLOWED_MIME', ['application/pdf']);

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "SNH_Proj";

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error)
{
    die("Connessione fallita: " . $mysqli->connect_error);
}

function debug($var, $label = '')
{
    echo "<pre>";
    if ($label)
    {
        echo "$label:\n";
    }
    echo htmlspecialchars(print_r($var, true));
    echo "</pre>";
}
