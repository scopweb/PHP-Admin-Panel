<?php
declare(strict_types=1);

// Start output buffering to prevent header issues
ob_start();

// Initialize session and security BEFORE any output
require_once __DIR__ . '/../Admin_config/security.php';
