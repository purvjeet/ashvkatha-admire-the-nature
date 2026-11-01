<?php
/**
 * Common Floating Pill Navbar & Left Sanctuary Drawer
 * Ashvkatha – Admire the Nature
 */
$current_page = basename($_SERVER['PHP_SELF']);
$logged_user = current_user();
?>
<header class="nav-header-wrapper" id="main-header">
    <!-- Left Upper Corner 3-Line Menu Button (Always showing, matching height & glassmorphism) -->
    <div class="nav-corner-container nav-corner-left">
        <button type="button" class="nav-menu-btn" id="sanctuary-menu-trigger" aria-label="Open Sanctuary Information & Account Menu" title="Sanctuary Info & Account">
            <span class="menu-bar"></span>
            <span class="menu-bar"></span>
            <span class="menu-bar"></span>
        </button>
    </div>

    <!-- Center Floating Pill Navbar (ONLY: Home, Suites, Logo, About Us, Contact Us) -->
    <div class="nav-header-container">
        <nav class="nav-pill">
            <a href="index.php" class="nav-link <?= ($current_page === 'index.php') ? 'active' : '' ?>">Home</a>
            <a href="rooms.php" class="nav-link <?= in_array($current_page, ['rooms.php', 'room-details.php', 'booking.php']) ? 'active' : '' ?>">Suites</a>
            
            <a href="index.php" class="nav-logo" title="Ashvkatha – Admire the Nature">
                <span class="nav-logo-main">ASHVKATHA</span>
                <span class="nav-logo-sub">Admire the Nature</span>
            </a>
            
            <a href="about.php" class="nav-link <?= ($current_page === 'about.php') ? 'active' : '' ?>">About Us</a>
            <a href="contact.php" class="nav-link <?= ($current_page === 'contact.php') ? 'active' : '' ?>">Contact Us</a>
        </nav>
    </div>
</header>

<!-- Sanctuary Information & Account Flyout Drawer Overlay (Slides in from LEFT) -->
<div class="sanctuary-drawer-overlay drawer-from-left" id="sanctuary-drawer-overlay">
    <div class="sanctuary-drawer-dialog">
        <div class="sanctuary-drawer-header">
            <div class="nav-logo">
                <span class="nav-logo-main">ASHVKATHA</span>
                <span class="nav-logo-sub">Admire the Nature</span>
            </div>
            <button type="button" class="sanctuary-drawer-close" id="sanctuary-drawer-close" aria-label="Close Menu">&times;</button>
        </div>

        <!-- Section 1: Patron Privileges & Access -->
        <div class="sanctuary-drawer-section">
            <div class="drawer-section-title">Patron Privileges</div>
            <?php if (is_logged_in()): ?>
                <div class="drawer-user-card">
                    <div class="drawer-user-avatar"><?= strtoupper(substr($logged_user['name'], 0, 1)) ?></div>
                    <div class="drawer-user-info">
                        <strong class="drawer-user-name"><?= e($logged_user['name']) ?></strong>
                        <span class="drawer-user-email"><?= e($logged_user['email']) ?></span>
                        <span class="drawer-user-role"><?= is_admin() ? 'Sanctuary Administrator' : 'Valued Patron' ?></span>
                    </div>
                </div>

                <div class="drawer-actions">
                    <?php if (is_admin()): ?>
                        <a href="admin/dashboard.php" class="drawer-btn drawer-btn-primary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                            <span>Admin Sanctuary Console</span>
                        </a>
                    <?php else: ?>
                        <a href="my-bookings.php" class="drawer-btn drawer-btn-primary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                            <span>My Reservations &amp; Stays</span>
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="drawer-btn drawer-btn-outline">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3H9c-1.1 0-2 .9-2 2v4h2V5h11v14H9v-4H7v4c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                        <span>Sign Out</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="drawer-guest-box">
                    <p style="margin:0 0 14px 0; font-size:0.88rem; color:#9ba8ab; line-height:1.5;">
                        Sign in to view your reservations, access allocated private suites, and book bespoke forest therapies.
                    </p>
                    <a href="login.php" class="drawer-btn drawer-btn-primary" style="display:flex; justify-content:center; text-align:center;">
                        Sign In to Sanctuary
                    </a>
                    <div style="display:flex; justify-content:space-between; margin-top:14px; font-size:0.82rem;">
                        <a href="register.php" style="color:var(--warm-gold); text-decoration:none; font-weight:600;">Create Patron Account &rarr;</a>
                        <a href="admin/index.php" style="color:#9ba8ab; text-decoration:none;">Staff Portal</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section 2: Sanctuary Navigation -->
        <div class="sanctuary-drawer-section">
            <div class="drawer-section-title">Sanctuary Exploration</div>
            <ul class="drawer-links-list">
                <li><a href="index.php" class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">Home Sanctuary</a></li>
                <li><a href="rooms.php" class="<?= in_array($current_page, ['rooms.php', 'room-details.php', 'booking.php']) ? 'active' : '' ?>">Suites &amp; Private Villas (24)</a></li>
                <li><a href="about.php" class="<?= ($current_page === 'about.php') ? 'active' : '' ?>">About Us &amp; Ecological Pillars</a></li>
                <li><a href="contact.php" class="<?= ($current_page === 'contact.php') ? 'active' : '' ?>">Contact Sanctuary Concierge</a></li>
            </ul>
        </div>

        <!-- Section 3: Sanctuary Concierge & Direct Inquiries -->
        <div class="sanctuary-drawer-section" style="margin-top:auto;">
            <div class="drawer-section-title">Sanctuary Concierge</div>
            <div class="drawer-info-grid">
                <div>
                    <span class="drawer-info-label">Reservations:</span>
                    <a href="tel:+912009022222" class="drawer-info-val">+91 2009 0222 22</a>
                </div>
                <div>
                    <span class="drawer-info-label">24/7 Concierge Desk:</span>
                    <a href="tel:+919876543210" class="drawer-info-val">+91 98765 43210</a>
                </div>
                <div>
                    <span class="drawer-info-label">Direct Email:</span>
                    <a href="mailto:contact@ashvkatha.in" class="drawer-info-val">contact@ashvkatha.in</a>
                </div>
                <div>
                    <span class="drawer-info-label">Check-in / Out:</span>
                    <span class="drawer-info-val">14:00 &bull; 11:00</span>
                </div>
            </div>
            <p class="drawer-location-tag">
                Ashvkatha Nature Sanctuary, Junagadh, Gujarat 362001
            </p>
        </div>
    </div>
</div>
