<?php

namespace USER;

require_once __DIR__ . '/../../config/config.php';
require_once DBM_PATH . '/users.php';
require_once DBM_PATH . '/session.php';
require_once DBM_PATH . '/quarantine.php';
require_once SRC_PATH . '/utils/log.php';
require_once SRC_PATH . '/utils/email.php';

/**
 * Restituisce l'utente corrente.
 * - Se esiste già una sessione valida, restituisce i dati utente da $_SESSION.
 * - Se non c'è sessione, ma c'è cookie remember_me, valida il token e crea nuova sessione.
 * - Se non trova nulla, ritorna null.
 */
function current_user($mysqli)
{
    if (!empty($_SESSION['user_id']))
    {
        log_info("SESSION - User {$_SESSION['user_id']} is logged in via session: ");
        return [
            'user_id'  => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'role'     => $_SESSION['role']
        ];
    }

    if (!empty($_COOKIE['remember_me']))
    {
        $user = get_token($mysqli, $_COOKIE['remember_me']);
        if ($user)
        {
            log_info("SESSION - User {$user['user_id']} is logged in via remember_me token");
            return $user;
        }
    }

    return null;
}


/**
 * Recupera i dati dell'utente usando il token remember me.
 * Se il token è valido, inizializza la sessione PHP.*/
function get_token($mysqli, $cookie)
{
    list($selector, $validator) = explode(':', base64_decode($cookie));

    $token = \DBM\getToken($mysqli, $selector);
    if (!$token || token_is_expired($token))
    {
        log_warning('Token not found or expired');
        clear_remember_cookie();
        return null;
    }

    if (!hash_equals($token['validator_hash'], hash('sha256', $validator)))
    {
        log_error('FATAL - Invalid token. Possible hijack attempt.');
        clear_remember_cookie();
        \DBM\deleteTokensBySelector($mysqli, $selector); // Per invalidare il token rubato
        return null;
    }

    $user = \DBM\getUserById($mysqli, $token['user_id']);
    if (!$user)
    {
        log_warning('Invalid token.');
        debug('FATAL EEROR: user_id in session_tokens non corrisponde a nessun utente.');
        clear_remember_cookie();
        return null;
    }

    session_init($user['user_id'], $user['email'], $user['role']);
    return $user;
}

/**
 * Effettua il login di un utente.
 * Se $remember_me è true, genera un token per creare una sessione persistente.
 * In ogni caso, inizializza la sessione PHP.
 * Restituisce i dati dell'utente loggato. */
