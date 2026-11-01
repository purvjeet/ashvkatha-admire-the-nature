<?php
/**
 * Room Categories Management - admin/room-types.php
 * Manage the 6 sanctuary room categories, pricing, and descriptions
 * Ashvkatha – Admire the Nature
 */
$page_title = "Manage Room Categories";
require_once __DIR__ . '/header.php';

$pdo = get_db();

// Handle Category Updates / Additions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_category') {
        $cat_id      = (int)($_POST['category_id'] ?? 0);
        $name        = sanitize_input($_POST['name'] ?? '');
        $slug        = sanitize_input($_POST['slug'] ?? strtolower(str_replace(' ', '-', $name)));
        $short_desc  = sanitize_input($_POST['short_desc'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $base_price  = (float)($_POST['base_price'] ?? 100);
        $capacity    = (int)($_POST['capacity'] ?? 2);
        $size_sqft   = (int)($_POST['size_sqft'] ?? 400);
        $bed_type    = sanitize_input($_POST['bed_type'] ?? 'King Bed');
        $amenities   = sanitize_input($_POST['amenities'] ?? '');
        $image       = sanitize_input($_POST['image'] ?? '');

        if (!empty($name) && $base_price > 0) {
            if ($cat_id > 0) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE room_types 
                    SET name = ?, slug = ?, short_desc = ?, description = ?, 
                        base_price = ?, capacity = ?, size_sqft = ?, bed_type = ?, 
                        amenities = ?, image = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $slug, $short_desc, $description,
                    $base_price, $capacity, $size_sqft, $bed_type,
                    $amenities, $image, $cat_id
                ]);
                set_flash_message('success', "Category {$name} updated successfully.");
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO room_types (name, slug, short_desc, description, base_price, capacity, size_sqft, bed_type, amenities, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $slug, $short_desc, $description,
                    $base_price, $capacity, $size_sqft, $bed_type,
                    $amenities, $image
                ]);
                set_flash_message('success', "New Category {$name} created successfully.");
            }
            header("Location: room-types.php");
            exit;
        }
    }
}

