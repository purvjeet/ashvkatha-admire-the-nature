<?php
/**
 * Admin Reservations / Bookings Management
 * Ashvkatha – Admire the Nature
 */
$page_title = "Reservations Management";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Status Updates / Room Reassignments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('Invalid security token. Please refresh and try again.', 'error');
        header('Location: bookings.php');
        exit;
    }

    $action = $_POST['action'];
    $booking_id = intval($_POST['booking_id'] ?? 0);

    if ($action === 'update_status' && $booking_id > 0) {
        $new_status = sanitize_input($_POST['new_status'] ?? '');
        $allowed = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'rejected'];
        $cancellation_reason = sanitize_input($_POST['cancellation_reason'] ?? '');

        if (in_array($new_status, $allowed, true)) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ?, cancellation_reason = CASE WHEN ? != '' THEN ? ELSE cancellation_reason END WHERE id = ?");
            $stmt->execute([$new_status, $cancellation_reason, $cancellation_reason, $booking_id]);

            // Synchronize payment status with booking status
            if ($new_status === 'cancelled' || $new_status === 'rejected') {
                $updPay = $pdo->prepare("UPDATE payments SET payment_status = 'refunded' WHERE booking_id = ?");
                $updPay->execute([$booking_id]);
            } elseif ($new_status === 'confirmed' || $new_status === 'checked_in' || $new_status === 'checked_out') {
                $updPay = $pdo->prepare("UPDATE payments SET payment_status = 'completed' WHERE booking_id = ?");
                $updPay->execute([$booking_id]);
            }

            set_flash_message("Booking #{$booking_id} status updated to " . strtoupper($new_status), 'success');
        } else {
            set_flash_message("Invalid status selected.", 'error');
        }
        header('Location: bookings.php');
        exit;
    }

    if ($action === 'reassign_room' && $booking_id > 0) {
        $new_room_id = intval($_POST['new_room_id'] ?? 0);
        
        // Fetch current booking dates
        $b_stmt = $pdo->prepare("SELECT check_in, check_out, room_id FROM bookings WHERE id = ?");
        $b_stmt->execute([$booking_id]);
        $curr = $b_stmt->fetch();

        if ($curr && $new_room_id > 0) {
            // Check availability of new room for these dates excluding current booking
            $av_stmt = $pdo->prepare("
                SELECT COUNT(*) FROM bookings
                WHERE room_id = ?
                  AND id != ?
                  AND status IN ('pending', 'confirmed', 'checked_in')
                  AND (check_in < ? AND check_out > ?)
            ");
            $av_stmt->execute([$new_room_id, $booking_id, $curr['check_out'], $curr['check_in']]);
            $is_busy = $av_stmt->fetchColumn() > 0;

            if ($is_busy) {
                set_flash_message("Selected room is occupied during these dates. Choose another room.", 'error');
            } else {
                $up_stmt = $pdo->prepare("UPDATE bookings SET room_id = ? WHERE id = ?");
                $up_stmt->execute([$new_room_id, $booking_id]);
                set_flash_message("Room successfully reassigned.", 'success');
            }
        }
        header('Location: bookings.php');
        exit;
    }
}

// Filters & Search
$status_filter = sanitize_input($_GET['status'] ?? 'all');
$search_query = sanitize_input($_GET['search'] ?? '');

$where_clauses = ["1=1"];
$params = [];

if ($status_filter !== 'all' && in_array($status_filter, ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'rejected'], true)) {
    $where_clauses[] = "b.status = ?";
    $params[] = $status_filter;
}

