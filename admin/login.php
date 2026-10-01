<?php
/**
 * PaushtikAahar Kitchen - Admin Login
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin()) {
    redirect('index.php');
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid session token. Please try again.';
    }

    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = 'Please provide admin email.';
    if (empty($password)) $errors[] = 'Please enter password.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            login_user($admin);
            set_flash_message('success', 'Welcome to Chef Kitchen Dashboard, ' . $admin['name'] . '!');
            redirect('index.php');
        } else {
            $errors[] = 'Invalid administrator credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal Login | PaushtikAahar Kitchen</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="auth-card border-0 shadow-lg">
        <div class="text-center mb-4">
          <div class="brand-icon mx-auto mb-3" style="background: #173F35; color: #f59e0b; width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
          <h3 class="fw-bold text-dark mb-1">Chef Admin Portal</h3>
          <p class="text-muted small">Kitchen & Meal Management System</p>
        </div>

        <!-- Default Credentials Demo Box -->
        <div class="p-3 bg-light rounded-3 mb-4 border small text-muted">
          <strong class="text-dark d-block mb-1"><i class="bi bi-key-fill text-warning me-1"></i> Admin Demo Account:</strong>
          Email: <code>admin@mealprep.com</code><br>
          Password: <code>admin123</code>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger rounded-3 mb-4 small">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php display_flash_messages(); ?>

        <form method="POST" action="login.php">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label-custom">Admin Email</label>
            <input type="email" name="email" class="form-control form-control-custom" value="<?= e($email) ?>" placeholder="admin@mealprep.com" required autofocus>
          </div>

          <div class="mb-4">
            <label class="form-label-custom">Password</label>
            <input type="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required>
          </div>

          <button type="submit" class="btn-primary-custom w-100 py-3 mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Access Dashboard
          </button>

          <div class="text-center">
            <a href="../index.php" class="text-muted small"><i class="bi bi-arrow-left"></i> Return to Main Website</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
