<?php
/**
 * Authentication and Session Management
 * Ashvkatha – Admire the Nature
 */

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
    }
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the current user has the admin role
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Retrieve current user session details
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? 'Guest',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'customer'
    ];
}

/**
 * Require customer login, otherwise redirect to login page
 */
function require_login($redirect_url = null) {
    if (!is_logged_in()) {
        $target = $redirect_url ? urlencode($redirect_url) : urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.php?redirect=" . $target);
        exit;
    }
}

/**
 * Require administrator login, otherwise redirect to admin login
 */
function require_admin($admin_login_url = null) {
    if (!is_admin()) {
        $url = $admin_login_url ?: 'index.php?error=unauthorized';
        header("Location: " . $url);
        exit;
    }
}

/**
 * Log in a user by populating session variables
 */
function login_user($user) {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int)$user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
}

/**
 * Log out user and destroy session safely
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
