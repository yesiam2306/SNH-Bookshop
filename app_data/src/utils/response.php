<?php

namespace RESP;

/**
 * Imposta un messaggio e reindirizza
 */
function redirect_with_message(string $message, bool $success, string $location)
{
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    $_SESSION['flash'] = [
        'text' => $message,
        'type' => $success ? 'success' : 'error'
    ];
    header("Location: $location");
    exit;
}

/**
 * Mostra il messaggio se presente
 */
function render_flash()
{
    if (isset($_SESSION['flash']))
    {
        $f = $_SESSION['flash'];
        echo "<div class='flash-message {$f['type']}'>{$f['text']}</div>";
        unset($_SESSION['flash']);
    }
}
