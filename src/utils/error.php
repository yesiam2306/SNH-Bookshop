<?php

namespace ERROR;

function show_error($message, $code)
{
    global $message_error;
    global $message_code;
    $message_error = htmlspecialchars($message);
    $message_code = $code;
    include __DIR__ . '/../templates/error.php';
    exit;
}

function generic_error($message = "Ops, something went wrong")
{
    $code = 505;
    show_error($message, $code);
}


function not_found($message = "File not found")
{
    $code = 404;
    show_error($message, $code);
}
