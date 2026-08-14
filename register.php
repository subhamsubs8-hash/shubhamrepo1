<?php
/**
 * PowerPrep Kitchen - Customer Registration
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$name = '';
$email = '';
$phone = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) $errors[] = 'Please enter your full name.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (empty($phone)) $errors[] = 'Please enter your phone number.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check if email already exists
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $errors[] = 'An account with this email address already exists. Please log in.';
        } else {
            // Hash password
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $pdo->prepare("
                INSERT INTO users (name, email, phone, password, role)
                VALUES (?, ?, ?, ?, 'customer')
            ");

            if ($stmt_insert->execute([$name, $email, $phone, $hashed_pass])) {
                $new_id = $pdo->lastInsertId();
                $user_data = [
                    'id'    => $new_id,
                    'name'  => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'role'  => 'customer'
                ];
                login_user($user_data);
                set_flash_message('success', 'Welcome to PowerPrep Kitchen, ' . $name . '! Your account is ready.');
                redirect('meals.php');
            } else {
                $errors[] = 'Failed to create account. Please try again.';
            }
        }
    }
}

$page_title = 'Create an Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
      <div class="auth-card">
        <div class="text-center mb-4">
          <div class="brand-icon mx-auto mb-2" style="background: #e8f3ef; color: #173F35; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <h2 class="fw-bold text-dark mb-1">Join PowerPrep</h2>
          <p class="text-muted small">Create your account to order fresh high-protein meals</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger rounded-3 mb-4">
            <ul class="mb-0 ps-3 small">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label-custom">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
              <input type="text" name="name" class="form-control form-control-custom border-start-0 ps-0" placeholder="John Doe" value="<?= e($name) ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control form-control-custom border-start-0 ps-0" placeholder="name@example.com" value="<?= e($email) ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
              <input type="tel" name="phone" class="form-control form-control-custom border-start-0 ps-0" placeholder="+1 (555) 019-2831" value="<?= e($phone) ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control form-control-custom border-start-0 ps-0" placeholder="Minimum 6 characters" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label-custom">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
              <input type="password" name="confirm_password" class="form-control form-control-custom border-start-0 ps-0" placeholder="Repeat your password" required>
            </div>
          </div>

          <button type="submit" class="btn-primary-custom w-100 py-3 mb-3">
            <i class="bi bi-person-plus-fill me-1"></i> Create Account
          </button>

          <div class="text-center text-muted small">
            Already have an account? <a href="login.php" class="fw-bold text-secondary">Log In here</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
