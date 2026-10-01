<?php
/**
 * PaushtikAahar Kitchen - Admin Order Detailed View & Status Updater
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    redirect('orders.php');
}

// Handle Status & Payment Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        $status = sanitize($_POST['status'] ?? 'Pending');
        $payment_status = sanitize($_POST['payment_status'] ?? 'Pending');

        $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $payment_status, $order_id]);
        set_flash_message('success', "Order #{$order_id} status updated successfully.");
        redirect("order-details.php?id={$order_id}");
    }
}

// Fetch Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash_message('danger', 'Order not found.');
    redirect('orders.php');
}

// Fetch Order Items
$stmt_items = $pdo->prepare("
    SELECT oi.*, m.image, m.category, m.protein, m.calories
    FROM order_items oi
    LEFT JOIN meals m ON oi.meal_id = m.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

$page_title = "Manage Order #{$order['id']}";
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <div class="d-flex align-items-center gap-2 mb-1">
    <a href="orders.php" class="text-muted"><i class="bi bi-arrow-left"></i> Back to Orders Queue</a>
  </div>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
      <h2 class="fw-bold text-dark mb-0">Order #<?= $order['id'] ?> Details</h2>
      <small class="text-muted">Placed on <?= date('F j, Y, g:i A', strtotime($order['created_at'])) ?></small>
    </div>
    <div>
      <span class="status-pill fs-6 px-3 py-2 status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
        <i class="bi bi-arrow-repeat me-1"></i> <?= e($order['status']) ?>
      </span>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left Column: Ordered Meals & Items Table -->
  <div class="col-lg-8">
    <div class="admin-card mb-4">
      <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-basket-fill text-secondary me-2"></i> Ordered Dishes (<?= count($items) ?> items)</h5>
      </div>

      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4 py-3">Meal</th>
                <th class="py-3 text-center">Quantity</th>
                <th class="py-3">Unit Price</th>
                <th class="pe-4 py-3 text-end">Line Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                      <?php if (!empty($item['image'])): ?>
                        <img src="../uploads/meals/<?= e($item['image']) ?>" alt="" class="rounded-3 shadow-xs" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='../uploads/meals/grilled_chicken_bowl.jpg'">
                      <?php endif; ?>
                      <div>
                        <strong class="text-dark d-block"><?= e($item['meal_name']) ?></strong>
                        <?php if (!empty($item['protein'])): ?>
                          <small class="text-muted"><?= (int)$item['protein'] ?>g Protein • <?= (int)$item['calories'] ?> kcal</small>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 text-center">
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6"><?= $item['quantity'] ?>x</span>
                  </td>
                  <td class="py-3 text-muted"><?= format_price($item['price']) ?></td>
                  <td class="pe-4 py-3 text-end fw-bold text-dark"><?= format_price($item['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Totals Row -->
        <div class="p-4 border-top bg-light">
          <div class="row justify-content-end">
            <div class="col-md-6">
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Food Subtotal:</span>
                <span class="fw-bold text-dark"><?= format_price($order['subtotal']) ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Delivery & Handling:</span>
                <span class="fw-bold text-dark"><?= format_price($order['delivery_fee']) ?></span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-5">Grand Total:</span>
                <span class="fs-4 fw-extrabold text-primary"><?= format_price($order['total']) ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Delivery & Notes Info -->
    <div class="admin-card p-4">
      <h5 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt-fill text-secondary me-2"></i> Delivery Location & Notes</h5>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="small text-muted fw-bold d-block">Street Address</label>
          <p class="text-dark fw-semibold mb-0"><?= e($order['delivery_address']) ?></p>
        </div>

        <div class="col-md-6">
          <label class="small text-muted fw-bold d-block">City / Delivery Zone</label>
          <p class="text-dark fw-semibold mb-0"><?= e($order['delivery_city']) ?></p>
        </div>

        <?php if (!empty($order['delivery_instructions'])): ?>
          <div class="col-12">
            <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle">
              <strong class="text-warning-emphasis d-block mb-1"><i class="bi bi-chat-left-text-fill me-1"></i> Customer Special Instructions:</strong>
              <div class="text-dark"><?= nl2br(e($order['delivery_instructions'])) ?></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Column: Status & Customer Controls -->
  <div class="col-lg-4">
    <!-- Status Control Box -->
    <div class="admin-card p-4 mb-4">
      <h5 class="fw-bold text-dark mb-3"><i class="bi bi-sliders text-secondary me-2"></i> Update Order Status</h5>

      <form method="POST" action="order-details.php?id=<?= $order['id'] ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label-custom">Kitchen Preparation Status</label>
          <select name="status" class="form-select form-control-custom">
            <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending (Order Placed)</option>
            <option value="Confirmed" <?= $order['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="Preparing" <?= $order['status'] === 'Preparing' ? 'selected' : '' ?>>Preparing Fresh (Cooking)</option>
            <option value="Ready" <?= $order['status'] === 'Ready' ? 'selected' : '' ?>>Ready (Packaged)</option>
            <option value="Out for Delivery" <?= $order['status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
            <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="form-label-custom">Payment Status</label>
          <select name="payment_status" class="form-select form-control-custom">
            <option value="Pending" <?= $order['payment_status'] === 'Pending' ? 'selected' : '' ?>>Payment Pending</option>
            <option value="Paid" <?= $order['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid (Received)</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 py-3">
          <i class="bi bi-check2-circle me-1"></i> Update Status
        </button>
      </form>
    </div>

    <!-- Customer Contact Card -->
    <div class="admin-card p-4">
      <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-fill text-secondary me-2"></i> Customer Contact</h5>

      <div class="d-flex flex-column gap-2 small">
        <div>
          <span class="text-muted d-block">Customer Name</span>
          <strong class="text-dark fs-6"><?= e($order['customer_name']) ?></strong>
        </div>

        <div>
          <span class="text-muted d-block">Phone</span>
          <a href="tel:<?= e($order['customer_phone']) ?>" class="text-primary fw-bold"><?= e($order['customer_phone']) ?></a>
        </div>

        <div>
          <span class="text-muted d-block">Email</span>
          <a href="mailto:<?= e($order['customer_email']) ?>" class="text-secondary"><?= e($order['customer_email']) ?></a>
        </div>

        <div>
          <span class="text-muted d-block">Payment Method Selected</span>
          <span class="badge bg-light text-dark border"><?= e($order['payment_method']) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
