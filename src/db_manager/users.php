<?php

namespace DBM;

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/utils/log.php';

function getUserById($mysqli, $user_id)
{
    $stmt = $mysqli->prepare('SELECT user_id, email, role
                              FROM users
                              WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_assoc();
}

function getUserByEmail($mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT user_id, email, role
                              FROM users
                              WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

// function getUsers($mysqli)
// {
//     $sql = "SELECT *
//             FROM users
//             LIMIT 1";
//     $result = $mysqli->query($sql);

//     if (!$result)
//     {
//         die("Errore nella query: " . $mysqli->error);
//     }

//     return $result->fetch_assoc();
// }

function insertUser($mysqli, $email, $passhash, $salt)
{
    $stmt = $mysqli->prepare('INSERT INTO users (email, passhash, salt) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $email, $passhash, $salt);
    return $stmt->execute();
}


function getSalt($mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT salt 
                              FROM users 
                              WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function login($mysqli, $email, $passhash)
{
    $stmt = $mysqli->prepare('SELECT user_id, role
                              FROM users
                              WHERE email = ? AND passhash = ?');

    $stmt->bind_param('ss', $email, $passhash);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_assoc();
}