if (!empty($search_query)) {
    $where_clauses[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR r.room_number LIKE ?)";
    $like = "%{$search_query}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where_clauses);

// Fetch all bookings matching filter
$query = "
    SELECT 
        b.*, 
        u.name AS guest_name, 
        u.email AS guest_email, 
        u.phone AS guest_phone,
        r.room_number,
        r.floor,
        rt.id AS room_type_id,
        rt.name AS room_type_name,
        p.transaction_id,
        p.payment_method,
        p.payment_status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    LEFT JOIN payments p ON p.booking_id = b.id
    WHERE {$where_sql}
    ORDER BY b.id DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Counts for filter tabs
$count_stmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM bookings 
    GROUP BY status
");
$counts_raw = $count_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$all_count = array_sum($counts_raw);

// Fetch all available rooms grouped by type for reassignment options
$rooms_stmt = $pdo->query("
    SELECT r.id, r.room_number, r.floor, r.room_type_id, rt.name as type_name
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE r.status = 'available'
    ORDER BY r.room_number ASC
");
$all_rooms = $rooms_stmt->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Reservations Sanctuary</h1>
        <p class="admin-page-subtitle">Track, confirm, check-in, and manage all guest reservations.</p>
    </div>
    <div>
        <a href="../booking.php" target="_blank" class="admin-btn admin-btn-primary">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            <span>Create Reservation</span>
        </a>
    </div>
</div>

<!-- Status Navigation Tabs -->
<div class="admin-filter-bar">
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
        <a href="bookings.php" class="admin-filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">
            All (<?= $all_count ?>)
        </a>
        <a href="bookings.php?status=pending" class="admin-filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">
            Pending (<?= $counts_raw['pending'] ?? 0 ?>)
        </a>
        <a href="bookings.php?status=confirmed" class="admin-filter-btn <?= $status_filter === 'confirmed' ? 'active' : '' ?>">
            Confirmed (<?= $counts_raw['confirmed'] ?? 0 ?>)
        </a>
        <a href="bookings.php?status=checked_in" class="admin-filter-btn <?= $status_filter === 'checked_in' ? 'active' : '' ?>">
            Checked-In (<?= $counts_raw['checked_in'] ?? 0 ?>)
        </a>
        <a href="bookings.php?status=checked_out" class="admin-filter-btn <?= $status_filter === 'checked_out' ? 'active' : '' ?>">
            Checked-Out (<?= $counts_raw['checked_out'] ?? 0 ?>)
        </a>
        <a href="bookings.php?status=cancelled" class="admin-filter-btn <?= $status_filter === 'cancelled' ? 'active' : '' ?>">
            Cancelled (<?= $counts_raw['cancelled'] ?? 0 ?>)
        </a>
    </div>

    <form method="GET" action="bookings.php" style="display:flex; gap:8px;">
        <?php if ($status_filter !== 'all'): ?>
            <input type="hidden" name="status" value="<?= e($status_filter) ?>">
        <?php endif; ?>
        <input 
            type="text" 
            name="search" 
            placeholder="Search booking #, guest, room..." 
            value="<?= e($search_query) ?>" 
            class="admin-input" 
            style="min-width:240px; padding:8px 14px; font-size:0.85rem;"
        >
        <button type="submit" class="admin-btn admin-btn-outline" style="padding:8px 16px;">Search</button>
        <?php if (!empty($search_query)): ?>
            <a href="bookings.php<?= $status_filter !== 'all' ? '?status=' . e($status_filter) : '' ?>" class="admin-btn admin-btn-outline" style="padding:8px 12px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Bookings Table -->
<div class="admin-card">
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Booking Ref</th>
                    <th>Guest Details</th>
                    <th>Suite / Room</th>
                    <th>Dates & Nights</th>
                    <th>Guests</th>
                    <th>Total & Pay</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:#888;">
                            No reservations found matching the selected filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): 
                        $nights = calculate_nights($b['check_in'], $b['check_out']);
                        $status_class = match($b['status']) {
                            'confirmed' => 'status-confirmed',
                            'checked_in' => 'status-checked-in',
                            'checked_out' => 'status-checked-out',
                            'pending' => 'status-pending',
                            'cancelled', 'rejected' => 'status-cancelled',
                            default => 'status-pending'
                        };
                    ?>
                    <tr>
                        <td>
                            <strong style="color:var(--warm-gold); font-family:monospace; font-size:0.95rem;"><?= e($b['booking_number']) ?></strong>
                            <div style="font-size:0.75rem; color:#888; margin-top:2px;">
                                Booked: <?= date('d M Y, h:i A', strtotime($b['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--ivory-cream);"><?= e($b['guest_name']) ?></div>
                            <div style="font-size:0.8rem; color:#aaa;"><?= e($b['guest_email']) ?></div>
                            <div style="font-size:0.8rem; color:#888;"><?= e($b['guest_phone'] ?: 'No phone') ?></div>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--warm-gold);">Room <?= e($b['room_number']) ?></div>
                            <div style="font-size:0.8rem; color:#aaa;"><?= e($b['room_type_name']) ?></div>
                            <div style="font-size:0.75rem; color:#777;">Floor <?= e($b['floor']) ?></div>
                        </td>
                        <td>
                            <div style="font-size:0.85rem; font-weight:600;"><?= format_date($b['check_in']) ?> &rarr; <?= format_date($b['check_out']) ?></div>
                            <span class="badge" style="background:rgba(213,185,135,0.15); color:var(--warm-gold); margin-top:4px; display:inline-block;">
                                <?= $nights ?> <?= $nights === 1 ? 'Night' : 'Nights' ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-size:0.85rem;">
                                <?= e($b['adults']) ?> Adults<?= $b['children'] > 0 ? ', ' . e($b['children']) . ' Child' : '' ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:700; color:var(--warm-gold); font-size:1rem;">
                                <?= format_currency($b['total_amount']) ?>
                            </div>
                            <div style="font-size:0.75rem; color:#aaa; margin-top:2px;">
                                <?= e($b['payment_method'] ?? 'Online') ?> &bull; 
                                <span style="text-transform:capitalize; color:<?= ($b['payment_status'] ?? '') === 'completed' ? '#4ade80' : '#f59e0b' ?>">
                                    <?= e($b['payment_status'] ?? 'pending') ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?= $status_class ?>">
                                <?= strtoupper(str_replace('_', ' ', $b['status'])) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap;">
                                <!-- Details Modal Button -->
                                <button type="button" class="admin-btn admin-btn-outline" style="padding:4px 8px; font-size:0.75rem;" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($b)) ?>)">
                                    Details
                                </button>

                                <!-- Status Change Dropdown / Form -->
                                <form method="POST" action="bookings.php" style="display:inline-block;" onsubmit="return confirmStatusChange(this);">
                                    <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?= e($b['id']) ?>">
                                    <select name="new_status" class="admin-select" style="padding:4px 8px; font-size:0.75rem; width:auto;" onchange="if(confirm('Are you sure you want to change this reservation status to ' + this.value.toUpperCase() + '?')) { this.form.submit(); } else { this.selectedIndex = 0; }">
                                        <option value="" disabled selected>Change Status</option>
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <option value="confirmed">Confirm</option>
                                            <option value="cancelled">Cancel</option>
                                        <?php elseif ($b['status'] === 'confirmed'): ?>
                                            <option value="checked_in">Check-In</option>
                                            <option value="cancelled">Cancel</option>
                                        <?php elseif ($b['status'] === 'checked_in'): ?>
                                            <option value="checked_out">Check-Out</option>
                                        <?php elseif ($b['status'] === 'cancelled' || $b['status'] === 'rejected'): ?>
                                            <option value="confirmed">Re-Confirm</option>
                                        <?php endif; ?>
                                    </select>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Booking Detailed View & Room Reassignment -->
<div id="bookingModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#132d25; border:1px solid rgba(213,185,135,0.25); border-radius:12px; max-width:650px; width:100%; max-height:90vh; overflow-y:auto; padding:30px; box-shadow:0 20px 50px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(213,185,135,0.15); padding-bottom:15px; margin-bottom:20px;">
            <h3 id="modalBookingTitle" style="color:var(--warm-gold); font-size:1.3rem; margin:0;">Booking Details</h3>
            <button type="button" onclick="closeDetailsModal()" style="background:none; border:none; color:#bbb; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <div id="modalBookingBody">
            <!-- Populated via Javascript -->
        </div>

        <!-- Room Reassignment Form inside modal -->
        <div style="margin-top:25px; padding-top:20px; border-top:1px solid rgba(213,185,135,0.15);">
            <h4 style="font-size:0.95rem; color:var(--warm-gold); margin-bottom:10px;">Reassign Guest Room</h4>
            <form method="POST" action="bookings.php" style="display:flex; gap:10px;">
                <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                <input type="hidden" name="action" value="reassign_room">
                <input type="hidden" name="booking_id" id="reassignBookingId" value="">
                
                <select name="new_room_id" id="reassignRoomSelect" class="admin-select" required style="flex:1;">
                    <option value="">Select Room...</option>
                    <?php foreach ($all_rooms as $rm): ?>
                        <option value="<?= e($rm['id']) ?>" data-type-id="<?= e($rm['room_type_id']) ?>">
                            Room <?= e($rm['room_number']) ?> (<?= e($rm['type_name']) ?> - Floor <?= e($rm['floor']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="admin-btn admin-btn-primary" style="padding:8px 16px;">Reassign</button>
            </form>
            <p style="font-size:0.75rem; color:#888; margin-top:6px;">Only select an available room. The system will prevent assignment if booked during these dates.</p>
        </div>

        <div style="margin-top:20px; text-align:right;">
            <button type="button" class="admin-btn admin-btn-outline" onclick="closeDetailsModal()">Close</button>
        </div>
    </div>
</div>

<script>
function openDetailsModal(booking) {
    document.getElementById('modalBookingTitle').innerText = 'Reservation #' + booking.booking_number;
    document.getElementById('reassignBookingId').value = booking.id;
    
    // Filter room options to matching room type by default
    const select = document.getElementById('reassignRoomSelect');
    for (let opt of select.options) {
        if (!opt.value) continue;
        if (opt.getAttribute('data-type-id') == booking.room_type_id) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    }

    const html = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:0.85rem; margin-bottom:15px;">
            <div>
                <span style="color:#888; display:block;">Guest Name</span>
                <strong style="color:var(--ivory-cream); font-size:0.95rem;">${booking.guest_name}</strong>
            </div>
            <div>
                <span style="color:#888; display:block;">Contact Info</span>
                <span>${booking.guest_email}<br>${booking.guest_phone || 'No phone'}</span>
            </div>
            <div>
                <span style="color:#888; display:block;">Suite & Category</span>
                <strong style="color:var(--warm-gold);">Room ${booking.room_number}</strong> (${booking.room_type_name})
            </div>
            <div>
                <span style="color:#888; display:block;">Stay Window</span>
                <span>${booking.check_in} &rarr; ${booking.check_out}</span>
            </div>
            <div>
                <span style="color:#888; display:block;">Party Size</span>
                <span>${booking.adults} Adults, ${booking.children} Children (Total ${booking.total_guests})</span>
            </div>
            <div>
                <span style="color:#888; display:block;">Total Financials</span>
                <strong style="color:var(--warm-gold); font-size:1.1rem;">₹${parseFloat(booking.total_amount).toLocaleString('en-IN', {minimumFractionDigits:2})}</strong>
            </div>
            <div>
                <span style="color:#888; display:block;">Payment Info</span>
                <span>Method: ${booking.payment_method || 'Online'}<br>Txn: <code style="color:#aaa;">${booking.transaction_id || 'N/A'}</code><br>Status: <strong>${booking.payment_status || 'Pending'}</strong></span>
            </div>
            <div>
                <span style="color:#888; display:block;">Reservation Status</span>
                <span class="status-badge" style="display:inline-block; margin-top:4px;">${booking.status.toUpperCase()}</span>
            </div>
        </div>
        ${booking.special_requests ? `
            <div style="background:rgba(0,0,0,0.2); padding:10px 14px; border-radius:6px; margin-bottom:10px; font-size:0.85rem;">
                <span style="color:var(--warm-gold); font-weight:600; display:block; margin-bottom:4px;">Guest Special Requests:</span>
                <p style="margin:0; color:#ddd;">${booking.special_requests}</p>
            </div>
        ` : ''}
        ${booking.cancellation_reason ? `
            <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); padding:10px 14px; border-radius:6px; font-size:0.85rem; color:#fca5a5;">
                <span style="font-weight:600; display:block; margin-bottom:4px;">Cancellation Reason:</span>
                <p style="margin:0;">${booking.cancellation_reason}</p>
            </div>
        ` : ''}
    `;

    document.getElementById('modalBookingBody').innerHTML = html;
    document.getElementById('bookingModal').style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

function confirmStatusChange(form) {
    const sel = form.querySelector('select[name="new_status"]');
    if (!sel.value) return false;
    return confirm("Are you sure you want to change this reservation status to " + sel.value.toUpperCase() + "?");
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
