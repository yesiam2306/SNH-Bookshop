<?php

namespace DBM;

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/utils/log.php';

function getNovelById($mysqli, $novel_id)
{
    $stmt = $mysqli->prepare('SELECT *
                              FROM novels
                              WHERE novel_id = ?');
    $stmt->bind_param('i', $novel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_assoc();
}

function getNovelByTitle($mysqli, $title, $email, $is_short)
{
    $stmt = $mysqli->prepare('SELECT COUNT(*) AS c
                              FROM novels
                              WHERE title = ? AND email = ? AND is_short = ?');
    $stmt->bind_param('ssi', $title, $email, $is_short);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_assoc();
}

function getContentById($mysqli, $novel_id)
{
    $stmt = $mysqli->prepare('SELECT content
                              FROM novels
                              WHERE novel_id = ? AND is_short = 1;');
    $stmt->bind_param('i', $novel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_assoc();
}

function getAllNovels($mysqli)
{
    $stmt = $mysqli->prepare('SELECT *
                              FROM novels');
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAllNovelsByOthers($mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT title, email, is_short, is_premium, content, file_original_name, file_stored_name
                              FROM novels
                              WHERE email <> ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function searchNovelsByTitle($mysqli, $email, $query)
{
    $like = '%' . $mysqli->real_escape_string($query) . '%';
    $stmt = $mysqli->prepare('SELECT * 
                              FROM novels
                              WHERE email <> ?
                              AND title LIKE ?');
    $stmt->bind_param('ss', $email, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Query error: ' . $mysqli->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


function insertShortNovel($mysqli, $title, $email, $content, $is_premium)
{
    $stmt = $mysqli->prepare('INSERT INTO novels (title, email, content, is_premium) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $title, $email, $content, $is_premium);
    return $stmt->execute();
}


function insertNewFile(
    $mysqli,
    $title,
    $email,
    $is_premium,
    $file_original_name,
    $file_stored_name,
    $file_size,
    $file_hash
) {
    $stmt = $mysqli->prepare('INSERT INTO novels (title, 
                                                email, 
                                                is_premium, 
                                                file_original_name,
                                                file_stored_name, 
                                                file_size, 
                                                file_hash, 
                                                is_short) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, 0)');
    $stmt->bind_param('ssissis', $title, $email, $is_premium, $file_original_name, $file_stored_name, $file_size, $file_hash);
    return $stmt->execute();
}

function countFiles($mysqli, $email, $hash)
{
    $stmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM novels WHERE email = ? AND file_hash = ?');
    $stmt->bind_param('ss', $email, $hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result)
    {
        die('Errore nella query: ' . $mysqli->error);
    }

    return $result->fetch_assoc();
}
