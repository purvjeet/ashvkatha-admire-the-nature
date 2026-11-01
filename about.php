<?php
/**
 * About Us Page - about.php
 * Ashvkatha – Admire the Nature
 */
$page_title = "About Us & Founding Sanctuary Vision | Ashvkatha";
$page_desc  = "Learn about Ashvkatha's vision: an ultra-luxury eco-retreat dedicated to serene forest hospitality, Ayurvedic living, and sustainable wildlife protection.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- 1. About Us Hero Section -->
<section class="page-banner" style="background: linear-gradient(180deg, rgba(6, 20, 27, 0.75) 0%, rgba(6, 20, 27, 0.96) 100%), url('images/hotel/about_hero.jpg') center/cover no-repeat;">
    <div class="container">
        <span class="section-label" style="color:var(--warm-gold);">Our Philosophy &bull; Established Under Surya Foundation</span>
        <h1 class="page-banner-title">Admire the Nature</h1>
        <p class="page-banner-desc">Where architecture bows to the ancient canopy, and luxury is defined by peace, quietude, holistic wellness, and harmonious co-existence.</p>
    </div>
</section>

<!-- 2. Introduction & The Ashvkatha Story -->
<section style="padding: 100px 0 80px; background: var(--bg-deep);">
    <div class="container">
        <div class="healing-container" style="align-items: center;">
            <div>
                <span class="section-label">A Sanctuary of Serenity</span>
                <h2 style="font-size: clamp(2.2rem, 3.5vw, 3rem); margin-bottom: 20px; color:#ccd0cf;">The Ashvkatha Journey</h2>
                <div class="gold-divider left"></div>
                <p style="font-size: 1.05rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 16px;">
                    Set across 3,500 protected acres in the Gir landscape of Junagadh, Gujarat, <strong>Ashvkatha</strong> emerged as a revolutionary luxury sanctuary: to offer discerning travelers world-class hospitality, private heated plunge pool villas, and Ayurvedic rejuvenation, all within an ecosystem that actively preserves and reveres native wildlife.
                </p>
                <p style="font-size: 1.05rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 24px;">
                    Supported by the <strong>Surya Foundation</strong>, our retreat adheres to the highest ecological benchmarks. A portion of every stay directly fuels native wildlife corridors, veterinary botanical research, and forest protection programs.
                </p>
                <div style="display:flex; gap:30px; margin-top:20px; flex-wrap:wrap;">
                    <div>
                        <div style="font-family:var(--font-title); font-size:2.4rem; font-weight:900; color:var(--warm-gold);">3,500+</div>
                        <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); font-weight:700;">Acres Protected</span>
                    </div>
                    <div>
                        <div style="font-family:var(--font-title); font-size:2.4rem; font-weight:900; color:var(--warm-gold);">24</div>
                        <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); font-weight:700;">Private Sanctuaries</span>
                    </div>
                    <div>
                        <div style="font-family:var(--font-title); font-size:2.4rem; font-weight:900; color:var(--warm-gold);">100%</div>
                        <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); font-weight:700;">Solar &amp; Eco-Powered</span>
                    </div>
                </div>
            </div>

            <div>
                <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 20px 50px rgba(6, 20, 27, 0.7); border:1px solid rgba(74, 92, 108, 0.45);">
                    <img src="images/hotel/our_story.jpg" alt="Ashvkatha Nature Sanctuary Architecture" style="width:100%; height:auto; display:block;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Our Mission & Vision -->
<section class="section-mission-vision reveal-on-scroll">
    <div class="container">
        <div class="mission-vision-grid">
            <div class="mv-card">
                <span class="section-label">Guiding Purpose</span>
                <h3 class="mv-title">Our Sacred Mission</h3>
                <p class="mv-desc">
                    To deliver an unprecedented standard of conscious luxury hospitality where guests can disconnect from noise, renew their vitality through ancient Ayurvedic science, and directly empower the conservation of Gir’s native wildlife and pristine biodiversity.
                </p>
            </div>
            <div class="mv-card">
                <span class="section-label">Future Horizon</span>
                <h3 class="mv-title">Our Sanctuary Vision</h3>
                <p class="mv-desc">
                    To stand as India's premier benchmark for zero-impact ecological sanctuaries—demonstrating that architectural elegance, fine gastronomic excellence, and profound spiritual reverence for the natural world can flourish in perfect harmony.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 4. Our Core Values (5 Formal Pillars) -->
