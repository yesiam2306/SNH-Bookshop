<?php
namespace USER;
require_once __DIR__ . '/../../config/config.php';
require_once DBM_PATH . '/users.php';
require_once DBM_PATH . '/session.php';
require_once SRC_PATH . "/utils/log.php";

/**
 * Restituisce l'utente corrente.
 * - Se esiste già una sessione valida, restituisce i dati utente da $_SESSION.
 * - Se non c'è sessione, ma c'è cookie remember_me, valida il token e crea nuova sessione.
 * - Se non trova nulla, ritorna null.
 */
function current_user($mysqli) {
    if (!empty($_SESSION['user_id'])) {
        log_info("SESSION - User {$_SESSION['user_id']} is logged in via session: ");
        return [
            'user_id'  => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['role']
        ];
    }

    if (!empty($_COOKIE['remember_me'])) {
        $user = get_token($mysqli, $_COOKIE['remember_me']);
        if ($user) {
        log_info("SESSION - User {$user['user_id']} is logged in via remember_me token");
            return $user; 
        }
    }

    return null;
}


/**
 * Recupera i dati dell'utente usando il token remember me.
 * Se il token è valido, inizializza la sessione PHP.*/
function get_token($mysqli, $cookie) {
    list($selector, $validator) = explode(':', base64_decode($cookie));

    $token = \DBM\getToken($mysqli, $selector);
    if (!$token || token_is_expired($token)) {
        log_warning("Token not found or expired");
        clear_remember_cookie();
        return null; 
    }

    if(!hash_equals($token['validator_hash'], hash('sha256', $validator))) {
        log_error("FATAL - Invalid token. Possible hijack attempt.");
        clear_remember_cookie();
        \DBM\deleteTokensBySelector($mysqli, $selector); // Per invalidare il token rubato
        return null;
    }

    $user = \DBM\getUserById($mysqli, $token['user_id']);
    if (!$user) 
    {
        log_warning("Invalid token.");
        debug("FATAL EEROR: user_id in session_tokens non corrisponde a nessun utente.");
        clear_remember_cookie();
        return null;
    }

    session_init($user['user_id'], $user['username'], $user['role']);
    return $user;
}

/**
 * Effettua il login di un utente. 
 * Se $remember_me è true, genera un token per creare una sessione persistente.
 * In ogni caso, inizializza la sessione PHP.
 * Restituisce i dati dell'utente loggato. */
function login($mysqli, $username, $password, $remember_me = false) {
    $salt = \DBM\getSalt($mysqli, $username);
    if (!$salt) 
    {
        $msg = "Invalid salt for user $username";
        debug($msg);
        $msg = "DB connection failed";
        log_error($msg);
        die($msg);
    }

    $passhash = hash('sha256', $salt['salt'] . $password);

    $rv = \DBM\login($mysqli, $username, $passhash);
    if (!$rv) {
        $msg = "Invalid credentials for username $username";
        log_warning($msg);
        // TODO: credo che qui vada messa la routine per bloccare l'utente dopo N tentativi falliti
        return null;
    }

    log_info("LOGIN - User {$rv['user_id']} logged in successfully");

    if ($remember_me) {
        generate_token($mysqli, $rv['user_id']);
    }

    session_init($rv['user_id'], $username, $rv['role'],);
    // TODO: mysqli, LOG
    return $rv;
}


/** 
 * Genera un token sicuro per il "ricordami" e lo salva nel database.
 * Imposta un cookie di lunga durata nel browser dell'utente.
 * Nel database, salva solo l'hash del validator in modo che se un attaccante
 * ruba il database, non possa usarlo per autenticarsi.
 * Nel cookie invece salva sia il selector che il validator. Quando l'utente ritorna,
 * il server cerca il selector nel database e confronta l'hash del validator. */
function generate_token($mysqli, $user_id) 
{
    $selector  = bin2hex(random_bytes(8));
    $validator = bin2hex(random_bytes(32));
    $validator_hash = hash('sha256', $validator);

    setcookie('remember_me', 
                base64_encode($selector . ':' . $validator), 
            [
                'expires'  => time() + 30*24*60*60, // 1 mese
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']), 
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        
    $rv = \DBM\insertToken($mysqli, $selector, $validator_hash, $user_id);

    if (!$rv) 
    {
        $msg = "DB connection failed";
        log_error($msg);
        die();
    }
    else
    {
        log_info("LOGIN - Token generated for user_id $user_id");
    }
}

/** 
 * Inizializza la sessione PHP con i dati dell'utente.
 * session_start() prende una sessione esistente o ne crea una nuova.
 * session_regenerate_id(true) per prevenire session fixation.
 * __csrf: token per protezione CSRF
 * __last_activity: timestamp dell'ultima attività per timeout automatico
 * __init_ip: indirizzo IP iniziale per prevenire hijacking
 * __init_ua: user agent iniziale per prevenire hijacking */
function session_init($user_id, $username, $role) {
    session_start();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
    $_SESSION['__last_activity'] = time();
    $_SESSION['__init_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['__init_ua'] = $_SERVER['HTTP_USER_AGENT'];
    log_info("SESSION - Session initialized for user: $user_id");
}

function logout($mysqli) {
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['user_id'])) 
    {
        return;
    }

    $user_id = $_SESSION['user_id'];
    clear_remember_cookie();
    clear_session();

    $rv = \DBM\deleteTokensByUserId($mysqli, $user_id);
    if (!$rv) 
    {
        $msg = "DB connection failed";
        log_error($msg);
        $msg = "Token non cancellato";
        debug($msg);
    }
    else
    {
        log_info("LOGOUT - User $user_id logged out and tokens deleted");
    }
}   

function clear_remember_cookie() 
{
    // Cancella cookie remember_me
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']), 
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clear_session() 
{
    // Cancella sessione PHP
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $params["path"],
            'domain'   => $params["domain"],
            'secure'   => $params["secure"],
            'httponly' => $params["httponly"],
            'samesite' => 'Lax'
        ]);
    }
    session_destroy();
}

function token_is_expired($token) {
    return (time() > strtotime($token['expires_at']));
}

function signup($mysqli, $username, $password) {
    $salt = bin2hex(random_bytes(16));
    $passhash = hash('sha256', $salt . $password);

    $rv = \DBM\getUserByUsername($mysqli, $username);
    if ($rv) {
        $msg = "SIGNUP - Signup failed because username already exists";
        log_warning($msg);
        return null;
    }

    $rv = \DBM\insertUser($mysqli, $username, $passhash, $salt);
    if (!$rv) {
        $msg = "DB connection failed";
        log_error($msg);
        return null;
    }

    log_info("SIGNUP - User $username registered successfully");
    return login($mysqli, $username, $password, false);
}

?>