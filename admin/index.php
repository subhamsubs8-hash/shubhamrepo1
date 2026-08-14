<?php
/**
 * PowerPrep Kitchen - Admin Dashboard Home
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'Kitchen Overview & Statistics';
require_once __DIR__ . '/includes/header.php';

// Calculate KPI Statistics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('Pending', 'Confirmed', 'Preparing')")->fetchColumn() ?: 0;
$total_revenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status != 'Cancelled'")->fetchColumn() ?: 0.00;
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() ?: 0;
$total_meals = $pdo->query("SELECT COUNT(*) FROM meals")->fetchColumn() ?: 0;

// Fetch Recent Orders (Latest 6)
$stmt_recent = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6");
$recent_orders = $stmt_recent->fetchAll();

// Fetch Popular Meals by Order Frequency
$stmt_popular = $pdo->query("
    SELECT oi.meal_name, m.image, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_sales
    FROM order_items oi
    LEFT JOIN meals m ON oi.meal_id = m.id
    GROUP BY oi.meal_name, m.image
    ORDER BY total_qty DESC
    LIMIT 5
");
$popular_meals = $stmt_popular->fetchAll();
?>

<!-- Header Title & Quick Action -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <h2 class="fw-bold text-dark mb-1">Kitchen Overview</h2>
    <p class="text-muted mb-0">Track incoming meal prep orders, revenue, and inventory at a glance.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="add-meal.php" class="btn btn-primary-custom">
      <i class="bi bi-plus-circle"></i> Add New Meal
    </a>
    <a href="orders.php" class="btn btn-outline-secondary">
      <i class="bi bi-bag-check"></i> Manage All Orders
    </a>
  </div>
</div>

<!-- 5 Key KPI Metric Cards -->
<div class="row g-3 mb-4">
  <!-- Total Orders -->
  <div class="col-xl-2 col-lg-4 col-sm-6">
    <div class="stat-card">
      <div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-number"><?= $total_orders ?></div>
      </div>
      <div class="stat-icon orders">
        <i class="bi bi-bag-check"></i>
      </div>
    </div>
  </div>

  <!-- Pending Orders -->
  <div class="col-xl-2 col-lg-4 col-sm-6">
    <div class="stat-card">
      <div>
        <div class="stat-label">Pending / Prep</div>
        <div class="stat-number text-warning-emphasis"><?= $pending_orders ?></div>
      </div>
      <div class="stat-icon pending">
        <i class="bi bi-fire"></i>
      </div>
    </div>
  </div>

  <!-- Total Revenue -->
  <div class="col-xl-3 col-lg-4 col-sm-6">
    <div class="stat-card">
      <div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-number text-success"><?= format_price($total_revenue) ?></div>
      </div>
      <div class="stat-icon revenue">
        <i class="bi bi-cash-coin"></i>
      </div>
    </div>
  </div>

  <!-- Total Customers -->
  <div class="col-xl-2 col-lg-6 col-sm-6">
    <div class="stat-card">
      <div>
        <div class="stat-label">Customers</div>
        <div class="stat-number"><?= $total_customers ?></div>
      </div>
      <div class="stat-icon customers">
        <i class="bi bi-people"></i>
      </div>
    </div>
  </div>

  <!-- Total Meals in Menu -->
  <div class="col-xl-3 col-lg-6 col-sm-12">
    <div class="stat-card">
      <div>
        <div class="stat-label">Active Meals Catalog</div>
        <div class="stat-number"><?= $total_meals ?></div>
      </div>
      <div class="stat-icon meals">
        <i class="bi bi-egg-fried"></i>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left Column: Recent Orders Table -->
  <div class="col-xl-8">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-clock-history me-2"></i> Recent Customer Orders</h5>
        <a href="orders.php" class="btn btn-sm btn-outline-custom">View All Orders</a>
      </div>

      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4 py-3">Order ID</th>
                <th class="py-3">Customer</th>
                <th class="py-3">Date</th>
                <th class="py-3">Total</th>
                <th class="py-3">Status</th>
                <th class="pe-4 py-3 text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_orders)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No orders received yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recent_orders as $ord): ?>
                  <tr>
                    <td class="ps-4 py-3 fw-bold text-dark">#<?= $ord['id'] ?></td>
                    <td class="py-3">
                      <strong class="text-dark d-block"><?= e($ord['customer_name']) ?></strong>
                      <small class="text-muted"><?= e($ord['customer_phone']) ?></small>
                    </td>
                    <td class="py-3 text-muted small"><?= date('M d, g:i A', strtotime($ord['created_at'])) ?></td>
                    <td class="py-3 fw-bold text-primary"><?= format_price($ord['total']) ?></td>
                    <td class="py-3">
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $ord['status'])) ?>">
                        <?= e($ord['status']) ?>
                      </span>
                    </td>
                    <td class="pe-4 py-3 text-end">
                      <a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-custom" title="View Details & Update Status">
                        Manage
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Popular High-Protein Meals -->
  <div class="col-xl-4">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-trophy-fill text-warning me-2"></i> Popular Meals</h5>
        <a href="meals.php" class="btn btn-sm btn-light">Manage</a>
      </div>

      <div class="admin-card-body p-3">
        <?php if (empty($popular_meals)): ?>
          <p class="text-muted text-center py-3 mb-0">No order data available yet.</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($popular_meals as $idx => $pop): ?>
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                <div class="d-flex align-items-center gap-3">
                  <span class="badge bg-secondary rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;"><?= $idx + 1 ?></span>
                  <?php if (!empty($pop['image'])): ?>
                    <img src="../uploads/meals/<?= e($pop['image']) ?>" alt="" class="rounded-2" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='../uploads/meals/grilled_chicken_bowl.jpg'">
                  <?php endif; ?>
                  <div>
                    <h6 class="fw-bold text-dark mb-0 small"><?= e($pop['meal_name']) ?></h6>
                    <small class="text-muted"><?= (int)$pop['total_qty'] ?> portions ordered</small>
                  </div>
                </div>
                <strong class="text-dark small"><?= format_price($pop['total_sales']) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
