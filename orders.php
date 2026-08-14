<?php
/**
 * PowerPrep Kitchen - Order Tracking & Order History
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

$user = current_user();
$orders = [];
$tracked_order = null;
$search_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$search_phone = isset($_GET['phone']) ? sanitize($_GET['phone']) : '';

// 1. Direct search by ID & Phone/Email (for guest tracking)
if ($search_id > 0) {
    if ($user) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (user_id = ? OR customer_email = ?) LIMIT 1");
        $stmt->execute([$search_id, $user['id'], $user['email']]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$search_id]);
    }
    $tracked_order = $stmt->fetch();
}

// 2. Fetch all orders for logged-in user
if ($user) {
    $stmt_user = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
    $stmt_user->execute([$user['id'], $user['email']]);
    $orders = $stmt_user->fetchAll();
}

// Helper to determine status progress step
function get_order_step_number($status) {
    return match($status) {
        'Pending'          => 1,
        'Confirmed'        => 2,
        'Preparing'        => 3,
        'Ready'            => 4,
        'Out for Delivery' => 5,
        'Delivered'        => 6,
        default            => 1
    };
}

$page_title = 'Track Orders & Order History';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="mb-4">
    <span class="section-tag">Real-Time Kitchen Updates</span>
    <h1 class="fw-bold text-dark mb-1">Track Your Meal Prep Orders</h1>
    <p class="text-muted">Monitor the preparation and delivery progress of your high-protein meals.</p>
  </div>

  <!-- Guest / Direct Order Lookup Form -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-search text-secondary me-2"></i> Quick Order Lookup</h5>
    <form method="GET" action="orders.php" class="row g-3 align-items-center">
      <div class="col-md-5">
        <label class="form-label-custom small">Order ID #</label>
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0">#</span>
          <input type="number" name="id" class="form-control form-control-custom border-start-0" placeholder="e.g. 1001" value="<?= $search_id > 0 ? $search_id : '' ?>" required>
        </div>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn-primary-custom w-100 py-2">
          <i class="bi bi-geo-alt"></i> Find & Track Order
        </button>
      </div>
    </form>
  </div>

  <!-- Specific Tracked Order Highlight -->
  <?php if ($tracked_order): ?>
    <?php
      $current_step = get_order_step_number($tracked_order['status']);
      $is_cancelled = $tracked_order['status'] === 'Cancelled';
      
      // Fetch items for this tracked order
      $stmt_ti = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
      $stmt_ti->execute([$tracked_order['id']]);
      $tracked_items = $stmt_ti->fetchAll();
    ?>
    <div class="card border-2 border-primary shadow-md rounded-4 mb-5 overflow-hidden">
      <div class="card-header bg-primary text-white p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
          <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-1">Order #<?= $tracked_order['id'] ?></span>
          <h4 class="fw-bold mb-0 text-white">Live Tracking Status</h4>
        </div>
        <div class="text-md-end">
          <span class="fs-5 fw-bold text-warning"><?= e($tracked_order['status']) ?></span>
          <small class="d-block text-white-50">Placed: <?= date('M d, Y g:i A', strtotime($tracked_order['created_at'])) ?></small>
        </div>
      </div>

      <div class="card-body p-4">
        <?php if ($is_cancelled): ?>
          <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2">
            <i class="bi bi-x-circle-fill fs-4"></i>
            <div>This order has been <strong>Cancelled</strong>. If you have questions, please contact Chef Alex.</div>
          </div>
        <?php else: ?>
          <!-- Timeline Progress Component -->
          <div class="timeline-container py-3">
            <div class="timeline-progress-line" style="width: <?= min(100, (($current_step - 1) / 5) * 100) ?>%;"></div>

            <!-- Step 1: Placed -->
            <div class="timeline-step <?= $current_step > 1 ? 'completed' : ($current_step === 1 ? 'current' : '') ?>">
              <div class="timeline-dot">
                <i class="bi <?= $current_step > 1 ? 'bi-check-lg' : 'bi-receipt' ?>"></i>
              </div>
              <span class="timeline-label">Order Placed</span>
            </div>

            <!-- Step 2: Confirmed -->
            <div class="timeline-step <?= $current_step > 2 ? 'completed' : ($current_step === 2 ? 'current' : '') ?>">
              <div class="timeline-dot">
                <i class="bi <?= $current_step > 2 ? 'bi-check-lg' : 'bi-check2-circle' ?>"></i>
              </div>
              <span class="timeline-label">Confirmed</span>
            </div>

            <!-- Step 3: Preparing -->
            <div class="timeline-step <?= $current_step > 3 ? 'completed' : ($current_step === 3 ? 'current' : '') ?>">
              <div class="timeline-dot">
                <i class="bi <?= $current_step > 3 ? 'bi-check-lg' : 'bi-fire' ?>"></i>
              </div>
              <span class="timeline-label">Preparing Fresh</span>
            </div>

            <!-- Step 4: Ready -->
            <div class="timeline-step <?= $current_step > 4 ? 'completed' : ($current_step === 4 ? 'current' : '') ?>">
              <div class="timeline-dot">
                <i class="bi <?= $current_step > 4 ? 'bi-check-lg' : 'bi-box-seam' ?>"></i>
              </div>
              <span class="timeline-label">Packaged Ready</span>
            </div>

            <!-- Step 5: Out for Delivery -->
            <div class="timeline-step <?= $current_step > 5 ? 'completed' : ($current_step === 5 ? 'current' : '') ?>">
              <div class="timeline-dot">
                <i class="bi <?= $current_step > 5 ? 'bi-check-lg' : 'bi-truck' ?>"></i>
              </div>
              <span class="timeline-label">Out for Delivery</span>
            </div>

            <!-- Step 6: Delivered -->
            <div class="timeline-step <?= $current_step === 6 ? 'completed' : '' ?>">
              <div class="timeline-dot">
                <i class="bi bi-house-check-fill"></i>
              </div>
              <span class="timeline-label">Delivered</span>
            </div>
          </div>
        <?php endif; ?>

        <!-- Order Items & Recipient Info -->
        <div class="row g-4 mt-2">
          <div class="col-md-7">
            <h6 class="fw-bold text-dark mb-3">Items in this Prep:</h6>
            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
              <?php foreach ($tracked_items as $item): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                  <div>
                    <span class="badge bg-secondary-subtle text-secondary me-2"><?= $item['quantity'] ?>x</span>
                    <strong class="text-dark"><?= e($item['meal_name']) ?></strong>
                  </div>
                  <span class="fw-bold text-dark"><?= format_price($item['subtotal']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="col-md-5">
            <div class="p-3 bg-light rounded-3 h-100">
              <h6 class="fw-bold text-dark mb-2">Delivery Summary</h6>
              <p class="small text-muted mb-1"><strong>Recipient:</strong> <?= e($tracked_order['customer_name']) ?></p>
              <p class="small text-muted mb-1"><strong>Phone:</strong> <?= e($tracked_order['customer_phone']) ?></p>
              <p class="small text-muted mb-1"><strong>Address:</strong> <?= e($tracked_order['delivery_address']) ?>, <?= e($tracked_order['delivery_city']) ?></p>
              <p class="small text-muted mb-2"><strong>Payment:</strong> <?= e($tracked_order['payment_method']) ?> (<?= e($tracked_order['payment_status']) ?>)</p>
              <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">Total:</span>
                <span class="fw-extrabold text-primary fs-5"><?= format_price($tracked_order['total']) ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php elseif ($search_id > 0): ?>
    <div class="alert alert-warning rounded-3 mb-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> No order found with ID #<?= $search_id ?>. Please check your order number.
    </div>
  <?php endif; ?>

  <!-- Logged in Customer Order History -->
  <?php if ($user): ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white p-4 border-bottom">
        <h4 class="fw-bold text-dark mb-0">Your Order History</h4>
      </div>

      <div class="card-body p-0">
        <?php if (empty($orders)): ?>
          <div class="p-5 text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
            <h5 class="fw-bold text-dark">No orders placed yet</h5>
            <p>Ready to start eating clean? Browse the menu and place your first order!</p>
            <a href="meals.php" class="btn-primary-custom">Explore Meals</a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4 py-3">Order ID</th>
                  <th class="py-3">Date</th>
                  <th class="py-3">Status</th>
                  <th class="py-3">Payment</th>
                  <th class="py-3">Total</th>
                  <th class="pe-4 py-3 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orders as $o): ?>
                  <tr>
                    <td class="ps-4 py-3 fw-bold text-dark">#<?= $o['id'] ?></td>
                    <td class="py-3 text-muted"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    <td class="py-3">
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $o['status'])) ?>">
                        <?= e($o['status']) ?>
                      </span>
                    </td>
                    <td class="py-3 small text-muted"><?= e($o['payment_method']) ?></td>
                    <td class="py-3 fw-bold text-primary"><?= format_price($o['total']) ?></td>
                    <td class="pe-4 py-3 text-end">
                      <a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-custom">
                        <i class="bi bi-eye"></i> Track
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-light">
      <h5 class="fw-bold text-dark mb-2">Want to save all your order history?</h5>
      <p class="text-muted mb-3">Log in or create a customer account to track past meals, re-order favorites, and view order timelines effortlessly.</p>
      <div class="d-flex justify-content-center gap-2">
        <a href="login.php" class="btn btn-outline-custom">Log In</a>
        <a href="register.php" class="btn-primary-custom">Sign Up</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
