<?php
/**
 * PowerPrep Kitchen - Admin Edit Meal
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('meals.php');
}

$stmt = $pdo->prepare("SELECT * FROM meals WHERE id = ?");
$stmt->execute([$id]);
$meal = $stmt->fetch();

if (!$meal) {
    set_flash_message('danger', 'Meal not found.');
    redirect('meals.php');
}

$errors = [];
$name = $meal['name'];
$category = $meal['category'];
$price = $meal['price'];
$calories = $meal['calories'];
$protein = $meal['protein'];
$carbs = $meal['carbs'];
$fat = $meal['fat'];
$fiber = $meal['fiber'];
$prep_time = $meal['preparation_time'];
$is_available = $meal['is_available'];
$is_featured = $meal['is_featured'];
$description = $meal['description'];
$ingredients = $meal['ingredients'];
$allergens = $meal['allergens'];
$existing_image = $meal['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security session token.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $category = sanitize($_POST['category'] ?? 'High Protein');
    $price = (float)($_POST['price'] ?? 0);
    $calories = (int)($_POST['calories'] ?? 0);
    $protein = (int)($_POST['protein'] ?? 0);
    $carbs = (int)($_POST['carbs'] ?? 0);
    $fat = (int)($_POST['fat'] ?? 0);
    $fiber = (int)($_POST['fiber'] ?? 0);
    $prep_time = sanitize($_POST['preparation_time'] ?? '20 mins');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $description = sanitize($_POST['description'] ?? '');
    $ingredients = sanitize($_POST['ingredients'] ?? '');
    $allergens = sanitize($_POST['allergens'] ?? 'None');

    if (empty($name)) $errors[] = 'Meal name is required.';
    if ($price <= 0) $errors[] = 'Please provide a valid price.';
    if ($calories <= 0) $errors[] = 'Calories must be greater than 0.';
    if ($protein <= 0) $errors[] = 'Protein must be greater than 0.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (empty($ingredients)) $errors[] = 'Ingredients list is required.';

    // Check Image Upload
    $new_image = $existing_image;
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handle_meal_image_upload('meal_image', __DIR__ . '/../uploads/meals/');
        if ($upload['success'] && $upload['filename']) {
            $new_image = $upload['filename'];
        } else {
            $errors[] = $upload['error'];
        }
    }

    if (empty($errors)) {
        $stmt_update = $pdo->prepare("
            UPDATE meals SET
                name = ?, description = ?, category = ?, ingredients = ?, allergens = ?, image = ?,
                price = ?, calories = ?, protein = ?, carbs = ?, fat = ?, fiber = ?, preparation_time = ?,
                is_available = ?, is_featured = ?
            WHERE id = ?
        ");

        $success = $stmt_update->execute([
            $name, $description, $category, $ingredients, $allergens, $new_image,
            $price, $calories, $protein, $carbs, $fat, $fiber, $prep_time,
            $is_available, $is_featured, $id
        ]);

        if ($success) {
            set_flash_message('success', 'Meal "' . $name . '" has been updated successfully!');
            redirect('meals.php');
        } else {
            $errors[] = 'Failed to update meal in database.';
        }
    }
}

$page_title = 'Edit Meal - ' . $meal['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <div class="d-flex align-items-center gap-2 mb-1">
    <a href="meals.php" class="text-muted"><i class="bi bi-arrow-left"></i> Back to Meals</a>
  </div>
  <h2 class="fw-bold text-dark">Edit Meal: <?= e($meal['name']) ?></h2>
  <p class="text-muted">Modify dish pricing, preparation time, and nutritional macros.</p>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger rounded-3 mb-4">
    <ul class="mb-0 ps-3">
      <?php foreach ($errors as $e): ?>
        <li><?= e($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="edit-meal.php?id=<?= $id ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-secondary me-2"></i> Recipe Information</h5>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label-custom">Meal Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-custom" value="<?= e($name) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label-custom">Category <span class="text-danger">*</span></label>
            <select name="category" class="form-select form-control-custom" required>
              <option value="Breakfast" <?= $category === 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
              <option value="Lunch" <?= $category === 'Lunch' ? 'selected' : '' ?>>Lunch</option>
              <option value="Dinner" <?= $category === 'Dinner' ? 'selected' : '' ?>>Dinner</option>
              <option value="High Protein" <?= $category === 'High Protein' ? 'selected' : '' ?>>High Protein</option>
              <option value="Low Carb" <?= $category === 'Low Carb' ? 'selected' : '' ?>>Low Carb</option>
              <option value="Vegetarian" <?= $category === 'Vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label-custom">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control form-control-custom" rows="3" required><?= e($description) ?></textarea>
          </div>
        </div>
      </div>

      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-secondary me-2"></i> Nutritional Macros</h5>

        <div class="row g-3">
          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-fire text-success me-1"></i> Calories (kcal) <span class="text-danger">*</span></label>
            <input type="number" name="calories" class="form-control form-control-custom" value="<?= $calories ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Protein (g) <span class="text-danger">*</span></label>
            <input type="number" name="protein" class="form-control form-control-custom" value="<?= $protein ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-flower2 text-warning me-1"></i> Carbs (g)</label>
            <input type="number" name="carbs" class="form-control form-control-custom" value="<?= $carbs ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-droplet-fill text-danger me-1"></i> Healthy Fats (g)</label>
            <input type="number" name="fat" class="form-control form-control-custom" value="<?= $fat ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-tree-fill text-success me-1"></i> Dietary Fiber (g)</label>
            <input type="number" name="fiber" class="form-control form-control-custom" value="<?= $fiber ?>">
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-stopwatch text-secondary me-1"></i> Cook / Prep Time</label>
            <input type="text" name="preparation_time" class="form-control form-control-custom" value="<?= e($prep_time) ?>">
          </div>
        </div>
      </div>

      <div class="admin-card p-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-basket-fill text-secondary me-2"></i> Ingredients & Allergens</h5>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label-custom">Whole Food Ingredients List <span class="text-danger">*</span></label>
            <textarea name="ingredients" class="form-control form-control-custom" rows="3" required><?= e($ingredients) ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label-custom">Allergens</label>
            <input type="text" name="allergens" class="form-control form-control-custom" value="<?= e($allergens) ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tag text-secondary me-2"></i> Pricing & Status</h5>

        <div class="mb-3">
          <label class="form-label-custom">Selling Price ($) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light">$</span>
            <input type="number" step="0.01" name="price" class="form-control form-control-custom" value="<?= $price ?>" required>
          </div>
        </div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_available" id="isAvailableSwitch" value="1" <?= $is_available ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="isAvailableSwitch">Available for Ordering</label>
        </div>

        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_featured" id="isFeaturedSwitch" value="1" <?= $is_featured ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="isFeaturedSwitch">Feature on Homepage</label>
        </div>
      </div>

      <!-- Meal Image -->
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-image text-secondary me-2"></i> Dish Photo</h5>

        <div class="mb-3 text-center">
          <small class="text-muted d-block mb-2">Current Photo:</small>
          <img src="../uploads/meals/<?= e($existing_image) ?>" alt="" class="img-fluid rounded-3 shadow-xs" style="max-height: 140px; object-fit: cover;" onerror="this.src='../uploads/meals/grilled_chicken_bowl.jpg'">
        </div>

        <div class="image-upload-box text-center" onclick="document.getElementById('mealImageInput').click();">
          <i class="bi bi-arrow-repeat fs-2 text-secondary mb-1 d-block"></i>
          <span class="fw-bold text-dark d-block small">Click to replace photo</span>
          <input type="file" name="meal_image" id="mealImageInput" class="d-none" accept="image/*">
        </div>

        <div class="text-center mt-3">
          <img id="imagePreview" src="" alt="Preview" class="image-preview mx-auto">
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="admin-card p-4">
        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-2 fs-6">
          <i class="bi bi-check2-all me-1"></i> Save Changes
        </button>
        <a href="meals.php" class="btn btn-outline-secondary w-100 py-2">
          Cancel
        </a>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
