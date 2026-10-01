<?php
/**
 * PaushtikAahar Kitchen - Customer Account Profile
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

require_login('login.php');

$user = current_user();
$user_id = $user['id'];

// Handle Profile Update POST
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (empty($name)) $errors[] = 'Name cannot be empty.';
    if (empty($phone)) $errors[] = 'Phone cannot be empty.';

    if (empty($errors)) {
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $hashed, $user_id]);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_phone'] = $phone;
                set_flash_message('success', 'Profile and password updated successfully!');
                redirect('account.php');
            }
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $user_id]);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            set_flash_message('success', 'Profile updated successfully!');
            redirect('account.php');
        }
    }
}

// Fetch user data fresh from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$db_user = $stmt->fetch();

// Fetch orders stats
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
$stmt_orders->execute([$user_id, $db_user['email']]);
$user_orders = $stmt_orders->fetchAll();

$total_orders = count($user_orders);
$total_spent = 0.0;
$active_orders = 0;

foreach ($user_orders as $ord) {
    $total_spent += (float)$ord['total'];
    if (!in_array($ord['status'], ['Delivered', 'Cancelled'])) {
        $active_orders++;
    }
}

$page_title = 'My Account Profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="mb-4">
    <span class="section-tag">Customer Portal</span>
    <h1 class="fw-bold text-dark mb-1">Welcome, <?= e($db_user['name']) ?></h1>
    <p class="text-muted">Manage your personal details and view your meal prep ordering history.</p>
  </div>

  <!-- Metric Quick Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-semibold">Total Orders</small>
          <h3 class="fw-bold text-dark mb-0"><?= $total_orders ?></h3>
        </div>
        <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-3">
          <i class="bi bi-box2-heart-fill"></i>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-semibold">Active In-Kitchen</small>
          <h3 class="fw-bold text-warning-emphasis mb-0"><?= $active_orders ?></h3>
        </div>
        <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis fs-3">
          <i class="bi bi-fire"></i>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-semibold">Total Invested in Health</small>
          <h3 class="fw-bold text-success mb-0"><?= format_price($total_spent) ?></h3>
        </div>
        <div class="rounded-3 p-3 bg-success-subtle text-success fs-3">
          <i class="bi bi-wallet2"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Profile Update Form -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-gear text-secondary me-2"></i> Profile Details</h5>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger rounded-3 mb-3">
            <ul class="mb-0 ps-3 small">
              <?php foreach ($errors as $e): ?>
                <li><?= e($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="account.php">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label-custom">Full Name</label>
            <input type="text" name="name" class="form-control form-control-custom" value="<?= e($db_user['name']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Email Address (Immutable)</label>
            <input type="email" class="form-control form-control-custom bg-light" value="<?= e($db_user['email']) ?>" readonly disabled>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Phone Number</label>
            <input type="tel" name="phone" class="form-control form-control-custom" value="<?= e($db_user['phone']) ?>" required>
          </div>

          <hr class="my-3">

          <div class="mb-3">
            <label class="form-label-custom">New Password <small class="text-muted fw-normal">(leave blank to keep current)</small></label>
            <input type="password" name="new_password" class="form-control form-control-custom" placeholder="New password">
          </div>

          <button type="submit" class="btn-primary-custom w-100 py-2">
            <i class="bi bi-check2-circle"></i> Save Changes
          </button>
        </form>
      </div>
    </div>

    <!-- Right Column: Recent Orders -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
          <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-secondary me-2"></i> Recent Orders</h5>
          <a href="meals.php" class="btn btn-sm btn-primary-custom">Order More</a>
        </div>

        <div class="card-body p-0">
          <?php if (empty($user_orders)): ?>
            <div class="p-4 text-center text-muted">
              You have not placed any orders yet.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4 py-3">Order</th>
                    <th class="py-3">Date</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Total</th>
                    <th class="pe-4 py-3 text-end">Track</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($user_orders as $ord): ?>
                    <tr>
                      <td class="ps-4 py-3 fw-bold text-dark">#<?= $ord['id'] ?></td>
                      <td class="py-3 text-muted small"><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
                      <td class="py-3">
                        <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $ord['status'])) ?>">
                          <?= e($ord['status']) ?>
                        </span>
                      </td>
                      <td class="py-3 fw-bold text-dark"><?= format_price($ord['total']) ?></td>
                      <td class="pe-4 py-3 text-end">
                        <a href="orders.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-custom">
                          <i class="bi bi-geo-alt"></i>
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
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
