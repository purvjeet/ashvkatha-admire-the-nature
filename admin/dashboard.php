<?php
/**
 * Admin Dashboard - admin/dashboard.php
 * Ashvkatha – Admire the Nature
 */
$page_title = "Executive Dashboard";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// KPI Calculations
$total_rooms     = (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$available_rooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn();
$occupied_rooms  = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'checked_in'")->fetchColumn();

$total_bookings     = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings   = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$confirmed_bookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
$total_customers    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

$total_revenue = (float)$pdo->query("
    SELECT COALESCE(SUM(p.amount), 0) 
    FROM payments p 
    JOIN bookings b ON p.booking_id = b.id 
    WHERE p.payment_status = 'completed' 
      AND b.status NOT IN ('cancelled', 'rejected')
")->fetchColumn();

// Recent 8 Bookings
$recent_bookings = $pdo->query("
    SELECT 
        b.*, 
        u.name as customer_name,
        u.email as customer_email,
        r.room_number,
        rt.name as category_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    ORDER BY b.id DESC
    LIMIT 8
")->fetchAll();

// Category stats
$category_stats = $pdo->query("
    SELECT 
        rt.*,
        COUNT(r.id) as total_physical_rooms
    FROM room_types rt
    LEFT JOIN rooms r ON rt.id = r.room_type_id
    GROUP BY rt.id
    ORDER BY rt.base_price ASC
")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Executive Sanctuary Dashboard</h1>
        <p class="admin-page-desc">Real-time overview of room occupancy, incoming reservations, and sanctuary finances.</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="rooms.php" class="btn btn-sm btn-outline-gold">+ Add Room</a>
        <a href="bookings.php" class="btn btn-sm btn-gold">Manage Bookings</a>
    </div>
</div>

<!-- 8 KPI Statistics Grid -->
<div class="admin-kpis">
    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Total Rooms</span>
        <div class="admin-kpi-value"><?= $total_rooms ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">Across 6 Luxury Categories</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Available Physical Rooms</span>
        <div class="admin-kpi-value" style="color:#e6a15a;"><?= $available_rooms ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;"><?= ($total_rooms - $available_rooms) ?> in maintenance/cleaning</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Currently Checked-In</span>
        <div class="admin-kpi-value" style="color:#ccd0cf;"><?= $occupied_rooms ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">Active in-house guests</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Total Revenue</span>
        <div class="admin-kpi-value" style="color:#e6a15a;"><?= format_currency($total_revenue) ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">Settled payments</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Total Reservations</span>
        <div class="admin-kpi-value"><?= $total_bookings ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">All-time bookings</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Confirmed Bookings</span>
        <div class="admin-kpi-value" style="color:#e6a15a;"><?= $confirmed_bookings ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">Upcoming arrivals</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Pending Approval</span>
        <div class="admin-kpi-value" style="color:#9ba8ab;"><?= $pending_bookings ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">Requires staff attention</span>
    </div>

    <div class="admin-kpi-card">
        <span class="admin-kpi-title">Registered Customers</span>
        <div class="admin-kpi-value"><?= $total_customers ?></div>
        <span style="font-size:0.75rem; color:#9ba8ab; margin-top:2px;">Sanctuary community</span>
    </div>
</div>

<!-- Recent Reservations Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Recent Reservations</h2>
        <a href="bookings.php" class="btn btn-sm btn-outline-gold">View All Bookings &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ref Number</th>
                    <th>Guest Name</th>
                    <th>Sanctuary Room</th>
                    <th>Dates (Nights)</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Quick Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_bookings)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 40px; color: var(--admin-muted);">
                            No reservations on record yet. Make a test reservation from the customer website.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_bookings as $b): ?>
                        <?php $n = calculate_nights($b['check_in'], $b['check_out']); ?>
                        <tr>
                            <td>
                                <strong><?= e($b['booking_number']) ?></strong><br>
                                <span style="font-size:0.75rem; color:var(--admin-muted);"><?= format_date($b['created_at'], 'd M, h:i A') ?></span>
                            </td>
                            <td>
                                <strong><?= e($b['customer_name']) ?></strong><br>
                                <span style="font-size:0.75rem; color:var(--admin-muted);"><?= e($b['customer_email']) ?></span>
                            </td>
                            <td>
                                <?= e($b['category_name']) ?><br>
                                <strong style="color:var(--forest-green);">Room <?= e($b['room_number']) ?></strong>
                            </td>
                            <td>
                                <?= format_date($b['check_in'], 'd M') ?> &rarr; <?= format_date($b['check_out'], 'd M') ?><br>
                                <span style="font-size:0.75rem; color:var(--admin-muted);"><?= $n ?> <?= ($n === 1 ? 'Night' : 'Nights') ?></span>
                            </td>
                            <td>
                                <strong><?= format_currency($b['total_amount']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $bClass = 'badge-confirmed';
                                if ($b['status'] === 'pending') $bClass = 'badge-pending';
                                elseif ($b['status'] === 'cancelled' || $b['status'] === 'rejected') $bClass = 'badge-cancelled';
                                elseif ($b['status'] === 'checked_in') $bClass = 'badge-active';
                                elseif ($b['status'] === 'checked_out') $bClass = 'badge-completed';
                                ?>
                                <span class="badge <?= $bClass ?>"><?= ucfirst(e($b['status'])) ?></span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="bookings.php?search=<?= urlencode($b['booking_number']) ?>" class="btn-action btn-action-view">Manage</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 6 Categories Inventory Summary -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Sanctuary Categories Inventory (24 Rooms)</h2>
        <a href="rooms.php" class="btn btn-sm btn-outline-gold">Manage Individual Rooms &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Base Rate</th>
                    <th>Max Occupancy</th>
                    <th>Floor Area</th>
                    <th>Bedding</th>
                    <th>Rooms Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($category_stats as $cat): ?>
                    <tr>
                        <td>
                            <strong><?= e($cat['name']) ?></strong>
                        </td>
                        <td><?= format_currency($cat['base_price']) ?> / night</td>
                        <td><?= e($cat['capacity']) ?> Guests</td>
                        <td><?= e($cat['size_sqft']) ?> sq.ft</td>
                        <td><?= e($cat['bed_type']) ?></td>
                        <td>
                            <strong style="color:var(--forest-green); font-size:1.05rem;"><?= e($cat['total_physical_rooms']) ?> Rooms</strong>
                        </td>
                        <td>
                            <a href="room-types.php?edit=<?= $cat['id'] ?>" class="btn-action btn-action-edit">Edit Pricing &amp; Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
