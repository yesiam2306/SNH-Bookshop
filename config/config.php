<?php

// === CREDENZIALI SERVIZIO EMAIL ===
$private = __DIR__ . '/config_private.php';
if (file_exists($private))
{
    require_once $private;
} else
{
    die("File config_private.php mancante — crea una copia locale con le tue credenziali.");
}


define("BASE_PATH", __DIR__ . "/..");
define("SRC_PATH", BASE_PATH . "/src");
define("DBM_PATH", SRC_PATH . "/db_manager");
define("LOG_PATH", BASE_PATH . "/logs");
define("EMAIL_FILE", BASE_PATH . '/vendor/autoload.php');

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

// === EMAIL SETTINGS ===
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_FROM_NAME', 'SNH Project');
define('MAIL_SECURE', 'tls');
define('SITE_BASE', 'http://localhost/SNH_Proj');

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
