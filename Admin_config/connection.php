<?php

declare(strict_types=1);

/**
 * Database Connection Configuration for PHP 8+
 * Uses PDO for secure database operations
 */

date_default_timezone_set('Asia/Kolkata');

// Database configuration - Use environment variables in production
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
    'name' => $_ENV['DB_NAME'] ?? 'your_db_name',
    'charset' => 'utf8mb4',
];

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db_config['host'],
        $db_config['name'],
        $db_config['charset']
    );

    $pdo_options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    $conn = new PDO($dsn, $db_config['user'], $db_config['pass'], $pdo_options);
} catch (PDOException $e) {
    // Log error in production, don't expose details
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

// Current datetime for queries
$date_time = date('Y-m-d H:i:s');
