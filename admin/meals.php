<?php
/**
 * PaushtikAahar Kitchen - Admin Meal Management Catalog
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Quick toggle availability action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $meal_id = (int)($_POST['meal_id'] ?? 0);
    $current_status = (int)($_POST['current_status'] ?? 0);
    $new_status = $current_status ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE meals SET is_available = ? WHERE id = ?");
    $stmt->execute([$new_status, $meal_id]);
    set_flash_message('success', 'Meal availability updated successfully.');
    redirect('meals.php');
}

// Fetch all meals
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$sql = "SELECT * FROM meals WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY is_available DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$meals = $stmt->fetchAll();

$page_title = 'Meal Inventory & Recipes';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <h2 class="fw-bold text-dark mb-1">Meal Catalog Management</h2>
    <p class="text-muted mb-0">Create, edit, or adjust the availability and nutritional macros of your dishes.</p>
  </div>
  <a href="add-meal.php" class="btn btn-primary-custom">
    <i class="bi bi-plus-lg me-1"></i> Add New Meal
  </a>
</div>

<!-- Filters Bar -->
<div class="admin-card p-3 mb-4">
  <form method="GET" action="meals.php" class="row g-2 align-items-center">
    <div class="col-md-5">
      <div class="input-group">
        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Search meal name..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-4">
      <select name="category" class="form-select form-control-custom">
        <option value="">All Categories</option>
        <option value="Breakfast" <?= $category === 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
        <option value="Lunch" <?= $category === 'Lunch' ? 'selected' : '' ?>>Lunch</option>
        <option value="Dinner" <?= $category === 'Dinner' ? 'selected' : '' ?>>Dinner</option>
        <option value="High Protein" <?= $category === 'High Protein' ? 'selected' : '' ?>>High Protein</option>
        <option value="Low Carb" <?= $category === 'Low Carb' ? 'selected' : '' ?>>Low Carb</option>
        <option value="Vegetarian" <?= $category === 'Vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary-custom w-100 py-2">Filter</button>
      <?php if (!empty($search) || !empty($category)): ?>
        <a href="meals.php" class="btn btn-outline-secondary py-2">Reset</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Meals Table -->
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Dish / Recipe</th>
            <th class="py-3">Category</th>
            <th class="py-3">Price</th>
            <th class="py-3">Macros (P / C / F)</th>
            <th class="py-3">Calories</th>
            <th class="py-3">Availability</th>
            <th class="pe-4 py-3 text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($meals)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No meals found in database.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($meals as $m): ?>
              <tr>
                <!-- Meal Image & Name -->
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <img src="../uploads/meals/<?= e($m['image']) ?>" alt="<?= e($m['name']) ?>" class="rounded-3 shadow-xs" style="width: 56px; height: 56px; object-fit: cover;" onerror="this.src='../uploads/meals/grilled_chicken_bowl.jpg'">
                    <div>
                      <strong class="text-dark d-block"><?= e($m['name']) ?></strong>
                      <small class="text-muted">Prep: <?= e($m['preparation_time']) ?></small>
                    </div>
                  </div>
                </td>

                <!-- Category -->
                <td class="py-3">
                  <span class="badge bg-light text-dark border"><?= e($m['category']) ?></span>
                </td>

                <!-- Price -->
                <td class="py-3 fw-bold text-dark">
                  <?= format_price($m['price']) ?>
                </td>

                <!-- Macros Breakdown -->
                <td class="py-3">
                  <div class="small">
                    <span class="text-primary fw-bold"><?= (int)$m['protein'] ?>g P</span> • 
                    <span class="text-warning-emphasis fw-bold"><?= (int)$m['carbs'] ?>g C</span> • 
                    <span class="text-danger fw-bold"><?= (int)$m['fat'] ?>g F</span>
                  </div>
                </td>

                <!-- Calories -->
                <td class="py-3">
                  <span class="badge bg-secondary-subtle text-dark fw-bold"><?= (int)$m['calories'] ?> kcal</span>
                </td>

                <!-- Availability Toggle -->
                <td class="py-3">
                  <form method="POST" action="meals.php" class="d-inline">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="meal_id" value="<?= $m['id'] ?>">
                    <input type="hidden" name="current_status" value="<?= $m['is_available'] ?>">
                    <button type="submit" class="btn btn-sm <?= $m['is_available'] ? 'btn-success' : 'btn-outline-danger' ?> rounded-pill px-3 py-1" title="Click to toggle availability">
                      <i class="bi <?= $m['is_available'] ? 'bi-check-circle-fill' : 'bi-dash-circle' ?> me-1"></i>
                      <?= $m['is_available'] ? 'Available' : 'Unavailable' ?>
                    </button>
                  </form>
                </td>

                <!-- Action Buttons -->
                <td class="pe-4 py-3 text-end">
                  <div class="btn-group">
                    <a href="../meal.php?id=<?= $m['id'] ?>" target="_blank" class="btn btn-sm btn-light border" title="Preview Public Page">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="edit-meal.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-light border" title="Edit Meal & Macros">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="delete-meal.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger border btn-confirm-delete" data-confirm="Are you sure you want to delete '<?= addslashes(e($m['name'])) ?>'?" title="Delete Meal">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
