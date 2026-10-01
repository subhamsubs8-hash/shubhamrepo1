<?php
/**
 * PaushtikAahar Kitchen - Meals Directory & Filter Page
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

// Handle quick add to cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_add') {
    $meal_id = (int)($_POST['meal_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $result = add_to_cart($meal_id, $quantity);
    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('warning', $result['message']);
    }
    redirect('cart.php');
}

// Fetch initial meals from DB
$category_param = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$query = "SELECT * FROM meals ORDER BY is_available DESC, id ASC";
$stmt = $pdo->query($query);
$all_meals = $stmt->fetchAll();

$page_title = 'Explore High-Protein Meals';
require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5 bg-white border-bottom">
  <div class="container text-center">
    <span class="section-tag">Healthy Meal Prep Menu</span>
    <h1 class="section-title mb-2">High-Protein Meal Selection</h1>
    <p class="section-subtitle">Fresh, macro-balanced meals prepared to order. Filter by category, nutrition, or search by name.</p>
    
    <!-- Search & Sort Bar -->
    <div class="row justify-content-center mt-4">
      <div class="col-lg-8">
        <div class="row g-2">
          <div class="col-md-8">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
              <input type="text" id="mealSearchInput" class="form-control form-control-custom border-start-0 ps-0" placeholder="Search meals (e.g. Chicken, Beef, Pasta, Salmon)...">
            </div>
          </div>
          <div class="col-md-4">
            <select id="mealSortSelect" class="form-select form-control-custom">
              <option value="default">Sort by: Default</option>
              <option value="protein-desc">Highest Protein 💪</option>
              <option value="calories-asc">Lowest Calories 🔥</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="filter-container mt-4">
      <button class="filter-btn <?= $category_param === 'all' ? 'active' : '' ?>" data-filter="all">All Meals</button>
      <button class="filter-btn <?= strtolower($category_param) === 'breakfast' ? 'active' : '' ?>" data-filter="breakfast">Breakfast</button>
      <button class="filter-btn <?= strtolower($category_param) === 'lunch' ? 'active' : '' ?>" data-filter="lunch">Lunch</button>
      <button class="filter-btn <?= strtolower($category_param) === 'dinner' ? 'active' : '' ?>" data-filter="dinner">Dinner</button>
      <button class="filter-btn <?= strtolower($category_param) === 'high protein' ? 'active' : '' ?>" data-filter="high protein">High Protein (50g+)</button>
      <button class="filter-btn <?= strtolower($category_param) === 'low carb' ? 'active' : '' ?>" data-filter="low carb">Low Carb / Keto</button>
      <button class="filter-btn <?= strtolower($category_param) === 'vegetarian' ? 'active' : '' ?>" data-filter="vegetarian">Vegetarian</button>
    </div>
  </div>
</div>

<!-- Meals Grid Section -->
<div class="container py-5">
  <div class="row g-4" id="mealsContainer">
    <?php foreach ($all_meals as $meal): ?>
      <div class="col-lg-4 col-md-6 meal-grid-item"
           data-name="<?= strtolower(e($meal['name'])) ?>"
           data-desc="<?= strtolower(e($meal['description'])) ?>"
           data-category="<?= strtolower(e($meal['category'])) ?>"
           data-protein="<?= (int)$meal['protein'] ?>"
           data-carbs="<?= (int)$meal['carbs'] ?>"
           data-calories="<?= (int)$meal['calories'] ?>"
           data-price="<?= (float)$meal['price'] ?>"
           data-available="<?= (int)$meal['is_available'] ?>">
        
        <div class="meal-card">
          <!-- Meal Image & Badges -->
          <div class="meal-card-image-wrap">
            <img src="uploads/meals/<?= e($meal['image']) ?>" alt="<?= e($meal['name']) ?>" onerror="this.src='uploads/meals/grilled_chicken_bowl.jpg'">
            <span class="meal-badge-category"><?= e($meal['category']) ?></span>
            <span class="meal-badge-avail <?= $meal['is_available'] ? 'available' : 'unavailable' ?>">
              <?= $meal['is_available'] ? '<i class="bi bi-check-circle me-1"></i>Available' : '<i class="bi bi-x-circle me-1"></i>Unavailable' ?>
            </span>
          </div>

          <!-- Meal Card Body -->
          <div class="meal-card-body">
            <h3 class="meal-title">
              <a href="meal.php?id=<?= $meal['id'] ?>"><?= e($meal['name']) ?></a>
            </h3>
            <p class="meal-desc"><?= e($meal['description']) ?></p>

            <!-- Macro Nutrition Badges Grid -->
            <div class="macro-grid">
              <div class="macro-item macro-cals">
                <span class="macro-icon">🔥</span>
                <span class="macro-value"><?= (int)$meal['calories'] ?></span>
                <span class="macro-label">kcal</span>
              </div>
              <div class="macro-item macro-protein">
                <span class="macro-icon">💪</span>
                <span class="macro-value"><?= (int)$meal['protein'] ?>g</span>
                <span class="macro-label">Protein</span>
              </div>
              <div class="macro-item macro-carbs">
                <span class="macro-icon">🌾</span>
                <span class="macro-value"><?= (int)$meal['carbs'] ?>g</span>
                <span class="macro-label">Carbs</span>
              </div>
              <div class="macro-item macro-fat">
                <span class="macro-icon">🥑</span>
                <span class="macro-value"><?= (int)$meal['fat'] ?>g</span>
                <span class="macro-label">Fat</span>
              </div>
            </div>

            <!-- Footer with Price & Actions -->
            <div class="meal-card-footer">
              <div class="meal-price"><?= format_price($meal['price']) ?></div>
              <div class="meal-actions">
                <a href="meal.php?id=<?= $meal['id'] ?>" class="btn-card-view" title="View Full Nutrition Details">
                  Details
                </a>
                <?php if ($meal['is_available']): ?>
                  <form method="POST" action="meals.php" class="d-inline">
                    <input type="hidden" name="action" value="quick_add">
                    <input type="hidden" name="meal_id" value="<?= $meal['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn-card-order">
                      <i class="bi bi-cart-plus"></i> Order Now
                    </button>
                  </form>
                <?php else: ?>
                  <button class="btn-card-order" disabled title="Currently out of stock">Unavailable</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Empty Search/Filter Message -->
  <div id="noMealsFound" class="text-center py-5" style="display: none;">
    <div class="mb-3">
      <i class="bi bi-search fs-1 text-muted"></i>
    </div>
    <h3 class="fw-bold text-dark">No meals found</h3>
    <p class="text-muted">Try adjusting your search terms or filter selection.</p>
    <button class="btn-outline-custom mt-2" onclick="document.getElementById('mealSearchInput').value=''; document.querySelector('.filter-btn[data-filter=all]').click();">
      Reset All Filters
    </button>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
