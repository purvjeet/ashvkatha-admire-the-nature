<?php
/**
 * Logout Action - logout.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout_user();
set_flash_message('info', 'You have been safely signed out. We look forward to welcoming you back to Ashvkatha.');
header("Location: index.php");
exit;
