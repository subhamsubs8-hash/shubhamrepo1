<?php
/**
 * Admin Layout Header Component
 * PaushtikAahar Kitchen - High-Protein Meal Ordering System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin('login.php');

$current_user = current_user();
$admin_page = basename($_SERVER['PHP_SELF']);
$site_name = get_setting('site_name', 'PaushtikAahar Kitchen');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? e($page_title) . ' | Admin Portal' : 'Chef Admin Dashboard | ' . e($site_name) ?></title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 CSS & Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Admin CSS Theme -->
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
  <!-- Include Sidebar -->
  <?php require_once __DIR__ . '/sidebar.php'; ?>

  <!-- Backdrop for mobile sidebar -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- Main Content Column -->
  <div class="admin-main">
    <!-- Top Bar -->
    <header class="admin-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
          <i class="bi bi-list fs-4"></i>
        </button>
        <h5 class="fw-bold text-dark mb-0 d-none d-sm-block">Chef & Kitchen Operations</h5>
      </div>

      <div class="d-flex align-items-center gap-3">
        <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-box-arrow-up-right me-1"></i> View Website
        </a>

        <div class="dropdown">
          <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-3 py-1 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="admin-avatar" style="width: 32px; height: 32px; font-size: 0.9rem;">
              <?= strtoupper(substr($current_user['name'], 0, 1)) ?>
            </div>
            <span class="small fw-bold text-dark d-none d-md-inline"><?= e($current_user['name']) ?></span>
            <i class="bi bi-chevron-down small text-muted"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
            <li><a class="dropdown-item py-2" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
          </ul>
        </div>
      </div>
    </header>

    <!-- Flash Alerts Container -->
    <div class="px-4 pt-3">
      <?php display_flash_messages(); ?>
    </div>

    <!-- Main Body Container -->
    <main class="admin-content">