function login($mysqli, $email, $password, $remember_me = false)
{
    $salt = \DBM\getSalt($mysqli, $email);
    if (!$salt)
    {
        $msg = "Invalid salt for user $email";
        // debug($msg);
        $msg = 'DB connection failed';
        log_error($msg);
        // die($msg);
        return;
    }

    $passhash = hash('sha256', $salt['salt'] . $password);

    $rv = \DBM\login($mysqli, $email, $passhash);
    if (!$rv)
    {
        $msg = "Invalid credentials for email {$email}";
        log_warning($msg);
        handle_quarantine($mysqli, $_SERVER['REMOTE_ADDR'], $email);
        return;
    }

    log_info("LOGIN - User {$rv['user_id']} logged in successfully");

    if ($remember_me)
    {
        generate_token($mysqli, $rv['user_id']);
    }

    session_init($rv['user_id'], $email, $rv['role'], );
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
    $selector       = bin2hex(random_bytes(8));
    $validator      = bin2hex(random_bytes(32));
    $validator_hash = hash('sha256', $validator);

    setcookie(
        'remember_me',
        base64_encode($selector . ':' . $validator),
        [
            'expires'  => time() + 30 * 24 * 60 * 60, // 1 mese
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

    $rv = \DBM\insertToken($mysqli, $selector, $validator_hash, $user_id);

    if (!$rv)
    {
        $msg = 'DB connection failed';
        log_error($msg);
        die();
    } else
    {
        log_info("LOGIN - Token generated for user_id {$user_id}");
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
function session_init($user_id, $email, $role)
{
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
        session_start();
    }

    session_regenerate_id(true);
    $_SESSION['user_id']         = $user_id;
    $_SESSION['email']           = $email;
    $_SESSION['role']            = $role;
    $_SESSION['__csrf']          = bin2hex(random_bytes(32));
    $_SESSION['__last_activity'] = time();
    $_SESSION['__init_ip']       = $_SERVER['REMOTE_ADDR'];
    $_SESSION['__init_ua']       = $_SERVER['HTTP_USER_AGENT'];
    log_info("SESSION - Session initialized for user: $user_id");
}

function logout($mysqli)
{
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
        $msg = 'DB connection failed';
        log_error($msg);
        $msg = 'Token non cancellato';
        debug($msg);
    } else
    {
        log_info("LOGOUT - User {$user_id} logged out and tokens deleted");
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
    if (ini_get('session.use_cookies'))
    {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax'
        ]);
    }
    session_destroy();
}

function token_is_expired($token)
{
    return (time() > strtotime($token['expires_at']));
}

function signup($mysqli, $email, $password, $role, $token_hash)
{
    $salt     = bin2hex(random_bytes(16));
    $passhash = hash('sha256', $salt . $password);

    $rv = \DBM\getUserByEmail($mysqli, $email);
    if ($rv)
    {
        $msg = 'SIGNUP - Signup failed because email already exists';
        log_warning($msg);
        return false;
    }

    $rv = \DBM\insertUser($mysqli, $email, $passhash, $salt, $role, $token_hash);
    if (!$rv)
    {
        $msg = 'DB connection failed';
        log_error($msg);
        return false;
    }

    log_info("SIGNUP - User $email registered successfully");
    return true;
}

function check_ip_attempts($mysqli, $ip)
{
    $records = \DBM\getQuarantineByIp($mysqli, $ip);
    if (!$records)
    {
        return true;
    }
    $attempts = 0;
    foreach ($records as $record)
    {
        $attempts += $record['attempts'];
    }
    if ($attempts >= 3)
    {
        $msg = "IP {$ip} attempted {$attempts} times for different emails";
        log_warning($msg);
        return false;
    }
    return true;
}

function check_email_attempts($mysqli, $email)
{
    $records = \DBM\getQuarantineByEmail($mysqli, $email);
    if (!$records)
    {
        return true;
    }
    $attempts = 0;
    foreach ($records as $record)
    {
        $attempts += $record['attempts'];
    }
    if ($attempts >= 3)
    {
        $msg = "Email {$email} attempted many times from different IPs";
        log_warning($msg);
        return false;
    }
    return true;
}

function handle_quarantine($mysqli, $ip, $email)
{
    $records = \DBM\getQuarantineByEmail($mysqli, $email);
    $attempts = 0;
    foreach ($records as $record)
    {
        $attempts += $record['attempts'];
    }

    $unlock_token = null;
    $token_hash = null;
    if ($attempts >= 2)
    {
        $unlock_token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $unlock_token);
    }

    $rv = \DBM\insertQuarantine($mysqli, $ip, $email, $token_hash);
    if ($rv)
    {
        log_info("IP {$ip} inserted in quarantine for email {$email}");
    } else
    {
        log_error("Failed to insert quarantine record for email {$email} IP {$ip}");
        return false;
    }

    $rv = \EMAIL\send_unlock_email($email, $unlock_token);
    return $rv;
}

function check_token($mysqli, $email, $token)
{
    $token_hash = hash('sha256', $token);
    $rv = \DBM\getTokenByEmail($mysqli, $email);
    if (!$rv)
    {
        log_warning("CONFIRM - No token found for email {$email}");
        return false;
    }

    $token_stored = $rv['token'];
    if (hash_equals($token_stored, $token_hash))
    {
        log_info("CONFIRM - Token is valid for email {$email}. Account confirmed.");
        return true;
    } else
    {
        log_warning("CONFIRM - Token is not valid for email {$email}");
        return false;
    }
}

