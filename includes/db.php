<?php
/**
 * Database Connection Helper (PDO)
 * Ashvkatha – Admire the Nature
 * 
 * Supports:
 * 1. Cloud / Vercel Environment Variables: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT
 * 2. Local XAMPP MySQL defaults (localhost, root, no password)
 * 3. Seamless SQLite fallback (ashvkatha_demo.sqlite) when MySQL is not available or for instant Vercel previews
 */

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'ashvkatha_hotel');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_CHARSET')) define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $sqlite_file = dirname(__DIR__) . '/database/ashvkatha_demo.sqlite';

        // Check if DB_DRIVER is explicitly set to sqlite
        if (getenv('DB_DRIVER') === 'sqlite' && file_exists($sqlite_file)) {
            $pdo = new PDO("sqlite:" . $sqlite_file);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        }

        // Attempt MySQL connection
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If MySQL is unreachable (e.g. running on Vercel without cloud DB, or local MySQL is off),
            // gracefully fall back to the built-in SQLite demo database
            if (file_exists($sqlite_file)) {
                try {
                    $pdo = new PDO("sqlite:" . $sqlite_file);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    return $pdo;
                } catch (PDOException $sqle) {
                    // Fall through to error
                }
            }
            die("Database connection failed. Please ensure MySQL is running in XAMPP or configure cloud DB environment variables. Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

// Global PDO instance for straightforward script access
$pdo = get_db_connection();
