<?php

function log_event($level, $message)
{
    $logFile = __DIR__ . '/../../logs/security.log';
    $time    = date('Y-m-d H:i:s');
    $ip      = $_SERVER['REMOTE_ADDR']     ?? 'unknown';
    $ua      = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $uid     = $_SESSION['user_id']        ?? '-';

    $line = "$time [$level] (uid=$uid) - $message | IP=$ip | UA=$ua" . PHP_EOL;

    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function log_info($message)
{
    log_event('INFO', $message);
}

function log_warning($message)
{
    log_event('WARNING', $message);
}

function log_error($message)
{
    log_event('ERROR', $message);
}
