<?php
/**
 * Configuration Example
 * 
 * Copy this file to config.php and customize the values for your environment.
 * 
 * For production, it's recommended to use environment variables instead.
 */

// Database Configuration
// These can be set via environment variables: DB_HOST, DB_NAME, DB_USER, DB_PASS
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'real_estate';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

// Create PDO connection
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $pdo = null;
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Base URL configuration (auto-detected)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = basename(dirname(__DIR__));

define('BASE_URL', "$protocol://$host/$project_folder/");
define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));

// Site configuration
define('SITE_NAME', 'Real Estate Platform');
define('SITE_EMAIL', 'contact@example.com');
define('DEFAULT_CURRENCY', 'USD');
define('ITEMS_PER_PAGE', 12);

// File upload configuration
define('UPLOAD_DIR', BASE_PATH . '/public/images/propiedades/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Email configuration (for contact forms)
// Configure these for your SMTP server
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.example.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'user@example.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@example.com');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Set to '0' in production
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/php_errors.log');

// Timezone
date_default_timezone_set('UTC'); // Change to your timezone