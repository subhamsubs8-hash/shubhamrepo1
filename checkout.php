<?php
/**
 * PaushtikAahar Kitchen - Checkout Page
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

$cart_items = get_cart_items();
if (empty($cart_items)) {
    set_flash_message('warning', 'Your cart is empty. Please select meals first.');
    redirect('meals.php');
}

$subtotal = get_cart_subtotal();
$delivery_fee = get_cart_delivery_fee();
$total = get_cart_total();

// Prefill if logged in
$user = current_user();
$form_name = $user ? $user['name'] : '';
$form_email = $user ? $user['email'] : '';
$form_phone = $user ? $user['phone'] : '';
$form_address = '';
$form_city = '';
$form_instructions = '';
$form_payment = 'Cash on Delivery';

$errors = [];

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security session token. Please try again.';
    }

    $form_name = sanitize($_POST['customer_name'] ?? '');
    $form_email = sanitize($_POST['customer_email'] ?? '');
    $form_phone = sanitize($_POST['customer_phone'] ?? '');
    $form_address = sanitize($_POST['delivery_address'] ?? '');
    $form_city = sanitize($_POST['delivery_city'] ?? '');
    $form_instructions = sanitize($_POST['delivery_instructions'] ?? '');
    $form_payment = sanitize($_POST['payment_method'] ?? 'Cash on Delivery');

    // Validation
    if (empty($form_name)) $errors[] = 'Please provide your full name.';
    if (empty($form_email) || !filter_var($form_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (empty($form_phone)) $errors[] = 'Please provide your contact phone number.';
    if (empty($form_address)) $errors[] = 'Please enter your delivery street address.';
    if (empty($form_city)) $errors[] = 'Please enter your city or area.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $user_id = $user ? $user['id'] : null;
            $payment_status = ($form_payment === 'Online Payment') ? 'Paid' : 'Pending';

            // Insert into orders table
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, customer_name, customer_email, customer_phone,
                    delivery_address, delivery_city, delivery_instructions,
                    subtotal, delivery_fee, total, payment_method, payment_status, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
            ");

            $stmt->execute([
                $user_id,
                $form_name,
                $form_email,
                $form_phone,
                $form_address,
                $form_city,
                $form_instructions,
                $subtotal,
                $delivery_fee,
                $total,
                $form_payment,
                $payment_status
            ]);

            $order_id = $pdo->lastInsertId();

            // Insert order items
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (order_id, meal_id, meal_name, price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($cart_items as $item) {
                $stmt_item->execute([
                    $order_id,
                    $item['id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $item['subtotal']
                ]);
            }

            $pdo->commit();

            // Clear session cart
            clear_cart();
            $_SESSION['last_order_id'] = $order_id;

            set_flash_message('success', 'Your high-protein meal order #' . $order_id . ' was placed successfully!');
            redirect('order-success.php?id=' . $order_id);

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not process order: ' . $e->getMessage();
        }
    }
}

$page_title = 'Secure Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="mb-4">
    <h1 class="fw-bold text-dark mb-1">Checkout & Delivery Details</h1>
    <p class="text-muted">Enter your contact and drop-off information so your personal cook can deliver fresh.</p>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger rounded-3 mb-4">
      <ul class="mb-0 ps-3">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="checkout.php" id="checkoutForm">
    <?= csrf_field() ?>
    
    <div class="row g-4">
      <!-- Left Column: Customer & Delivery Info Form -->
      <div class="col-lg-7">
        <!-- Contact Information -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-person-lines-fill text-secondary me-2"></i> 1. Contact Information
          </h5>

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-custom">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="customer_name" class="form-control form-control-custom" value="<?= e($form_name) ?>" required placeholder="e.g. John Doe">
            </div>

            <div class="col-md-6">
              <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="customer_email" class="form-control form-control-custom" value="<?= e($form_email) ?>" required placeholder="john@example.com">
            </div>

            <div class="col-md-6">
              <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="customer_phone" class="form-control form-control-custom" value="<?= e($form_phone) ?>" required placeholder="+1 (555) 019-2831">
            </div>
          </div>
        </div>

        <!-- Delivery Address -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-geo-alt-fill text-secondary me-2"></i> 2. Delivery Address
          </h5>

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-custom">Street Address / Apt / Suite <span class="text-danger">*</span></label>
              <input type="text" name="delivery_address" class="form-control form-control-custom" value="<?= e($form_address) ?>" required placeholder="e.g. 742 Evergreen Terrace, Apt 3B">
            </div>

            <div class="col-md-12">
              <label class="form-label-custom">City / Area / Neighborhood <span class="text-danger">*</span></label>
              <input type="text" name="delivery_city" class="form-control form-control-custom" value="<?= e($form_city) ?>" required placeholder="e.g. Downtown Metro / North Hills">
            </div>

            <div class="col-12">
              <label class="form-label-custom">Special Delivery Notes (Optional)</label>
              <textarea name="delivery_instructions" class="form-control form-control-custom" rows="2" placeholder="e.g. Leave with doorman, call upon arrival, gate code #1234..."><?= e($form_instructions) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-credit-card-2-front-fill text-secondary me-2"></i> 3. Payment Method
          </h5>

          <div class="d-flex flex-column gap-3">
            <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
              <input type="radio" name="payment_method" value="Cash on Delivery" <?= $form_payment === 'Cash on Delivery' ? 'checked' : '' ?> class="form-check-input mt-0">
              <div>
                <strong class="text-dark d-block">Cash on Delivery (COD)</strong>
                <small class="text-muted">Pay the delivery driver in cash or local debit upon receiving your fresh meal package.</small>
              </div>
            </label>

            <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer">
              <input type="radio" name="payment_method" value="Online Payment" <?= $form_payment === 'Online Payment' ? 'checked' : '' ?> class="form-check-input mt-0">
              <div>
                <strong class="text-dark d-block">Online Card / Digital Wallet</strong>
                <small class="text-muted">Simulate instant card payment (Zero fee, instantaneous approval).</small>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Review & Submit -->
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
          <h5 class="fw-bold text-dark mb-3">Order Items (<?= count($cart_items) ?>)</h5>

          <div class="d-flex flex-column gap-3 mb-3" style="max-height: 260px; overflow-y: auto;">
            <?php foreach ($cart_items as $item): ?>
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= $item['quantity'] ?>x</span>
                  <div>
                    <h6 class="mb-0 text-dark small fw-bold"><?= e($item['name']) ?></h6>
                    <small class="text-muted"><?= (int)$item['protein'] ?>g Protein</small>
                  </div>
                </div>
                <span class="fw-bold text-dark small"><?= format_price($item['subtotal']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span class="fw-bold text-dark"><?= format_price($subtotal) ?></span>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted">Delivery Fee</span>
            <span class="fw-bold text-dark"><?= format_price($delivery_fee) ?></span>
          </div>

          <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-4">
            <span class="fw-bold fs-5 text-dark">Total Due</span>
            <span class="fs-4 fw-extrabold text-primary"><?= format_price($total) ?></span>
          </div>

          <button type="submit" class="btn-primary-custom w-100 py-3 fs-5">
            <i class="bi bi-lock-fill me-1"></i> Place Order Now
          </button>

          <p class="text-center text-muted small mt-3 mb-0">
            <i class="bi bi-shield-check text-success"></i> Your order is sent directly to Chef Alex for immediate preparation.
          </p>
        </div>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
