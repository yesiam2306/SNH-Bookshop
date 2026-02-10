<?php

namespace ADMIN;

require_once __DIR__ . '/../../config/config.php';
require_once DBM_PATH . '/admin.php';
require_once SRC_PATH . '/utils/log.php';


function search_user($mysqli, $email, $query)
{
    $rv = \DBM\searchUserByEmail($mysqli, $email, $query);
    if (!$rv)
    {
        log_warning("ADMIN - no users found.");
    }
    return $rv;
}

function show_users($mysqli, $email)
{
    $rv = \DBM\getUsersForAdmin($mysqli, $email);
    if (!$rv)
    {
        log_warning("ADMIN - no users retrieved.");
    }
    return $rv;
}

function update_user_role($mysqli, $user_id, $role)
{
    $rv = \DBM\updateUserRole($mysqli, $user_id, $role);
    if (!$rv)
    {
        log_error("ADMIN - Failed to update user role for user ID {$user_id}");
        return false;
    }
    return true;
}
