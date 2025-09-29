<?php

namespace DBM;

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/utils/log.php';

function insertToken($mysqli, $selector, $validator_hash, $user_id)
{
    $stmt = $mysqli->prepare('INSERT INTO session_tokens (selector, validator_hash, user_id) VALUES (?, ?, ?)');
    $stmt->bind_param('ssi', $selector, $validator_hash, $user_id);
    return $stmt->execute();
}

function getToken($mysqli, $selector)
{
    $stmt = $mysqli->prepare('SELECT * FROM session_tokens WHERE selector = ?');
    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function deleteTokensByUserId($mysqli, $user_id)
{
    $stmt = $mysqli->prepare('DELETE FROM session_tokens WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    return $stmt->execute();
}

function deleteTokensBySelector($mysqli, $selector)
{
    $stmt = $mysqli->prepare('DELETE FROM session_tokens WHERE selector = ?');
    $stmt->bind_param('s', $selector);
    return $stmt->execute();
}
