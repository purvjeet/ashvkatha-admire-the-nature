<?php
/**
 * Customer Registration Page - register.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$errors = [];

if (is_logged_in()) {
    header("Location: my-bookings.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Security session expired. Please reload and try again.";
    }

    $name     = sanitize_input($_POST['name'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone    = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors[] = "Please provide your full legal name.";
    }
    if (!$email) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters in length.";
    }
    if ($password !== $confirm) {
        $errors[] = "Password confirmation does not match.";
    }

    // Check if email is already taken
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors[] = "An account with this email address already exists. Please sign in instead.";
        }
    }

    // Insert user
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password, role, status)
                VALUES (?, ?, ?, ?, 'customer', 'active')
            ");
            $stmt->execute([$name, $email, $phone, $hashedPassword]);
            $newId = (int)$pdo->lastInsertId();

            login_user([
                'id'    => $newId,
                'name'  => $name,
                'email' => $email,
                'role'  => 'customer'
            ]);

            set_flash_message('success', "Welcome to the Ashvkatha Sanctuary, {$name}! Your account has been registered.");
            header("Location: rooms.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Registration could not be completed at this time. Please try again.";
        }
    }
}

$page_title = "Create Patron Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card" style="max-width: 520px;">
        <div class="auth-header">
            <div class="auth-logo">ASHVKATHA</div>
            <div class="auth-subtitle">Admire the Nature</div>
            <h2 class="auth-title">Create Account</h2>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 4px;">Join our community of mindful travellers and wildlife supporters.</p>
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

        <form action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Maya Sterling" value="<?= e($_POST['name'] ?? '') ?>" required autofocus>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="maya@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="+91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 chars" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 10px;">
                Create Sanctuary Account &rarr;
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: #9ba8ab;">
            Already have an account? <a href="login.php" style="color: var(--warm-gold); font-weight: 700; text-decoration: underline;">Sign In here</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
