<?php
/**
 * Room Details Page - room-details.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = get_db();

$room_id = (int)($_GET['id'] ?? 0);
$check_in  = sanitize_input($_GET['check_in'] ?? date('Y-m-d'));
$check_out = sanitize_input($_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day')));

// Fetch category details
$stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ? AND status = 'active'");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    echo "<div class='container' style='padding: 160px 0; text-align: center;'>";
    echo "<h2>Sanctuary Not Found</h2>";
    echo "<p>The requested room category is not available.</p>";
    echo "<a href='rooms.php' class='btn btn-gold' style='margin-top:20px;'>Back to Sanctuaries</a>";
    echo "</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$amenities = array_filter(array_map('trim', explode(',', $room['amenities'])));

// Check availability for chosen dates
$available_count = get_available_room_count_for_category($pdo, $room['id'], $check_in, $check_out);
$nights = calculate_nights($check_in, $check_out);
$subtotal = $room['base_price'] * max(1, $nights);
$tax = ($subtotal * 12) / 100;
$total = $subtotal + $tax;

// Fetch reviews for this category
$revStmt = $pdo->prepare("
    SELECT r.*, u.name as guest_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.room_type_id = ? AND r.is_approved = 1
    ORDER BY r.id DESC
");
$revStmt->execute([$room['id']]);
$reviews = $revStmt->fetchAll();

// Fetch active curated experiences
$services = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY price ASC")->fetchAll();

// Secondary gallery images
$gallery = [
    $room['image'],
    'images/hotel/hotel_terrace.jpg',
    'images/hotel/gallery_2.jpg',
    'images/hotel/gallery_5.jpg'
];
?>

<section class="room-details-section">
    <div class="container">
        <!-- Breadcrumbs -->
        <div style="margin-bottom: 24px; font-size: 0.85rem; color: var(--text-muted);">
            <a href="index.php" style="color:var(--text-muted); text-decoration:none;">Home</a> &nbsp;/&nbsp;
            <a href="rooms.php" style="color:var(--text-muted); text-decoration:none;">Sanctuaries</a> &nbsp;/&nbsp;
            <span style="color:var(--warm-gold); font-weight:600;"><?= e($room['name']) ?></span>
        </div>

        <div class="room-details-layout">
            <!-- Left Main Column -->
            <div class="room-main-column">
                
                <!-- Gallery -->
                <div class="room-gallery">
                    <img id="main-gallery-view" src="<?= e($room['image']) ?>" alt="<?= e($room['name']) ?>" class="room-main-img">
                    <div class="room-gallery-thumbs">
                        <?php foreach ($gallery as $idx => $imgUrl): ?>
                            <img src="<?= e($imgUrl) ?>" alt="Sanctuary View <?= $idx+1 ?>" 
                                 class="room-thumb-img <?= ($idx === 0) ? 'active' : '' ?>"
                                 onclick="document.getElementById('main-gallery-view').src = this.src; document.querySelectorAll('.room-thumb-img').forEach(el => el.classList.remove('active')); this.classList.add('active');">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Title & Meta Header -->
                <div class="room-header-meta">
                    <div>
                        <span class="room-badge-category">Exclusive Sanctuary Collection</span>
                        <h1 class="room-single-title"><?= e($room['name']) ?></h1>
                    </div>
                    <div>
                        <?php if ($available_count > 0): ?>
                            <span class="badge badge-available" style="font-size:0.9rem; padding: 6px 14px;">Available (<?= $available_count ?> Free)</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled" style="font-size:0.9rem; padding: 6px 14px;">Sold Out for Dates</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 4-Column Specs Bar -->
                <div class="room-specs-bar">
                    <div class="spec-box">
                        <span class="spec-box-label">Sanctuary Size</span>
                        <span class="spec-box-val"><?= e($room['size_sqft']) ?> sq.ft</span>
                    </div>
                    <div class="spec-box">
                        <span class="spec-box-label">Bedding</span>
                        <span class="spec-box-val"><?= e($room['bed_type']) ?></span>
                    </div>
                    <div class="spec-box">
                        <span class="spec-box-label">Maximum Guests</span>
                        <span class="spec-box-val"><?= e($room['capacity']) ?> Guests</span>
                    </div>
                    <div class="spec-box">
                        <span class="spec-box-label">Surroundings</span>
                        <span class="spec-box-val">Gir Forest View</span>
                    </div>
                </div>

                <!-- Room Description Article -->
                <article class="room-article">
                    <h3>Architecture &amp; Living Concept</h3>
                    <p><?= nl2br(e($room['description'])) ?></p>
                    
                    <h3 style="margin-top: 36px;">Sanctuary Amenities &amp; Inclusions</h3>
                    <div class="amenities-grid">
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="amenity-item">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                <span><?= e($amenity) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h3 style="margin-top: 36px;">Hospitality &amp; Sanctuary Policies</h3>
                    <ul style="color:var(--text-muted); padding-left: 20px; line-height: 1.8; font-size: 0.95rem;">
                        <li><strong>Check-in Time:</strong> 14:00 onwards &bull; <strong>Check-out Time:</strong> 11:00 AM</li>
                        <li><strong>Eco-Sanctuary Guidelines:</strong> Ashvkatha is a plastic-free and zero-noise ecological retreat.</li>
                        <li><strong>Organic Dining:</strong> Complimentary artisanal breakfast prepared from local botanical farms included.</li>
                        <li><strong>Cancellation:</strong> Free cancellation up to 48 hours prior to arrival date.</li>
                    </ul>
                </article>

                <!-- Curated Experiences Add-ons Available with this Suite -->
                <div class="room-article">
                    <span class="section-label" style="color:var(--warm-gold); margin-bottom:4px;">Enhance Your Stay</span>
                    <h3>Curated Sanctuary Experiences</h3>
                    <p style="color:#9ba8ab; font-size:0.95rem; margin-bottom:20px;">Pair your reservation of <?= e($room['name']) ?> with our signature wellness, wildlife, and culinary rituals. Select experiences below or in the reservation sidebar to bundle directly with your suite stay.</p>
                    
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
                        <?php foreach ($services as $svc): ?>
                            <div style="background:rgba(6,20,27,0.7); border:1px solid rgba(74,92,108,0.35); border-radius:var(--radius-sm); padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; gap:8px;">
                                        <strong style="color:#ccd0cf; font-size:1.02rem;"><?= e($svc['name']) ?></strong>
                                        <span style="color:var(--warm-gold); font-weight:700; font-size:0.95rem; white-space:nowrap;">+<?= format_currency($svc['price']) ?></span>
                                    </div>
                                    <p style="font-size:0.86rem; color:#9ba8ab; line-height:1.5; margin:0 0 16px;"><?= e($svc['description']) ?></p>
                                </div>
                                <button type="button" class="btn btn-outline-gold btn-sm" onclick="selectSidebarExperience(<?= $svc['id'] ?>)" style="width:100%; text-align:center;">
                                    &plus; Add to Suite Booking
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Guest Reviews -->
                <div class="room-article">
                    <h3>Guest Impressions (<?= count($reviews) ?>)</h3>
                    <?php if (empty($reviews)): ?>
                        <p style="color:var(--text-muted); margin-top: 10px;">Be the first guest to share an impression after your stay.</p>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:20px; margin-top:20px;">
                            <?php foreach ($reviews as $rev): ?>
                                <div style="padding:16px 20px; background:rgba(6, 20, 27, 0.75); border:1px solid rgba(74, 92, 108, 0.35); border-radius:var(--radius-sm);">
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px;">
                                        <strong style="color:#ccd0cf;"><?= e($rev['guest_name']) ?></strong>
                                        <span style="color:var(--warm-gold); font-size:0.9rem;"><?= str_repeat('&#9733;', (int)$rev['rating']) ?></span>
                                    </div>
                                    <p style="margin:0; font-size:0.92rem; font-style:italic; color:#ccd0cf;">&ldquo;<?= e($rev['comment']) ?>&rdquo;</p>
                                    <span style="font-size:0.75rem; color:#9ba8ab; display:block; margin-top:6px;"><?= format_date($rev['created_at']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Sticky Booking Sidebar -->
            <aside class="room-sidebar-column">
                <div class="booking-sidebar-card">
                    <div class="sidebar-price-header">
                        <div>
                            <span class="sidebar-price-amount"><?= format_currency($room['base_price']) ?></span>
                            <span class="sidebar-price-per">/ night</span>
                        </div>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Taxes &amp; Breakfast included</span>
                    </div>

                    <?php if ($available_count > 0): ?>
                        <div class="sidebar-availability-status badge-available">
                            &check; Ready for Reservation (<?= $available_count ?> Rooms Available)
                        </div>
                    <?php else: ?>
                        <div class="sidebar-availability-status badge-cancelled">
                            &cross; No rooms free for selected dates
                        </div>
                    <?php endif; ?>

                    <form action="booking.php" method="GET" class="sidebar-form">
                        <input type="hidden" name="room_type" value="<?= $room['id'] ?>">
                        
                        <div class="form-group" style="margin-bottom:12px;">
                            <label for="check_in" class="form-label">Check-In Date</label>
                            <input type="date" name="check_in" id="check_in" class="form-control" value="<?= e($check_in) ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label for="check_out" class="form-label">Check-Out Date</label>
                            <input type="date" name="check_out" id="check_out" class="form-control" value="<?= e($check_out) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label for="guests" class="form-label">Guests</label>
                            <select name="guests" id="guests" class="form-control">
                                <?php for ($g = 1; $g <= $room['capacity']; $g++): ?>
                                    <option value="<?= $g ?>" <?= ($g == 2) ? 'selected' : '' ?>><?= $g ?> <?= ($g == 1) ? 'Guest' : 'Guests' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Curated Experiences Add-on Selector in Sidebar -->
                        <div class="sidebar-services-group" style="margin-bottom: 14px;">
                            <label class="form-label" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#9ba8ab;">Curated Experiences</span>
                                <span style="font-size:0.72rem; color:var(--warm-gold);">Optional Add-ons</span>
                            </label>
                            <div style="display:flex; flex-direction:column; gap:8px; max-height:190px; overflow-y:auto; padding-right:4px;">
                                <?php foreach ($services as $svc): ?>
                                    <label class="sidebar-service-item" id="sidebar-svc-label-<?= $svc['id'] ?>" style="display:flex; align-items:center; justify-content:space-between; padding:9px 12px; background:rgba(6,20,27,0.75); border:1px solid rgba(74,92,108,0.35); border-radius:var(--radius-sm); cursor:pointer; font-size:0.82rem; transition:all 0.2s;">
                                        <span style="display:flex; align-items:center; gap:8px; color:#ccd0cf;">
                                            <input type="checkbox" name="services[]" id="sidebar-svc-input-<?= $svc['id'] ?>" value="<?= $svc['id'] ?>" class="service-checkbox" data-price="<?= $svc['price'] ?>" style="accent-color:var(--warm-gold); width:15px; height:15px; cursor:pointer;">
                                            <?= e($svc['name']) ?>
                                        </span>
                                        <span style="color:var(--warm-gold); font-weight:600; font-size:0.8rem; white-space:nowrap; margin-left:8px;">+<?= format_currency($svc['price']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="sidebar-price-breakdown">
                            <div class="price-row">
                                <span id="calculated-nights"><?= $nights ?> <?= ($nights == 1) ? 'Night' : 'Nights' ?></span>
                                <span id="room-subtotal"><?= format_currency($subtotal) ?></span>
                            </div>
                            <div class="price-row" id="experiences-breakdown-row" style="display:none;">
                                <span>Curated Experiences</span>
                                <span id="experiences-amount" style="color:var(--warm-gold); font-weight:600;">$0.00</span>
                            </div>
                            <div class="price-row">
                                <span>Estimated Eco-Tax (12%)</span>
                                <span id="tax-amount"><?= format_currency($tax) ?></span>
                            </div>
                            <div class="price-row total-row">
                                <span>Total Payable</span>
                                <span id="total-amount"><?= format_currency($total) ?></span>
                            </div>
                        </div>

                        <!-- Data attributes for live JS calculation -->
                        <span id="base-price-val" data-price="<?= $room['base_price'] ?>" data-tax="12" style="display:none;"></span>

                        <?php if ($available_count > 0): ?>
                            <button type="submit" class="btn btn-gold" style="width:100%; padding:14px; font-size:1rem;">
                                Reserve Sanctuary &rarr;
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn" style="width:100%; background:#ccc; color:#666; cursor:not-allowed;" disabled>
                                Sold Out For Selected Dates
                            </button>
                        <?php endif; ?>
                    </form>

                    <div style="text-align: center; margin-top: 16px; font-size: 0.78rem; color: var(--text-muted);">
                        <span>Instant Confirmation &bull; No Hidden Resort Fees</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
function selectSidebarExperience(serviceId) {
    const cb = document.getElementById('sidebar-svc-input-' + serviceId);
    if (cb) {
        cb.checked = true;
        cb.dispatchEvent(new Event('change'));
        const label = document.getElementById('sidebar-svc-label-' + serviceId);
        if (label) {
            label.style.borderColor = 'var(--warm-gold)';
            label.style.background = 'rgba(230, 161, 90, 0.12)';
        }
        document.querySelector('.room-sidebar-column').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
