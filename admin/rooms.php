<?php
/**
 * Room Management - admin/rooms.php
 * Manage individual physical rooms (24 total initially)
 * Ashvkatha – Admire the Nature
 */
$page_title = "Manage Rooms (24 Inventory)";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Form Submissions (Add / Update / Safe Deactivate)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add Room
    if ($action === 'add_room') {
        $room_number  = sanitize_input($_POST['room_number'] ?? '');
        $room_type_id = (int)($_POST['room_type_id'] ?? 0);
        $floor        = (int)($_POST['floor'] ?? 1);
        $status       = sanitize_input($_POST['status'] ?? 'available');
        $notes        = sanitize_input($_POST['notes'] ?? '');

        if (!empty($room_number) && $room_type_id > 0) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO rooms (room_type_id, room_number, floor, status, notes)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$room_type_id, $room_number, $floor, $status, $notes]);
                set_flash_message('success', "Room {$room_number} added successfully to inventory.");
                header("Location: rooms.php");
                exit;
            } catch (PDOException $e) {
                set_flash_message('error', "Could not add room. Room number may already exist.");
            }
        }
    }

    // Update Room
    if ($action === 'edit_room') {
        $room_id      = (int)($_POST['room_id'] ?? 0);
        $room_type_id = (int)($_POST['room_type_id'] ?? 0);
        $floor        = (int)($_POST['floor'] ?? 1);
        $status       = sanitize_input($_POST['status'] ?? 'available');
        $notes        = sanitize_input($_POST['notes'] ?? '');

        if ($room_id > 0 && $room_type_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE rooms 
                SET room_type_id = ?, floor = ?, status = ?, notes = ? 
                WHERE id = ?
            ");
            $stmt->execute([$room_type_id, $floor, $status, $notes, $room_id]);
            set_flash_message('success', "Room details updated successfully.");
            header("Location: rooms.php");
            exit;
        }
    }

    // Safe Deactivation / Delete Protection
    if ($action === 'deactivate_room') {
        $room_id = (int)($_POST['room_id'] ?? 0);
        if ($room_id > 0) {
            // Check if room has historical bookings
            $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ?");
            $cntStmt->execute([$room_id]);
            $has_bookings = (int)$cntStmt->fetchColumn() > 0;

            if ($has_bookings) {
                // Safe deactivation: set status to inactive to preserve booking history
                $pdo->prepare("UPDATE rooms SET status = 'inactive' WHERE id = ?")->execute([$room_id]);
                set_flash_message('info', "Room has historical reservations. It has been safely deactivated rather than deleted.");
            } else {
                // Permanently delete only if never booked
                $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$room_id]);
                set_flash_message('success', "Room removed from inventory.");
            }
            header("Location: rooms.php");
            exit;
        }
    }
}

// Fetch room categories for dropdowns
$categories = $pdo->query("SELECT id, name FROM room_types ORDER BY base_price ASC")->fetchAll();

// Fetch all physical rooms
$filter_cat = (int)($_GET['category'] ?? 0);
$filter_status = sanitize_input($_GET['status'] ?? '');

