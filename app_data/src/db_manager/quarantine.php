<?php

namespace DBM;

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/utils/log.php';

function getQuarantineByEmail($mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT *
                              FROM quarantine
                              WHERE email = ? AND last_attempt > (NOW() - INTERVAL 10 MINUTE)');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        log_error("DB Error: " . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


function getQuarantineByIp($mysqli, $ip)
{
    $stmt = $mysqli->prepare('SELECT *
                              FROM quarantine
                              WHERE ip = ? AND last_attempt > (NOW() - INTERVAL 10 MINUTE)');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        log_error("DB Error: " . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTokenByKey($mysqli, $ip, $email)
{
    $stmt = $mysqli->prepare('SELECT unlock_token
                              FROM quarantine
                              WHERE ip = ? AND email = ?');
    $stmt->bind_param('ss', $ip, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        log_error("DB Error: " . $mysqli->error);
    }

    return $result->fetch_assoc();
}

function insertQuarantine($mysqli, $ip, $email, $unlock_token)
{
    $stmt = $mysqli->prepare('INSERT INTO quarantine (ip, email, unlock_token)
                                VALUES (?, ?, ?) ON DUPLICATE KEY
                                UPDATE attempts = attempts + 1,
                                    last_attempt = NOW(),
                                    unlock_token = VALUES(unlock_token);');
    $stmt->bind_param('sss', $ip, $email, $unlock_token);
    $stmt->execute();
    return true;
}

function deleteQuarantineRecord($mysqli, $ip, $email)
{
    $stmt = $mysqli->prepare('DELETE FROM quarantine WHERE ip = ? AND email = ?;');
    $stmt->bind_param('ss', $ip, $email);
    $stmt->execute();
    return true;
}
