<?php
/**
 * Rooms Catalog - rooms.php
 * Ashvkatha – Admire the Nature
 */
$page_title = "Sanctuaries & Suites | Ashvkatha";
$page_desc  = "Discover all 24 luxury sanctuaries across 6 categories. Natural materials, private terraces, heated plunge pools, and forest horizons.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = get_db();

// Filter parameters
$selected_category = sanitize_input($_GET['category'] ?? '');
$check_in          = sanitize_input($_GET['check_in'] ?? '');
$check_out         = sanitize_input($_GET['check_out'] ?? '');
$guests            = (int)($_GET['guests'] ?? 0);
$sort              = sanitize_input($_GET['sort'] ?? 'price_asc');

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$default_check_in = $check_in ?: $today;
$default_check_out = $check_out ?: $tomorrow;

// Query building
$sql = "SELECT * FROM room_types WHERE status = 'active'";
$params = [];

if ($selected_category) {
    $sql .= " AND slug = :slug";
    $params[':slug'] = $selected_category;
}

if ($guests > 0) {
    $sql .= " AND capacity >= :capacity";
    $params[':capacity'] = $guests;
}

if ($sort === 'price_desc') {
    $sql .= " ORDER BY base_price DESC";
} else {
    $sql .= " ORDER BY base_price ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$room_types = $stmt->fetchAll();

// All categories for filter pills and bundling modal
$all_categories = $pdo->query("SELECT id, name, slug, base_price FROM room_types WHERE status = 'active' ORDER BY base_price ASC")->fetchAll();

// All active services for curated experiences bundling
$services = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY price ASC")->fetchAll();
?>

<!-- 1. Suites Hero Section -->
<section class="page-banner" style="background: linear-gradient(180deg, rgba(6, 20, 27, 0.78) 0%, rgba(6, 20, 27, 0.96) 100%), url('images/hotel/hero_bg.jpg') center/cover no-repeat;">
    <div class="container">
        <span class="section-label" style="color:var(--warm-gold);">24 Private Sanctuaries &bull; 6 Categories</span>
        <h1 class="page-banner-title">Our Sanctuaries &amp; Suites</h1>
        <p class="page-banner-desc">Every residence at Ashvkatha is handcrafted to merge with the surrounding forest, offering panoramic woodland terraces, botanical baths, and uncompromised quietude.</p>
    </div>
</section>

<!-- 2. Accommodation Introduction Narrative -->
<section class="suites-intro-section">
    <div class="container">
        <div class="suites-intro-card">
            <span class="section-label">Architectural Harmony</span>
            <h2 class="suites-intro-title">Consciously Designed for Deep Restoration</h2>
            <div class="gold-divider"></div>
            <p class="suites-intro-lead">
                Ashvkatha features strictly <strong>24 private residences</strong> distributed evenly across <strong>6 distinct categories</strong> (4 suites per category). Designed by master eco-architects using local river-stone masonry, reclaimed Burmese teak, and solar energy, each suite guarantees acoustic privacy, expansive forest vistas, and zero light pollution for undisturbed starry skies.
            </p>
        </div>
    </div>
</section>

<!-- 3. Availability Checker & Filter Section -->
<section id="quick-book" class="rooms-filter-section">
    <div class="container">
        <div class="suites-search-card">
            <form action="rooms.php" method="GET" class="suites-search-form">
                <div class="suites-field">
                    <label for="check_in" class="suites-label">Check-In</label>
                    <input type="date" name="check_in" id="check_in" class="suites-input" min="<?= $today ?>" value="<?= e($default_check_in) ?>" required>
                </div>
                
                <div class="suites-field">
                    <label for="check_out" class="suites-label">Check-Out</label>
                    <input type="date" name="check_out" id="check_out" class="suites-input" min="<?= $tomorrow ?>" value="<?= e($default_check_out) ?>" required>
                </div>
                
                <div class="suites-field">
                    <label for="category" class="suites-label">Sanctuary Category</label>
                    <select name="category" id="category" class="suites-select">
                        <option value="">All 6 Categories</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>" <?= ($selected_category === $cat['slug']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="suites-field">
                    <label for="guests" class="suites-label">Guests</label>
                    <select name="guests" id="guests" class="suites-select">
                        <option value="0" <?= ($guests === 0) ? 'selected' : '' ?>>All Capacities</option>
                        <option value="1" <?= ($guests === 1) ? 'selected' : '' ?>>1 Guest</option>
                        <option value="2" <?= ($guests === 2) ? 'selected' : '' ?>>2 Guests</option>
                        <option value="3" <?= ($guests === 3) ? 'selected' : '' ?>>3 Guests</option>
                        <option value="4" <?= ($guests === 4) ? 'selected' : '' ?>>4 Guests</option>
                        <option value="6" <?= ($guests === 6) ? 'selected' : '' ?>>5+ Guests (Presidential)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-gold suites-search-btn">Check Availability</button>
            </form>
        </div>

        <div class="filter-card">
            <!-- Category Filter Pills -->
            <div class="filter-pills">
                <a href="rooms.php" class="filter-pill <?= empty($selected_category) ? 'active' : '' ?>">All Categories</a>
                <?php foreach ($all_categories as $cat): ?>
                    <a href="rooms.php?category=<?= e($cat['slug']) ?><?= $check_in ? '&check_in='.e($check_in).'&check_out='.e($check_out) : '' ?>" 
                       class="filter-pill <?= ($selected_category === $cat['slug']) ? 'active' : '' ?>">
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Sort Selector -->
            <form action="rooms.php" method="GET" class="filter-selects">
                <?php if ($selected_category): ?>
                    <input type="hidden" name="category" value="<?= e($selected_category) ?>">
                <?php endif; ?>
                <?php if ($check_in && $check_out): ?>
                    <input type="hidden" name="check_in" value="<?= e($check_in) ?>">
                    <input type="hidden" name="check_out" value="<?= e($check_out) ?>">
                <?php endif; ?>

                <select name="sort" class="suites-select" style="width: auto; padding: 7px 14px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </form>
        </div>

        <?php if ($check_in && $check_out): ?>
            <div style="margin-top: 16px; padding: 12px 20px; background: rgba(17, 33, 45, 0.85); border: 1px solid rgba(74, 92, 108, 0.45); border-radius: var(--radius-sm); font-size: 0.9rem; color: #ccd0cf;">
                Showing live room availability for dates: <strong style="color:var(--warm-gold);"><?= format_date($check_in) ?></strong> to <strong style="color:var(--warm-gold);"><?= format_date($check_out) ?></strong> 
                (<?= calculate_nights($check_in, $check_out) ?> Nights).
                <a href="rooms.php" style="margin-left: 10px; color: var(--warm-gold); text-decoration: underline;">Clear Dates</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. Room Categories Listing (All 6 Categories with Full Specs) -->
<section style="padding: 40px 0 80px;">
    <div class="container">
        <?php if (empty($room_types)): ?>
            <div style="text-align: center; padding: 80px 20px; background: #11212d; border-radius: var(--radius-md); border: 1px solid rgba(74, 92, 108, 0.45);">
                <h3 style="color:#ccd0cf;">No Accommodations Found</h3>
                <p style="color:#9ba8ab;">No room categories match your chosen filters. Please adjust your criteria or clear filters.</p>
                <a href="rooms.php" class="btn btn-gold" style="margin-top: 15px;">View All Categories</a>
            </div>
        <?php else: ?>
            <div class="rooms-grid">
                <?php 
                $shown_categories = 0;
                foreach ($room_types as $room): 
                    $amenities = array_filter(array_map('trim', explode(',', $room['amenities'])));
                    $available_count = 4;
                    if ($check_in && $check_out) {
                        $available_count = get_available_room_count_for_category($pdo, $room['id'], $check_in, $check_out);
                        // User requirement: no reserved rooms show if dates are picked, only show free rooms
                        if ($available_count <= 0) {
                            continue;
                        }
                    }
                    $shown_categories++;
                ?>
                    <div class="room-card reveal-on-scroll">
                        <div class="room-card-img-wrap">
                            <img src="<?= e($room['image']) ?>" alt="<?= e($room['name']) ?>" class="room-card-img" loading="lazy">
                            <div class="room-card-price-tag">
                                <?= format_currency($room['base_price']) ?> <span>/ night</span>
                            </div>
                        </div>

                        <div class="room-card-body">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                                <span class="section-label" style="margin-bottom:0;"><?= e($room['size_sqft']) ?> SQ.FT &bull; 4 IN INVENTORY</span>
                                <?php if ($check_in && $check_out): ?>
                                    <span class="badge badge-available" style="background: rgba(34, 197, 94, 0.16); border: 1px solid rgba(74, 222, 128, 0.55); color: #4ade80; font-size: 0.78rem; font-weight: 700; padding: 4px 12px; border-radius: 30px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 5px;">
                                        <span style="color: #4ade80; font-size: 0.85rem; font-weight: 800;">&bull;</span> <?= $available_count ?> Free of 4 Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-category-count" style="background: rgba(230, 161, 90, 0.18); border: 1px solid rgba(230, 161, 90, 0.55); color: #f6c48f; font-size: 0.78rem; font-weight: 700; padding: 4px 12px; border-radius: 30px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);">
                                        <span style="color: var(--warm-gold); font-size: 0.85rem; font-weight: 800;">&bull;</span> 4 Rooms In Category
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="room-card-title"><?= e($room['name']) ?></h3>
                            <p class="room-card-desc"><?= e($room['short_desc']) ?></p>

                            <!-- Specifications -->
                            <div class="room-specs-list">
                                <div class="room-spec-item" title="Capacity">
                                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <?= e($room['capacity']) ?> Guests
                                </div>
                                <div class="room-spec-item" title="Room Size">
                                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
                                    <?= e($room['size_sqft']) ?> sq.ft
                                </div>
                                <div class="room-spec-item" title="Bed Type">
                                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M20 9.557V3h-2v2H6V3H4v6.557C2.814 10.198 2 11.498 2 13v8h2v-2h16v2h2v-8c0-1.502-.814-2.802-2-3.443zM18 7v2.09c-.61-.06-1.25-.09-2-.09H8c-.75 0-1.39.03-2 .09V7h12z"/></svg>
                                    <?= e($room['bed_type']) ?>
                                </div>
                            </div>

                            <!-- Amenities Badges (High Visibility & Readability) -->
                            <div class="room-amenities-badges" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom: 22px;">
                                <?php foreach (array_slice($amenities, 0, 4) as $am): ?>
                                    <span class="room-amenity-badge" style="font-size: 0.82rem; font-weight: 600; background: rgba(6, 20, 27, 0.88); border: 1px solid rgba(230, 161, 90, 0.5); padding: 5px 12px; border-radius: 6px; color: #ffffff; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35); letter-spacing: 0.01em;">
                                        <span class="amenity-bullet" style="color: var(--warm-gold); font-size: 0.95rem; font-weight: 800; line-height: 1;">&bull;</span> <?= e($am) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($amenities) > 4): ?>
                                    <span class="room-amenities-more" style="font-size: 0.82rem; color: var(--warm-gold); font-weight: 600; align-self: center; margin-left: 2px;">+<?= count($amenities) - 4 ?> more</span>
                                <?php endif; ?>
                            </div>

                            <!-- Curated Experiences Add-on Indicator -->
                            <div class="room-experience-strip" style="margin-bottom: 14px; padding: 8px 12px; background: rgba(6, 20, 27, 0.65); border: 1px dashed rgba(230, 161, 90, 0.4); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.78rem; color: #9ba8ab; display: flex; align-items: center; gap: 6px;">
                                    <span style="color: var(--warm-gold);">&starf;</span> Curated Experiences Available
                                </span>
                                <button type="button" onclick="openBundleModal(<?= $room['id'] ?>, '<?= addslashes(e($room['name'])) ?>', <?= (float)$room['base_price'] ?>)" style="font-size: 0.76rem; background:none; border:none; color:var(--warm-gold); cursor:pointer; font-weight:700; text-decoration:underline;">
                                    + Bundle Experience
                                </button>
                            </div>

                            <!-- Booking Actions -->
                            <div class="room-card-actions">
                                <a href="room-details.php?id=<?= $room['id'] ?><?= $check_in ? '&check_in='.e($check_in).'&check_out='.e($check_out) : '' ?>" class="btn btn-outline-gold btn-sm" style="flex:1;">View Details</a>
                                <a href="booking.php?room_type=<?= $room['id'] ?><?= $check_in ? '&check_in='.e($check_in).'&check_out='.e($check_out) : '' ?>" class="btn btn-gold btn-sm" style="flex:1;">Book Sanctuary</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($shown_categories === 0): ?>
                <div style="text-align: center; padding: 70px 20px; background: #11212d; border-radius: var(--radius-md); border: 1px solid rgba(74, 92, 108, 0.45); margin-top:20px;">
                    <h3 style="color:#ccd0cf;">All Rooms Reserved for Selected Dates</h3>
                    <p style="color:#9ba8ab; max-width:550px; margin:10px auto 20px;">Every sanctuary in the selected criteria is fully booked for <?= format_date($check_in) ?> to <?= format_date($check_out) ?>. Please select alternative dates to see available rooms.</p>
                    <a href="rooms.php" class="btn btn-gold">Clear Dates &amp; View All Inventory</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- 4.5 Curated Sanctuary Experiences (Addable to Suites Booking) -->
