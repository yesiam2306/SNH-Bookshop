<?php

require_once __DIR__ . '/../../config/config.php';

function insertAdmin($mysqli, $email, $passhash)
{
    $stmt = $mysqli->prepare("INSERT INTO users (email, passhash, role) VALUES (?, ?, 'admin')");
    $stmt->bind_param('ss', $email, $passhash);
    return $stmt->execute();
}