<section style="background: #11212d; padding: 100px 0; border-top:1px solid rgba(74, 92, 108, 0.35); border-bottom:1px solid rgba(74, 92, 108, 0.35);">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Guiding Principles</span>
            <h2 class="section-title" style="color:#ccd0cf;">Our Five Core Values</h2>
            <div class="gold-divider"></div>
            <p class="section-lead" style="color:#9ba8ab;">Every element of our hotel operations works in rhythm with nature, maintaining a delicate equilibrium between guest indulgence and ecological flourishing.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
            
            <div class="experience-card">
                <div class="experience-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 style="font-size:1.25rem; margin-bottom:12px; color:#ccd0cf;">1. Reverent Hospitality</h3>
                <p style="font-size:0.92rem; color:var(--text-muted); line-height:1.6;">Rooted in *Atithi Devo Bhava*, guest care is conducted with quiet dignity, intuitive anticipation, and warmth that feels deeply familial.</p>
            </div>

            <div class="experience-card">
                <div class="experience-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M19 10.5h-5.5V5c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v5.5H5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5h5.5V19c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-5.5H19c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5z"/></svg>
                </div>
                <h3 style="font-size:1.25rem; margin-bottom:12px; color:#ccd0cf;">2. Uncompromised Comfort</h3>
                <p style="font-size:0.92rem; color:var(--text-muted); line-height:1.6;">From temperature-controlled plunge pools to handcrafted organic mattresses, physical restoration is elevated to an art form.</p>
            </div>

            <div class="experience-card">
                <div class="experience-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L2 12h3v8h14v-8h3L12 3zm0 13c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>
                </div>
                <h3 style="font-size:1.25rem; margin-bottom:12px; color:#ccd0cf;">3. Architectural Quality</h3>
                <p style="font-size:0.92rem; color:var(--text-muted); line-height:1.6;">Built exclusively with local river stone, sustainable teak, brass accents, and glass facades that celebrate the surrounding canopy.</p>
            </div>

            <div class="experience-card">
                <div class="experience-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c.61 0 1.1.49 1.1 1.1s-.49 1.1-1.1 1.1-1.1-.49-1.1-1.1.49-1.1 1.1-1.1z"/></svg>
                </div>
                <h3 style="font-size:1.25rem; margin-bottom:12px; color:#ccd0cf;">4. Reverence for Nature</h3>
                <p style="font-size:0.92rem; color:var(--text-muted); line-height:1.6;">Zero single-use plastics, 100% solar microgrid power, rainwater regeneration, and strict non-invasive illumination protect nighttime fauna.</p>
            </div>

            <div class="experience-card">
                <div class="experience-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h3 style="font-size:1.25rem; margin-bottom:12px; color:#ccd0cf;">5. Guest Well-Being</h3>
                <p style="font-size:0.92rem; color:var(--text-muted); line-height:1.6;">Every journey is personalized to release emotional tension, restore sleep cycles, and cultivate lasting inner tranquility.</p>
            </div>

        </div>
    </div>
</section>

<!-- 5. Nature & Sustainability Commitments -->
<section class="section-sustainability reveal-on-scroll">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Ecological Stewardship</span>
            <h2 class="section-title">Living in True Harmony with the Forest</h2>
            <div class="gold-divider"></div>
        </div>

        <div class="sustainability-grid">
            <div class="sustainability-card">
                <div class="sustainability-stat">100%</div>
                <h4 class="sustainability-title">Solar Energy Grid</h4>
                <p class="sustainability-desc">Our entire sanctuary operates independently on solar photovoltaic generation, minimizing carbon footprint across all operations.</p>
            </div>
            <div class="sustainability-card">
                <div class="sustainability-stat">Zero</div>
                <h4 class="sustainability-title">Single-Use Plastics</h4>
                <p class="sustainability-desc">From glass water carafes to copper in-room bath amenities, single-use synthetics are prohibited throughout the estate.</p>
            </div>
            <div class="sustainability-card">
                <div class="sustainability-stat">10M+ L</div>
                <h4 class="sustainability-title">Rainwater Harvesting</h4>
                <p class="sustainability-desc">Integrated catchments and bioswales replenish the regional groundwater table and supply our botanical organic gardens.</p>
            </div>
        </div>
    </div>
</section>

<!-- 6. Facilities & Hospitality Overview -->
<section style="padding: 100px 0; background: var(--bg-deep);">
    <div class="container">
        <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
            <span class="section-label">Sanctuary Grounds</span>
            <h2 class="section-title">The Estate &amp; Facilities</h2>
            <div class="gold-divider"></div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px;">
            <div class="amenity-item" style="text-align:left;">
                <h4 style="color:#ccd0cf; font-size:1.2rem; margin-bottom:8px;">The Ayurvedic Shala</h4>
                <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.6;">Dedicated treatment suites with steam cabinets, natural herbal oil presses, and consultation chambers presided over by experienced Vaidyas.</p>
            </div>
            <div class="amenity-item" style="text-align:left;">
                <h4 style="color:#ccd0cf; font-size:1.2rem; margin-bottom:8px;">The Forest Dining Pavilion</h4>
                <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.6;">An open-air dining shala overlooking the woodland canopy, serving seasonal organic degustation menus tailored to guest dosha profiles.</p>
            </div>
            <div class="amenity-item" style="text-align:left;">
                <h4 style="color:#ccd0cf; font-size:1.2rem; margin-bottom:8px;">The Botanical Library</h4>
                <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.6;">A tranquil timber sanctuary housing curated literature on Indian wildlife, natural philosophy, botanical drawings, and rare poetry.</p>
            </div>
        </div>
    </div>
</section>

<!-- 7. Call to Action Banner -->
<section class="section-final-cta">
    <div class="container-narrow text-center">
        <span class="section-label" style="color:var(--warm-gold);">Your Sanctuary Awaits</span>
        <h2 style="font-size:clamp(2.4rem, 4.2vw, 3.6rem); color:#ccd0cf; margin-bottom:20px;">Experience the Harmony of Ashvkatha</h2>
        <p style="font-size:1.15rem; color:var(--text-muted); line-height:1.8; margin-bottom:36px; max-width:680px; margin-left:auto; margin-right:auto;">
            Choose from our 24 luxury suites and immerse yourself in the true luxury of nature.
        </p>
        <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
            <a href="rooms.php" class="btn btn-gold btn-lg">Browse Sanctuaries &rarr;</a>
            <a href="contact.php" class="btn btn-outline-gold btn-lg">Contact Concierge</a>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
