<?php
/**
 * Booking Processing Action - actions/booking-action.php
 * Handles MySQL Transaction, double-booking prevention, service attachments, and payment logging
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/../includes/functions.php';

require_login();
$user = current_user();
$pdo  = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../rooms.php");
    exit;
}

// 1. Verify CSRF Token
$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    set_flash_message('error', 'Security session expired. Please resubmit your booking.');
    header("Location: ../rooms.php");
    exit;
}

// 2. Collect and sanitize input
$room_type_id     = (int)($_POST['room_type_id'] ?? 0);
$check_in         = sanitize_input($_POST['check_in'] ?? '');
$check_out        = sanitize_input($_POST['check_out'] ?? '');
$adults           = max(1, (int)($_POST['adults'] ?? 1));
$children         = max(0, (int)($_POST['children'] ?? 0));
$special_requests = sanitize_input($_POST['special_requests'] ?? '');
$payment_method   = sanitize_input($_POST['payment_method'] ?? 'Online Payment');
$selected_services= $_POST['services'] ?? [];

$today = date('Y-m-d');

// 3. Validation
$errors = [];

if (empty($check_in) || empty($check_out)) {
    $errors[] = "Please select both check-in and check-out dates.";
} elseif ($check_in < $today) {
    $errors[] = "Check-in date cannot be in the past.";
} elseif ($check_out <= $check_in) {
    $errors[] = "Check-out date must be after your check-in date.";
}

$nights = calculate_nights($check_in, $check_out);
if ($nights < 1) {
    $errors[] = "Minimum reservation stay is 1 night.";
}

$total_guests = $adults + $children;

// Validate Room Type
$typeStmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ? AND status = 'active'");
$typeStmt->execute([$room_type_id]);
$room_type = $typeStmt->fetch();

if (!$room_type) {
    $errors[] = "Selected sanctuary category does not exist.";
} elseif ($total_guests > $room_type['capacity']) {
    $errors[] = "Total guests ({$total_guests}) exceeds category capacity ({$room_type['capacity']} guests).";
}

if (!empty($errors)) {
    set_flash_message('error', implode(' ', $errors));
    header("Location: ../booking.php?room_type=" . $room_type_id . "&check_in=" . urlencode($check_in) . "&check_out=" . urlencode($check_out));
    exit;
}

// 4. Begin MySQL Transaction to avoid race condition double-bookings
try {
    $pdo->beginTransaction();

    // Check availability with physical room allocation
    $allocated_room_id = find_available_room_for_category($pdo, $room_type_id, $check_in, $check_out);

    if (!$allocated_room_id) {
        $pdo->rollBack();
        set_flash_message('error', "Sorry, all rooms in {$room_type['name']} were reserved moments ago. Please choose alternative dates or categories.");
        header("Location: ../rooms.php");
        exit;
    }

    // Double-check physical room availability
    if (!is_room_available($pdo, $allocated_room_id, $check_in, $check_out)) {
        $pdo->rollBack();
        set_flash_message('error', "Room conflict detected. Please select alternative dates.");
        header("Location: ../rooms.php");
        exit;
    }

    // Calculate room charges
    $room_total = (float)$room_type['base_price'] * $nights;

    // Calculate selected add-on services
    $services_total = 0.00;
    $valid_services = [];

    if (!empty($selected_services) && is_array($selected_services)) {
        $inPlaceholders = implode(',', array_fill(0, count($selected_services), '?'));
        $svcStmt = $pdo->prepare("SELECT * FROM services WHERE id IN ($inPlaceholders) AND status = 'active'");
        $svcStmt->execute(array_map('intval', $selected_services));
        $valid_services = $svcStmt->fetchAll();

        foreach ($valid_services as $vSvc) {
            $services_total += (float)$vSvc['price'];
        }
    }

    // Calculate tax & grand total
    $tax_percent = (float)get_setting('tax_rate_percent', '12');
    $subtotal_with_services = $room_total + $services_total;
    $tax_amount = ($subtotal_with_services * $tax_percent) / 100;
    $grand_total = $subtotal_with_services + $tax_amount;

    // Generate unique booking number
    $booking_number = generate_booking_number();

    // Insert Booking Record
    $bookingInsert = $pdo->prepare("
        INSERT INTO bookings (
            booking_number, user_id, room_id, check_in, check_out, 
            adults, children, total_guests, total_amount, status, special_requests
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 'confirmed', ?
        )
    ");
    $bookingInsert->execute([
        $booking_number,
        $user['id'],
        $allocated_room_id,
        $check_in,
        $check_out,
        $adults,
        $children,
        $total_guests,
        $grand_total,
        $special_requests
    ]);

    $booking_id = (int)$pdo->lastInsertId();

    // Attach Booking Services
    if (!empty($valid_services)) {
        $bsInsert = $pdo->prepare("
            INSERT INTO booking_services (booking_id, service_id, quantity, unit_price, total_price)
            VALUES (?, ?, 1, ?, ?)
        ");
        foreach ($valid_services as $sRow) {
            $bsInsert->execute([
                $booking_id,
                $sRow['id'],
                $sRow['price'],
                $sRow['price']
            ]);
        }
    }

    // Insert Payment Record
    $transaction_id = generate_transaction_id('PAY');
    $paymentInsert = $pdo->prepare("
        INSERT INTO payments (
            booking_id, user_id, amount, payment_method, transaction_id, payment_status
        ) VALUES (
            ?, ?, ?, ?, ?, 'completed'
        )
    ");
    $paymentInsert->execute([
        $booking_id,
        $user['id'],
        $grand_total,
        $payment_method,
        $transaction_id
    ]);

    // Commit Transaction
    $pdo->commit();

    set_flash_message('success', "Congratulations, {$user['name']}! Your reservation {$booking_number} is confirmed. A sanctuary room has been assigned.");
    header("Location: ../my-bookings.php");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash_message('error', "Booking could not be finalized: " . $e->getMessage());
    header("Location: ../rooms.php");
    exit;
}
