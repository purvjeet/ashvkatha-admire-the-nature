<?php
/**
 * Database Connection Helper (PDO)
 * Ashvkatha – Admire the Nature
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'ashvkatha_hotel');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed. Please ensure MySQL is running in XAMPP. Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

// Global PDO instance for straightforward script access
$pdo = get_db_connection();