// Fetch all categories
$categories = $pdo->query("
    SELECT rt.*, (SELECT COUNT(*) FROM rooms r WHERE r.room_type_id = rt.id) as physical_rooms_count
    FROM room_types rt
    ORDER BY rt.base_price ASC
")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Sanctuary Categories (<?= count($categories) ?> Types)</h1>
        <p class="admin-page-desc">Configure rates per night, maximum guest capacity, botanical amenities, and imagery.</p>
    </div>
    <button type="button" class="btn btn-sm btn-gold" onclick="openAddCategoryModal()">
        + Add Category
    </button>
</div>

<!-- Categories Grid -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 28px; margin-bottom: 40px;">
    <?php foreach ($categories as $cat): ?>
        <div class="admin-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">
            <div style="position:relative; width:100%; height:200px; overflow:hidden;">
                <img src="<?= e($cat['image']) ?>" alt="<?= e($cat['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                <div style="position:absolute; bottom:12px; right:12px; background:rgba(12,36,28,0.9); color:var(--warm-gold); padding:6px 14px; border-radius:30px; font-weight:700; font-size:1.1rem; border:1px solid rgba(213,185,135,0.3);">
                    <?= format_currency($cat['base_price']) ?> <span style="font-size:0.75rem; color:#fff; font-weight:400;">/ night</span>
                </div>
            </div>

            <div style="padding: 24px; display:flex; flex-direction:column; flex-grow:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                    <h3 style="font-size:1.45rem; margin:0; color:var(--forest-green);"><?= e($cat['name']) ?></h3>
                    <span class="badge badge-available"><?= e($cat['physical_rooms_count']) ?> Physical Rooms</span>
                </div>

                <p style="font-size:0.88rem; color:var(--admin-muted); line-height:1.5; margin-bottom:16px;">
                    <?= e($cat['short_desc']) ?>
                </p>

                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; padding:12px 0; border-top:1px solid var(--admin-border); border-bottom:1px solid var(--admin-border); margin-bottom:16px; font-size:0.82rem; text-align:center;">
                    <div>
                        <span style="color:var(--admin-muted); display:block; font-size:0.72rem; text-transform:uppercase;">Area</span>
                        <strong><?= e($cat['size_sqft']) ?> sq.ft</strong>
                    </div>
                    <div>
                        <span style="color:var(--admin-muted); display:block; font-size:0.72rem; text-transform:uppercase;">Capacity</span>
                        <strong><?= e($cat['capacity']) ?> Guests</strong>
                    </div>
                    <div>
                        <span style="color:var(--admin-muted); display:block; font-size:0.72rem; text-transform:uppercase;">Bed</span>
                        <strong><?= e($cat['bed_type']) ?></strong>
                    </div>
                </div>

                <div style="font-size:0.8rem; color:var(--admin-muted); margin-bottom:20px;">
                    <strong>Amenities:</strong> <?= e($cat['amenities']) ?>
                </div>

                <div style="margin-top:auto;">
                    <button type="button" class="btn btn-outline-gold btn-sm" style="width:100%;" onclick="openEditCategoryModal(<?= htmlspecialchars(json_encode($cat)) ?>)">
                        Edit Category &amp; Pricing &rarr;
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Category Modal -->
<div class="admin-modal" id="modal-category">
    <div class="admin-modal-dialog" style="max-width: 680px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 id="cat-modal-title" style="margin:0; font-size:1.5rem; color:var(--forest-green);">Edit Category</h3>
            <button type="button" onclick="document.getElementById('modal-category').classList.remove('show')" style="background:none; border:none; font-size:1.8rem; cursor:pointer;">&times;</button>
        </div>

        <form action="room-types.php" method="POST">
            <input type="hidden" name="action" value="save_category">
            <input type="hidden" name="category_id" id="cat-id" value="">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="cat-name" class="form-label">Category Name *</label>
                    <input type="text" name="name" id="cat-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="cat-slug" class="form-label">Slug (URL friendly)</label>
                    <input type="text" name="slug" id="cat-slug" class="form-control" placeholder="e.g. presidential-suite">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px;">
                <div class="form-group">
                    <label for="cat-price" class="form-label">Rate / Night ($) *</label>
                    <input type="number" step="0.01" name="base_price" id="cat-price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="cat-capacity" class="form-label">Max Guests</label>
                    <input type="number" name="capacity" id="cat-capacity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="cat-size" class="form-label">Size (sq.ft)</label>
                    <input type="number" name="size_sqft" id="cat-size" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="cat-bed" class="form-label">Bed Type</label>
                    <input type="text" name="bed_type" id="cat-bed" class="form-control" placeholder="King Bed" required>
                </div>
            </div>

            <div class="form-group">
                <label for="cat-image" class="form-label">Photo Image URL</label>
                <input type="url" name="image" id="cat-image" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="cat-short-desc" class="form-label">Short Summary (Shown in catalog cards)</label>
                <input type="text" name="short_desc" id="cat-short-desc" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="cat-desc" class="form-label">Full Narrative Description</label>
                <textarea name="description" id="cat-desc" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="cat-amenities" class="form-label">Amenities (Comma-separated)</label>
                <textarea name="amenities" id="cat-amenities" class="form-control" rows="2" placeholder="Forest View, Heated Jacuzzi, Organic Botanicals..."></textarea>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; padding:14px; margin-top:10px;">
                Save Sanctuary Category &rarr;
            </button>
        </form>
    </div>
</div>

<script>
function openAddCategoryModal() {
    document.getElementById('cat-modal-title').innerText = 'Add New Sanctuary Category';
    document.getElementById('cat-id').value = '';
    document.getElementById('cat-name').value = '';
    document.getElementById('cat-slug').value = '';
    document.getElementById('cat-price').value = '150.00';
    document.getElementById('cat-capacity').value = '2';
    document.getElementById('cat-size').value = '400';
    document.getElementById('cat-bed').value = 'King Bed';
    document.getElementById('cat-image').value = 'images/rooms/standard_room.jpg';
    document.getElementById('cat-short-desc').value = '';
    document.getElementById('cat-desc').value = '';
    document.getElementById('cat-amenities').value = 'Forest View, High-Speed Wi-Fi, Rain Shower, Artisanal Bar';
    document.getElementById('modal-category').classList.add('show');
}

function openEditCategoryModal(cat) {
    document.getElementById('cat-modal-title').innerText = 'Edit ' + cat.name;
    document.getElementById('cat-id').value = cat.id;
    document.getElementById('cat-name').value = cat.name;
    document.getElementById('cat-slug').value = cat.slug;
    document.getElementById('cat-price').value = cat.base_price;
    document.getElementById('cat-capacity').value = cat.capacity;
    document.getElementById('cat-size').value = cat.size_sqft;
    document.getElementById('cat-bed').value = cat.bed_type;
    document.getElementById('cat-image').value = cat.image;
    document.getElementById('cat-short-desc').value = cat.short_desc;
    document.getElementById('cat-desc').value = cat.description;
    document.getElementById('cat-amenities').value = cat.amenities;
    document.getElementById('modal-category').classList.add('show');
}
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
