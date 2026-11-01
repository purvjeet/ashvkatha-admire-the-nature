<?php
/**
 * Admin Panel Common Header & Sidebar Layout
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/../includes/functions.php';

// Require administrator authentication
require_admin('index.php?error=unauthorized');

$admin_user = current_user();
$current_admin_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_title) ? $page_title . " | Ashvkatha Admin" : "Dashboard | Ashvkatha Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@400;500;600;700&family=Schibsted+Grotesk:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">

<!-- Admin Sidebar Navigation -->
<aside class="admin-sidebar">
    <a href="dashboard.php" class="admin-sidebar-brand">
        <span class="admin-brand-title">ASHVKATHA</span>
        <span class="admin-brand-sub">Admin Sanctuary Console</span>
    </a>

    <ul class="admin-nav">
        <li class="admin-nav-item">
            <a href="dashboard.php" class="<?= ($current_admin_page === 'dashboard.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="bookings.php" class="<?= ($current_admin_page === 'bookings.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                <span>Reservations</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="rooms.php" class="<?= ($current_admin_page === 'rooms.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 4H5c-1.11 0-2 .9-2 2v12c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.89-2-2-2zm0 14H5V8h14v10z"/></svg>
                <span>Room Inventory (24)</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="room-types.php" class="<?= ($current_admin_page === 'room-types.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
                <span>Room Categories (6)</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="customers.php" class="<?= ($current_admin_page === 'customers.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <span>Customers</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="payments.php" class="<?= ($current_admin_page === 'payments.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                <span>Payments</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="services.php" class="<?= ($current_admin_page === 'services.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                <span>Experiences / Services</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="reviews.php" class="<?= ($current_admin_page === 'reviews.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                <span>Guest Reviews</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="settings.php" class="<?= ($current_admin_page === 'settings.php') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                <span>Hotel Settings</span>
            </a>
        </li>
    </ul>

    <div class="admin-sidebar-footer">
        <div class="admin-user-info">
            <span class="admin-user-name"><?= e($admin_user['name']) ?></span>
            <span>Administrator</span>
        </div>
        <a href="../logout.php" title="Sign Out" style="color:#ff7b7b; display:flex;">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3H9c-1.1 0-2 .9-2 2v4h2V5h11v14H9v-4H7v4c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
        </a>
    </div>
</aside>

<!-- Admin Main Page Content Container -->
<main class="admin-main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <a href="../index.php" target="_blank" style="font-size:0.85rem; color:var(--warm-gold); font-weight:600; text-decoration:none;">&larr; View Live Customer Website</a>
        </div>
    </div>
    
    <?= render_flash_message() ?>