$sql = "
    SELECT r.*, rt.name as category_name, rt.base_price,
           (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.id) as booking_count
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE 1=1
";
$params = [];
if ($filter_cat > 0) {
    $sql .= " AND r.room_type_id = ?";
    $params[] = $filter_cat;
}
if (!empty($filter_status)) {
    $sql .= " AND r.status = ?";
    $params[] = $filter_status;
}
$sql .= " ORDER BY r.floor ASC, r.room_number ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Physical Room Inventory (<?= count($rooms) ?> Rooms)</h1>
        <p class="admin-page-desc">Track real-time room statuses, housekeeping, and maintenance across all floors.</p>
    </div>
    <button type="button" class="btn btn-sm btn-gold" onclick="document.getElementById('modal-add-room').classList.add('show')">
        + Add New Physical Room
    </button>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding: 18px 24px;">
    <form action="rooms.php" method="GET" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
        <div>
            <label style="font-size:0.75rem; font-weight:700; color:var(--admin-muted); display:block; margin-bottom:4px;">Filter Category</label>
            <select name="category" class="form-control" style="width: auto; padding: 8px 12px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="0">All 6 Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filter_cat === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size:0.75rem; font-weight:700; color:var(--admin-muted); display:block; margin-bottom:4px;">Filter Status</label>
            <select name="status" class="form-control" style="width: auto; padding: 8px 12px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="available" <?= ($filter_status === 'available') ? 'selected' : '' ?>>Available</option>
                <option value="maintenance" <?= ($filter_status === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                <option value="cleaning" <?= ($filter_status === 'cleaning') ? 'selected' : '' ?>>Cleaning</option>
                <option value="inactive" <?= ($filter_status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <?php if ($filter_cat || $filter_status): ?>
            <div style="align-self: flex-end; margin-bottom: 4px;">
                <a href="rooms.php" style="font-size:0.85rem; color:var(--forest-green); text-decoration:underline;">Reset Filters</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Rooms Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Room #</th>
                    <th>Category</th>
                    <th>Floor</th>
                    <th>Rate / Night</th>
                    <th>Current Status</th>
                    <th>Notes / Vista</th>
                    <th>History</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td>
                            <strong style="font-size:1.15rem; color:var(--forest-green);">Room <?= e($r['room_number']) ?></strong>
                        </td>
                        <td><?= e($r['category_name']) ?></td>
                        <td>Floor <?= e($r['floor']) ?></td>
                        <td><?= format_currency($r['base_price']) ?></td>
                        <td>
                            <?php
                            $stClass = 'badge-available';
                            if ($r['status'] === 'maintenance') $stClass = 'badge-maintenance';
                            elseif ($r['status'] === 'cleaning') $stClass = 'badge-cleaning';
                            elseif ($r['status'] === 'inactive') $stClass = 'badge-cancelled';
                            ?>
                            <span class="badge <?= $stClass ?>"><?= ucfirst(e($r['status'])) ?></span>
                        </td>
                        <td style="font-size:0.85rem; color:var(--admin-muted);"><?= e($r['notes'] ?: 'None') ?></td>
                        <td>
                            <span style="font-size:0.85rem; font-weight:600;"><?= e($r['booking_count']) ?> bookings</span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button type="button" class="btn-action btn-action-edit" onclick="editRoom(<?= htmlspecialchars(json_encode($r)) ?>)">
                                    Edit
                                </button>
                                <?php if ($r['status'] !== 'inactive'): ?>
                                    <form action="rooms.php" method="POST" onsubmit="return confirm('Deactivate or remove Room <?= e($r['room_number']) ?>?');" style="display:inline;">
                                        <input type="hidden" name="action" value="deactivate_room">
                                        <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn-action btn-action-delete">Deactivate</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Room Modal -->
<div class="admin-modal" id="modal-add-room">
    <div class="admin-modal-dialog">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:1.4rem; color:var(--forest-green);">Add Physical Room</h3>
            <button type="button" onclick="document.getElementById('modal-add-room').classList.remove('show')" style="background:none; border:none; font-size:1.8rem; cursor:pointer;">&times;</button>
        </div>
        <form action="rooms.php" method="POST">
            <input type="hidden" name="action" value="add_room">

            <div class="form-group">
                <label for="room_number" class="form-label">Room Number * (e.g. 701)</label>
                <input type="text" name="room_number" id="room_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="room_type_id" class="form-label">Category *</label>
                <select name="room_type_id" id="room_type_id" class="form-control" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="floor" class="form-label">Floor Number</label>
                    <input type="number" name="floor" id="floor" class="form-control" value="1" min="1" max="10" required>
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Initial Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="cleaning">Cleaning</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes / View</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g. East wing facing zen garden"></textarea>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; padding:13px; margin-top:10px;">Save Room &rarr;</button>
        </form>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="admin-modal" id="modal-edit-room">
    <div class="admin-modal-dialog">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:1.4rem; color:var(--forest-green);">Edit Room <span id="edit-room-num"></span></h3>
            <button type="button" onclick="document.getElementById('modal-edit-room').classList.remove('show')" style="background:none; border:none; font-size:1.8rem; cursor:pointer;">&times;</button>
        </div>
        <form action="rooms.php" method="POST">
            <input type="hidden" name="action" value="edit_room">
            <input type="hidden" name="room_id" id="edit-room-id" value="">

            <div class="form-group">
                <label for="edit_room_type_id" class="form-label">Category</label>
                <select name="room_type_id" id="edit_room_type_id" class="form-control" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="edit_floor" class="form-label">Floor</label>
                    <input type="number" name="floor" id="edit_floor" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_status" class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_notes" class="form-label">Notes</label>
                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; padding:13px; margin-top:10px;">Update Room Details &rarr;</button>
        </form>
    </div>
</div>

<script>
function editRoom(room) {
    document.getElementById('edit-room-id').value = room.id;
    document.getElementById('edit-room-num').innerText = room.room_number;
    document.getElementById('edit_room_type_id').value = room.room_type_id;
    document.getElementById('edit_floor').value = room.floor;
    document.getElementById('edit_status').value = room.status;
    document.getElementById('edit_notes').value = room.notes || '';
    document.getElementById('modal-edit-room').classList.add('show');
}
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
