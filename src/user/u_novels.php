<?php

namespace USER;

require_once __DIR__ . '/../../config/config.php';
require_once DBM_PATH . '/users.php';
require_once DBM_PATH . '/novels.php';
require_once SRC_PATH . '/utils/log.php';

function show_catalog($mysqli, $email)
{
    $rv = \DBM\getAllNovelsByOthers($mysqli, $email);
    if (!$rv)
    {
        log_warning("CATALOG - no novels retrieved.");
    }
    return $rv;
}

function upload_novel($mysqli, $title, $email, $content, $is_premium)
{
    $rv = \DBM\insertShortNovel($mysqli, $title, $email, $content, $is_premium);
    if (!$rv)
    {
        log_error("CATALOG - Upload failed.");
        return false;
    }
    return true;
}

// function show_content($mysqli, $novel_id)
// {
//     $rv = \DBM\getContentById($mysqli, $novel_id);
//     if (!$rv)
//     {
//         log_error("CATALOG - Novel {$novel_id} not found.");
//         return null;
//     }

//     return $rv;
// }

/* Funzione di debug per creare 100 novel */
function create_catalog($mysqli)
{
    for ($i = 0; $i < 100; $i++)
    {
        $is_premium = rand(0, 1);
        $title = 'title ' . $i;
        $content = "Ciao, sto facendo questo progetto di SNH e mi sto divertendo moltissimo. è molto interessante e spero di finire presto e di prendere una buona valutazione." . $i;
        $content_hash = hash('sha256', $content);
        $rv = \DBM\insertShortNovel($mysqli, $title, 'b@gmail.com', $content_hash, $is_premium);
        if (!$rv)
        {
            log_error("CATALOG - Upload failed.");
            return false;
        }
    }
    return true;
}

function search_catalog($mysqli, $email, $query)
{
    return \DBM\searchNovelsByTitle($mysqli, $email, $query);
}
