<?php
/**
 * Booking Reservation Page - booking.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/functions.php';

// Require customer to be logged in to complete reservation
require_login();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = get_db();
$user = current_user();

$room_type_id = (int)($_GET['room_type'] ?? 1);
$check_in     = sanitize_input($_GET['check_in'] ?? date('Y-m-d'));
$check_out    = sanitize_input($_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day')));
$guests       = (int)($_GET['guests'] ?? 2);

// Fetch chosen category
$stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ? AND status = 'active'");
$stmt->execute([$room_type_id]);
$room_type = $stmt->fetch();

if (!$room_type) {
    header("Location: rooms.php");
    exit;
}

// Find a free physical room in this category for the requested dates
$available_room_id = find_available_room_for_category($pdo, $room_type_id, $check_in, $check_out);

// Detect pre-selected curated experiences from URL (from suites page or room details)
$preselected_services = [];
if (!empty($_GET['services']) && is_array($_GET['services'])) {
    $preselected_services = array_map('intval', $_GET['services']);
} elseif (!empty($_GET['service_id'])) {
    $preselected_services = [(int)$_GET['service_id']];
}

// Calculate nights and pricing
$nights = calculate_nights($check_in, $check_out);
if ($nights < 1) $nights = 1;

$room_subtotal = $room_type['base_price'] * $nights;
$tax_percent   = (float)get_setting('tax_rate_percent', '12');

// Fetch active services
$services = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY price ASC")->fetchAll();

// Add-on services initial calculation
$initial_services_total = 0.00;
foreach ($services as $svc) {
    if (in_array((int)$svc['id'], $preselected_services)) {
        $initial_services_total += (float)$svc['price'];
    }
}

$subtotal_with_services = $room_subtotal + $initial_services_total;
$tax_amount    = ($subtotal_with_services * $tax_percent) / 100;
$initial_total = $subtotal_with_services + $tax_amount;
?>

<!-- Booking Hero Banner -->
<section class="page-banner" style="background: linear-gradient(180deg, rgba(12, 36, 28, 0.75) 0%, rgba(8, 24, 19, 0.95) 100%), url('<?= e($room_type['image']) ?>') center/cover no-repeat;">
    <div class="container">
        <span class="section-label" style="color:var(--warm-gold);">Reservation Confirmation</span>
        <h1 class="page-banner-title">Confirm Your Sanctuary</h1>
        <p class="page-banner-desc"><?= e($room_type['name']) ?> &bull; <?= $nights ?> <?= ($nights === 1 ? 'Night' : 'Nights') ?> of wilderness luxury</p>
    </div>
</section>

<!-- Booking Checkout Container -->
<section style="padding: 60px 0 100px;">
    <div class="container">
        
        <?php if (!$available_room_id): ?>
            <div style="background: #fff; border-radius: var(--radius-lg); padding: 50px 30px; text-align: center; border: 1px solid rgba(220,53,69,0.3); box-shadow: var(--shadow-md);">
                <div style="font-size: 3rem; color: #dc3545; margin-bottom: 12px;">&cross;</div>
                <h2 style="color: #dc3545; margin-bottom: 10px;">Sanctuary Sold Out</h2>
                <p style="color: var(--text-muted); max-width: 580px; margin: 0 auto 24px;">All 4 physical rooms of <?= e($room_type['name']) ?> are reserved for your requested dates (<?= format_date($check_in) ?> to <?= format_date($check_out) ?>).</p>
                <div style="display:flex; justify-content:center; gap: 14px;">
                    <a href="rooms.php" class="btn btn-gold">Browse Other Available Categories</a>
                    <a href="room-details.php?id=<?= $room_type['id'] ?>" class="btn btn-forest">Change Dates</a>
                </div>
            </div>
        <?php else: ?>

            <form action="actions/booking-action.php" method="POST" id="booking-reservation-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="room_type_id" value="<?= $room_type['id'] ?>">
                <input type="hidden" name="allocated_room_id" value="<?= $available_room_id ?>">

                <div class="booking-checkout-layout">
                    <!-- Left: Reservation Details & Preferences -->
                    <div class="checkout-box">
                        <h2 class="checkout-title">1. Guest &amp; Stay Parameters</h2>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label for="check_in" class="form-label">Check-In Date *</label>
                                <input type="date" name="check_in" id="check_in" class="form-control" value="<?= e($check_in) ?>" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="check_out" class="form-label">Check-Out Date *</label>
                                <input type="date" name="check_out" id="check_out" class="form-control" value="<?= e($check_out) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label for="adults" class="form-label">Number of Adults</label>
                                <select name="adults" id="adults" class="form-control">
                                    <?php for ($a = 1; $a <= $room_type['capacity']; $a++): ?>
                                        <option value="<?= $a ?>" <?= ($a == min($guests, $room_type['capacity'])) ? 'selected' : '' ?>><?= $a ?> Adult<?= ($a > 1 ? 's' : '') ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="children" class="form-label">Number of Children</label>
                                <select name="children" id="children" class="form-control">
                                    <option value="0">0 Children</option>
                                    <option value="1">1 Child</option>
                                    <option value="2">2 Children</option>
                                </select>
                            </div>
                        </div>

                        <h2 class="checkout-title" style="margin-top: 40px;" id="experiences-section">2. Enhance Your Sanctuary Stay</h2>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top:-16px; margin-bottom: 20px;">Select optional bespoke services to be prepared for your arrival:</p>

                        <div class="services-selection-grid">
                            <?php foreach ($services as $svc): 
                                $is_checked = in_array((int)$svc['id'], $preselected_services);
                            ?>
                                <label class="service-select-card <?= $is_checked ? 'selected' : '' ?>">
                                    <input type="checkbox" name="services[]" value="<?= $svc['id'] ?>" class="service-checkbox" data-price="<?= $svc['price'] ?>" <?= $is_checked ? 'checked' : '' ?>>
                                    <div class="service-card-info">
                                        <div class="service-card-title"><?= e($svc['name']) ?></div>
                                        <div class="service-card-desc"><?= e($svc['description']) ?></div>
                                        <div class="service-card-price">+<?= format_currency($svc['price']) ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <h2 class="checkout-title" style="margin-top: 40px;">3. Special Requests &amp; Notes</h2>
                        <div class="form-group">
                            <label for="special_requests" class="form-label">Preferences / Dietary / Flight Timings</label>
                            <textarea name="special_requests" id="special_requests" class="form-control" rows="3" placeholder="Kindly let us know if you have specific dietary preferences (vegan, sattvic, gluten-free), arrival flight numbers, or celebration requests..."></textarea>
                        </div>

                        <h2 class="checkout-title" style="margin-top: 40px;">4. Payment Selection</h2>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-bottom: 24px;">
                            <label style="border:1px solid rgba(74, 92, 108, 0.45); background:rgba(6,20,27,0.7); border-radius:var(--radius-sm); padding:14px; display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="Online Payment" checked style="accent-color:var(--warm-gold);">
                                <span style="font-weight:600; font-size:0.9rem; color:#ccd0cf;">Online Payment</span>
                            </label>
                            <label style="border:1px solid rgba(74, 92, 108, 0.45); background:rgba(6,20,27,0.7); border-radius:var(--radius-sm); padding:14px; display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="UPI" style="accent-color:var(--warm-gold);">
                                <span style="font-weight:600; font-size:0.9rem; color:#ccd0cf;">UPI Instant</span>
                            </label>
                            <label style="border:1px solid rgba(74, 92, 108, 0.45); background:rgba(6,20,27,0.7); border-radius:var(--radius-sm); padding:14px; display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="Cash" style="accent-color:var(--warm-gold);">
                                <span style="font-weight:600; font-size:0.9rem; color:#ccd0cf;">Pay on Arrival</span>
                            </label>
                        </div>
                    </div>

                    <!-- Right: Sticky Order Summary & Submission -->
                    <aside>
                        <div class="checkout-box" style="position: sticky; top: 100px; border: 1px solid rgba(74, 92, 108, 0.45);">
                            <h3 style="font-size: 1.45rem; margin-bottom: 16px; color:#ccd0cf;">Reservation Summary</h3>
                            
                            <div style="display:flex; gap:16px; align-items:center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(74, 92, 108, 0.35);">
                                <img src="<?= e($room_type['image']) ?>" alt="<?= e($room_type['name']) ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);">
                                <div>
                                    <strong style="color:#ccd0cf; font-size:1.1rem; display:block;"><?= e($room_type['name']) ?></strong>
                                    <span style="font-size:0.8rem; color:var(--text-muted);"><?= e($room_type['size_sqft']) ?> sq.ft &bull; <?= e($room_type['bed_type']) ?></span>
                                </div>
                            </div>

                            <div class="sidebar-price-breakdown" style="margin-bottom: 24px;">
                                <div class="price-row">
                                    <span>Rate per Night</span>
                                    <span><?= format_currency($room_type['base_price']) ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Duration</span>
                                    <span id="calculated-nights"><?= $nights ?> <?= ($nights == 1 ? 'Night' : 'Nights') ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Sanctuary Subtotal</span>
                                    <span id="room-subtotal"><?= format_currency($room_subtotal) ?></span>
                                </div>
                                <div class="price-row" id="summary-services-row" style="<?= $initial_services_total > 0 ? '' : 'display:none;' ?>">
                                    <span>Curated Experiences</span>
                                    <span id="summary-services-amount" style="color:var(--warm-gold); font-weight:600;">+<?= format_currency($initial_services_total) ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Eco-Tax &amp; Hospitality (<?= $tax_percent ?>%)</span>
                                    <span id="tax-amount"><?= format_currency($tax_amount) ?></span>
                                </div>
                                <div class="price-row total-row" style="font-size: 1.25rem;">
                                    <span>Grand Total</span>
                                    <span id="total-amount" class="text-gold"><?= format_currency($initial_total) ?></span>
                                </div>
                            </div>

                            <!-- Live JS calculation helper anchor -->
                            <span id="base-price-val" data-price="<?= $room_type['base_price'] ?>" data-tax="<?= $tax_percent ?>" style="display:none;"></span>

                            <button type="submit" class="btn btn-gold btn-lg" style="width: 100%; padding: 15px; font-size: 1.05rem;">
                                Complete Reservation &rarr;
                            </button>

                            <div style="margin-top: 16px; font-size: 0.78rem; color: var(--text-muted); text-align: center; line-height: 1.5;">
                                By clicking confirm, you agree to our 48-hour free cancellation policy and sanctuary guidelines.
                            </div>
                        </div>
                    </aside>
                </div>
            </form>

        <?php endif; ?>

    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
