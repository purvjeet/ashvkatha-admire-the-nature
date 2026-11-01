<?php
/**
 * Admin Hotel Services & Experiences Management
 * Ashvkatha – Admire the Nature
 */
$page_title = "Experiences & Services";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Service Add / Edit / Toggle / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('Security token expired. Please retry.', 'error');
        header('Location: services.php');
        exit;
    }

    $action = $_POST['action'];

    if ($action === 'create_service') {
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $icon = sanitize_input($_POST['icon'] ?? 'spa');
        $status = sanitize_input($_POST['status'] ?? 'active');

        if (!empty($name) && $price >= 0) {
            $stmt = $pdo->prepare("INSERT INTO services (name, description, price, icon, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $icon, $status]);
            set_flash_message("New sanctuary experience '{$name}' created successfully.", 'success');
        } else {
            set_flash_message("Please provide a valid experience name and price.", 'error');
        }
        header('Location: services.php');
        exit;
    }

    if ($action === 'update_service') {
        $id = intval($_POST['service_id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $icon = sanitize_input($_POST['icon'] ?? 'spa');
        $status = sanitize_input($_POST['status'] ?? 'active');

        if ($id > 0 && !empty($name) && $price >= 0) {
            $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, price = ?, icon = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $icon, $status, $id]);
            set_flash_message("Experience '{$name}' updated successfully.", 'success');
        } else {
            set_flash_message("Invalid experience details provided.", 'error');
        }
        header('Location: services.php');
        exit;
    }

    if ($action === 'toggle_status') {
        $id = intval($_POST['service_id'] ?? 0);
        $curr = sanitize_input($_POST['current_status'] ?? 'active');
        $new_st = ($curr === 'active') ? 'inactive' : 'active';
        $stmt = $pdo->prepare("UPDATE services SET status = ? WHERE id = ?");
        $stmt->execute([$new_st, $id]);
        set_flash_message("Experience status updated to " . strtoupper($new_st), 'success');
        header('Location: services.php');
        exit;
    }

    if ($action === 'delete_service') {
        $id = intval($_POST['service_id'] ?? 0);
        // Check if referenced in booking_services
        $chk = $pdo->prepare("SELECT COUNT(*) FROM booking_services WHERE service_id = ?");
        $chk->execute([$id]);
        if ($chk->fetchColumn() > 0) {
            // Safe deactivation
            $stmt = $pdo->prepare("UPDATE services SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message("Experience has past booking records and has been safely set to INACTIVE instead of deleting.", 'info');
        } else {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message("Experience permanently removed.", 'success');
        }
        header('Location: services.php');
        exit;
    }
}

// Fetch all services with usage statistics
$services = $pdo->query("
    SELECT 
        s.*,
        COUNT(bs.id) AS bookings_count,
        COALESCE(SUM(bs.total_price), 0) AS total_revenue
    FROM services s
    LEFT JOIN booking_services bs ON bs.service_id = s.id
    GROUP BY s.id
    ORDER BY s.id ASC
")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Curated Experiences & Services</h1>
        <p class="admin-page-subtitle">Manage retreat add-ons, spa therapies, wildlife excursions, and dining experiences.</p>
    </div>
    <div>
        <button type="button" class="admin-btn admin-btn-primary" onclick="openCreateServiceModal()">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            <span>Add New Experience</span>
        </button>
    </div>
</div>

<!-- Services Cards Grid -->
<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:20px;">
    <?php foreach ($services as $srv): ?>
        <div class="admin-card" style="display:flex; flex-direction:column; justify-content:space-between; position:relative;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <div>
                        <span class="badge" style="background:rgba(213,185,135,0.15); color:var(--warm-gold); text-transform:uppercase; font-size:0.7rem; letter-spacing:0.05em;">
                            <?= e($srv['icon']) ?>
                        </span>
                        <h3 style="color:var(--ivory-cream); font-size:1.15rem; margin:8px 0 4px 0; font-family:'Schibsted Grotesk',sans-serif;">
                            <?= e($srv['name']) ?>
                        </h3>
                    </div>
                    <span class="status-badge <?= $srv['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                        <?= strtoupper($srv['status']) ?>
                    </span>
                </div>

                <p style="font-size:0.85rem; color:#aaa; line-height:1.5; margin-bottom:16px;">
                    <?= e($srv['description']) ?>
                </p>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; align-items:baseline; padding:12px 0; border-top:1px solid rgba(213,185,135,0.15); border-bottom:1px solid rgba(213,185,135,0.15); margin-bottom:14px;">
                    <div>
                        <span style="font-size:0.75rem; color:#888; display:block;">Rate per Experience</span>
                        <strong style="color:var(--warm-gold); font-size:1.25rem;">
                            <?= format_currency($srv['price']) ?>
                        </strong>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:0.75rem; color:#888; display:block;">Lifetime Booked</span>
                        <span style="font-size:0.85rem; color:var(--ivory-cream);">
                            <?= $srv['bookings_count'] ?> times (<?= format_currency($srv['total_revenue']) ?>)
                        </span>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <form method="POST" action="services.php" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="service_id" value="<?= e($srv['id']) ?>">
                        <input type="hidden" name="current_status" value="<?= e($srv['status']) ?>">
                        <button type="submit" class="admin-btn admin-btn-outline" style="padding:6px 12px; font-size:0.75rem;">
                            <?= $srv['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>

                    <div style="display:flex; gap:6px;">
                        <button type="button" class="admin-btn admin-btn-outline" style="padding:6px 12px; font-size:0.75rem;" onclick="openEditServiceModal(<?= htmlspecialchars(json_encode($srv)) ?>)">
                            Edit
                        </button>
                        <form method="POST" action="services.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this experience?');">
                            <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete_service">
                            <input type="hidden" name="service_id" value="<?= e($srv['id']) ?>">
                            <button type="submit" class="admin-btn admin-btn-outline" style="padding:6px 10px; font-size:0.75rem; color:#f87171;">
                                &times;
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal: Create / Edit Service -->
<div id="serviceModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#132d25; border:1px solid rgba(213,185,135,0.25); border-radius:12px; max-width:520px; width:100%; padding:30px; box-shadow:0 20px 50px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(213,185,135,0.15); padding-bottom:15px; margin-bottom:20px;">
            <h3 id="serviceModalTitle" style="color:var(--warm-gold); font-size:1.2rem; margin:0;">Add New Experience</h3>
            <button type="button" onclick="closeServiceModal()" style="background:none; border:none; color:#bbb; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="services.php">
            <input type="hidden" name="csrf_token" value="<?= e(get_csrf_token()) ?>">
            <input type="hidden" name="action" id="serviceFormAction" value="create_service">
            <input type="hidden" name="service_id" id="serviceId" value="">

            <div style="margin-bottom:16px;">
                <label class="admin-label">Experience Name</label>
                <input type="text" name="name" id="serviceName" class="admin-input" required placeholder="e.g. Forest Herbal Massage">
            </div>

            <div style="margin-bottom:16px;">
                <label class="admin-label">Description</label>
                <textarea name="description" id="serviceDescription" class="admin-input" rows="3" required placeholder="Description of the healing or nature ritual..."></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label class="admin-label">Price (₹)</label>
                    <input type="number" step="0.01" name="price" id="servicePrice" class="admin-input" required placeholder="0.00">
                </div>
                <div>
                    <label class="admin-label">Icon Identifier</label>
                    <select name="icon" id="serviceIcon" class="admin-select">
                        <option value="spa">Spa / Wellness</option>
                        <option value="safari">Safari / Nature Excursion</option>
                        <option value="dining">Fine Forest Dining</option>
                        <option value="transfer">Luxury Chauffeur Transfer</option>
                        <option value="stargazing">Stargazing & Astronomy</option>
                        <option value="yoga">Yoga & Meditation</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label class="admin-label">Status</label>
                <select name="status" id="serviceStatus" class="admin-select">
                    <option value="active">Active (Visible in checkout)</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="admin-btn admin-btn-outline" onclick="closeServiceModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" id="serviceModalSubmit">Save Experience</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateServiceModal() {
    document.getElementById('serviceModalTitle').innerText = 'Add New Experience';
    document.getElementById('serviceFormAction').value = 'create_service';
    document.getElementById('serviceId').value = '';
    document.getElementById('serviceName').value = '';
    document.getElementById('serviceDescription').value = '';
    document.getElementById('servicePrice').value = '';
    document.getElementById('serviceIcon').value = 'spa';
    document.getElementById('serviceStatus').value = 'active';
    document.getElementById('serviceModalSubmit').innerText = 'Create Experience';
    document.getElementById('serviceModal').style.display = 'flex';
}

function openEditServiceModal(srv) {
    document.getElementById('serviceModalTitle').innerText = 'Edit ' + srv.name;
    document.getElementById('serviceFormAction').value = 'update_service';
    document.getElementById('serviceId').value = srv.id;
    document.getElementById('serviceName').value = srv.name;
    document.getElementById('serviceDescription').value = srv.description;
    document.getElementById('servicePrice').value = srv.price;
    document.getElementById('serviceIcon').value = srv.icon;
    document.getElementById('serviceStatus').value = srv.status;
    document.getElementById('serviceModalSubmit').innerText = 'Save Changes';
    document.getElementById('serviceModal').style.display = 'flex';
}

function closeServiceModal() {
    document.getElementById('serviceModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
