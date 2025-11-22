<?php

declare(strict_types=1);

/**
 * Security Helper Functions for PHP 8+
 * Provides CSRF protection, XSS sanitization, and session management
 */

/**
 * Initialize secure session with proper settings
 */
function init_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');

        session_start();
    }
}

/**
 * Regenerate session ID (call after login)
 */
function regenerate_session(): void
{
    session_regenerate_id(true);
}

/**
 * Generate CSRF token and store in session
 */
function generate_csrf_token(): string
{
    init_secure_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from POST request
 */
function validate_csrf_token(?string $token): bool
{
    init_secure_session();

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden input field
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

/**
 * Sanitize output for HTML context (XSS protection)
 */
function escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generate secure random token for password reset
 */
function generate_reset_token(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Hash password using modern algorithm
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Check if password hash needs rehashing
 */
function needs_rehash(string $hash): bool
{
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}

/**
 * Redirect with proper exit
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Show alert and redirect using JavaScript
 */
function alert_redirect(string $message, string $url): never
{
    $safe_message = escape($message);
    $safe_url = escape($url);

    echo "<script type='text/javascript'>alert('{$safe_message}');";
    echo "window.location.href = '{$safe_url}';</script>";
    exit;
}

/**
 * Check if user is authenticated
 */
function is_authenticated(): bool
{
    init_secure_session();
    return isset($_SESSION['adm_Id']) && !empty($_SESSION['adm_Id']);
}

/**
 * Require authentication or redirect to login
 */
function require_auth(): void
{
    if (!is_authenticated()) {
        redirect('login.php');
    }
}

/**
 * Get authenticated user data from session
 */
function get_authenticated_user(): array
{
    init_secure_session();
    return $_SESSION['user_data'] ?? [];
}

// Auto-initialize secure session when this file is included
init_secure_session();
