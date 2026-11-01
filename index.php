<?php
/**
 * Homepage - index.php
 * Ashvkatha – Admire the Nature
 */
$page_title = "Admire the Nature | Luxury Resort & Wildlife Sanctuary";
$page_desc  = "Welcome to Ashvkatha – where ultra-luxury hospitality embraces untouched wilderness, wildlife conservation, and serene Ayurvedic living.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = get_db();

// Fetch all 6 room categories
$room_types = $pdo->query("SELECT * FROM room_types WHERE status = 'active' ORDER BY base_price ASC LIMIT 6")->fetchAll();

// Fetch hotel experiences / services
$services = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY id ASC LIMIT 5")->fetchAll();

// Fetch approved guest reviews
$reviews = $pdo->query("
    SELECT r.*, u.name as guest_name, rt.name as room_type_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE r.is_approved = 1
    ORDER BY r.id DESC LIMIT 3
")->fetchAll();

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<!-- 1. Hero Section with Background Video / Photography & Direct Actions -->
<section class="hero-wrapper">
    <video id="hero-bg-video-1" class="bg-video video-active" autoplay muted playsinline preload="auto">
        <source src="videos/hero_bg.mp4" type="video/mp4">
        <source src="https://cms.vantara.in/uploads/JUNE_2026_HOMEPAGE_HORIZONTAL_e6ea29466f.mp4" type="video/mp4">
    </video>
    <video id="hero-bg-video-2" class="bg-video video-inactive" muted playsinline preload="auto">
        <source src="videos/hero_bg_2.mp4" type="video/mp4">
        <source src="https://cms.vantara.in/uploads/JUNE_2026_ABOUT_US_HORIZONTAL_e00b18db3e.mp4" type="video/mp4">
    </video>
    
    <div class="hero-gradient-overlay"></div>

    <div class="hero-content">
        <h1 class="hero-title">Where Luxury Meets the Untamed Beauty of Nature.</h1>
        
        <div class="hero-actions">
            <a href="rooms.php" class="btn btn-gold btn-lg">Explore Suites &rarr;</a>
            <a href="rooms.php#quick-book" class="btn btn-outline-white btn-lg">Book Sanctuary</a>
        </div>
    </div>
</section>

<!-- 2. Quick Booking Bar (Seamless Home Availability Search) -->
<section id="quick-book" class="quick-booking-bar-section">
    <div class="container">
        <div class="quick-booking-bar">
            <form action="rooms.php" method="GET" class="quick-booking-form">
                <div class="quick-booking-field">
                    <span class="quick-field-icon">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                    </span>
                    <div>
                        <label for="home_check_in" class="quick-field-label">Check-In</label>
                        <input type="date" name="check_in" id="home_check_in" class="quick-field-input" min="<?= $today ?>" value="<?= $today ?>" required>
                    </div>
                </div>

                <div class="quick-booking-field">
                    <span class="quick-field-icon">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                    </span>
                    <div>
                        <label for="home_check_out" class="quick-field-label">Check-Out</label>
                        <input type="date" name="check_out" id="home_check_out" class="quick-field-input" min="<?= $tomorrow ?>" value="<?= $tomorrow ?>" required>
                    </div>
                </div>

                <div class="quick-booking-field">
                    <span class="quick-field-icon">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </span>
                    <div>
                        <label for="home_guests" class="quick-field-label">Guests</label>
                        <select name="guests" id="home_guests" class="quick-field-select">
                            <option value="1">1 Guest</option>
                            <option value="2" selected>2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="6">5+ Guests (Presidential)</option>
                        </select>
                    </div>
                </div>

                <div class="quick-booking-action">
                    <button type="submit" class="btn btn-gold quick-search-btn">
                        <span>Check Availability</span>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- 3. Welcome to Ashvkatha (Founding Vision & Architectural Immersion) -->
<section class="section-healing reveal-on-scroll">
    <div class="healing-container">
        <div class="healing-visual">
            <div class="healing-image-wrapper">
                <img src="images/hotel/hotel_terrace.jpg" alt="Ashvkatha Luxury Hotel Terrace" class="healing-video" style="object-fit:cover;">
                
                <!-- Dynamic Interactive Stats Card -->
                <div class="healing-stat-card">
                    <div class="healing-stat-nav">
                        <button type="button" class="healing-stat-nav-dot active" aria-label="Slide 1"></button>
                        <button type="button" class="healing-stat-nav-dot" aria-label="Slide 2"></button>
                        <button type="button" class="healing-stat-nav-dot" aria-label="Slide 3"></button>
                    </div>
                    <div class="healing-stat-content">
                        <span id="healing-stat-lbl" class="healing-stat-label">ACRES OF PROTECTED HAVEN</span>
                        <div id="healing-stat-val" class="healing-stat-value">3,500+</div>
                        <p id="healing-stat-txt" class="healing-stat-text">A vast, tranquil wildlife retreat in the Gir foothills, home to 24 handcrafted luxury sanctuaries and Ayurvedic wellness pavilions.</p>
                        <a href="rooms.php" class="btn btn-sm btn-gold" style="align-self:flex-start; margin-top:4px;">Explore Sanctuaries &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="healing-info">
            <span class="healing-sub">Where Ultra-Luxury Meets Wildlife Reverence</span>
            <h2 class="healing-title-text">Welcome to Ashvkatha</h2>
            <p class="healing-desc">Set amidst the tranquil, verdant foothills of Gir in Junagadh, Ashvkatha is an ultra-luxury eco-sanctuary retreat where bespoke hospitality exists in sacred communion with nature.</p>
            <p class="healing-desc">Surrounded by 3,500+ protected acres of native flora and wildlife stewardship supported by the Surya Foundation, our 24 private sanctuaries were created as serene sensory escapes. Here, every dawn brings birdsong, every afternoon invites Ayurvedic tranquility, and every evening celebrates timeless hospitality.</p>
            <a href="about.php" class="btn btn-outline-gold" style="align-self: flex-start; margin-top: 10px;">Our Sanctuary Story &rarr;</a>
        </div>
    </div>
</section>

<!-- 4. Featured Suites (Handcrafted Residences Preview) -->
<section class="section-rooms">
    <div class="container">
        <div style="text-align: center;">
            <span class="section-label">24 Exclusive Sanctuaries Across 6 Categories</span>
            <h2 class="section-title">Accommodations Designed for Serenity</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">Every suite is built with sustainable teak and local river stone, featuring panoramic terraces, private plunge pools, and uninterrupted forest horizons.</p>
        </div>

        <div class="rooms-grid">
            <?php foreach ($room_types as $room): ?>
                <div class="room-card reveal-on-scroll">
                    <div class="room-card-img-wrap">
                        <img src="<?= e($room['image']) ?>" alt="<?= e($room['name']) ?>" class="room-card-img" loading="lazy">
                        <div class="room-card-price-tag">
                            <?= format_currency($room['base_price']) ?> <span>/ night</span>
                        </div>
                    </div>
                    
                    <div class="room-card-body">
                        <span class="section-label" style="margin-bottom:4px;"><?= e($room['size_sqft']) ?> SQ.FT &bull; <?= e($room['bed_type']) ?></span>
                        <h3 class="room-card-title"><?= e($room['name']) ?></h3>
                        <p class="room-card-desc"><?= e($room['short_desc']) ?></p>
                        
                        <div class="room-specs-list">
                            <div class="room-spec-item" title="Capacity">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                Up to <?= e($room['capacity']) ?> Guests
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

                        <div class="room-card-actions">
                            <a href="room-details.php?id=<?= $room['id'] ?>" class="btn btn-outline-gold btn-sm" style="flex:1;">View Details</a>
                            <a href="booking.php?room_type=<?= $room['id'] ?>" class="btn btn-gold btn-sm" style="flex:1;">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 50px;">
            <a href="rooms.php" class="btn btn-gold btn-lg">View All Suites (Complete 24-Room Inventory) &rarr;</a>
        </div>
    </div>
</section>

<!-- 5. Why Choose Us (Distinctive Sanctuary Hallmarks) -->
<section class="section-why-choose reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">The Ashvkatha Distinction</span>
            <h2 class="section-title">Why Discerning Guests Choose Us</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">A harmonious blend of bespoke luxury and ecological consciousness that cannot be replicated.</p>
        </div>

        <div class="why-choose-grid">
            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 class="why-title">Unrivaled Comfort</h3>
                <p class="why-desc">Tailored mattresses, organic Egyptian cotton linens, acoustic forest isolation, and custom herbal pillow menus guarantee restorative sleep.</p>
            </div>

            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                </div>
                <h3 class="why-title">Conscious Luxury</h3>
                <p class="why-desc">Private heated plunge pools, brass fixtures, and indigenous river-stone masonry built to five-star international hospitality benchmarks.</p>
            </div>

            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                </div>
                <h3 class="why-title">Nature Immersion</h3>
                <p class="why-desc">Direct access to 3,500 protected acres of biodiversity, birdwatching canopies, starlit astronomy shalas, and Gir trail excursions.</p>
            </div>

            <div class="why-card">
                <div class="why-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h3 class="why-title">Authentic Hospitality</h3>
                <p class="why-desc">Rooted in ancient Indian *Atithi Devo Bhava*, our personalized butler and concierge services attend discreetly to every wish.</p>
            </div>
        </div>
    </div>
</section>

<!-- 6. Hotel Amenities (Comprehensive Facilities) -->
<section class="section-amenities reveal-on-scroll">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 24px; margin-bottom: 24px;">
            <div>
                <span class="section-label">Sanctuary Facilities</span>
                <h2 class="section-title">World-Class Hotel Amenities</h2>
                <div class="gold-divider" style="margin: 12px 0 0 0;"></div>
            </div>

            <!-- Facilities Carousel Controls: Row Navigation -->
            <div class="facilities-nav-controls">
                <div class="facilities-nav-pills">
                    <button type="button" class="facilities-pill-btn active" id="fac-pill-0" onclick="setFacilitiesSlide(0)">
                        <span>01</span> Living &amp; Dining
                    </button>
                    <button type="button" class="facilities-pill-btn" id="fac-pill-1" onclick="setFacilitiesSlide(1)">
                        <span>02</span> Wellness &amp; Butler
                    </button>
                </div>
                <div class="facilities-arrow-group">
                    <button type="button" class="facilities-arrow-btn" onclick="prevFacilitiesSlide()" aria-label="Previous Amenities">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    </button>
                    <button type="button" class="facilities-arrow-btn" onclick="nextFacilitiesSlide()" aria-label="Next Amenities">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <p class="section-lead" style="margin-top:-14px; margin-bottom: 28px;">Thoughtfully integrated amenities designed to pamper and restore without intruding on the pristine natural surroundings.</p>

        <!-- Viewport: Only ONE row visible at a time (Max 3 amenities per row) -->
        <div class="facilities-viewport">
            <div class="facilities-track" id="facilitiesTrack">
                <!-- Row 1: 3 Amenities -->
                <div class="facilities-slide-row">
                    <div class="amenity-item">
                        <div class="amenity-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4C7.31 4 3.07 5.9 0 8.98L12 21 24 8.98A16.88 16.88 0 0 0 12 4zm0 3.33c3.73 0 7.15 1.38 9.8 3.66L12 18.96 2.2 10.99C4.85 8.71 8.27 7.33 12 7.33z"/></svg>
                        </div>
                        <h4 class="amenity-name">High-Speed Wi-Fi</h4>
                        <p class="amenity-desc">Complimentary optical-fiber wireless connectivity across all private villas, terraces, and public pavilions.</p>
                    </div>

                    <div class="amenity-item">
                        <div class="amenity-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                        </div>
                        <h4 class="amenity-name">Farm-to-Table Restaurant</h4>
                        <p class="amenity-desc">Organic, multi-course Ayurvedic fine dining crafted daily from our heirloom gardens and local agrarian partners.</p>
                    </div>

                    <div class="amenity-item">
                        <div class="amenity-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.04 3H5.81l1.04-3zM19 17H5v-4.66l.12-.34h13.77l.11.34V17z"/></svg>
                        </div>
                        <h4 class="amenity-name">Valet &amp; EV Charging</h4>
                        <p class="amenity-desc">Secured private valet parking, 24/7 guest transit buggies, and high-speed electric vehicle charging docks.</p>
                    </div>
                </div>

                <!-- Row 2: Next 3 Amenities -->
                <div class="facilities-slide-row">
                    <div class="amenity-item">
                        <div class="amenity-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93z"/></svg>
                        </div>
                        <h4 class="amenity-name">Infinity Forest Pool</h4>
                        <p class="amenity-desc">Solar-heated cliffside pool overlooking the Gir woodlands, accompanied by quiet sun lounges and refreshment service.</p>
                    </div>

                    <div class="amenity-item">
                        <div class="amenity-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-3.03 0-5.5-2.47-5.5-5.5 0-1.82.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z"/></svg>
                        </div>
                        <h4 class="amenity-name">Ayurvedic Wellness Spa</h4>
                        <p class="amenity-desc">Ancient rejuvenation treatments, signature herbal hot-stone massages, and bespoke aromatherapy wellness plans.</p>
                    </div>

                    <div class="amenity-item">
                        <div class="amenity-icon">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z"/></svg>
                        </div>
                        <h4 class="amenity-name">24/7 Butler &amp; Concierge</h4>
                        <p class="amenity-desc">Dedicated personal staff to curate itinerary arrangements, in-suite dining, laundry care, and luggage transfers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let currentFacSlide = 0;
function setFacilitiesSlide(idx) {
    currentFacSlide = idx;
    const track = document.getElementById('facilitiesTrack');
    if (track) {
        track.style.transform = 'translateX(-' + (idx * 50) + '%)';
    }
    const p0 = document.getElementById('fac-pill-0');
    const p1 = document.getElementById('fac-pill-1');
    if (p0) p0.classList.toggle('active', idx === 0);
    if (p1) p1.classList.toggle('active', idx === 1);
}
function prevFacilitiesSlide() {
    setFacilitiesSlide(currentFacSlide === 0 ? 1 : 0);
}
function nextFacilitiesSlide() {
    setFacilitiesSlide(currentFacSlide === 0 ? 1 : 0);
}
</script>

<!-- 7. Nature Experience (Immersive Surroundings) -->
<section class="section-nature-experience reveal-on-scroll">
    <div class="nature-experience-banner" style="background: linear-gradient(180deg, rgba(6, 20, 27, 0.7) 0%, rgba(6, 20, 27, 0.95) 100%), url('images/hotel/nature_exp.jpg') center/cover no-repeat;">
        <div class="container" style="position:relative; z-index:2; text-align:center;">
            <span class="section-label" style="color:var(--warm-gold);">Pristine Surroundings</span>
            <h2 class="section-title" style="color:#ccd0cf; font-size:clamp(2.2rem, 3.8vw, 3.2rem);">Surrounded by 3,500 Acres of Untamed Wonder</h2>
            <div class="gold-divider"></div>
            <p class="section-lead" style="color:#9ba8ab; max-width:820px; margin:0 auto 36px;">
                Awaken to the calls of peacocks, wander along quiet botanical paths shaded by ancient banyan trees, and immerse yourself in the sacred stillness of the Gir wilderness.
            </p>
            <div style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
                <a href="about.php" class="btn btn-gold btn-lg">Explore Our Forest Philosophy</a>
                <a href="rooms.php" class="btn btn-outline-white btn-lg">Reserve a Nature Sanctuary</a>
            </div>
        </div>
    </div>
</section>

<!-- 8. Special Experiences (Curated Sanctuary Services) -->
<section class="section-experiences">
    <div class="container">
        <div style="text-align: center;">
            <span class="section-label">Curated Sanctuary Experiences</span>
            <h2 class="section-title">Immersive Forest Living</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">From dawn wildlife walks to starlit organic degustations, our bespoke services immerse you in the healing essence of the forest.</p>
        </div>

        <div class="experiences-grid">
            <?php foreach ($services as $svc): ?>
                <div class="experience-card reveal-on-scroll">
                    <div class="experience-icon">
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
                    <h4 class="experience-title"><?= e($svc['name']) ?></h4>
                    <p class="experience-desc"><?= e($svc['description']) ?></p>
                    <div class="experience-price"><?= format_currency($svc['price']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 9. Guest Testimonials -->
<?php if (!empty($reviews)): ?>
<section class="section-reviews reveal-on-scroll">
    <div class="container">
        <div style="text-align: center;">
            <span class="section-label" style="color:var(--warm-gold);">Guest Impressions</span>
            <h2 class="section-title text-white">Words from Our Visitors</h2>
            <div class="gold-divider"></div>
        </div>

        <div class="reviews-grid">
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card">
                    <div class="review-stars">
                        <?= str_repeat('&#9733;', (int)$rev['rating']) ?>
                    </div>
                    <p class="review-comment">&ldquo;<?= e($rev['comment']) ?>&rdquo;</p>
                    <div class="review-author">&mdash; <?= e($rev['guest_name']) ?> &bull; <?= e($rev['room_type_name']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. Hotel Photo Gallery (Curated Masonry Showcase) -->
<section class="section-gallery reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Moments of Tranquility</span>
            <h2 class="section-title">The Ashvkatha Gallery</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">A visual glimpse into our handcrafted architecture, wilderness landscapes, and restorative living spaces.</p>
        </div>

        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="images/hotel/gallery_1.jpg" alt="Ashvkatha Ayurvedic Wellness Shala" loading="lazy">
                <div class="gallery-overlay">
                    <span class="gallery-caption">Ayurvedic Wellness Shala</span>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/hotel/gallery_2.jpg" alt="Heated Plunge Pool at Twilight" loading="lazy">
                <div class="gallery-overlay">
                    <span class="gallery-caption">Heated Plunge Pool at Twilight</span>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/hotel/gallery_3.jpg" alt="Botanical Forest Terraces" loading="lazy">
                <div class="gallery-overlay">
                    <span class="gallery-caption">Botanical Forest Terraces</span>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/hotel/gallery_4.jpg" alt="Estate Dining Pavilion" loading="lazy">
                <div class="gallery-overlay">
                    <span class="gallery-caption">Estate Dining Pavilion</span>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/hotel/gallery_5.jpg" alt="Sunlit Suite Architecture" loading="lazy">
                <div class="gallery-overlay">
                    <span class="gallery-caption">Sunlit Suite Architecture</span>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/hotel/gallery_6.jpg" alt="Starlit Wilderness Lounge" loading="lazy">
                <div class="gallery-overlay">
                    <span class="gallery-caption">Starlit Wilderness Lounge</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 11. Special Offers & Packages -->
<section class="section-packages reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Seasonal Retreats</span>
            <h2 class="section-title">Exclusive Sanctuary Offers</h2>
            <div class="gold-divider"></div>
            <p class="section-lead">Curated seasonal itineraries tailored for deep restoration, romance, and forest exploration.</p>
        </div>

        <div class="packages-grid">
            <div class="package-card">
                <div class="package-badge">Romantic Sanctuary</div>
                <h3 class="package-title">Wilderness Romance Escape</h3>
                <div class="package-price">₹28,500 <span>/ 3 Nights</span></div>
                <p class="package-desc">Complimentary private candlelight dinner under the forest canopy, daily Ayurvedic breakfast in suite, and evening starlit champagne.</p>
                <ul class="package-features">
                    <li>&bull; Luxury Suite or Premium Deluxe</li>
                    <li>&bull; 1 Private 4-Course Candlelight Dinner</li>
                    <li>&bull; 60-Minute Couples Aromatherapy</li>
                </ul>
                <a href="rooms.php" class="btn btn-outline-gold btn-sm" style="width:100%; margin-top:20px;">Book Escape</a>
            </div>

            <div class="package-card featured-package">
                <div class="package-badge" style="background:var(--warm-gold); color:var(--charcoal);">Most Coveted</div>
                <h3 class="package-title">Ayurvedic Rejuvenation Journey</h3>
                <div class="package-price">₹42,000 <span>/ 5 Nights</span></div>
                <p class="package-desc">Comprehensive holistic wellness retreat featuring certified doctor consultation, daily herbal detox baths, and custom organic farm gastronomy.</p>
                <ul class="package-features">
                    <li>&bull; Daily Ayurvedic Spa &amp; Herbal Baths</li>
                    <li>&bull; Personalized Farm-to-Table Diet Plan</li>
                    <li>&bull; Sunrise Yoga &amp; Pranayama Shala</li>
                </ul>
                <a href="rooms.php" class="btn btn-gold btn-sm" style="width:100%; margin-top:20px;">Reserve Wellness</a>
            </div>

            <div class="package-card">
                <div class="package-badge">Extended Retreat</div>
                <h3 class="package-title">Sanctuary Residence Stay</h3>
                <div class="package-price">15% Savings <span>/ 4+ Nights</span></div>
                <p class="package-desc">Linger longer in nature. Enjoy exclusive savings on extended stays with dedicated personal butler service and airport transfers.</p>
                <ul class="package-features">
                    <li>&bull; Applicable to All 6 Suite Categories</li>
                    <li>&bull; Complimentary Luxury SUV Transit</li>
                    <li>&bull; Daily Laundry &amp; In-Suite Refreshments</li>
                </ul>
                <a href="rooms.php" class="btn btn-outline-gold btn-sm" style="width:100%; margin-top:20px;">Reserve Extended</a>
            </div>
        </div>
    </div>
</section>

<!-- 12. Final Booking Call-to-Action Banner -->
<section class="section-final-cta">
    <div class="container-narrow text-center">
        <span class="section-label" style="color:var(--warm-gold);">Begin Your Journey</span>
        <h2 style="font-size:clamp(2.4rem, 4.2vw, 3.6rem); color:#ccd0cf; margin-bottom:20px;">Your Sanctuary Awaits</h2>
        <p style="font-size:1.15rem; color:var(--text-muted); line-height:1.8; margin-bottom:36px; max-width:680px; margin-left:auto; margin-right:auto;">
            Step away from the urban rush and surrender to the timeless rhythm of the Gir forest. Reserve one of our 24 handcrafted sanctuaries today.
        </p>
        <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
            <a href="rooms.php" class="btn btn-gold btn-lg">Browse All 24 Sanctuaries &rarr;</a>
            <a href="contact.php" class="btn btn-outline-gold btn-lg">Speak with Concierge</a>
        </div>
    </div>
</section>

<!-- Dual Background Video Sequential Loop Controller -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const v1 = document.getElementById('hero-bg-video-1');
    const v2 = document.getElementById('hero-bg-video-2');
    if (!v1 || !v2) return;

    let currentVid = v1;
    let nextVid = v2;
    let isFading = false;

    function crossFadeVideos() {
        if (isFading) return;
        isFading = true;

        nextVid.currentTime = 0;
        const playPromise = nextVid.play();

        const doTransition = () => {
            nextVid.classList.remove('video-inactive');
            nextVid.classList.add('video-active');
            currentVid.classList.remove('video-active');
            currentVid.classList.add('video-inactive');

            setTimeout(() => {
                try { currentVid.pause(); } catch(e) {}
                // Swap references for continuous back-and-forth loop
                const temp = currentVid;
                currentVid = nextVid;
                nextVid = temp;
                isFading = false;
            }, 1200);
        };

        if (playPromise !== undefined) {
            playPromise.then(doTransition).catch(() => {
                isFading = false;
                try { currentVid.play(); } catch(e) {}
            });
        } else {
            doTransition();
        }
    }

    [v1, v2].forEach(function(videoEl) {
        // Trigger smooth fade 0.6 seconds before the video ends
        videoEl.addEventListener('timeupdate', function() {
            if (this === currentVid && this.duration && (this.currentTime >= this.duration - 0.6)) {
                crossFadeVideos();
            }
        });
        // Fallback for ended event
        videoEl.addEventListener('ended', function() {
            if (this === currentVid) {
                crossFadeVideos();
            }
        });
    });

    // Ensure first video plays on load
    v1.play().catch(function() {
        // Fallback for strict browser autoplay policies
        window.addEventListener('click', function unlockAutoplay() {
            v1.play().catch(function() {});
            window.removeEventListener('click', unlockAutoplay);
        }, { once: true });
    });
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
