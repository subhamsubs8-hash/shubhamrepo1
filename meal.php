<?php
/**
 * PowerPrep Kitchen - Meal Details & Macro Breakdown
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

$meal_id = (int)($_GET['id'] ?? 0);
if ($meal_id <= 0) {
    set_flash_message('danger', 'Invalid meal selected.');
    redirect('meals.php');
}

$stmt = $pdo->prepare("SELECT * FROM meals WHERE id = ? LIMIT 1");
$stmt->execute([$meal_id]);
$meal = $stmt->fetch();

if (!$meal) {
    set_flash_message('danger', 'Meal not found.');
    redirect('meals.php');
}

// Handle Add to Cart / Order Now POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $action_type = $_POST['submit_action'] ?? 'add_cart';

    $result = add_to_cart($meal['id'], $quantity);
    if ($result['success']) {
        set_flash_message('success', $result['message']);
        if ($action_type === 'buy_now') {
            redirect('checkout.php');
        } else {
            redirect('cart.php');
        }
    } else {
        set_flash_message('warning', $result['message']);
        redirect('meal.php?id=' . $meal['id']);
    }
}

// Calculate Macro percentages for visual charts
$total_macro_grams = max(1, $meal['protein'] + $meal['carbs'] + $meal['fat']);
$protein_pct = round(($meal['protein'] / $total_macro_grams) * 100);
$carbs_pct   = round(($meal['carbs'] / $total_macro_grams) * 100);
$fat_pct     = round(($meal['fat'] / $total_macro_grams) * 100);

$page_title = $meal['name'] . ' - Macros & Ingredients';
$page_description = $meal['description'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-light py-3 border-bottom">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Home</a></li>
        <li class="breadcrumb-item"><a href="meals.php">Meals</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($meal['name']) ?></li>
      </ol>
    </nav>
  </div>
</div>

<!-- Main Meal Detail Container -->
<div class="container py-5">
  <div class="row g-5">
    <!-- Left Column: Large Image & Quick Stats -->
    <div class="col-lg-6">
      <div class="position-relative mb-4">
        <img src="uploads/meals/<?= e($meal['image']) ?>" alt="<?= e($meal['name']) ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 480px; object-fit: cover;" onerror="this.src='uploads/meals/grilled_chicken_bowl.jpg'">
        <span class="meal-badge-category" style="font-size: 0.85rem; padding: 0.4rem 1rem;">
          <i class="bi bi-tag-fill me-1"></i> <?= e($meal['category']) ?>
        </span>
      </div>

      <!-- Preparation & Allergy Callouts -->
      <div class="row g-3">
        <div class="col-6">
          <div class="p-3 bg-white border rounded-3 text-center">
            <i class="bi bi-stopwatch text-secondary fs-3 mb-1 d-block"></i>
            <small class="text-muted d-block fw-semibold">Cook Time</small>
            <strong class="text-dark"><?= e($meal['preparation_time']) ?></strong>
          </div>
        </div>
        <div class="col-6">
          <div class="p-3 bg-white border rounded-3 text-center">
            <i class="bi bi-exclamation-octagon text-danger fs-3 mb-1 d-block"></i>
            <small class="text-muted d-block fw-semibold">Allergens</small>
            <strong class="text-dark"><?= e($meal['allergens'] ?: 'None') ?></strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Info, Macro Card, Quantity & Order Form -->
    <div class="col-lg-6">
      <div class="d-flex align-items-center gap-2 mb-2">
        <?php if ($meal['is_available']): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
            <i class="bi bi-check-circle-fill me-1"></i> Freshly Available to Order
          </span>
        <?php else: ?>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
            <i class="bi bi-x-circle-fill me-1"></i> Currently Unavailable
          </span>
        <?php endif; ?>
      </div>

      <h1 class="fw-bold text-dark display-6 mb-2"><?= e($meal['name']) ?></h1>
      <div class="fs-2 fw-extrabold text-primary mb-3"><?= format_price($meal['price']) ?></div>
      
      <p class="text-muted lead fs-6 mb-4">
        <?= nl2br(e($meal['description'])) ?>
      </p>

      <!-- Nutrition Breakdown Box -->
      <div class="macro-breakdown-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold text-primary mb-0"><i class="bi bi-pie-chart-fill me-2 text-secondary"></i> Nutritional Profile</h5>
          <span class="badge bg-dark text-white px-3 py-2 rounded-pill fs-6"><?= (int)$meal['calories'] ?> kcal</span>
        </div>

        <!-- Protein Bar -->
        <div class="macro-bar-item">
          <div class="macro-bar-header">
            <span class="text-primary"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Protein</span>
            <span><?= (int)$meal['protein'] ?>g <small class="text-muted fw-normal">(<?= $protein_pct ?>%)</small></span>
          </div>
          <div class="macro-progress-track">
            <div class="macro-progress-fill fill-protein" style="width: <?= $protein_pct ?>%;"></div>
          </div>
        </div>

        <!-- Carbs Bar -->
        <div class="macro-bar-item">
          <div class="macro-bar-header">
            <span class="text-warning-emphasis"><i class="bi bi-flower2 text-warning me-1"></i> Carbohydrates</span>
            <span><?= (int)$meal['carbs'] ?>g <small class="text-muted fw-normal">(<?= $carbs_pct ?>%)</small></span>
          </div>
          <div class="macro-progress-track">
            <div class="macro-progress-fill fill-carbs" style="width: <?= $carbs_pct ?>%;"></div>
          </div>
        </div>

        <!-- Fat Bar -->
        <div class="macro-bar-item">
          <div class="macro-bar-header">
            <span class="text-danger"><i class="bi bi-droplet-fill text-danger me-1"></i> Healthy Fats</span>
            <span><?= (int)$meal['fat'] ?>g <small class="text-muted fw-normal">(<?= $fat_pct ?>%)</small></span>
          </div>
          <div class="macro-progress-track">
            <div class="macro-progress-fill fill-fat" style="width: <?= $fat_pct ?>%;"></div>
          </div>
        </div>

        <!-- Dietary Fiber -->
        <?php if (!empty($meal['fiber'])): ?>
          <div class="macro-bar-item">
            <div class="macro-bar-header">
              <span class="text-success"><i class="bi bi-tree-fill text-success me-1"></i> Dietary Fiber</span>
              <span><?= (int)$meal['fiber'] ?>g</span>
            </div>
            <div class="macro-progress-track">
              <div class="macro-progress-fill fill-fiber" style="width: <?= min(100, $meal['fiber'] * 7) ?>%;"></div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Fresh Whole Ingredients -->
      <div class="bg-white p-3 rounded-3 border mb-4">
        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-basket-fill text-secondary me-2"></i> Whole Food Ingredients</h6>
        <p class="text-muted small mb-0">
          <?= e($meal['ingredients']) ?>
        </p>
      </div>

      <!-- Add to Cart & Buy Now Form -->
      <?php if ($meal['is_available']): ?>
        <form method="POST" action="meal.php?id=<?= $meal['id'] ?>">
          <div class="d-flex flex-column flex-sm-row align-items-stretch gap-3">
            <!-- Quantity Control -->
            <div class="quantity-control align-self-start" style="height: 50px;">
              <button type="button" class="qty-btn qty-minus"><i class="bi bi-dash"></i></button>
              <input type="number" name="quantity" class="qty-input" value="1" min="1" max="50">
              <button type="button" class="qty-btn qty-plus"><i class="bi bi-plus"></i></button>
            </div>

            <!-- Submit Buttons -->
            <button type="submit" name="submit_action" value="add_cart" class="btn btn-outline-custom flex-grow-1 py-3">
              <i class="bi bi-bag-plus"></i> Add to Cart
            </button>
            <button type="submit" name="submit_action" value="buy_now" class="btn btn-primary-custom flex-grow-1 py-3">
              <i class="bi bi-lightning-fill"></i> Order Now
            </button>
          </div>
        </form>
      <?php else: ?>
        <div class="alert alert-warning rounded-3 d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-5"></i>
          <div>This meal is currently sold out. Please check back tomorrow or browse other dishes!</div>
        </div>
        <a href="meals.php" class="btn btn-outline-custom w-100 py-3">
          <i class="bi bi-arrow-left"></i> Browse Available Meals
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
