<?php
/**
 * PowerPrep Kitchen - Login Portal
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart-functions.php';

if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('account.php');
    }
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid session token. Please submit again.';
    }

    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = 'Please enter your email address.';
    if (empty($password)) $errors[] = 'Please enter your password.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            set_flash_message('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            }

            $redirect_url = $_SESSION['intended_url'] ?? 'account.php';
            unset($_SESSION['intended_url']);
            redirect($redirect_url);
        } else {
            $errors[] = 'Invalid email or password. Please check your credentials.';
        }
    }
}

$page_title = 'Log In to PowerPrep';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
      <div class="auth-card">
        <div class="text-center mb-4">
          <div class="brand-icon mx-auto mb-2" style="background: #e8f3ef; color: #173F35; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="bi bi-person-fill-lock"></i>
          </div>
          <h2 class="fw-bold text-dark mb-1">Welcome Back</h2>
          <p class="text-muted small">Log in to manage your high-protein meal prep</p>
        </div>

        <!-- Demo Accounts Helper -->
        <div class="p-3 bg-light rounded-3 mb-4 border small">
          <div class="fw-bold text-dark mb-1"><i class="bi bi-key-fill text-warning me-1"></i> Quick Demo Logins:</div>
          <div class="text-muted">
            • <strong>Customer:</strong> <code>customer@example.com</code> / <code>customer123</code><br>
            • <strong>Cook / Admin:</strong> <code>admin@mealprep.com</code> / <code>admin123</code>
          </div>
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

        <form method="POST" action="login.php">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label-custom">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control form-control-custom border-start-0 ps-0" placeholder="name@example.com" value="<?= e($email) ?>" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <div class="d-flex justify-content-between">
              <label class="form-label-custom">Password</label>
            </div>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control form-control-custom border-start-0 ps-0" placeholder="Enter your password" required>
            </div>
          </div>

          <button type="submit" class="btn-primary-custom w-100 py-3 mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Log In
          </button>

          <div class="text-center text-muted small">
            Don't have an account yet? <a href="register.php" class="fw-bold text-secondary">Sign Up here</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
