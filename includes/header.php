<?php
/**
 * Global Header Component
 * PowerPrep Kitchen - High-Protein Meal Ordering System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart-functions.php';

$cart_count = get_cart_count();
$current_page = basename($_SERVER['PHP_SELF']);
$site_name = get_setting('site_name', 'PowerPrep Kitchen');
$tagline = get_setting('tagline', 'High-Protein Meals Made Fresh For Your Goals');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? e($page_title) . ' | ' . e($site_name) : e($site_name) . ' - ' . e($tagline) ?></title>
  <meta name="description" content="<?= isset($page_description) ? e($page_description) : 'Delicious, freshly prepared high-protein meals with complete macro breakdowns. Fuel your fitness goals with clean, chef-crafted meal prep.' ?>">
  
  <!-- Google Fonts: Outfit & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 CSS & Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Custom Design System -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Sticky Modern Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
      <!-- Brand Logo -->
      <a class="navbar-brand brand-logo" href="index.php">
        <div class="brand-icon">
          <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div>Power<span>Prep</span></div>
      </a>

      <!-- Right Mobile Actions (Cart + Hamburger) -->
      <div class="d-flex align-items-center gap-2 d-lg-none">
        <a href="cart.php" class="cart-btn-badge" aria-label="Shopping Cart">
          <i class="bi bi-bag-check-fill"></i>
          <?php if ($cart_count > 0): ?>
            <span class="cart-count"><?= $cart_count ?></span>
          <?php endif; ?>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="bi bi-list fs-2 text-dark"></i>
        </button>
      </div>

      <!-- Nav Links -->
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2">
          <li class="nav-item">
            <a class="nav-link nav-link-custom <?= $current_page === 'index.php' ? 'active' : '' ?>" href="index.php">
              <i class="bi bi-house-door me-1"></i> Home
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-custom <?= $current_page === 'meals.php' || $current_page === 'meal.php' ? 'active' : '' ?>" href="meals.php">
              <i class="bi bi-fire me-1"></i> Explore Meals
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-custom <?= $current_page === 'index.php' ? '' : '' ?>" href="index.php#how-it-works">
              <i class="bi bi-check2-circle me-1"></i> How It Works
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-custom <?= $current_page === 'orders.php' ? 'active' : '' ?>" href="orders.php">
              <i class="bi bi-truck me-1"></i> Track Order
            </a>
          </li>
        </ul>

        <!-- Desktop Action Buttons -->
        <div class="d-flex align-items-center gap-3">
          <!-- Cart Button -->
          <a href="cart.php" class="cart-btn-badge d-none d-lg-inline-flex" title="View Cart">
            <i class="bi bi-bag-check-fill"></i>
            <?php if ($cart_count > 0): ?>
              <span class="cart-count"><?= $cart_count ?></span>
            <?php endif; ?>
          </a>

          <?php if (is_logged_in()): ?>
            <div class="dropdown">
              <button class="btn btn-outline-custom dropdown-toggle py-2 px-3 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5 text-secondary"></i>
                <span class="d-none d-sm-inline"><?= e($_SESSION['user_name']) ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                <li><a class="dropdown-item py-2" href="account.php"><i class="bi bi-person me-2"></i> My Account</a></li>
                <li><a class="dropdown-item py-2" href="orders.php"><i class="bi bi-box-seam me-2"></i> My Orders</a></li>
                <?php if (is_admin()): ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item py-2 text-primary fw-bold" href="admin/index.php"><i class="bi bi-speedometer2 me-2"></i> Admin Dashboard</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-custom py-2 px-3">
              <i class="bi bi-box-arrow-in-right"></i> Log In
            </a>
            <a href="register.php" class="btn btn-primary-custom py-2 px-3 d-none d-sm-inline-flex">
              Sign Up
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Flash Messages Component -->
  <?php display_flash_messages(); ?>

  <main>
