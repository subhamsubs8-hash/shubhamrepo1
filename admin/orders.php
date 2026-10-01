<?php
/**
 * PaushtikAahar Kitchen - Admin Orders Management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Quick status change POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? 'Pending');

    $allowed = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for Delivery', 'Delivered', 'Cancelled'];
    if (in_array($new_status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        set_flash_message('success', "Order #{$order_id} status changed to '{$new_status}'.");
    }
    redirect('orders.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

// Filter query
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($status_filter !== 'all' && !empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ? OR id = ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = (int)$search;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$page_title = 'Customer Orders & Kitchen Queue';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <h2 class="fw-bold text-dark mb-1">Customer Orders Queue</h2>
    <p class="text-muted mb-0">Track kitchen preparation, delivery fulfillment, and payment statuses.</p>
  </div>
</div>

<!-- Filter Tabs & Search -->
<div class="admin-card p-3 mb-4">
  <form method="GET" action="orders.php" class="row g-2 align-items-center">
    <div class="col-md-5">
      <div class="input-group">
        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Search by customer name, phone, or order #..." value="<?= e($search) ?>">
      </div>
    </div>

    <div class="col-md-4">
      <select name="status" class="form-select form-control-custom">
        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Order Statuses</option>
        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending (New)</option>
        <option value="Confirmed" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
        <option value="Preparing" <?= $status_filter === 'Preparing' ? 'selected' : '' ?>>Preparing Fresh</option>
        <option value="Ready" <?= $status_filter === 'Ready' ? 'selected' : '' ?>>Ready to Ship</option>
        <option value="Out for Delivery" <?= $status_filter === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
        <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
    </div>

    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary-custom w-100 py-2">Filter</button>
      <?php if ($status_filter !== 'all' || !empty($search)): ?>
        <a href="orders.php" class="btn btn-outline-secondary py-2">Reset</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Order ID</th>
            <th class="py-3">Customer Details</th>
            <th class="py-3">Date / Time</th>
            <th class="py-3">Total</th>
            <th class="py-3">Payment</th>
            <th class="py-3">Current Status</th>
            <th class="pe-4 py-3 text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No orders found matching the filter.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $ord): ?>
              <tr>
                <td class="ps-4 py-3 fw-bold text-dark">
                  <a href="order-details.php?id=<?= $ord['id'] ?>" class="text-primary">#<?= $ord['id'] ?></a>
                </td>

                <td class="py-3">
                  <strong class="text-dark d-block"><?= e($ord['customer_name']) ?></strong>
                  <div class="small text-muted">
                    <span><i class="bi bi-telephone"></i> <?= e($ord['customer_phone']) ?></span> • 
                    <span><i class="bi bi-geo-alt"></i> <?= e($ord['delivery_city']) ?></span>
                  </div>
                </td>

                <td class="py-3 text-muted small">
                  <?= date('M d, Y', strtotime($ord['created_at'])) ?><br>
                  <span class="text-muted"><?= date('g:i A', strtotime($ord['created_at'])) ?></span>
                </td>

                <td class="py-3 fw-bold text-dark fs-6">
                  <?= format_price($ord['total']) ?>
                </td>

                <td class="py-3">
                  <span class="badge <?= $ord['payment_status'] === 'Paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?> rounded-pill">
                    <?= e($ord['payment_method']) ?> (<?= e($ord['payment_status']) ?>)
                  </span>
                </td>

                <td class="py-3">
                  <form method="POST" action="orders.php<?= $status_filter !== 'all' ? '?status=' . urlencode($status_filter) : '' ?>" class="d-inline">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                    <select name="status" class="form-select form-select-sm rounded-pill font-weight-bold" onchange="this.form.submit()" style="width: auto; font-size: 0.8rem; font-weight: 700;">
                      <option value="Pending" <?= $ord['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="Confirmed" <?= $ord['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                      <option value="Preparing" <?= $ord['status'] === 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                      <option value="Ready" <?= $ord['status'] === 'Ready' ? 'selected' : '' ?>>Ready</option>
                      <option value="Out for Delivery" <?= $ord['status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                      <option value="Delivered" <?= $ord['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                      <option value="Cancelled" <?= $ord['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                  </form>
                </td>

                <td class="pe-4 py-3 text-end">
                  <a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-custom" title="View Full Details">
                    <i class="bi bi-eye me-1"></i> Details
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
