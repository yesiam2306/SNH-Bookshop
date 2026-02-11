<?php

namespace DBM;

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/utils/log.php';

function getUsersForAdmin($mysqli, $email)
{
    $sql = "SELECT user_id, email, role
            FROM users
            WHERE email <> ?
            AND role <> 'pending';";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        log_error("DB Error: " . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function insertAdmin($mysqli, $email, $passhash)
{
    $stmt = $mysqli->prepare("INSERT INTO users (email, passhash, role) VALUES (?, ?, 'admin')");
    $stmt->bind_param('ss', $email, $passhash);
    return $stmt->execute();
}

function searchUserByEmail($mysqli, $email, $query)
{
    $like = '%' . $query . '%';
    $stmt = $mysqli->prepare('SELECT user_id, email, role 
                              FROM users
                              WHERE email LIKE ? AND email <> ? AND role <> "pending"');
    $stmt->bind_param('ss', $like, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        log_error("DB Error: " . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}
