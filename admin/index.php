<?php
/**
 * Admin Login Gateway - admin/index.php
 * Ashvkatha – Admire the Nature
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    header("Location: dashboard.php");
    exit;
}

$pdo = get_db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Security session expired. Please reload and try again.";
    }

    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) {
        $errors[] = "Please provide both admin email and password.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            login_user($admin);
            set_flash_message('success', "Welcome to the Administration Console, {$admin['name']}.");
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid administrator credentials or unauthorized account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Sign In | Ashvkatha</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Schibsted+Grotesk:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body style="background: #06141b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div class="auth-card" style="background:#11212d; border: 1.5px solid #4a5c6c; box-shadow: 0 25px 60px rgba(6,20,27,0.7);">
        <div class="auth-header">
            <div class="auth-logo" style="color:#e6a15a;">ASHVKATHA</div>
            <div class="auth-subtitle" style="color:#9ba8ab;">Hotel Management Console</div>
            <h2 class="auth-title" style="font-size:1.6rem; color:#ccd0cf;">Admin Authentication</h2>
            <p style="font-size:0.85rem; color:#9ba8ab; margin-top:4px;">Restricted access for hotel staff &amp; sanctuary managers.</p>
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

        <form action="index.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label for="email" class="form-label">Admin Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="admin@ashvkatha.com" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Admin Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 10px;">
                Authenticate &amp; Open Console &rarr;
            </button>
        </form>

        <div style="margin-top: 24px; padding: 14px; background: var(--ivory); border-radius: var(--radius-sm); font-size: 0.8rem; color: var(--text-muted);">
            <div>Default Admin: <code>admin@ashvkatha.com</code></div>
            <div>Password: <code>admin123</code></div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="../index.php" style="font-size: 0.85rem; color: var(--forest-green); text-decoration: underline;">&larr; Return to Guest Website</a>
        </div>
    </div>

</body>
</html>
