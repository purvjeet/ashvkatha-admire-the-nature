<?php
/**
 * Customer & Admin Login Page - login.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$errors = [];
$redirect = sanitize_input($_GET['redirect'] ?? 'my-bookings.php');

// If already logged in, redirect
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: " . ($redirect ?: 'my-bookings.php'));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Security session expired. Please reload and try again.";
    }

    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $redirect = sanitize_input($_POST['redirect'] ?? 'my-bookings.php');

    if (!$email) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors[] = "Please provide your account password.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['status']) && $user['status'] === 'inactive') {
                $errors[] = "Your patron account has been deactivated. Please contact hotel management.";
            } else {
                login_user($user);
                set_flash_message('success', "Welcome back, {$user['name']}! You are signed in.");

                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: " . ($redirect ?: 'my-bookings.php'));
                }
                exit;
            }
        } else {
            $errors[] = "Invalid credentials. Please verify your email and password.";
        }
    }
}

$page_title = "Guest Sign In";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">ASHVKATHA</div>
            <div class="auth-subtitle">Admire the Nature</div>
            <h2 class="auth-title">Guest Sign In</h2>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 4px;">Sign in to view your reservations and sanctuary privileges.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="padding: 12px 16px; background: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; border-radius: var(--radius-sm); margin-bottom: 20px; color: #dc3545; font-size: 0.88rem;">
                <ul style="margin:0; padding-left: 18px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="guest@ashvkatha.com" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label for="password" class="form-label">Password</label>
                </div>
                <input type="password" name="password" id="password" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 10px;">
                Sign In to Sanctuary &rarr;
            </button>
        </form>

        <!-- Quick Demo Credentials Callout -->
        <div style="margin-top: 24px; padding: 14px; background: rgba(6, 20, 27, 0.75); border-radius: var(--radius-sm); border: 1px solid rgba(74, 92, 108, 0.35); font-size: 0.8rem; color: #9ba8ab;">
            <strong style="color:var(--warm-gold); display:block; margin-bottom:4px;">Demo Accounts:</strong>
            <div>Customer: <code>guest@ashvkatha.com</code> / <code>guest123</code></div>
            <div>Admin: <code>admin@ashvkatha.com</code> / <code>admin123</code></div>
        </div>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: #9ba8ab;">
            Don't have an account? <a href="register.php" style="color: var(--warm-gold); font-weight: 700; text-decoration: underline;">Create Sanctuary Account</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
