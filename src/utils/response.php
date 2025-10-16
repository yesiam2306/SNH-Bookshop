<?php

namespace RESP;

global $resp_code, $resp_message, $resp_redirect;
$resp_code = 200;
$resp_message = '';
$resp_redirect = 'index.php';

function set_response($code, $message, $redirect = 'index.php')
{
    global $resp_code, $resp_message, $resp_redirect;
    $resp_code = $code;
    $resp_message = $message;
    $resp_redirect = $redirect;
}

function get_response()
{
    global $resp_code, $resp_message, $resp_redirect;
    return [
        'code' => $resp_code,
        'message' => $resp_message,
        'redirect' => $resp_redirect
    ];
}

function reset_response()
{
    global $resp_code, $resp_message, $resp_redirect;
    $resp_code = 200;
    $resp_message = '';
    $resp_redirect = 'index.php';
}

function get_and_reset()
{
    [$code, $message, $redirect] = get_response();
    reset_response();
    return[
        'code' => $code,
        'message' => $message,
        'redirect' => $redirect
    ];
}
