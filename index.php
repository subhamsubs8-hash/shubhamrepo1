<?php
/**
 * PaushtikAahar Kitchen - Homepage
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

// Handle quick add to cart POST from meal cards
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

// Fetch featured meals from database
$stmt = $pdo->prepare("SELECT * FROM meals WHERE is_available = 1 ORDER BY is_featured DESC, protein DESC LIMIT 6");
$stmt->execute();
$featured_meals = $stmt->fetchAll();

$page_title = 'High-Protein Meals Made Fresh For You';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <!-- Left Column Content -->
      <div class="col-lg-6">
        <div class="hero-badge">
          <i class="bi bi-fire text-warning"></i> Fuel Your Body With Better Meals
        </div>
        <h1 class="hero-title">
          High-Protein Meals Made <span style="color: #2E7D5B;">Fresh for You</span>
        </h1>
        <p class="hero-subtitle">
          Delicious, nutritious meals prepared fresh by your personal cook with the exact protein and balanced macros you need to reach your fitness goals.
        </p>
        
        <div class="d-flex flex-wrap gap-3 mb-4">
          <a href="meals.php" class="btn-primary-custom">
            <i class="bi bi-fire"></i> Explore Meals
          </a>
          <a href="#how-it-works" class="btn-outline-custom">
            <i class="bi bi-play-circle"></i> How It Works
          </a>
        </div>

        <!-- Quick Trust Indicators -->
        <div class="row pt-3 border-top border-secondary-subtle g-3 text-start">
          <div class="col-4">
            <h3 class="fw-bold mb-0 text-primary">50g+</h3>
            <small class="text-muted fw-semibold">Avg. Protein/Meal</small>
          </div>
          <div class="col-4">
            <h3 class="fw-bold mb-0 text-primary">100%</h3>
            <small class="text-muted fw-semibold">Fresh Ingredients</small>
          </div>
          <div class="col-4">
            <h3 class="fw-bold mb-0 text-primary">0</h3>
            <small class="text-muted fw-semibold">Preservatives</small>
          </div>
        </div>
      </div>

      <!-- Right Column Hero Image -->
      <div class="col-lg-6">
        <div class="hero-image-wrapper">
          <img src="assets/images/hero_meal.jpg" alt="High Protein Meal Prep Bowl" onerror="this.src='uploads/meals/grilled_chicken_bowl.jpg'">
          
          <!-- Floating Highlight Card -->
          <div class="hero-floating-card">
            <div class="floating-icon">
              <i class="bi bi-activity"></i>
            </div>
            <div>
              <div class="fw-bold text-dark fs-6">Calculated Macros</div>
              <div class="text-muted small">55g Protein • 620 kcal</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 my-3">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Why Choose Us</span>
      <h2 class="section-title">Built for Your Nutrition & Performance</h2>
      <p class="section-subtitle">Every single meal is handcrafted with macro precision to help you build muscle, lose fat, and save hours of cooking.</p>
    </div>

    <div class="row g-4">
      <!-- Feature 1 -->
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <i class="bi bi-fire"></i>
          </div>
          <h4>High Protein</h4>
          <p>40g to 60g+ of quality lean protein per serving to support muscle growth and all-day satiety.</p>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <i class="bi bi-flower1"></i>
          </div>
          <h4>Fresh Ingredients</h4>
          <p>Real whole foods, farm-fresh produce, and premium meats. Never frozen, artificial, or pre-packaged.</p>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <i class="bi bi-pie-chart-fill"></i>
          </div>
          <h4>Balanced Macros</h4>
          <p>Every meal displays exact calories, protein, carbs, and fat so tracking your intake is effortless.</p>
        </div>
      </div>

      <!-- Feature 4 -->
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <i class="bi bi-heart-pulse-fill"></i>
          </div>
          <h4>Prepared to Order</h4>
          <p>Cooked fresh upon your order by a passionate personal cook dedicated to your wellness.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Meals Section -->
<section class="py-5 bg-white border-top border-bottom border-light-subtle">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
      <div>
        <span class="section-tag">Featured Dishes</span>
        <h2 class="section-title mb-1">Meals Built for Your Goals</h2>
        <p class="text-muted mb-0">Chef-crafted popular high-protein favorites ready for your table.</p>
      </div>
      <a href="meals.php" class="btn-outline-custom mt-3 mt-md-0 align-self-start align-self-md-auto">
        View Full Menu <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($featured_meals as $meal): ?>
        <div class="col-lg-4 col-md-6">
          <div class="meal-card">
            <!-- Meal Image & Badges -->
            <div class="meal-card-image-wrap">
              <img src="uploads/meals/<?= e($meal['image']) ?>" alt="<?= e($meal['name']) ?>" onerror="this.src='uploads/meals/grilled_chicken_bowl.jpg'">
              <span class="meal-badge-category"><?= e($meal['category']) ?></span>
              <span class="meal-badge-avail <?= $meal['is_available'] ? 'available' : 'unavailable' ?>">
                <?= $meal['is_available'] ? '<i class="bi bi-check-circle me-1"></i>Available' : 'Unavailable' ?>
              </span>
            </div>

            <!-- Meal Card Body -->
            <div class="meal-card-body">
              <h3 class="meal-title">
                <a href="meal.php?id=<?= $meal['id'] ?>"><?= e($meal['name']) ?></a>
              </h3>
              <p class="meal-desc"><?= e($meal['description']) ?></p>

              <!-- Nutrition Badges Grid -->
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
                  <a href="meal.php?id=<?= $meal['id'] ?>" class="btn-card-view" title="View Nutrition Breakdown">
                    Details
                  </a>
                  <?php if ($meal['is_available']): ?>
                    <form method="POST" action="index.php" class="d-inline">
                      <input type="hidden" name="action" value="quick_add">
                      <input type="hidden" name="meal_id" value="<?= $meal['id'] ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit" class="btn-card-order">
                        <i class="bi bi-cart-plus"></i> Order Now
                      </button>
                    </form>
                  <?php else: ?>
                    <button class="btn-card-order" disabled>Unavailable</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5 my-3">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Simple 3-Step Process</span>
      <h2 class="section-title">How It Works</h2>
      <p class="section-subtitle">Eating clean, high-protein food has never been this effortless.</p>
    </div>

    <div class="row g-4 justify-content-center">
      <!-- Step 1 -->
      <div class="col-lg-4 col-md-6">
        <div class="step-card">
          <div class="step-num">1</div>
          <div class="fs-1 text-secondary mb-3 mt-2">
            <i class="bi bi-journal-check"></i>
          </div>
          <h4 class="fw-bold mb-2">Choose Your Meals</h4>
          <p class="text-muted mb-0">Browse our menu of chef-crafted high-protein meals and filter by calories, protein target, or dietary preferences.</p>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="col-lg-4 col-md-6">
        <div class="step-card">
          <div class="step-num">2</div>
          <div class="fs-1 text-secondary mb-3 mt-2">
            <i class="bi bi-cart3"></i>
          </div>
          <h4 class="fw-bold mb-2">Place Your Order</h4>
          <p class="text-muted mb-0">Select your meal quantities and enter your delivery address with flexible checkout (Cash on Delivery or Online Payment).</p>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="col-lg-4 col-md-6">
        <div class="step-card">
          <div class="step-num">3</div>
          <div class="fs-1 text-secondary mb-3 mt-2">
            <i class="bi bi-box2-heart"></i>
          </div>
          <h4 class="fw-bold mb-2">I Prepare It Fresh</h4>
          <p class="text-muted mb-0">I cook each order fresh using premium ingredients, package it in microwave-ready containers, and deliver directly to you.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #173F35 0%, #2E7D5B 100%);">
  <div class="container text-center py-4">
    <h2 class="display-5 fw-bold mb-3">Ready to Eat Better & Reach Your Goals?</h2>
    <p class="lead text-white-50 mx-auto mb-4" style="max-width: 650px;">
      Stop spending hours grocery shopping, prepping, and counting macros. Let your personal cook handle your nutrition.
    </p>
    <a href="meals.php" class="btn-accent px-5 py-3 fs-5">
      <i class="bi bi-fire me-2"></i> Browse All Meals Now
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
