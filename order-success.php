<?php
/**
 * PowerPrep Kitchen - Order Success / Confirmation
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

$order_id = (int)($_GET['id'] ?? ($_SESSION['last_order_id'] ?? 0));

if ($order_id <= 0) {
    redirect('meals.php');
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash_message('danger', 'Order not found.');
    redirect('meals.php');
}

// Fetch items
$stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

$page_title = 'Order Confirmation #' . $order['id'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <!-- Success Banner -->
      <div class="text-center mb-5">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width: 84px; height: 84px;">
          <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
        </div>
        <h1 class="fw-bold text-dark display-6 mb-2">Order Successfully Placed!</h1>
        <p class="lead text-muted mx-auto" style="max-width: 520px;">
          Thank you, <strong><?= e($order['customer_name']) ?></strong>! Your meal will be prepared fresh by Chef Alex.
        </p>
        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fs-6">
          Order #<?= $order['id'] ?> • Status: <?= e($order['status']) ?>
        </span>
      </div>

      <!-- Receipt Card -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
          <div>
            <h5 class="fw-bold text-dark mb-0">Order Summary</h5>
            <small class="text-muted">Placed on <?= date('F j, Y, g:i A', strtotime($order['created_at'])) ?></small>
          </div>
          <span class="badge bg-primary px-3 py-2 rounded-pill"><?= e($order['payment_method']) ?></span>
        </div>

        <div class="card-body p-4">
          <!-- Items Table -->
          <div class="table-responsive mb-4">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col">Meal Item</th>
                  <th scope="col" class="text-center">Qty</th>
                  <th scope="col">Price</th>
                  <th scope="col" class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <td class="fw-bold text-dark"><?= e($item['meal_name']) ?></td>
                    <td class="text-center"><span class="badge bg-light text-dark border"><?= $item['quantity'] ?></span></td>
                    <td class="text-muted"><?= format_price($item['price']) ?></td>
                    <td class="text-end fw-bold text-dark"><?= format_price($item['subtotal']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Cost Breakdown -->
          <div class="row justify-content-end mb-4">
            <div class="col-md-6">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal</span>
                <span class="fw-bold text-dark"><?= format_price($order['subtotal']) ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Delivery Fee</span>
                <span class="fw-bold text-dark"><?= format_price($order['delivery_fee']) ?></span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-5">Total Paid / Due</span>
                <span class="fs-4 fw-extrabold text-primary"><?= format_price($order['total']) ?></span>
              </div>
            </div>
          </div>

          <!-- Delivery Details Grid -->
          <div class="p-3 bg-light rounded-3">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-geo-alt-fill text-secondary me-1"></i> Delivery Information</h6>
            <div class="row g-2 small text-muted">
              <div class="col-md-6">
                <strong>Recipient:</strong> <?= e($order['customer_name']) ?><br>
                <strong>Phone:</strong> <?= e($order['customer_phone']) ?><br>
                <strong>Email:</strong> <?= e($order['customer_email']) ?>
              </div>
              <div class="col-md-6">
                <strong>Address:</strong> <?= e($order['delivery_address']) ?><br>
                <strong>City/Area:</strong> <?= e($order['delivery_city']) ?><br>
                <?php if (!empty($order['delivery_instructions'])): ?>
                  <strong>Notes:</strong> <?= e($order['delivery_instructions']) ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer bg-white p-4 border-top d-flex flex-column flex-sm-row justify-content-between gap-3">
          <a href="meals.php" class="btn btn-outline-custom">
            <i class="bi bi-fire"></i> Order More Meals
          </a>
          <a href="orders.php" class="btn btn-primary-custom">
            <i class="bi bi-truck"></i> View My Orders & Tracking
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
