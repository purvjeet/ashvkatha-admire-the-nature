<?php
/**
 * Admin Guest Reviews Moderation
 * Ashvkatha – Admire the Nature
 */
$page_title = "Reviews Moderation";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Status Updates / Deletion
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('Security token expired. Please retry.', 'error');
        header('Location: reviews.php');
        exit;
    }

    $action = $_POST['action'];
    $review_id = intval($_POST['review_id'] ?? 0);

    if ($action === 'set_approved' && $review_id > 0) {
        $approved_val = intval($_POST['is_approved'] ?? 1);
        $stmt = $pdo->prepare("UPDATE reviews SET is_approved = ? WHERE id = ?");
        $stmt->execute([$approved_val, $review_id]);
        set_flash_message($approved_val ? "Review published live on website." : "Review hidden from website.", 'success');
        header('Location: reviews.php');
        exit;
    }

    if ($action === 'delete_review' && $review_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        set_flash_message("Review permanently deleted.", 'success');
        header('Location: reviews.php');
        exit;
    }
}

// Filter and Search
$status_filter = sanitize_input($_GET['status'] ?? 'all');
$rating_filter = intval($_GET['rating'] ?? 0);
$search = sanitize_input($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if ($status_filter === 'approved') {
    $where[] = "rv.is_approved = 1";
} elseif ($status_filter === 'pending') {
    $where[] = "rv.is_approved = 0";
}

if ($rating_filter >= 1 && $rating_filter <= 5) {
    $where[] = "rv.rating = ?";
    $params[] = $rating_filter;
}

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR rt.name LIKE ? OR rv.comment LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where);

// Aggregates
$total_reviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$avg_rating = $pdo->query("SELECT ROUND(COALESCE(AVG(rating), 5.0), 1) FROM reviews WHERE is_approved = 1")->fetchColumn();
$pending_count = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0")->fetchColumn();
$approved_count = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 1")->fetchColumn();

// Query
$query = "
    SELECT 
        rv.*,
        u.name AS guest_name,
        u.email AS guest_email,
        rt.name AS room_type_name
    FROM reviews rv
    JOIN users u ON rv.user_id = u.id
    JOIN room_types rt ON rv.room_type_id = rt.id
    WHERE {$where_sql}
    ORDER BY rv.id DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Guest Testimonials & Reviews</h1>
        <p class="admin-page-subtitle">Moderate guest ratings and showcase authentic testimonials on the sanctuary homepage.</p>
    </div>
</div>

<!-- KPIs -->
<div class="admin-kpis" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Average Rating (Live)</span>
        <span class="admin-kpi-value" style="color:var(--warm-gold);"><?= $avg_rating ?> / 5.0 &#9733;</span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Total Reviews</span>
        <span class="admin-kpi-value"><?= number_format($total_reviews) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Live on Website</span>
        <span class="admin-kpi-value" style="color:#4ade80;"><?= number_format($approved_count) ?></span>
    </div>
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Hidden / Moderated</span>
        <span class="admin-kpi-value" style="color:#f59e0b;"><?= number_format($pending_count) ?></span>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-filter-bar">
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="reviews.php" class="admin-filter-btn <?= $status_filter === 'all' && $rating_filter === 0 ? 'active' : '' ?>">All (<?= $total_reviews ?>)</a>
        <a href="reviews.php?status=approved" class="admin-filter-btn <?= $status_filter === 'approved' ? 'active' : '' ?>">Live / Approved (<?= $approved_count ?>)</a>
        <a href="reviews.php?status=pending" class="admin-filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">Hidden (<?= $pending_count ?>)</a>
        <a href="reviews.php?rating=5" class="admin-filter-btn <?= $rating_filter === 5 ? 'active' : '' ?>">5 Stars &#9733;</a>
    </div>

    <form method="GET" action="reviews.php" style="display:flex; gap:8px;">
        <input 
            type="text" 
            name="search" 
            placeholder="Search patron, text, room..." 
            value="<?= e($search) ?>" 
            class="admin-input" 
            style="min-width:240px; padding:8px 14px; font-size:0.85rem;"
        >
        <button type="submit" class="admin-btn admin-btn-outline" style="padding:8px 16px;">Search</button>
        <?php if (!empty($search) || $status_filter !== 'all' || $rating_filter > 0): ?>
            <a href="reviews.php" class="admin-btn admin-btn-outline" style="padding:8px 12px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Reviews Table -->
<div class="admin-card">
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Patron</th>
                    <th>Suite / Category</th>
                    <th>Rating</th>
                    <th style="max-width:350px;">Feedback</th>
                    <th>Date</th>
                    <th>Visibility</th>
                    <th style="text-align:right;">Moderation Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px; color:#888;">
                            No reviews found matching the filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rv): 
                        $status_badge = $rv['is_approved'] ? 'status-active' : 'status-pending';
                        $status_label = $rv['is_approved'] ? 'LIVE / APPROVED' : 'HIDDEN';
                    ?>
                    <tr>
                        <td>
                            <strong style="color:var(--ivory-cream);"><?= e($rv['guest_name']) ?></strong>
                            <div style="font-size:0.75rem; color:#888;"><?= e($rv['guest_email']) ?></div>
                        </td>
                        <td>
                            <span style="color:var(--warm-gold); font-size:0.85rem; font-weight:600;">
                                <?= e($rv['room_type_name']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="color:var(--warm-gold); font-size:1.1rem; letter-spacing:2px;">
                                <?= str_repeat('&#9733;', $rv['rating']) ?><span style="color:rgba(213,185,135,0.25);"><?= str_repeat('&#9733;', 5 - $rv['rating']) ?></span>
                            </div>
                        </td>
                        <td style="max-width:350px;">
                            <p style="margin:0; font-size:0.85rem; color:#ccc; line-height:1.4;">
                                "<?= e($rv['comment']) ?>"
                            </p>
                        </td>
                        <td>
                            <span style="font-size:0.8rem; color:#888;">
                                <?= date('d M Y', strtotime($rv['created_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $status_badge ?>">
                                <?= $status_label ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; justify-content:flex-end; gap:6px;">
                                <?php if (!$rv['is_approved']): ?>
                                    <form method="POST" action="reviews.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="set_approved">
                                        <input type="hidden" name="review_id" value="<?= e($rv['id']) ?>">
                                        <input type="hidden" name="is_approved" value="1">
                                        <button type="submit" class="admin-btn admin-btn-primary" style="padding:4px 8px; font-size:0.75rem;">
                                            Publish Live
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="reviews.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="set_approved">
                                        <input type="hidden" name="review_id" value="<?= e($rv['id']) ?>">
                                        <input type="hidden" name="is_approved" value="0">
                                        <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 8px; font-size:0.75rem;">
                                            Hide
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="reviews.php" style="display:inline;" onsubmit="return confirm('Permanently delete this review?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_review">
                                    <input type="hidden" name="review_id" value="<?= e($rv['id']) ?>">
                                    <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 8px; font-size:0.75rem; color:#f87171;">
                                        &times;
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
