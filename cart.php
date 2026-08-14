<?php
/**
 * PowerPrep Kitchen - Shopping Cart
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_qty') {
        $meal_id = (int)($_POST['meal_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        update_cart_quantity($meal_id, $qty);
        set_flash_message('info', 'Cart updated successfully.');
        redirect('cart.php');
    }

    if ($action === 'remove_item') {
        $meal_id = (int)($_POST['meal_id'] ?? 0);
        remove_from_cart($meal_id);
        set_flash_message('warning', 'Meal removed from your cart.');
        redirect('cart.php');
    }

    if ($action === 'clear_cart') {
        clear_cart();
        set_flash_message('info', 'Your cart has been cleared.');
        redirect('cart.php');
    }
}

$cart_items = get_cart_items();
$subtotal = get_cart_subtotal();
$delivery_fee = get_cart_delivery_fee();
$total = get_cart_total();

$page_title = 'Your Meal Prep Cart';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="fw-bold text-dark mb-1">Your Meal Prep Cart</h1>
      <p class="text-muted mb-0">Review your customized high-protein menu before placing your order.</p>
    </div>
    <?php if (!empty($cart_items)): ?>
      <form method="POST" action="cart.php" onsubmit="return confirm('Clear all items from your cart?');">
        <input type="hidden" name="action" value="clear_cart">
        <button type="submit" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-trash3"></i> Clear Cart
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (empty($cart_items)): ?>
    <!-- Empty Cart State -->
    <div class="bg-white rounded-4 border p-5 text-center my-4 shadow-sm">
      <div class="mb-3">
        <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
      </div>
      <h3 class="fw-bold text-dark mb-2">Your cart is currently empty</h3>
      <p class="text-muted mb-4" style="max-width: 460px; margin: 0 auto;">
        You haven't selected any chef-crafted meals yet. Fuel your week by picking your favorite protein-packed dishes!
      </p>
      <a href="meals.php" class="btn-primary-custom px-4 py-3">
        <i class="bi bi-fire"></i> Browse Fresh Meals Menu
      </a>
    </div>
  <?php else: ?>
    <!-- Cart Items & Order Summary -->
    <div class="row g-4">
      <!-- Items List -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col" class="ps-4 py-3">Meal Item</th>
                  <th scope="col" class="py-3">Price</th>
                  <th scope="col" class="py-3">Quantity</th>
                  <th scope="col" class="py-3">Subtotal</th>
                  <th scope="col" class="pe-4 py-3 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cart_items as $item): ?>
                  <tr>
                    <!-- Meal Info -->
                    <td class="ps-4 py-3">
                      <div class="d-flex align-items-center gap-3">
                        <img src="uploads/meals/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="rounded-3 shadow-xs" style="width: 70px; height: 70px; object-fit: cover;" onerror="this.src='uploads/meals/grilled_chicken_bowl.jpg'">
                        <div>
                          <h6 class="fw-bold mb-1">
                            <a href="meal.php?id=<?= $item['id'] ?>" class="text-dark"><?= e($item['name']) ?></a>
                          </h6>
                          <div class="small text-muted">
                            <span class="text-primary fw-semibold"><?= (int)$item['protein'] ?>g Protein</span> • 
                            <span><?= (int)$item['calories'] ?> kcal</span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Price -->
                    <td class="py-3 fw-bold text-dark">
                      <?= format_price($item['price']) ?>
                    </td>

                    <!-- Quantity Control Form -->
                    <td class="py-3">
                      <form method="POST" action="cart.php" class="d-inline-flex align-items-center">
                        <input type="hidden" name="action" value="update_qty">
                        <input type="hidden" name="meal_id" value="<?= $item['id'] ?>">
                        <div class="quantity-control">
                          <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="qty-btn"><i class="bi bi-dash"></i></button>
                          <span class="qty-input px-2 d-flex align-items-center justify-content-center"><?= $item['quantity'] ?></span>
                          <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="qty-btn"><i class="bi bi-plus"></i></button>
                        </div>
                      </form>
                    </td>

                    <!-- Line Total -->
                    <td class="py-3 fw-bold text-primary fs-6">
                      <?= format_price($item['subtotal']) ?>
                    </td>

                    <!-- Remove Action -->
                    <td class="pe-4 py-3 text-end">
                      <form method="POST" action="cart.php" class="d-inline">
                        <input type="hidden" name="action" value="remove_item">
                        <input type="hidden" name="meal_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Remove Item">
                          <i class="bi bi-trash fs-5"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-4">
          <a href="meals.php" class="btn btn-outline-custom">
            <i class="bi bi-arrow-left"></i> Continue Choosing Meals
          </a>
        </div>
      </div>

      <!-- Order Summary Card -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
          <h4 class="fw-bold text-dark mb-3">Order Summary</h4>
          
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Items Subtotal</span>
            <span class="fw-bold text-dark"><?= format_price($subtotal) ?></span>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted">Fresh Delivery Fee</span>
            <span class="fw-bold text-dark"><?= format_price($delivery_fee) ?></span>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <span class="fw-bold fs-5 text-dark">Estimated Total</span>
              <small class="d-block text-muted">All fresh food taxes included</small>
            </div>
            <span class="fs-3 fw-extrabold text-primary"><?= format_price($total) ?></span>
          </div>

          <a href="checkout.php" class="btn-primary-custom w-100 py-3 fs-6">
            Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
          </a>

          <!-- Safety Guarantees -->
          <div class="mt-4 pt-3 border-top text-muted small">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-shield-check text-success fs-5"></i>
              <span>Cooked fresh upon ordering</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-truck text-secondary fs-5"></i>
              <span>Insulated, temperature-safe packaging</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
