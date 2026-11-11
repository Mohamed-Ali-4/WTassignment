<?php
/**
 * Admin authentication + CSRF protection.
 * The admin password comes from the ADMIN_PASSWORD environment variable.
 */

declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('admin_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function admin_password(): string
{
    return (string) (getenv('ADMIN_PASSWORD') ?: '');
}

function is_admin(): bool
{
    start_secure_session();
    return !empty($_SESSION['is_admin']);
}

/** Redirect to the login page unless an admin is signed in. */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Abort the request unless it is a POST carrying the right CSRF token. */
function require_post_with_csrf(): void
{
    start_secure_session();
    $sent = $_POST['csrf'] ?? '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !is_string($sent)
        || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(400);
        exit('Invalid request.');
    }
}

/** Navigation shown in the header of admin pages. */
function admin_nav(): string
{
    return '<form method="post" action="logout.php" class="admin-nav">' . csrf_field()
        . '<a href="index.php">Applications</a>'
        . '<a href="../index.html" target="_blank" rel="noopener">Open form &#8599;</a>'
        . '<button type="submit">Log out</button></form>';
}
