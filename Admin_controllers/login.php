<?php

declare(strict_types=1);

/**
 * Login Controller for PHP 8+
 * Handles authentication with prepared statements and secure password hashing
 */

/**
 * Validate user credentials
 * Returns user data if valid, null otherwise
 */
function validate_login(PDO $conn, string $username, string $password): ?array
{
    $sql = "SELECT * FROM `tb_admin` WHERE `adm_username` = :username AND `adm_status` = 'Active'";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        return null;
    }

    // Verify password using modern hashing
    if (!password_verify($password, $user['adm_password'])) {
        // Fallback: Check if password is still MD5 hashed (for migration)
        if ($user['adm_password'] === md5($password)) {
            // Upgrade password to modern hash
            upgrade_password_hash($conn, (int) $user['adm_Id'], $password);
        } else {
            return null;
        }
    } elseif (password_needs_rehash($user['adm_password'], PASSWORD_DEFAULT)) {
        // Check if password needs rehashing
        upgrade_password_hash($conn, (int) $user['adm_Id'], $password);
    }

    return $user;
}

/**
 * Upgrade MD5 password to modern hash
 */
function upgrade_password_hash(PDO $conn, int $userId, string $password): void
{
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE `tb_admin` SET `adm_password` = :password WHERE `adm_Id` = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'password' => $newHash,
        'id' => $userId,
    ]);
}

/**
 * Check if username exists
 */
function check_user_exists(PDO $conn, string $username): ?array
{
    $sql = "SELECT `adm_Id`, `adm_username`, `adm_email` FROM `tb_admin`
            WHERE `adm_username` = :username AND `adm_status` = 'Active'";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);

    return $stmt->fetch() ?: null;
}

/**
 * Generate password reset token
 */
function create_reset_token(PDO $conn, int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $sql = "UPDATE `tb_admin` SET `reset_token` = :token, `reset_expires` = :expires
            WHERE `adm_Id` = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'token' => $token,
        'expires' => $expires,
        'id' => $userId,
    ]);

    return $token;
}

/**
 * Validate reset token
 */
function validate_reset_token(PDO $conn, string $token): ?array
{
    $sql = "SELECT `adm_Id`, `adm_username` FROM `tb_admin`
            WHERE `reset_token` = :token
            AND `reset_expires` > NOW()
            AND `adm_status` = 'Active'";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['token' => $token]);

    return $stmt->fetch() ?: null;
}

/**
 * Reset password using token
 */
function reset_password_with_token(PDO $conn, string $token, string $newPassword): bool
{
    $user = validate_reset_token($conn, $token);

    if (!$user) {
        return false;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $sql = "UPDATE `tb_admin` SET
            `adm_password` = :password,
            `reset_token` = NULL,
            `reset_expires` = NULL
            WHERE `adm_Id` = :id";

    $stmt = $conn->prepare($sql);

    return $stmt->execute([
        'password' => $hashedPassword,
        'id' => $user['adm_Id'],
    ]);
}

/**
 * Change password for authenticated user
 */
function change_user_password(PDO $conn, int $userId, string $oldPassword, string $newPassword): bool
{
    // First verify old password
    $sql = "SELECT `adm_password` FROM `tb_admin` WHERE `adm_Id` = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    // Verify old password (supports both modern hash and legacy MD5)
    $oldValid = password_verify($oldPassword, $user['adm_password'])
                || $user['adm_password'] === md5($oldPassword);

    if (!$oldValid) {
        return false;
    }

    // Update to new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = "UPDATE `tb_admin` SET `adm_password` = :password WHERE `adm_Id` = :id";

    $stmt = $conn->prepare($sql);

    return $stmt->execute([
        'password' => $hashedPassword,
        'id' => $userId,
    ]);
}
