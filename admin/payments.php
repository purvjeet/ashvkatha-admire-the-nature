<?php
/**
 * Admin Payments & Transactions Ledger
 * Ashvkatha – Admire the Nature
 */
$page_title = "Payment Ledger";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Payment Status Update (e.g. mark completed, refunded, failed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('Security token verification failed.', 'error');
        header('Location: payments.php');
        exit;
    }

    $action = $_POST['action'];
    $payment_id = intval($_POST['payment_id'] ?? 0);

    if ($action === 'update_payment_status' && $payment_id > 0) {
        $new_status = sanitize_input($_POST['new_status'] ?? '');
        $allowed = ['completed', 'pending', 'failed', 'refunded'];
        if (in_array($new_status, $allowed, true)) {
            $stmt = $pdo->prepare("UPDATE payments SET payment_status = ? WHERE id = ?");
            $stmt->execute([$new_status, $payment_id]);
            set_flash_message("Payment transaction #{$payment_id} marked as " . strtoupper($new_status), 'success');
        } else {
            set_flash_message("Invalid payment status requested.", 'error');
        }
        header('Location: payments.php');
        exit;
    }
}

// Search and Filter
$status_filter = sanitize_input($_GET['status'] ?? 'all');
$method_filter = sanitize_input($_GET['method'] ?? 'all');
$search = sanitize_input($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if ($status_filter !== 'all' && in_array($status_filter, ['completed', 'pending', 'failed', 'refunded'], true)) {
    $where[] = "p.payment_status = ?";
    $params[] = $status_filter;
}

if ($method_filter !== 'all' && in_array($method_filter, ['Cash', 'UPI', 'Online Payment'], true)) {
    $where[] = "p.payment_method = ?";
    $params[] = $method_filter;
}

if (!empty($search)) {
    $where[] = "(p.transaction_id LIKE ? OR b.booking_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where);

// Metrics
$revenue_total = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'completed'")->fetchColumn();
$count_completed = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'completed'")->fetchColumn();
$count_pending = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetchColumn();
$count_refunded = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'refunded'")->fetchColumn();

// Query Payments
$query = "
    SELECT 
        p.*,
        b.booking_number,
        b.check_in,
        b.check_out,
        b.status as booking_status,
        u.name as guest_name,
        u.email as guest_email,
        r.room_number,
        rt.name as room_type_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON p.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE {$where_sql}
    ORDER BY p.id DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Payments & Financial Ledger</h1>
        <p class="admin-page-subtitle">Audited financial transactions, receipts, and settlement logs.</p>
    </div>
</div>

<!-- Financial KPIs -->
<div class="admin-kpis" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Settled Revenue</span>
        <span class="admin-kpi-value" style="color:var(--warm-gold);"><?= format_currency($revenue_total) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Completed Transactions</span>
        <span class="admin-kpi-value" style="color:#4ade80;"><?= number_format($count_completed) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Pending Settlements</span>
        <span class="admin-kpi-value" style="color:#f59e0b;"><?= number_format($count_pending) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Refunded</span>
        <span class="admin-kpi-value" style="color:#f87171;"><?= number_format($count_refunded) ?></span>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-filter-bar">
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="payments.php" class="admin-filter-btn <?= $status_filter === 'all' && $method_filter === 'all' ? 'active' : '' ?>">All</a>
        <a href="payments.php?status=completed" class="admin-filter-btn <?= $status_filter === 'completed' ? 'active' : '' ?>">Completed</a>
        <a href="payments.php?status=pending" class="admin-filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
        <a href="payments.php?status=refunded" class="admin-filter-btn <?= $status_filter === 'refunded' ? 'active' : '' ?>">Refunded</a>
        <a href="payments.php?method=Online+Payment" class="admin-filter-btn <?= $method_filter === 'Online Payment' ? 'active' : '' ?>">Online</a>
        <a href="payments.php?method=UPI" class="admin-filter-btn <?= $method_filter === 'UPI' ? 'active' : '' ?>">UPI</a>
        <a href="payments.php?method=Cash" class="admin-filter-btn <?= $method_filter === 'Cash' ? 'active' : '' ?>">Cash</a>
    </div>

    <form method="GET" action="payments.php" style="display:flex; gap:8px;">
        <input 
            type="text" 
            name="search" 
            placeholder="Search Txn ID, Booking #, Guest..." 
            value="<?= e($search) ?>" 
            class="admin-input" 
            style="min-width:240px; padding:8px 14px; font-size:0.85rem;"
        >
        <button type="submit" class="admin-btn admin-btn-outline" style="padding:8px 16px;">Search</button>
        <?php if (!empty($search) || $status_filter !== 'all' || $method_filter !== 'all'): ?>
            <a href="payments.php" class="admin-btn admin-btn-outline" style="padding:8px 12px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Payments Ledger Table -->
<div class="admin-card">
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Txn ID</th>
                    <th>Booking Ref</th>
                    <th>Patron Name</th>
                    <th>Suite Assigned</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date Paid</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px; color:#888;">
                            No payment records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): 
                        $badge_color = match($p['payment_status']) {
                            'completed' => '#4ade80',
                            'pending' => '#f59e0b',
                            'failed' => '#f87171',
                            'refunded' => '#a78bfa',
                            default => '#ccc'
                        };
                    ?>
                    <tr>
                        <td>
                            <code style="color:var(--warm-gold); font-weight:700; font-size:0.85rem; background:rgba(213,185,135,0.08); padding:3px 6px; border-radius:4px;">
                                <?= e($p['transaction_id']) ?>
                            </code>
                        </td>
                        <td>
                            <a href="bookings.php?search=<?= urlencode($p['booking_number']) ?>" style="color:var(--ivory-cream); font-weight:600; text-decoration:none;">
                                <?= e($p['booking_number']) ?>
                            </a>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--ivory-cream);"><?= e($p['guest_name']) ?></div>
                            <div style="font-size:0.75rem; color:#888;"><?= e($p['guest_email']) ?></div>
                        </td>
                        <td>
                            <div style="font-size:0.85rem; color:var(--warm-gold); font-weight:600;">Room <?= e($p['room_number']) ?></div>
                            <div style="font-size:0.75rem; color:#888;"><?= e($p['room_type_name']) ?></div>
                        </td>
                        <td>
                            <strong style="color:var(--warm-gold); font-size:1.05rem;">
                                <?= format_currency($p['amount']) ?>
                            </strong>
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(255,255,255,0.08); color:var(--ivory-cream);">
                                <?= e($p['payment_method']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge" style="color:<?= $badge_color ?>; border-color:<?= $badge_color ?>;">
                                <?= strtoupper($p['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-size:0.8rem; color:#aaa;">
                                <?= date('d M Y, h:i A', strtotime($p['paid_at'])) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" action="payments.php" style="display:inline-block;" onsubmit="return confirm('Update payment status?');">
                                <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                <input type="hidden" name="action" value="update_payment_status">
                                <input type="hidden" name="payment_id" value="<?= e($p['id']) ?>">
                                <select name="new_status" class="admin-select" style="padding:4px 8px; font-size:0.75rem; width:auto;" onchange="this.form.submit()">
                                    <option value="" disabled selected>Update</option>
                                    <option value="completed" <?= $p['payment_status'] === 'completed' ? 'disabled' : '' ?>>Completed</option>
                                    <option value="pending" <?= $p['payment_status'] === 'pending' ? 'disabled' : '' ?>>Pending</option>
                                    <option value="refunded" <?= $p['payment_status'] === 'refunded' ? 'disabled' : '' ?>>Refunded</option>
                                    <option value="failed" <?= $p['payment_status'] === 'failed' ? 'disabled' : '' ?>>Failed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
