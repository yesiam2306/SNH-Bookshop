<?php

namespace FILE;

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/user/u_auth.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/error.php';
require_once DBM_PATH . '/novels.php';

function check_title($title)
{
    if (empty($title) || strlen($title) > 50)
    {
        log_warning("UPLOAD - Invalid title");
        return false;
    }
    return true;
}

function check_short_content($content)
{
    if (empty($content) || strlen($content) > 200)
    {
        log_warning("UPLOAD - Invalid short content");
        return false;
    }
    return true;
}

function new_short_novel($mysqli, $title, $email, $content, $is_premium)
{
    $rv = \DBM\insertShortNovel($mysqli, $title, $email, $content, $is_premium);
    if (!$rv)
    {
        log_error("UPLOAD - DB insert failed (short)");
        return false;
    }
    return true;
}

function check_size($file_size)
{
    if ($file_size > UPLOAD_MAX_SIZE)
    {
        log_warning("UPLOAD - File too large");
        return false;
    }
    return true;
}

function check_mime($finfo, $tmp_path, $allowed_mime)
{
    $mime = $finfo->file($tmp_path);
    if (!in_array($mime, $allowed_mime))
    {
        log_warning("UPLOAD - Invalid file type");
        return false;
    }
    return true;
}

function check_hash($mysqli, $hash, $email)
{
    $count_files = \DBM\countFiles($mysqli, $email, $hash);
    if (!isset($count_files['c']))
    {
        log_error("UPLOAD - DB error while checking file hash.");
        return false;
    }

    $count = $count_files['c'];
    if ($count !== 0)
    {
        log_warning("UPLOAD - The file already exists.");
        return false;
    }

    return true;
}

function move_file($tmp_path, $dest)
{
    if (!move_uploaded_file($tmp_path, $dest))
    {
        log_error('UPLOAD - Failed to move uploaded file.');
        return false;
    }
    return true;
}

function insertNewFile(
    $mysqli,
    $title,
    $email,
    $is_premium,
    $file_original,
    $stored_name,
    $file_size,
    $hash
) {
    $rv = \DBM\insertNewFile(
        $mysqli,
        $title,
        $email,
        $is_premium,
        $file_original,
        $stored_name,
        $file_size,
        $hash
    );

    if (!$rv)
    {
        log_error('UPLOAD - Failed to move insert the file in the DB.');
        return false;
    }
    return true;

}