<section id="curated-experiences" class="section-curated-experiences reveal-on-scroll" style="padding: 70px 0 90px; background: radial-gradient(circle at 50% 30%, rgba(17, 33, 45, 0.95) 0%, rgba(6, 20, 27, 1) 100%); border-top: 1px solid rgba(74, 92, 108, 0.35); border-bottom: 1px solid rgba(74, 92, 108, 0.35);">
    <div class="container">
        <div style="text-align: center; max-width: 780px; margin: 0 auto 48px;">
            <span class="section-label" style="color:var(--warm-gold);">Curated Sanctuary Experiences</span>
            <h2 class="section-title text-white">Bespoke Rituals to Add to Your Suite Stay</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">Enhance your sanctuary stay with holistic Ayurvedic treatments, guided Gir biodiversity safaris, and private organic dining. Any experience below can be bundled directly into your suite reservation with instant confirmation.</p>
        </div>

        <div class="experiences-grid">
            <?php foreach ($services as $svc): ?>
                <div class="experience-card" style="background:#11212d; border: 1px solid rgba(74, 92, 108, 0.45); border-radius: var(--radius-md); padding: 30px 26px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease;">
                    <div>
                        <div class="experience-icon" style="background: rgba(230, 161, 90, 0.12); color: var(--warm-gold); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 18px;">
                            <?php if ($svc['icon'] === 'spa'): ?>
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                            <?php elseif ($svc['icon'] === 'compass'): ?>
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c.61 0 1.1.49 1.1 1.1s-.49 1.1-1.1 1.1-1.1-.49-1.1-1.1.49-1.1 1.1-1.1z"/></svg>
                            <?php elseif ($svc['icon'] === 'utensils'): ?>
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                            <?php elseif ($svc['icon'] === 'car'): ?>
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.04 3H5.81l1.04-3zM19 17H5v-4.66l.12-.34h13.77l.11.34V17z"/></svg>
                            <?php else: ?>
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-3.03 0-5.5-2.47-5.5-5.5 0-1.82.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z"/></svg>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 1.25rem; color: #ccd0cf; margin-bottom: 8px;"><?= e($svc['name']) ?></h4>
                        <p style="font-size: 0.9rem; color: #9ba8ab; line-height: 1.6; margin-bottom: 20px;"><?= e($svc['description']) ?></p>
                    </div>

                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:16px; padding-top:14px; border-top: 1px solid rgba(74, 92, 108, 0.35);">
                            <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ba8ab;">Experience Rate</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: var(--warm-gold);"><?= format_currency($svc['price']) ?></span>
                        </div>
                        <button type="button" class="btn btn-gold btn-sm" style="width:100%; text-align:center;" onclick="openExperienceModal(<?= $svc['id'] ?>, '<?= addslashes(e($svc['name'])) ?>', <?= (float)$svc['price'] ?>, '<?= addslashes(e($svc['description'])) ?>')">
                            Add to Suite Booking &rarr;
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. Sanctuary Accommodation Policies -->
<section class="section-policies reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Guest Information</span>
            <h2 class="section-title">Sanctuary Reservation Policies</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">Transparent guidelines to ensure your stay remains tranquil, seamless, and completely restorative.</p>
        </div>

        <div class="policies-grid">
            <div class="policy-card">
                <div class="policy-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </div>
                <h3 class="policy-title">Check-in &amp; Check-out</h3>
                <p class="policy-desc">Check-in begins at <strong>2:00 PM</strong> to allow thorough sanctuary preparation. Check-out is until <strong>11:00 AM</strong>. Early arrivals and late departures can be arranged with our concierge upon request.</p>
            </div>

            <div class="policy-card">
                <div class="policy-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg>
                </div>
                <h3 class="policy-title">Cancellation &amp; Refunds</h3>
                <p class="policy-desc">Cancellations made <strong>48 hours prior</strong> to arrival receive a 100% full refund with zero cancellation fees. For group retreats or peak holiday stays, a 7-day flexible notice period applies.</p>
            </div>

            <div class="policy-card">
                <div class="policy-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h3 class="policy-title">Extra Guests &amp; Children</h3>
                <p class="policy-desc">Children under 6 stay complimentary sharing existing bedding. Additional guests (above maximum capacity) can be accommodated with an artisanal rollaway cot for ₹2,500/night including breakfast.</p>
            </div>
        </div>
    </div>