function tokenReset($mysqli, $email, $token_hash)
{
    $rv = \DBM\getUserByEmail($mysqli, $email);
    if (!$rv)
    {
        $msg = 'RESET - Token update failed because user does not exist';
        log_warning($msg);
        return false;
    }

    $rv = \DBM\updateUserToken($mysqli, $email, $token_hash);
    if (!$rv)
    {
        $msg = 'DB connection failed';
        log_error($msg);
        return false;
    }

    log_info("RESET - User $email token updated successfully");
    return true;
}

function reset_password($mysqli, $email, $password)
{
    $salt     = bin2hex(random_bytes(16));
    $passhash = hash('sha256', $salt . $password);

    $rv = \DBM\getUserByEmail($mysqli, $email);
    if (!$rv)
    {
        $msg = "RESET - Reset failed because user {$email} does not exist";
        log_warning($msg);
        return false;
    }

    if ($rv['role'] === 'Pending')
    {
        $msg = "RESET - Reset failed because user {$email} is pending confirmation";
        log_warning($msg);
        return false;
    }

    $rv = \DBM\updateUserPassword($mysqli, $email, $passhash, $salt);
    if (!$rv)
    {
        $msg = 'DB connection failed';
        log_error($msg);
        return false;
    }

    log_info("RESET - Password for user $email updated successfully");
    return true;
}

function reset_quarantine($mysqli, $ip, $email)
{
    $rv = \DBM\deleteQuarantineRecord($mysqli, $ip, $email);
    if ($rv)
    {
        log_info("IP {$ip} removed from quarantine for email {$email}");
    } else
    {
        log_error("Failed to delete quarantine record for email {$email} IP {$ip}");
    }
    return $rv;
}

function unlock_token($mysqli, $ip, $email, $token_hash)
{
    $records = \DBM\getQuarantineByEmail($mysqli, $email);
    if (!$records)
    {
        log_warning("UNLOCK - No quarantine records found for email {$email}");
        return false;
    }

    $found = false;
    foreach ($records as $rec)
    {
        if ((!empty($rec['unlock_token'])) && hash_equals($rec['unlock_token'], $token_hash))
        {
            $found = true;
            break;
        }
    }

    if (!$found)
    {
        log_warning("UNLOCK - Token not valid for email {$email} from IP {$ip}");
        return false;
    }

    $rv = reset_quarantine($mysqli, $ip, $email);
    if ($rv)
    {
        log_info("UNLOCK - Email {$email} unlocked successfully via unlock token.");
        return true;
    }
    return false;
}

function become_premium($mysqli, $email)
{
    $rv = \DBM\updateUserRole($mysqli, $email, 'Premium');
    if (!$rv)
    {
        log_error("PREMIUM - Failed to upgrade user {$email}");
        return false;
    }

    log_info("PREMIUM - {$email} successfully upgraded to Premium");

    edit_session("Premium");

    return true;
}

function confirm($mysqli, $email, $role)
{
    $rv = \DBM\updateUserRole($mysqli, $email, $role);
    if (!$rv)
    {
        log_error("CONFIRM - Failed to confirm user {$email}");
        return false;
    }

    log_info("CONFIRM - {$email} successfully confirmed with role {$role}");

    edit_session($role);

    $rv = \DBM\resetToken($mysqli, $email);
    if (!$rv)
    {
        log_error("CONFIRM - Failed to reset token for user {$email}");
        return false;
    }

    return true;
}


function edit_session($role)
{
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
        session_start();
    }

    $_SESSION['role']    = $role;

    log_info("SESSION - Session data updated for user {$_SESSION['email']}");
}
