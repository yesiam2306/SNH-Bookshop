<?php

namespace VALIDATOR;

const ALLOWED_SYMBOLS = '!\"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';

function sanitize_string(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitize_email(string $email): ?string
{
    $email = trim($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function validate_password(string $password, string $email = ''): array
{
    $errors = [];

    if (strlen($password) < 12)
    {
        $errors[] = "Password must be at least 12 characters long.";
    }
    if (!preg_match('/[a-z]/', $password))
    {
        $errors[] = "Password must include a lowercase letter.";
    }
    if (!preg_match('/[A-Z]/', $password))
    {
        $errors[] = "Password must include an uppercase letter.";
    }
    if (!preg_match('/[0-9]/', $password))
    {
        $errors[] = "Password must include a number.";
    }
    if (!preg_match('/[' . preg_quote(ALLOWED_SYMBOLS, '/') . ']/', $password))
    {
        $errors[] = "Password must include at least one special character (" . ALLOWED_SYMBOLS . ").";
    }
    if ($email && stripos($password, strtok($email, '@')) !== false)
    {
        $errors[] = "Password should not contain part of the email.";
    }

    return $errors;
}
