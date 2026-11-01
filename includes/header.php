<?php
/**
 * Common Header Include
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title . " | Ashvkatha – Admire the Nature" : "Ashvkatha – Admire the Nature | Luxury Nature Resort & Sanctuary";
$page_desc  = isset($page_desc) ? $page_desc : "Experience sanctuary luxury amidst pristine nature at Ashvkatha. Handcrafted suites, private plunge pools, and organic Ayurvedic living.";
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_desc) ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= e($page_title) ?>">
    <meta property="og:description" content="<?= e($page_desc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://cms.vantara.in/uploads/JUNE_2026_HOMEPAGE_HORIZONTAL_e6ea29466f.mp4">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&family=Schibsted+Grotesk:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/rooms.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/responsive.css">
    
    <!-- Main Script -->
    <script src="js/script.js" defer></script>
</head>
<body class="<?= 'page-' . pathinfo($current_page, PATHINFO_FILENAME) ?>">

    <!-- Background Ambient Audio -->
    <audio id="ambient-audio" loop preload="none">
        <source src="<?= e(get_setting('ambient_audio', 'https://cms.vantara.in/uploads/meditation_music_without_nature_sound_256142_4fbaeb3540.mp3')) ?>" type="audio/mp3">
    </audio>

    <!-- Floating Media Dock (Video Play/Pause & Nature Ambient Audio side-by-side) -->
    <div class="floating-media-dock" id="floating-media-dock">
        <button type="button" id="video-toggle-btn" class="floating-media-btn" title="Pause Video" aria-label="Toggle Hero Video Playback">
            <svg id="video-icon-pause" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
            </svg>
            <svg id="video-icon-play" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </button>
        <button type="button" id="audio-toggle-btn" class="floating-media-btn" title="Play Nature Ambient Audio" aria-label="Toggle Nature Music">
            <svg id="audio-icon-muted" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.21.05-.42.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
            </svg>
            <svg id="audio-icon-playing" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
            </svg>
        </button>
    </div>

    <!-- Flash Alert Feedback Container -->
    <?= render_flash_message() ?>
