<?php

declare(strict_types=1);

/**
 * Login Validation Module for PHP 8+
 * Handles form submissions with CSRF protection and secure authentication
 */

require_once __DIR__ . '/../Admin_config/connection.php';
require_once __DIR__ . '/../Admin_config/security.php';
require_once __DIR__ . '/../Admin_controllers/login.php';

// Handle Login Form
if (isset($_POST['submit'])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        alert_redirect('Invalid request. Please try again.', '../views/login.php');
    }

    $username = trim($_POST['mail'] ?? '');
    $password = $_POST['pass'] ?? '';

    if (empty($username) || empty($password)) {
        alert_redirect('Please fill in all fields.', '../views/login.php');
    }

    $result = validate_login($conn, $username, $password);

    if ($result !== null) {
        init_secure_session();
        regenerate_session();

        // Store user data in session
        $_SESSION['adm_Id'] = $result['adm_Id'];
        $_SESSION['adm_name'] = $result['adm_name'];
        $_SESSION['adm_username'] = $result['adm_username'];
        $_SESSION['adm_email'] = $result['adm_email'] ?? '';
        $_SESSION['user_data'] = $result;

        redirect('../views/index.php');
    } else {
        alert_redirect('Invalid Credentials', '../views/login.php?wrong_credentials');
    }
}

// Handle Forgot Password Form
if (isset($_POST['forget'])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        alert_redirect('Invalid request. Please try again.', '../views/forgot-password.php');
    }

    $username = trim($_POST['username'] ?? '');

    if (empty($username)) {
        alert_redirect('Please enter your username.', '../views/forgot-password.php');
    }

    $result = check_user_exists($conn, $username);

    if ($result !== null) {
        // Generate secure reset token
        $token = create_reset_token($conn, (int) $result['adm_Id']);

        // In production, send this token via email
        // For now, redirect with token (should be sent by email in production)
        redirect('../views/resetpassword.php?token=' . urlencode($token));
    } else {
        // Use same message to prevent username enumeration
        alert_redirect('If the user exists, a reset link will be sent.', '../views/forgot-password.php');
    }
}

// Handle Password Reset Form
if (isset($_POST['reset'])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        alert_redirect('Invalid request. Please try again.', '../views/login.php');
    }

    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new'] ?? '';
    $confirmPassword = $_POST['confirm'] ?? '';

    if (empty($token)) {
        alert_redirect('Invalid reset link.', '../views/login.php');
    }

    if (empty($newPassword) || empty($confirmPassword)) {
        alert_redirect('Please fill in all fields.', '../views/resetpassword.php?token=' . urlencode($token));
    }

    if ($newPassword !== $confirmPassword) {
        alert_redirect('Passwords did not match.', '../views/resetpassword.php?token=' . urlencode($token));
    }

    if (strlen($newPassword) < 8) {
        alert_redirect('Password must be at least 8 characters.', '../views/resetpassword.php?token=' . urlencode($token));
    }

    if (reset_password_with_token($conn, $token, $newPassword)) {
        alert_redirect('Password changed successfully!', '../views/login.php?password_changed');
    } else {
        alert_redirect('Invalid or expired reset link.', '../views/forgot-password.php');
    }
}

// Handle Change Password Form (for authenticated users)
if (isset($_POST['change'])) {
    init_secure_session();

    // Check if user is authenticated
    if (!is_authenticated()) {
        redirect('../views/login.php');
    }

    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        alert_redirect('Invalid request. Please try again.', '../views/changepass.php');
    }

    $oldPassword = $_POST['old'] ?? '';
    $newPassword = $_POST['new'] ?? '';
    $confirmPassword = $_POST['confirm'] ?? '';

    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        alert_redirect('Please fill in all fields.', '../views/changepass.php');
    }

    if ($newPassword !== $confirmPassword) {
        alert_redirect('New passwords did not match.', '../views/changepass.php');
    }

    if (strlen($newPassword) < 8) {
        alert_redirect('Password must be at least 8 characters.', '../views/changepass.php');
    }

    $userId = (int) $_SESSION['adm_Id'];

    if (change_user_password($conn, $userId, $oldPassword, $newPassword)) {
        alert_redirect('Password changed successfully!', '../views/index.php');
    } else {
        alert_redirect('Old password is incorrect.', '../views/changepass.php');
    }
}
