<?php
/**
 * PowerPrep Kitchen - Admin Customer Management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "
    SELECT u.*, 
           COUNT(o.id) as order_count,
           COALESCE(SUM(o.total), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON (o.user_id = u.id OR o.customer_email = u.email)
    WHERE u.role = 'customer'
";

$params = [];
if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " GROUP BY u.id ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$page_title = 'Customer Directory';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <h2 class="fw-bold text-dark mb-1">Registered Customers</h2>
    <p class="text-muted mb-0">View all customer accounts, order counts, and lifetime spent.</p>
  </div>
</div>

<!-- Search Bar -->
<div class="admin-card p-3 mb-4">
  <form method="GET" action="customers.php" class="row g-2 align-items-center">
    <div class="col-md-9">
      <div class="input-group">
        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Search by customer name, email, or phone..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary-custom w-100 py-2">Search</button>
      <?php if (!empty($search)): ?>
        <a href="customers.php" class="btn btn-outline-secondary py-2">Reset</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Customers Table -->
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Customer</th>
            <th class="py-3">Contact Email</th>
            <th class="py-3">Phone</th>
            <th class="py-3 text-center">Orders Placed</th>
            <th class="py-3">Total Invested</th>
            <th class="pe-4 py-3 text-end">Member Since</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">No customers found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($customers as $c): ?>
              <tr>
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="admin-avatar" style="width: 38px; height: 38px; font-size: 1rem;">
                      <?= strtoupper(substr($c['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <strong class="text-dark d-block"><?= e($c['name']) ?></strong>
                      <small class="text-muted">ID: #CUST-<?= $c['id'] ?></small>
                    </div>
                  </div>
                </td>

                <td class="py-3 text-muted">
                  <a href="mailto:<?= e($c['email']) ?>" class="text-secondary"><?= e($c['email']) ?></a>
                </td>

                <td class="py-3 text-muted">
                  <?= e($c['phone']) ?>
                </td>

                <td class="py-3 text-center">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                    <?= (int)$c['order_count'] ?> orders
                  </span>
                </td>

                <td class="py-3 fw-bold text-success">
                  <?= format_price($c['total_spent']) ?>
                </td>

                <td class="pe-4 py-3 text-end text-muted small">
                  <?= date('M d, Y', strtotime($c['created_at'])) ?>
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
