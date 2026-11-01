<?php
/**
 * Customer Booking History & Cancellation - my-bookings.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/functions.php';

require_login();
$user = current_user();
$pdo  = get_db();

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($csrf)) {
        $cancel_booking_id = (int)($_POST['booking_id'] ?? 0);
        $reason = sanitize_input($_POST['cancellation_reason'] ?? 'Guest requested cancellation');

        // Verify booking belongs to this user and is active
        $chkStmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
        $chkStmt->execute([$cancel_booking_id, $user['id']]);
        $bRow = $chkStmt->fetch();

        if ($bRow && in_array($bRow['status'], ['pending', 'confirmed'])) {
            $upd = $pdo->prepare("
                UPDATE bookings 
                SET status = 'cancelled', cancellation_reason = ? 
                WHERE id = ?
            ");
            $upd->execute([$reason, $cancel_booking_id]);

            // Update payment status to refunded if payment exists
            $updPay = $pdo->prepare("UPDATE payments SET payment_status = 'refunded' WHERE booking_id = ?");
            $updPay->execute([$cancel_booking_id]);

            set_flash_message('success', "Reservation {$bRow['booking_number']} has been cancelled. Any payments have been marked for refund.");
            header("Location: my-bookings.php");
            exit;
        } else {
            set_flash_message('error', 'Booking cannot be cancelled or was not found.');
        }
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($csrf)) {
        $booking_id   = (int)($_POST['booking_id'] ?? 0);
        $room_type_id = (int)($_POST['room_type_id'] ?? 0);
        $rating       = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $comment      = sanitize_input($_POST['comment'] ?? '');

        if (!empty($comment) && $room_type_id > 0) {
            $revStmt = $pdo->prepare("
                INSERT INTO reviews (booking_id, user_id, room_type_id, rating, comment, is_approved)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $revStmt->execute([$booking_id ?: null, $user['id'], $room_type_id, $rating, $comment]);
            set_flash_message('success', 'Thank you! Your guest review has been recorded.');
            header("Location: my-bookings.php");
            exit;
        }
    }
}

$page_title = "My Reservations | Ashvkatha";
$page_desc  = "Manage your sanctuary reservations, review allocated rooms, and request bespoke services.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch all bookings for this customer
$stmt = $pdo->prepare("
    SELECT b.*, rt.name as category_name, rt.slug as category_slug, rt.image as category_image,
           r.room_number, r.floor,
           p.payment_method, p.payment_status, p.transaction_id
    FROM bookings b
    JOIN room_types rt ON b.room_type_id = rt.id
    LEFT JOIN rooms r ON b.room_id = r.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();
?>

<!-- Banner -->
<section class="page-banner" style="padding: 140px 8% 60px; background: linear-gradient(180deg, rgba(6, 20, 27, 0.75) 0%, rgba(6, 20, 27, 0.96) 100%), url('images/hotel/hero_bg.jpg') center/cover no-repeat;">
    <div class="container">
        <span class="section-label" style="color:var(--warm-gold);">Guest Sanctuary Portal</span>
        <h1 class="page-banner-title">My Sanctuary Reservations</h1>
        <p class="page-banner-desc">Welcome back, <?= e($user['name']) ?>. Manage your past and upcoming sanctuary stays.</p>
    </div>
</section>

<!-- Bookings Content -->
<section style="padding: 60px 0 100px;">
    <div class="container">
        
        <?php if (empty($bookings)): ?>
            <div style="background:#11212d; border-radius:var(--radius-lg); padding:70px 20px; text-align:center; border:1px solid rgba(74,92,108,0.45); box-shadow:0 10px 35px rgba(6,20,27,0.5);">
                <div style="font-size:3rem; color:var(--warm-gold); margin-bottom:12px;">&bull;</div>
                <h3 style="font-size:1.8rem; margin-bottom:8px; color:#ccd0cf;">No Current Reservations</h3>
                <p style="color:var(--text-muted); max-width:500px; margin:0 auto 24px;">You have not made any sanctuary bookings yet. Our 24 luxury rooms are ready to welcome you.</p>
                <a href="rooms.php" class="btn btn-gold">Explore Sanctuaries &rarr;</a>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:28px;">
                <?php foreach ($bookings as $b): ?>
                    <?php
                    $nights = calculate_nights($b['check_in'], $b['check_out']);
                    
                    // Fetch attached services
                    $svcStmt = $pdo->prepare("
                        SELECT bs.*, s.name as service_name 
                        FROM booking_services bs
                        JOIN services s ON bs.service_id = s.id
                        WHERE bs.booking_id = ?
                    ");
                    $svcStmt->execute([$b['id']]);
                    $attached_services = $svcStmt->fetchAll();

                    $can_cancel = in_array($b['status'], ['pending', 'confirmed']) && ($b['check_in'] >= date('Y-m-d'));
                    ?>
                    <div style="background:#11212d; border-radius:var(--radius-lg); padding:32px; border:1px solid rgba(74,92,108,0.45); box-shadow:0 10px 35px rgba(6,20,27,0.5);">
                        
                        <!-- Top Header Row -->
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; padding-bottom:20px; border-bottom:1px solid rgba(74,92,108,0.35);">
                            <div>
                                <span class="section-label" style="margin-bottom:2px;">Booking Reference</span>
                                <h3 style="font-size:1.5rem; margin:0; color:#ccd0cf;"><?= e($b['booking_number']) ?></h3>
                                <span style="font-size:0.8rem; color:var(--text-muted);">Reserved on <?= format_date($b['created_at'], 'd M Y, h:i A') ?></span>
                            </div>
                            <div style="text-align:right;">
                                <?php
                                $badgeClass = 'badge-confirmed';
                                if ($b['status'] === 'pending') $badgeClass = 'badge-pending';
                                elseif ($b['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
                                elseif ($b['status'] === 'checked_in') $badgeClass = 'badge-active';
                                elseif ($b['status'] === 'checked_out') $badgeClass = 'badge-completed';
                                ?>
                                <span class="badge <?= $badgeClass ?>" style="font-size:0.88rem; padding:6px 16px;">
                                    Status: <?= ucfirst(e($b['status'])) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Main Content Grid -->
                        <div style="display:grid; grid-template-columns: 140px 1fr 1fr; gap:24px; align-items:center; margin: 24px 0; flex-wrap:wrap;">
                            <img src="<?= e($b['category_image']) ?>" alt="<?= e($b['category_name']) ?>" style="width:140px; height:105px; object-fit:cover; border-radius:var(--radius-md); border:1px solid rgba(74,92,108,0.35);">
                            
                            <div>
                                <h4 style="font-size:1.35rem; margin-bottom:4px; color:#ccd0cf;"><?= e($b['category_name']) ?></h4>
                                <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:8px;">
                                    Assigned: <strong style="color:var(--warm-gold);">Room <?= e($b['room_number']) ?></strong> &bull; Floor <?= e($b['floor']) ?>
                                </p>
                                <p style="font-size:0.88rem; color:#ccd0cf; margin:0;">
                                    Guests: <?= e($b['total_guests']) ?> (<?= e($b['adults']) ?> Adults, <?= e($b['children']) ?> Children)
                                </p>
                            </div>

                            <div style="background:rgba(6,20,27,0.75); border:1px solid rgba(74,92,108,0.35); padding:16px 20px; border-radius:var(--radius-md);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.9rem;">
                                    <span style="color:var(--text-muted);">Check-In:</span>
                                    <strong style="color:#ccd0cf;"><?= format_date($b['check_in']) ?> (14:00)</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.9rem;">
                                    <span style="color:var(--text-muted);">Check-Out:</span>
                                    <strong style="color:#ccd0cf;"><?= format_date($b['check_out']) ?> (11:00)</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; font-size:0.95rem; border-top:1px solid rgba(74,92,108,0.35); padding-top:6px; color:#ccd0cf;">
                                    <span>Total Stay (<?= $nights ?> Nights):</span>
                                    <strong style="color:var(--warm-gold); font-size:1.15rem;"><?= format_currency($b['total_amount']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Add-on Services Attached -->
                        <?php if (!empty($attached_services)): ?>
                            <div style="margin-top:12px; padding:12px 16px; background:rgba(6,20,27,0.6); border:1px solid rgba(74,92,108,0.35); border-radius:var(--radius-sm); font-size:0.85rem;">
                                <strong style="color:var(--warm-gold);">Included Bespoke Experiences:</strong>
                                <?php foreach ($attached_services as $s): ?>
                                    <span style="margin-left:8px; display:inline-block; background:#11212d; color:#ccd0cf; padding:3px 10px; border-radius:4px; border:1px solid rgba(74,92,108,0.45);">
                                        &check; <?= e($s['service_name']) ?> (<?= format_currency($s['total_price']) ?>)
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Special Requests -->
                        <?php if (!empty($b['special_requests'])): ?>
                            <div style="margin-top:10px; font-size:0.85rem; color:var(--text-muted);">
                                <strong>Special Requests:</strong> <?= e($b['special_requests']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-top:18px; border-top:1px solid rgba(74,92,108,0.35); flex-wrap:wrap; gap:12px;">
                            <div style="font-size:0.82rem; color:var(--text-muted);">
                                Payment: <strong><?= e($b['payment_method'] ?? 'Online Payment') ?></strong> &bull; TXN: <code><?= e($b['transaction_id'] ?? 'N/A') ?></code>
                            </div>

                            <div style="display:flex; gap:10px;">
                                <!-- Review Button -->
                                <button type="button" class="btn btn-outline-gold btn-sm" onclick="openReviewModal(<?= $b['id'] ?>, <?= $b['room_type_id'] ?>, '<?= e($b['category_name']) ?>')">
                                    Leave Impression &#9733;
                                </button>

                                <!-- Cancellation Button -->
                                <?php if ($can_cancel): ?>
                                    <form action="my-bookings.php" method="POST" onsubmit="return confirm('Are you sure you wish to cancel this sanctuary reservation?');">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="cancel_booking">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-sm" style="background:rgba(220,53,69,0.15); color:#f87171; border:1px solid #dc3545;">
                                            Cancel Reservation
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Review Modal Dialog -->
<div id="review-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(6,20,27,0.88); backdrop-filter:blur(8px); z-index:10000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#11212d; border-radius:var(--radius-lg); max-width:500px; width:100%; padding:36px; box-shadow:0 20px 50px rgba(6,20,27,0.8); border:1px solid rgba(74,92,108,0.45); position:relative;">
        <button type="button" onclick="document.getElementById('review-modal').style.display='none'" style="position:absolute; top:18px; right:20px; background:none; border:none; font-size:1.8rem; color:#ccd0cf; cursor:pointer;">&times;</button>
        
        <h3 id="modal-category-title" style="margin-bottom:8px; font-size:1.6rem; color:#ccd0cf;">Guest Impression</h3>
        <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:20px;">Share your experience of stillness, wildlife, and sanctuary hospitality.</p>

        <form action="my-bookings.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="submit_review">
            <input type="hidden" name="booking_id" id="modal-booking-id" value="">
            <input type="hidden" name="room_type_id" id="modal-room-type-id" value="">

            <div class="form-group">
                <label for="rating" class="form-label">Sanctuary Experience Rating</label>
                <select name="rating" id="rating" class="form-control" required>
                    <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; 5 Stars (Exceptional Sanctuary Experience)</option>
                    <option value="4">&#9733;&#9733;&#9733;&#9733; 4 Stars (Very Good)</option>
                    <option value="3">&#9733;&#9733;&#9733; 3 Stars (Satisfactory)</option>
                    <option value="2">&#9733;&#9733; 2 Stars (Needs Improvement)</option>
                    <option value="1">&#9733; 1 Star</option>
                </select>
            </div>

            <div class="form-group">
                <label for="comment" class="form-label">Your Impression &amp; Comments *</label>
                <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Describe the sounds of the forest, the room comfort, or the organic cuisine..." required></textarea>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; padding:14px; margin-top:10px;">
                Publish Guest Impression &rarr;
            </button>
        </form>
    </div>
</div>

<script>
function openReviewModal(bookingId, roomTypeId, categoryName) {
    document.getElementById('modal-booking-id').value = bookingId;
    document.getElementById('modal-room-type-id').value = roomTypeId;
    document.getElementById('modal-category-title').innerText = 'Impression: ' + categoryName;
    document.getElementById('review-modal').style.display = 'flex';
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
