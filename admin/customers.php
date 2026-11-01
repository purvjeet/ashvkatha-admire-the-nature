<?php
/**
 * Admin Customers / Guests Directory
 * Ashvkatha – Admire the Nature
 */
$page_title = "Customer Directory";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Customer Status Toggle (Active / Blocked)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('Security token expired. Please retry.', 'error');
        header('Location: customers.php');
        exit;
    }

    $action = $_POST['action'];
    $customer_id = intval($_POST['customer_id'] ?? 0);

    if ($action === 'toggle_status' && $customer_id > 0) {
        $new_status = ($_POST['current_status'] === 'active') ? 'inactive' : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'customer'");
        $stmt->execute([$new_status, $customer_id]);
        set_flash_message("Guest account status changed to " . strtoupper($new_status), 'success');
        header('Location: customers.php');
        exit;
    }
}

// Search & Filter
$search = sanitize_input($_GET['search'] ?? '');
$status_filter = sanitize_input($_GET['status'] ?? 'all');

$where = ["u.role = 'customer'"];
$params = [];

if ($status_filter !== 'all' && in_array($status_filter, ['active', 'inactive'], true)) {
    $where[] = "u.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where);

// Query guests along with aggregate booking metrics
$query = "
    SELECT 
        u.*,
        COUNT(b.id) AS total_bookings,
        COALESCE(SUM(CASE WHEN b.status NOT IN ('cancelled', 'rejected') THEN b.total_amount ELSE 0 END), 0) AS total_spend,
        MAX(b.created_at) AS last_booking_date
    FROM users u
    LEFT JOIN bookings b ON b.user_id = u.id
    WHERE {$where_sql}
    GROUP BY u.id
    ORDER BY u.id DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Total counts
$total_guests_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$active_guests_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'")->fetchColumn();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Guest Directory</h1>
        <p class="admin-page-subtitle">Registered patron accounts, reservation history, and lifetime patronage.</p>
    </div>
</div>

<div class="admin-kpis" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Total Patrons</span>
        <span class="admin-kpi-value"><?= number_format($total_guests_count) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Active Accounts</span>
        <span class="admin-kpi-value" style="color:#4ade80;"><?= number_format($active_guests_count) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Restricted / Inactive</span>
        <span class="admin-kpi-value" style="color:#f87171;"><?= number_format($total_guests_count - $active_guests_count) ?></span>
    </div>
</div>

<div class="admin-filter-bar">
    <div style="display:flex; gap:8px;">
        <a href="customers.php" class="admin-filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">All Guests</a>
        <a href="customers.php?status=active" class="admin-filter-btn <?= $status_filter === 'active' ? 'active' : '' ?>">Active</a>
        <a href="customers.php?status=inactive" class="admin-filter-btn <?= $status_filter === 'inactive' ? 'active' : '' ?>">Inactive</a>
    </div>

    <form method="GET" action="customers.php" style="display:flex; gap:8px;">
        <?php if ($status_filter !== 'all'): ?>
            <input type="hidden" name="status" value="<?= e($status_filter) ?>">
        <?php endif; ?>
        <input 
            type="text" 
            name="search" 
            placeholder="Search patron name, email, phone..." 
            value="<?= e($search) ?>" 
            class="admin-input" 
            style="min-width:240px; padding:8px 14px; font-size:0.85rem;"
        >
        <button type="submit" class="admin-btn admin-btn-outline" style="padding:8px 16px;">Search</button>
        <?php if (!empty($search)): ?>
            <a href="customers.php" class="admin-btn admin-btn-outline" style="padding:8px 12px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Patron Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Total Stays</th>
                    <th>Lifetime Spend</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:#888;">
                            No customer records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600; color:var(--ivory-cream); font-size:0.95rem;"><?= e($c['name']) ?></div>
                            <div style="font-size:0.75rem; color:#888;">ID: #<?= e($c['id']) ?></div>
                        </td>
                        <td>
                            <a href="mailto:<?= e($c['email']) ?>" style="color:var(--warm-gold); font-size:0.85rem; text-decoration:none;">
                                <?= e($c['email']) ?>
                            </a>
                        </td>
                        <td>
                            <span style="font-size:0.85rem; color:#ccc;">
                                <?= e($c['phone'] ?: '—') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(213,185,135,0.15); color:var(--warm-gold);">
                                <?= e($c['total_bookings']) ?> <?= $c['total_bookings'] === 1 ? 'Booking' : 'Bookings' ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color:var(--warm-gold); font-size:0.95rem;">
                                <?= format_currency($c['total_spend']) ?>
                            </strong>
                        </td>
                        <td>
                            <span style="font-size:0.8rem; color:#888;">
                                <?= date('d M Y', strtotime($c['created_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $c['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                <?= strtoupper($c['status']) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; justify-content:flex-end; gap:6px;">
                                <!-- View Patron Bookings link -->
                                <a href="bookings.php?search=<?= urlencode($c['email']) ?>" class="admin-btn admin-btn-outline" style="padding:4px 10px; font-size:0.75rem;">
                                    View Stays
                                </a>

                                <!-- Toggle Active / Inactive -->
                                <form method="POST" action="customers.php" style="display:inline-block;" onsubmit="return confirm('Change status for <?= htmlspecialchars(addslashes($c['name'])) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="customer_id" value="<?= e($c['id']) ?>">
                                    <input type="hidden" name="current_status" value="<?= e($c['status']) ?>">
                                    <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 10px; font-size:0.75rem; color:<?= $c['status'] === 'active' ? '#f87171' : '#4ade80' ?>;">
                                        <?= $c['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