</section>

<!-- 6. Accommodation FAQs Accordion -->
<section class="section-faqs reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Questions &amp; Answers</span>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="gold-divider"></div>
        </div>

        <div class="faqs-list">
            <details class="faq-item">
                <summary class="faq-question">
                    <span>Are the private plunge pools temperature-controlled throughout the year?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Yes. All plunge pools in our Luxury Suites and Presidential Suites feature eco-friendly solar thermal heating, maintaining a soothing, comfortable temperature even during chilly Gir winter mornings and evenings.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-question">
                    <span>Can dining be served privately on our sanctuary terrace?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Certainly. Ashvkatha offers 24-hour private in-suite dining. Your dedicated butler can arrange sunrise breakfasts or multi-course candlelight dinners overlooking the forest canopy on your private terrace.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-question">
                    <span>What airport is closest, and do you arrange private transfers?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Keshod Airport (IXK) is approximately 38 km (~50 minutes) away, while Rajkot International Airport (HSR) is approximately 105 km (~2.5 hours). We provide luxury private SUV transfers directly from either airport to the sanctuary.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-question">
                    <span>Is the sanctuary suitable for quiet restorative retreats and work?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Yes. Every suite is acoustically buffered with natural insulation, has high-speed optical-fiber Wi-Fi, handcrafted ergonomic writing desks, and panoramic nature views designed specifically for creative work or deep rest.</p>
                </div>
            </details>
        </div>
    </div>
