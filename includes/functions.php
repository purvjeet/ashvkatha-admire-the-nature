<?php
/**
 * Core Helper Functions
 * Ashvkatha – Admire the Nature
 */

if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Return current PDO database connection
 */
function get_db() {
    return get_db_connection();
}

/**
 * HTML Output Escaper for XSS protection
 */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize string input
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return trim(strip_tags((string)$data));
}

/**
 * Retrieve a setting by key with in-memory caching
 */
function get_setting($key, $default = '') {
    static $settings_cache = null;
    $pdo = get_db();

    if ($settings_cache === null) {
        $settings_cache = [];
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $settings_cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // fallback if table is not yet created
            return $default;
        }
    }

    return $settings_cache[$key] ?? $default;
}

/**
 * Format currency using hotel setting
 */
function format_currency($amount) {
    $symbol = get_setting('currency_symbol', '$');
    return $symbol . number_format((float)$amount, 2);
}

/**
 * Format date for friendly luxury display
 */
function format_date($date_str, $format = 'd M Y') {
    if (!$date_str) return '';
    try {
        $dt = new DateTime($date_str);
        return $dt->format($format);
    } catch (Exception $e) {
        return (string)$date_str;
    }
}

/**
 * Calculate total nights between check-in and check-out
 */
function calculate_nights($check_in, $check_out) {
    try {
        $d1 = new DateTime($check_in);
        $d2 = new DateTime($check_out);
        $diff = $d1->diff($d2);
        return ($d1 < $d2) ? (int)$diff->days : 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Check if a specific room is available for requested dates
 * Overlap rule: (existing.check_in < requested_check_out AND existing.check_out > requested_check_in)
 */
function is_room_available($pdo, $room_id, $check_in, $check_out, $exclude_booking_id = null) {
    $sql = "
        SELECT COUNT(*) 
        FROM bookings 
        WHERE room_id = :room_id 
          AND status IN ('pending', 'confirmed', 'checked_in')
          AND check_in < :check_out 
          AND check_out > :check_in
    ";

    $params = [
        ':room_id'   => (int)$room_id,
        ':check_in'  => $check_in,
        ':check_out' => $check_out
    ];

    if ($exclude_booking_id) {
        $sql .= " AND id != :exclude_booking_id";
        $params[':exclude_booking_id'] = (int)$exclude_booking_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $overlapping = (int)$stmt->fetchColumn();

    return $overlapping === 0;
}

/**
 * Find an available physical room for a given room category (room_type_id)
 * Returns the first room ID found or null if sold out
 */
function find_available_room_for_category($pdo, $room_type_id, $check_in, $check_out) {
    // Select all active rooms in this category that do not have overlapping bookings
    $sql = "
        SELECT r.id, r.room_number 
        FROM rooms r
        WHERE r.room_type_id = :room_type_id 
          AND r.status = 'available'
          AND r.id NOT IN (
              SELECT b.room_id 
              FROM bookings b 
              WHERE b.status IN ('pending', 'confirmed', 'checked_in')
                AND b.check_in < :check_out 
                AND b.check_out > :check_in
          )
        ORDER BY r.floor ASC, r.room_number ASC 
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room_type_id' => (int)$room_type_id,
        ':check_in'     => $check_in,
        ':check_out'    => $check_out
    ]);

    $room = $stmt->fetch();
    return $room ? (int)$room['id'] : null;
}

/**
 * Get count of available rooms for a given category and dates
 */
function get_available_room_count_for_category($pdo, $room_type_id, $check_in, $check_out) {
    $sql = "
        SELECT COUNT(*) 
        FROM rooms r
        WHERE r.room_type_id = :room_type_id 
          AND r.status = 'available'
          AND r.id NOT IN (
              SELECT b.room_id 
              FROM bookings b 
              WHERE b.status IN ('pending', 'confirmed', 'checked_in')
                AND b.check_in < :check_out 
                AND b.check_out > :check_in
          )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room_type_id' => (int)$room_type_id,
        ':check_in'     => $check_in,
        ':check_out'    => $check_out
    ]);

    return (int)$stmt->fetchColumn();
}

/**
 * Generate a unique booking confirmation number (e.g. ASHV-20261101-ABCD)
 */
function generate_booking_number() {
    $datePart = date('Ymd');
    $randPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    return "ASHV-{$datePart}-{$randPart}";
}

/**
 * Generate unique transaction reference (e.g. TXN-20261101-XXXX)
 */
function generate_transaction_id($prefix = 'TXN') {
    $datePart = date('Ymd');
    $randPart = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    return "{$prefix}-{$datePart}-{$randPart}";
}

/**
 * CSRF Protection Token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token() {
    return csrf_token();
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set session flash alert message (supports either ($type, $message) or ($message, $type))
 */
function set_flash_message($param1, $param2 = 'info') {
    $standard_types = ['success', 'error', 'info', 'warning'];
    if (in_array($param1, $standard_types, true)) {
        $type = $param1;
        $message = $param2;
    } elseif (in_array($param2, $standard_types, true)) {
        $type = $param2;
        $message = $param1;
    } else {
        $type = 'info';
        $message = $param1;
    }

    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

/**
 * Render Flash Alert Banner HTML
 */
function render_flash_message() {
    $flash = get_flash_message();
    if (!$flash) return '';

    $typeClass = $flash['type'] === 'error' ? 'alert-error' : ($flash['type'] === 'success' ? 'alert-success' : 'alert-info');
    return '
    <div class="flash-alert ' . e($typeClass) . '">
        <div class="flash-content">
            <span class="flash-text">' . e($flash['message']) . '</span>
            <button type="button" class="flash-close" onclick="this.parentElement.parentElement.remove();">&times;</button>
        </div>
    </div>';
}
