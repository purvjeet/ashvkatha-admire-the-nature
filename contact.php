<?php
/**
 * Contact Us Page - contact.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Security token mismatch. Please reload the form.";
    }

    $name    = sanitize_input($_POST['name'] ?? '');
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone   = sanitize_input($_POST['phone'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? 'General Inquiry');
    $message = sanitize_input($_POST['message'] ?? '');

    if (empty($name)) {
        $errors[] = "Please provide your full name.";
    }
    if (!$email) {
        $errors[] = "Please provide a valid email address.";
    }
    if (empty($message)) {
        $errors[] = "Please enter your message or inquiry.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, phone, subject, message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            
            set_flash_message('success', "Thank you for reaching out, {$name}. Our sanctuary concierge will respond to your inquiry shortly.");
            header("Location: contact.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Could not record message at this moment. Please try again or call our front desk directly.";
        }
    }
}

$page_title = "Contact Sanctuary Concierge | Ashvkatha";
$page_desc  = "Connect with the Ashvkatha Sanctuary Concierge for reservations, private retreats, and wildlife conservation inquiries.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- 1. Contact Banner -->
<section class="page-banner" style="background: linear-gradient(180deg, rgba(6, 20, 27, 0.78) 0%, rgba(6, 20, 27, 0.96) 100%), url('images/hotel/contact_hero.jpg') center/cover no-repeat;">
    <div class="container">
        <span class="section-label" style="color:var(--warm-gold);">24/7 Guest Concierge &bull; Junagadh Sanctuary</span>
        <h1 class="page-banner-title">Contact Sanctuary Concierge</h1>
        <p class="page-banner-desc">Have a question regarding sanctuary reservations, private wellness packages, or wildlife support? Our dedicated team is at your service.</p>
    </div>
</section>

<!-- 2. Concierge Welcome Message & Direct Channels -->
<section style="padding: 100px 0 60px;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 60px; align-items: start;">
            
            <!-- Left Contact Info Cards & Map -->
            <div>
                <span class="section-label">Personalized Care</span>
                <h2 style="font-size: 2.4rem; margin-bottom: 24px; color:#ccd0cf;">Sanctuary Liaison</h2>
                <div class="gold-divider left"></div>
                <p style="color: var(--text-muted); font-size: 1.02rem; line-height: 1.7; margin-bottom: 32px;">
                    Whether planning a restorative personal retreat, group ecological journey, or bespoke Ayurvedic healing amidst the ancient forest, our concierge attends to every request with discreet care.
                </p>

                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <div style="display:flex; gap:16px; align-items:flex-start;">
                        <div style="width:48px; height:48px; border-radius:50%; background:rgba(230, 161, 90, 0.12); color:var(--warm-gold); display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid rgba(74, 92, 108, 0.45);">
                            <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        </div>
                        <div>
                            <strong style="color:#ccd0cf; font-size:1.1rem; display:block;">Physical Sanctuary Address</strong>
                            <p style="margin:4px 0 0; color:var(--text-muted); font-size:0.95rem;"><?= e(get_setting('hotel_address', 'Ashvkatha Nature Sanctuary, Gir Forest Foothills, Junagadh, Gujarat 362001, India')) ?></p>
                        </div>
                    </div>

                    <div style="display:flex; gap:16px; align-items:flex-start;">
                        <div style="width:48px; height:48px; border-radius:50%; background:rgba(230, 161, 90, 0.12); color:var(--warm-gold); display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid rgba(74, 92, 108, 0.45);">
                            <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        </div>
                        <div>
                            <strong style="color:#ccd0cf; font-size:1.1rem; display:block;">Concierge Telephone</strong>
                            <p style="margin:4px 0 0; color:var(--text-muted); font-size:0.95rem;">
                                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', get_setting('hotel_phone', '+91 2009 0222 22'))) ?>" style="color:var(--warm-gold); font-weight:700; text-decoration:none;"><?= e(get_setting('hotel_phone', '+91 2009 0222 22')) ?></a> &bull; Available 24 Hours
                            </p>
                        </div>
                    </div>

                    <div style="display:flex; gap:16px; align-items:flex-start;">
                        <div style="width:48px; height:48px; border-radius:50%; background:rgba(230, 161, 90, 0.12); color:var(--warm-gold); display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid rgba(74, 92, 108, 0.45);">
                            <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        </div>
                        <div>
                            <strong style="color:#ccd0cf; font-size:1.1rem; display:block;">Official Electronic Mail</strong>
                            <p style="margin:4px 0 0; color:var(--text-muted); font-size:0.95rem;">
                                <a href="mailto:<?= e(get_setting('hotel_email', 'contact@ashvkatha.in')) ?>" style="color:var(--warm-gold); text-decoration:underline;"><?= e(get_setting('hotel_email', 'contact@ashvkatha.in')) ?></a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Google Maps Embed -->
                <div style="margin-top:36px; border-radius:var(--radius-md); overflow:hidden; border:1px solid rgba(74, 92, 108, 0.45); box-shadow:0 10px 30px rgba(6, 20, 27, 0.4);">
                    <iframe 
                        title="Ashvkatha Hotel Location in Junagadh" 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d118552.28588835848!2d70.40428454999999!3d21.5222203!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3958018c6a27771f%3A0x86ef10b642674393!2sJunagadh%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" 
                        width="100%" 
                        height="260" 
                        style="border:0; display:block;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <!-- Right Contact Form Card (Preserves 100% of Backend PHP Logic) -->
            <div class="checkout-box" style="box-shadow: 0 10px 35px rgba(6, 20, 27, 0.5);">
                <h3 class="checkout-title" style="margin-bottom: 20px;">Send a Message</h3>

                <?php if (!empty($errors)): ?>
                    <div style="padding: 14px 18px; background: rgba(220, 53, 69, 0.15); border: 1px solid #dc3545; border-radius: var(--radius-sm); margin-bottom: 20px; color: #f87171; font-size: 0.9rem;">
                        <ul style="margin:0; padding-left: 20px;">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g., Ananya Deshmukh" value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="name@domain.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="+91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject of Inquiry</label>
                        <select name="subject" id="subject" class="form-control">
                            <option value="Room Reservation Inquiry">Room &amp; Suite Reservation Inquiry</option>
                            <option value="Ayurvedic Spa & Wellness Package">Ayurvedic Spa &amp; Wellness Package</option>
                            <option value="Wildlife Safari & Nature Trail">Wildlife Safari &amp; Nature Trail</option>
                            <option value="Private Event & Retreat Booking">Private Event &amp; Retreat Booking</option>
                            <option value="Wildlife Protection Support">Wildlife Protection Support (Surya Foundation)</option>
                            <option value="General Information">General Inquiries</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Your Message or Special Request *</label>
                        <textarea name="message" id="message" class="form-control" rows="5" placeholder="Share your intended travel dates, special dietary needs, or any specific questions..." required><?= e($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold btn-lg" style="width: 100%; margin-top: 10px;">
                        Submit Inquiry to Concierge &rarr;
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- 3. Directions & Transportation Guidelines -->
<section style="padding: 60px 0; background: #11212d; border-top: 1px solid rgba(74, 92, 108, 0.35); border-bottom: 1px solid rgba(74, 92, 108, 0.35);">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Seamless Arrival</span>
            <h2 class="section-title" style="color:#ccd0cf;">Directions &amp; Transportation</h2>
            <div class="gold-divider"></div>
            <p class="section-lead" style="color:#9ba8ab;">Located in the serene Gir foothills of Junagadh, Ashvkatha is readily accessible by air, rail, and private chauffeur.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            <div class="policy-card">
                <div class="policy-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                </div>
                <h3 class="policy-title">By Air</h3>
                <p class="policy-desc">
                    &bull; <strong>Keshod Airport (IXK):</strong> 38 km (~50 mins)<br>
                    &bull; <strong>Rajkot International (HSR):</strong> 105 km (~2.5 hrs)<br>
                    &bull; Private luxury SUV pickups available on request.
                </p>
            </div>

            <div class="policy-card">
                <div class="policy-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c-4 0-8 .5-8 4v9.5C4 17.43 5.57 19 7.5 19L6 20.5v.5h12v-.5L16.5 19c1.93 0 3.5-1.57 3.5-3.5V6c0-3.5-4-4-8-4zM7.5 17c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V7h12v4z"/></svg>
                </div>
                <h3 class="policy-title">By Rail</h3>
                <p class="policy-desc">
                    &bull; <strong>Junagadh Junction (JND):</strong> 42 km (~1 hr)<br>
                    &bull; Direct express trains connect Mumbai, Ahmedabad, and Delhi.<br>
                    &bull; Escorted station transfer provided upon booking.
                </p>
            </div>

            <div class="policy-card">
                <div class="policy-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </div>
                <h3 class="policy-title">Sanctuary Hours</h3>
                <p class="policy-desc">
                    &bull; <strong>Front Desk &amp; Concierge:</strong> 24 Hours / 7 Days<br>
                    &bull; <strong>Check-In:</strong> 2:00 PM onwards<br>
                    &bull; <strong>Check-Out:</strong> Until 11:00 AM
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 4. Frequently Asked Questions (Contact & Arrival) -->
<section class="section-faqs reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Questions &amp; Assistance</span>
            <h2 class="section-title">Arrival &amp; Concierge FAQs</h2>
            <div class="gold-divider"></div>
        </div>

        <div class="faqs-list">
            <details class="faq-item">
                <summary class="faq-question">
                    <span>How can I arrange airport or railway station transfer?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Simply provide your flight or train schedule when submitting the contact form above, or call our concierge desk directly at +91 2009 0222 22 at least 24 hours prior to arrival.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-question">
                    <span>Is secured private parking and EV charging available?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Yes. Ashvkatha provides 24/7 guarded private valet parking and Type 2 fast electric vehicle charging stations complimentary for all resident guests.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-question">
                    <span>What if my flight arrives late at night?</span>
                    <span class="faq-chevron">&plus;</span>
                </summary>
                <div class="faq-answer">
                    <p>Our front desk and butler service operate 24 hours a day. Regardless of what hour you reach the sanctuary, your villa will be fully prepared, warm tea provided, and check-in conducted seamlessly in-suite.</p>
                </div>
            </details>
        </div>
    </div>
</section>

<?php
// User requested: Remove "Get in Touch" on contact us page
$show_get_in_touch = false;
require_once __DIR__ . '/includes/footer.php';
?>
