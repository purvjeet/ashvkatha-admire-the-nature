<?php
/**
 * Admin Hotel Configuration & Settings Console
 * Ashvkatha – Admire the Nature
 */
$page_title = "Hotel Configuration & Settings";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Settings Update
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('Security token expired. Please retry.', 'error');
        header('Location: settings.php');
        exit;
    }

    $action = $_POST['action'];

    if ($action === 'save_settings') {
        $allowed_keys = [
            // 1. Identity & Contact
            'hotel_name',
            'hotel_tagline',
            'hotel_phone',
            'hotel_emergency_phone',
            'hotel_email',
            'hotel_support_email',
            'hotel_address',
            'airport_distance',

            // 2. Reservation Policies
            'currency_symbol',
            'tax_rate_percent',
            'check_in_time',
            'check_out_time',
            'min_stay_nights',
            'max_stay_nights',
            'max_advance_days',
            'cancellation_policy',
            'checkin_guidelines',

            // 3. Property Features & Eco-Commitments
            'complimentary_breakfast',
            'wifi_network_name',
            'pet_policy',
            'eco_pledge_note',

            // 4. Media & Maps
            'background_video',
            'ambient_audio',
            'google_maps_embed_url',

            // 5. Social Media
            'social_instagram',
            'social_facebook',
            'social_youtube',
            'social_tripadvisor'
        ];

        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $stmt->execute([$key, $val]);
            }
        }

        set_flash_message("Hotel settings and sanctuary policies updated successfully.", 'success');
        header('Location: settings.php');
        exit;
    }

    if ($action === 'toggle_message_read') {
        $msg_id = intval($_POST['message_id'] ?? 0);
        $curr = intval($_POST['current_read'] ?? 0);
        $new_read = ($curr === 1) ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = ? WHERE id = ?");
        $stmt->execute([$new_read, $msg_id]);
        set_flash_message("Inquiry marked as " . ($new_read ? 'READ' : 'UNREAD'), 'success');
        header('Location: settings.php#inquiries');
        exit;
    }

    if ($action === 'delete_message') {
        $msg_id = intval($_POST['message_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$msg_id]);
        set_flash_message("Inquiry message deleted.", 'success');
        header('Location: settings.php#inquiries');
        exit;
    }
}

// Fetch all current settings into key => value array
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch Contact Inquiries
$inquiries = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
$unread_count = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Hotel Configuration & Policies</h1>
        <p class="admin-page-subtitle">Configure sanctuary branding, booking rules, financial tax rates, guest arrival protocols, media streams, and patron messages.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; align-items:start;">
    <!-- Settings Form Column -->
    <div>
        <form method="POST" action="settings.php">
            <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
            <input type="hidden" name="action" value="save_settings">

            <!-- SECTION 1: Identity & Direct Communications -->
            <div class="admin-card" style="margin-bottom:24px;">
                <h2 style="font-size:1.15rem; color:var(--warm-gold); margin-top:0; margin-bottom:16px; font-family:'Schibsted Grotesk',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    <span>Sanctuary Identity & Contact Channels</span>
                </h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Official Sanctuary Name</label>
                        <input type="text" name="hotel_name" class="admin-input" required value="<?= e($settings['hotel_name'] ?? 'Ashvkatha – Admire the Nature') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Tagline & Motto</label>
                        <input type="text" name="hotel_tagline" class="admin-input" required value="<?= e($settings['hotel_tagline'] ?? 'Luxury Nature Retreat & Wildlife Sanctuary') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Front Desk Phone</label>
                        <input type="text" name="hotel_phone" class="admin-input" required value="<?= e($settings['hotel_phone'] ?? '+91 2009 0222 22') ?>">
                    </div>
                    <div>
                        <label class="admin-label">24/7 Concierge / Emergency Phone</label>
                        <input type="text" name="hotel_emergency_phone" class="admin-input" value="<?= e($settings['hotel_emergency_phone'] ?? '+91 98765 43210') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Primary Reservations Email</label>
                        <input type="email" name="hotel_email" class="admin-input" required value="<?= e($settings['hotel_email'] ?? 'contact@ashvkatha.in') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Guest Relations & Escalations Email</label>
                        <input type="email" name="hotel_support_email" class="admin-input" value="<?= e($settings['hotel_support_email'] ?? 'concierge@ashvkatha.in') ?>">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="admin-label">Physical Sanctuary Address</label>
                    <textarea name="hotel_address" class="admin-input" rows="2" required><?= e($settings['hotel_address'] ?? 'Ashvkatha Nature Sanctuary, Junagadh, Gujarat 362001, India') ?></textarea>
                </div>

                <div>
                    <label class="admin-label">Distance to Nearest Transit Hubs / Airports</label>
                    <input type="text" name="airport_distance" class="admin-input" value="<?= e($settings['airport_distance'] ?? 'Keshod Airport (IXK) - 38 km | Rajkot Int. Airport (HSR) - 105 km') ?>">
                </div>
            </div>

            <!-- SECTION 2: Reservation & Financial Policies -->
            <div class="admin-card" style="margin-bottom:24px;">
                <h2 style="font-size:1.15rem; color:var(--warm-gold); margin-top:0; margin-bottom:16px; font-family:'Schibsted Grotesk',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                    <span>Reservation, Pricing & Stay Parameters</span>
                </h2>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="admin-input" required value="<?= e($settings['currency_symbol'] ?? '₹') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Tax Rate (%)</label>
                        <input type="number" step="0.1" name="tax_rate_percent" class="admin-input" required value="<?= e($settings['tax_rate_percent'] ?? '12') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Check-In Time</label>
                        <input type="time" name="check_in_time" class="admin-input" required value="<?= e($settings['check_in_time'] ?? '14:00') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Check-Out Time</label>
                        <input type="time" name="check_out_time" class="admin-input" required value="<?= e($settings['check_out_time'] ?? '11:00') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Min Stay (Nights)</label>
                        <input type="number" min="1" name="min_stay_nights" class="admin-input" value="<?= e($settings['min_stay_nights'] ?? '1') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Max Stay (Nights)</label>
                        <input type="number" min="1" name="max_stay_nights" class="admin-input" value="<?= e($settings['max_stay_nights'] ?? '30') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Max Booking Advance (Days)</label>
                        <input type="number" min="30" name="max_advance_days" class="admin-input" value="<?= e($settings['max_advance_days'] ?? '365') ?>">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="admin-label">Cancellation Policy Terms</label>
                    <input type="text" name="cancellation_policy" class="admin-input" value="<?= e($settings['cancellation_policy'] ?? 'Free cancellation up to 48 hours prior to arrival date.') ?>">
                </div>

                <div>
                    <label class="admin-label">Guest Arrival & ID Guidelines</label>
                    <textarea name="checkin_guidelines" class="admin-input" rows="2"><?= e($settings['checkin_guidelines'] ?? 'Valid government-issued photo ID required for all adult guests upon arrival. Quiet sanctuary hours observed from 22:00 to 07:00.') ?></textarea>
                </div>
            </div>

            <!-- SECTION 3: Property Amenities & Ecological Stewardship -->
            <div class="admin-card" style="margin-bottom:24px;">
                <h2 style="font-size:1.15rem; color:var(--warm-gold); margin-top:0; margin-bottom:16px; font-family:'Schibsted Grotesk',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                    <span>Property Amenities & Ecological Commitments</span>
                </h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Breakfast Inclusions</label>
                        <input type="text" name="complimentary_breakfast" class="admin-input" value="<?= e($settings['complimentary_breakfast'] ?? 'Yes (Farm-to-table organic breakfast buffet included with all suites)') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Guest Wi-Fi Network & Access Details</label>
                        <input type="text" name="wifi_network_name" class="admin-input" value="<?= e($settings['wifi_network_name'] ?? 'Ashvkatha-Sanctuary-HighSpeed (Complimentary Starlink fiber)') ?>">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="admin-label">Pet Accommodation Policy</label>
                    <input type="text" name="pet_policy" class="admin-input" value="<?= e($settings['pet_policy'] ?? 'Eco-sanctuary guidelines: strictly trained pets welcome in designated private villas with advance notice.') ?>">
                </div>

                <div>
                    <label class="admin-label">Eco-Stewardship Statement</label>
                    <textarea name="eco_pledge_note" class="admin-input" rows="2"><?= e($settings['eco_pledge_note'] ?? 'Ashvkatha is an eco-sensitive sanctuary powered by 100% solar energy with zero single-use plastics and rainwater harvesting.') ?></textarea>
                </div>
            </div>

            <!-- SECTION 4: Ambient Streams & Interactive Google Maps -->
            <div class="admin-card" style="margin-bottom:24px;">
                <h2 style="font-size:1.15rem; color:var(--warm-gold); margin-top:0; margin-bottom:16px; font-family:'Schibsted Grotesk',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                    <span>Media Streaming & Interactive Map Embed</span>
                </h2>

                <div style="margin-bottom:16px;">
                    <label class="admin-label">Homepage Hero 4K Video Stream (MP4 URL)</label>
                    <input type="url" name="background_video" class="admin-input" required value="<?= e($settings['background_video'] ?? 'https://cms.vantara.in/uploads/JUNE_2026_HOMEPAGE_HORIZONTAL_e6ea29466f.mp4') ?>">
                </div>

                <div style="margin-bottom:16px;">
                    <label class="admin-label">Ambient Nature Meditation Audio Stream (MP3 URL)</label>
                    <input type="url" name="ambient_audio" class="admin-input" required value="<?= e($settings['ambient_audio'] ?? 'https://cms.vantara.in/uploads/meditation_music_without_nature_sound_256142_4fbaeb3540.mp3') ?>">
                </div>

                <div>
                    <label class="admin-label">Google Maps Interactive Embed URL (iframe src)</label>
                    <input type="text" name="google_maps_embed_url" class="admin-input" value="<?= e($settings['google_maps_embed_url'] ?? '') ?>">
                </div>
            </div>

            <!-- SECTION 5: Social Media Profiles -->
            <div class="admin-card" style="margin-bottom:24px;">
                <h2 style="font-size:1.15rem; color:var(--warm-gold); margin-top:0; margin-bottom:16px; font-family:'Schibsted Grotesk',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
                    <span>Social Media & Review Communities</span>
                </h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="admin-label">Instagram Profile</label>
                        <input type="url" name="social_instagram" class="admin-input" placeholder="https://instagram.com/..." value="<?= e($settings['social_instagram'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="admin-label">Facebook Page</label>
                        <input type="url" name="social_facebook" class="admin-input" placeholder="https://facebook.com/..." value="<?= e($settings['social_facebook'] ?? '') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="admin-label">YouTube Documentary Channel</label>
                        <input type="url" name="social_youtube" class="admin-input" placeholder="https://youtube.com/@..." value="<?= e($settings['social_youtube'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="admin-label">TripAdvisor Listing URL</label>
                        <input type="url" name="social_tripadvisor" class="admin-input" placeholder="https://tripadvisor.com/..." value="<?= e($settings['social_tripadvisor'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="display:flex; justify-content:flex-end; margin-bottom:30px;">
                <button type="submit" class="admin-btn admin-btn-primary" style="padding:12px 32px; font-size:1rem;">
                    Save All Hotel Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Contact Inquiries Column -->
    <div id="inquiries">
        <div class="admin-card" style="position:sticky; top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="font-size:1.15rem; color:var(--warm-gold); margin:0; font-family:'Schibsted Grotesk',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    <span>Patron Inquiries</span>
                </h2>
                <?php if ($unread_count > 0): ?>
                    <span class="badge" style="background:rgba(239,68,68,0.2); color:#fca5a5; font-size:0.75rem;">
                        <?= $unread_count ?> Unread
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($inquiries)): ?>
                <p style="color:#888; font-size:0.85rem; text-align:center; padding:30px 0;">No inquiries received yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px; max-height:750px; overflow-y:auto; padding-right:4px;">
                    <?php foreach ($inquiries as $msg): ?>
                        <div style="background:rgba(6,20,27,0.6); border:1px solid <?= $msg['is_read'] ? 'rgba(74,92,108,0.25)' : 'rgba(230,161,90,0.35)' ?>; border-radius:8px; padding:12px 14px; position:relative;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                                <strong style="color:#ccd0cf; font-size:0.85rem;"><?= e($msg['name']) ?></strong>
                                <span style="font-size:0.7rem; color:#9ba8ab;"><?= date('d M, h:i A', strtotime($msg['created_at'])) ?></span>
                            </div>
                            <div style="font-size:0.75rem; color:var(--warm-gold); margin-bottom:4px;">
                                <a href="mailto:<?= e($msg['email']) ?>" style="color:var(--warm-gold); text-decoration:none;"><?= e($msg['email']) ?></a>
                                <?= $msg['phone'] ? ' &bull; ' . e($msg['phone']) : '' ?>
                            </div>
                            <div style="font-size:0.8rem; font-weight:600; color:#ccd0cf; margin-bottom:4px;">
                                Sub: <?= e($msg['subject']) ?>
                            </div>
                            <p style="font-size:0.8rem; color:#9ba8ab; line-height:1.4; margin:0 0 10px 0;">
                                <?= nl2br(e($msg['message'])) ?>
                            </p>
                            
                            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid rgba(74,92,108,0.25); padding-top:6px;">
                                <form method="POST" action="settings.php" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_message_read">
                                    <input type="hidden" name="message_id" value="<?= e($msg['id']) ?>">
                                    <input type="hidden" name="current_read" value="<?= e($msg['is_read']) ?>">
                                    <button type="submit" style="background:none; border:none; color:<?= $msg['is_read'] ? '#888' : '#4ade80' ?>; font-size:0.75rem; cursor:pointer; padding:0;">
                                        <?= $msg['is_read'] ? 'Mark Unread' : 'Mark as Read' ?>
                                    </button>
                                </form>

                                <form method="POST" action="settings.php" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?= e($msg['id']) ?>">
                                    <button type="submit" style="background:none; border:none; color:#f87171; font-size:0.75rem; cursor:pointer; padding:0;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
