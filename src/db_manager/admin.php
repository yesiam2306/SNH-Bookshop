<?php

require_once __DIR__ . '/../../config/config.php';

function insertAdmin($mysqli, $username, $passhash)
{
    $stmt = $mysqli->prepare("INSERT INTO users (username, passhash, role) VALUES (?, ?, 'admin')");
    $stmt->bind_param('ss', $username, $passhash);
    return $stmt->execute();
}
