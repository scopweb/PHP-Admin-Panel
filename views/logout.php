<?php

declare(strict_types=1);

require_once __DIR__ . '/../Admin_config/security.php';

init_secure_session();

// Only accept POST requests for logout (CSRF protection)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // Validate CSRF token
    if (validate_csrf_token($_POST['csrf_token'] ?? null)) {
        // Clear all session data
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
    }
}

// Redirect to login page
header('Location: login.php?logged_out');
exit;
