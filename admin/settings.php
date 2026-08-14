<?php
/**
 * PowerPrep Kitchen - Admin Kitchen Settings
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        $keys = [
            'site_name', 'tagline', 'cook_name', 'contact_email',
            'contact_phone', 'delivery_fee', 'currency_symbol',
            'address', 'business_hours'
        ];

        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                set_setting($k, trim($_POST[$k]));
            }
        }

        set_flash_message('success', 'Kitchen business settings updated successfully!');
        redirect('settings.php');
    }
}

$site_name = get_setting('site_name', 'PowerPrep Kitchen');
$tagline = get_setting('tagline', 'High-Protein Meals Made Fresh For Your Goals');
$cook_name = get_setting('cook_name', 'Chef Alex Reynolds');
$contact_email = get_setting('contact_email', 'alex@powerprepkitchen.com');
$contact_phone = get_setting('contact_phone', '+1 (555) 349-7890');
$delivery_fee = get_setting('delivery_fee', '3.99');
$currency_symbol = get_setting('currency_symbol', '$');
$address = get_setting('address', '742 Evergreen Fitness Blvd, Suite 101');
$business_hours = get_setting('business_hours', 'Mon - Sat: 7:00 AM - 9:00 PM');

$page_title = 'Kitchen Settings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <h2 class="fw-bold text-dark mb-1">Kitchen & Business Settings</h2>
  <p class="text-muted mb-0">Configure your cook profile, contact details, delivery fee, and operational hours.</p>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="admin-card p-4">
      <form method="POST" action="settings.php">
        <?= csrf_field() ?>

        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shop text-secondary me-2"></i> Brand & Cook Profile</h5>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label-custom">Website / Kitchen Name</label>
            <input type="text" name="site_name" class="form-control form-control-custom" value="<?= e($site_name) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label-custom">Chef / Cook Name</label>
            <input type="text" name="cook_name" class="form-control form-control-custom" value="<?= e($cook_name) ?>" required>
          </div>

          <div class="col-12">
            <label class="form-label-custom">Brand Tagline</label>
            <input type="text" name="tagline" class="form-control form-control-custom" value="<?= e($tagline) ?>" required>
          </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-truck text-secondary me-2"></i> Order & Delivery Preferences</h5>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label-custom">Standard Delivery Fee ($)</label>
            <div class="input-group">
              <span class="input-group-text bg-light">$</span>
              <input type="number" step="0.01" name="delivery_fee" class="form-control form-control-custom" value="<?= e($delivery_fee) ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label-custom">Currency Symbol</label>
            <input type="text" name="currency_symbol" class="form-control form-control-custom" value="<?= e($currency_symbol) ?>" required>
          </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-telephone-fill text-secondary me-2"></i> Contact & Location</h5>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label-custom">Contact Email</label>
            <input type="email" name="contact_email" class="form-control form-control-custom" value="<?= e($contact_email) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label-custom">Contact Phone</label>
            <input type="text" name="contact_phone" class="form-control form-control-custom" value="<?= e($contact_phone) ?>" required>
          </div>

          <div class="col-12">
            <label class="form-label-custom">Kitchen Address</label>
            <input type="text" name="address" class="form-control form-control-custom" value="<?= e($address) ?>" required>
          </div>

          <div class="col-12">
            <label class="form-label-custom">Operating Hours</label>
            <input type="text" name="business_hours" class="form-control form-control-custom" value="<?= e($business_hours) ?>" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 py-3 fs-6">
          <i class="bi bi-check2-circle me-1"></i> Save Kitchen Settings
        </button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