</section>

<!-- Interactive Experience & Suite Bundling Modal -->
<div id="experience-bundle-modal" class="bundle-modal-backdrop" style="display:none;" onclick="if(event.target === this) closeBundleModal()">
    <div class="bundle-modal-card">
        <button type="button" class="bundle-modal-close" onclick="closeBundleModal()" aria-label="Close Modal">&times;</button>
        <span class="section-label" style="color:var(--warm-gold); margin-bottom:4px;">Sanctuary Experience Add-On</span>
        <h3 id="modal-experience-title" style="color:#ccd0cf; font-size:1.6rem; font-family:var(--font-serif); margin-bottom:8px;">Bundle Experience with Suite</h3>
        <p id="modal-experience-desc" style="color:#9ba8ab; font-size:0.9rem; line-height:1.5; margin-bottom:20px;">Combine this signature ritual with your chosen sanctuary suite for an unforgettable restorative retreat.</p>

        <form action="booking.php" method="GET" id="bundle-booking-form">
            <input type="hidden" name="service_id" id="modal-service-id" value="">

            <div class="form-group" style="margin-bottom:14px;">
                <label for="modal-suite-select" class="form-label" style="font-size:0.82rem; color:#9ba8ab; margin-bottom:6px; display:block;">1. Select Sanctuary Suite Category</label>
                <select name="room_type" id="modal-suite-select" class="form-control" onchange="updateModalEstimate()">
                    <?php foreach ($all_categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-price="<?= $cat['base_price'] ?>">
                            <?= e($cat['name']) ?> &bull; <?= format_currency($cat['base_price']) ?> / night
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div class="form-group">
                    <label for="modal-check-in" class="form-label" style="font-size:0.82rem; color:#9ba8ab; margin-bottom:6px; display:block;">2. Check-In Date</label>
                    <input type="date" name="check_in" id="modal-check-in" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= e($default_check_in) ?>" required onchange="updateModalEstimate()">
                </div>
                <div class="form-group">
                    <label for="modal-check-out" class="form-label" style="font-size:0.82rem; color:#9ba8ab; margin-bottom:6px; display:block;">Check-Out Date</label>
                    <input type="date" name="check_out" id="modal-check-out" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($default_check_out) ?>" required onchange="updateModalEstimate()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label for="modal-guests" class="form-label" style="font-size:0.82rem; color:#9ba8ab; margin-bottom:6px; display:block;">3. Total Guests</label>
                <select name="guests" id="modal-guests" class="form-control">
                    <option value="1">1 Guest</option>
                    <option value="2" selected>2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                </select>
            </div>

            <!-- Live Bundling Price Breakdown -->
            <div style="background:rgba(6,20,27,0.75); border:1px solid rgba(74,92,108,0.35); border-radius:var(--radius-sm); padding:16px; margin-bottom:20px; font-size:0.88rem;">
                <div style="display:flex; justify-content:space-between; color:#9ba8ab; margin-bottom:6px;">
                    <span>Suite Stay (<span id="modal-calc-nights">1 Night</span>):</span>
                    <span id="modal-suite-subtotal" style="color:#ccd0cf;">$0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; color:#9ba8ab; margin-bottom:6px;">
                    <span>Curated Experience (<span id="modal-exp-name-sub">Selected</span>):</span>
                    <span id="modal-exp-subtotal" style="color:var(--warm-gold);">+$0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; color:#9ba8ab; margin-bottom:8px; border-bottom:1px solid rgba(74,92,108,0.25); padding-bottom:6px;">
                    <span>Estimated Eco-Tax (12%):</span>
                    <span id="modal-tax-amount" style="color:#ccd0cf;">$0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-weight:700; color:var(--warm-gold); font-size:1.05rem;">
                    <span>Estimated Total:</span>
                    <span id="modal-total-amount">$0.00</span>
                </div>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; padding:14px; font-size:1rem;">
                Proceed to Reserve Suite with Experience &rarr;
            </button>
        </form>
    </div>
</div>

<script>
let currentModalServicePrice = 0;

function openExperienceModal(serviceId, serviceName, servicePrice, serviceDesc) {
    document.getElementById('modal-service-id').value = serviceId;
    document.getElementById('modal-experience-title').innerText = 'Bundle ' + serviceName;
    document.getElementById('modal-experience-desc').innerText = serviceDesc || 'Combine this signature ritual with your chosen sanctuary suite for an unforgettable restorative retreat.';
    document.getElementById('modal-exp-name-sub').innerText = serviceName;
    currentModalServicePrice = parseFloat(servicePrice || 0);
    document.getElementById('experience-bundle-modal').style.display = 'flex';
    updateModalEstimate();
}

function openBundleModal(roomId, roomName, roomPrice) {
    const suiteSelect = document.getElementById('modal-suite-select');
    if (suiteSelect) {
        suiteSelect.value = roomId;
    }
    // Default to the first service if none selected
    const firstService = <?= json_encode($services[0] ?? null) ?>;
    if (firstService) {
        openExperienceModal(firstService.id, firstService.name, firstService.price, firstService.description);
    }
}

function closeBundleModal() {
    document.getElementById('experience-bundle-modal').style.display = 'none';
}

function updateModalEstimate() {
    const suiteSelect = document.getElementById('modal-suite-select');
    const selectedOption = suiteSelect ? suiteSelect.options[suiteSelect.selectedIndex] : null;
    const roomPrice = selectedOption ? parseFloat(selectedOption.dataset.price || 0) : 0;
    
    const d1Str = document.getElementById('modal-check-in').value;
    const d2Str = document.getElementById('modal-check-out').value;
    
    let nights = 1;
    if (d1Str && d2Str) {
        const d1 = new Date(d1Str);
        const d2 = new Date(d2Str);
        if (d2 > d1) {
            nights = Math.ceil(Math.abs(d2 - d1) / (1000 * 60 * 60 * 24));
        }
    }
    
    document.getElementById('modal-calc-nights').innerText = nights + (nights === 1 ? ' Night' : ' Nights');
    
    const suiteSubtotal = roomPrice * nights;
    const expSubtotal = currentModalServicePrice;
    const subtotal = suiteSubtotal + expSubtotal;
    const tax = (subtotal * 0.12);
    const grandTotal = subtotal + tax;
    
    document.getElementById('modal-suite-subtotal').innerText = '$' + suiteSubtotal.toFixed(2);
    document.getElementById('modal-exp-subtotal').innerText = '+$' + expSubtotal.toFixed(2);
    document.getElementById('modal-tax-amount').innerText = '$' + tax.toFixed(2);
    document.getElementById('modal-total-amount').innerText = '$' + grandTotal.toFixed(2);
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
