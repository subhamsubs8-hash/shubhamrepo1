<?php
/**
 * PowerPrep Kitchen - Admin Add Meal
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$errors = [];
$name = '';
$category = 'High Protein';
$price = '';
$calories = '';
$protein = '';
$carbs = '';
$fat = '';
$fiber = '0';
$prep_time = '20 mins';
$is_available = 1;
$is_featured = 0;
$description = '';
$ingredients = '';
$allergens = 'None';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security session token. Please try again.';
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

    // Handle Image Upload
    $image_filename = 'grilled_chicken_bowl.jpg'; // fallback
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handle_meal_image_upload('meal_image', __DIR__ . '/../uploads/meals/');
        if ($upload['success'] && $upload['filename']) {
            $image_filename = $upload['filename'];
        } else {
            $errors[] = $upload['error'];
        }
    }

    if (empty($errors)) {
        $base_slug = slugify($name);
        $slug = $base_slug;
        $counter = 1;

        // Ensure unique slug
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM meals WHERE slug = ?");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) break;
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        $stmt_insert = $pdo->prepare("
            INSERT INTO meals (
                name, slug, description, category, ingredients, allergens, image,
                price, calories, protein, carbs, fat, fiber, preparation_time,
                is_available, is_featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt_insert->execute([
            $name, $slug, $description, $category, $ingredients, $allergens, $image_filename,
            $price, $calories, $protein, $carbs, $fat, $fiber, $prep_time,
            $is_available, $is_featured
        ]);

        if ($success) {
            set_flash_message('success', 'New meal "' . $name . '" has been published successfully!');
            redirect('meals.php');
        } else {
            $errors[] = 'Failed to save meal into database.';
        }
    }
}

$page_title = 'Add New High-Protein Meal';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <div class="d-flex align-items-center gap-2 mb-1">
    <a href="meals.php" class="text-muted"><i class="bi bi-arrow-left"></i> Back to Meals</a>
  </div>
  <h2 class="fw-bold text-dark">Add New High-Protein Meal</h2>
  <p class="text-muted">Enter meal details, recipe ingredients, and macro profile for customer ordering.</p>
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

<form method="POST" action="add-meal.php" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Left Column: Primary Meal Info & Macros -->
    <div class="col-lg-8">
      <!-- General Information Card -->
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-secondary me-2"></i> Meal Details</h5>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label-custom">Meal Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. Herb Crusted Salmon & Rice" value="<?= e($name) ?>" required>
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
            <textarea name="description" class="form-control form-control-custom" rows="3" placeholder="Appetizing description of flavors, texture, and fitness benefits..." required><?= e($description) ?></textarea>
          </div>
        </div>
      </div>

      <!-- Nutritional Profile Macros Card -->
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-secondary me-2"></i> Nutritional Macros</h5>

        <div class="row g-3">
          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-fire text-success me-1"></i> Calories (kcal) <span class="text-danger">*</span></label>
            <input type="number" name="calories" class="form-control form-control-custom" placeholder="e.g. 620" value="<?= $calories ?: '' ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Protein (g) <span class="text-danger">*</span></label>
            <input type="number" name="protein" class="form-control form-control-custom" placeholder="e.g. 55" value="<?= $protein ?: '' ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-flower2 text-warning me-1"></i> Carbs (g)</label>
            <input type="number" name="carbs" class="form-control form-control-custom" placeholder="e.g. 48" value="<?= $carbs ?: '' ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-droplet-fill text-danger me-1"></i> Healthy Fats (g)</label>
            <input type="number" name="fat" class="form-control form-control-custom" placeholder="e.g. 16" value="<?= $fat ?: '' ?>" required>
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-tree-fill text-success me-1"></i> Dietary Fiber (g)</label>
            <input type="number" name="fiber" class="form-control form-control-custom" placeholder="e.g. 8" value="<?= $fiber ?>">
          </div>

          <div class="col-md-4 col-6">
            <label class="form-label-custom"><i class="bi bi-stopwatch text-secondary me-1"></i> Cook / Prep Time</label>
            <input type="text" name="preparation_time" class="form-control form-control-custom" placeholder="e.g. 20-25 mins" value="<?= e($prep_time) ?>">
          </div>
        </div>
      </div>

      <!-- Ingredients & Allergens -->
      <div class="admin-card p-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-basket-fill text-secondary me-2"></i> Ingredients & Allergens</h5>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label-custom">Whole Food Ingredients List <span class="text-danger">*</span></label>
            <textarea name="ingredients" class="form-control form-control-custom" rows="3" placeholder="e.g. Grilled chicken breast, jasmine rice, steamed broccoli, olive oil, lemon seasoning" required><?= e($ingredients) ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label-custom">Allergens</label>
            <input type="text" name="allergens" class="form-control form-control-custom" placeholder="e.g. Dairy, Nuts, Eggs (or None)" value="<?= e($allergens) ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Image Upload, Pricing & Publish Settings -->
    <div class="col-lg-4">
      <!-- Price & Status -->
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tag text-secondary me-2"></i> Pricing & Status</h5>

        <div class="mb-3">
          <label class="form-label-custom">Selling Price ($) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light">$</span>
            <input type="number" step="0.01" name="price" class="form-control form-control-custom" placeholder="12.99" value="<?= $price ?: '' ?>" required>
          </div>
        </div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_available" id="isAvailableSwitch" value="1" <?= $is_available ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="isAvailableSwitch">Available for Ordering</label>
          <small class="text-muted d-block">If turned off, users will see "Currently Unavailable".</small>
        </div>

        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_featured" id="isFeaturedSwitch" value="1" <?= $is_featured ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="isFeaturedSwitch">Feature on Homepage</label>
        </div>
      </div>

      <!-- Meal Image Upload -->
      <div class="admin-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-image text-secondary me-2"></i> Dish Photo</h5>

        <div class="image-upload-box text-center" onclick="document.getElementById('mealImageInput').click();">
          <i class="bi bi-cloud-arrow-up fs-1 text-secondary mb-2 d-block"></i>
          <span class="fw-bold text-dark d-block">Click to upload photo</span>
          <small class="text-muted">JPG, PNG, WebP (Max 5MB)</small>
          <input type="file" name="meal_image" id="mealImageInput" class="d-none" accept="image/*">
        </div>

        <div class="text-center mt-3">
          <img id="imagePreview" src="" alt="Preview" class="image-preview mx-auto">
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="admin-card p-4">
        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-2 fs-6">
          <i class="bi bi-check-circle-fill me-1"></i> Publish Meal to Menu
        </button>
        <a href="meals.php" class="btn btn-outline-secondary w-100 py-2">
          Cancel
        </a>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
