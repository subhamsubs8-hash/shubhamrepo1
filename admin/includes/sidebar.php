<?php
/**
 * Admin Sidebar Navigation Component
 */
$page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
  <a href="index.php" class="admin-sidebar-brand">
    <div class="brand-icon" style="background: rgba(245,158,11,0.2); color: #f59e0b; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
      <i class="bi bi-lightning-charge-fill"></i>
    </div>
    <div>Power<span>Prep</span> <small style="font-size: 0.7rem; display: block; color: #a7f3d0; font-weight: 500;">Chef Admin</small></div>
  </a>

  <ul class="admin-nav">
    <li class="admin-nav-item">
      <a href="index.php" class="admin-nav-link <?= $page === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li class="admin-nav-item">
      <a href="meals.php" class="admin-nav-link <?= in_array($page, ['meals.php', 'add-meal.php', 'edit-meal.php']) ? 'active' : '' ?>">
        <i class="bi bi-egg-fried"></i>
        <span>Meal Management</span>
      </a>
    </li>

    <li class="admin-nav-item">
      <a href="orders.php" class="admin-nav-link <?= in_array($page, ['orders.php', 'order-details.php']) ? 'active' : '' ?>">
        <i class="bi bi-bag-check"></i>
        <span>Orders & Prep</span>
      </a>
    </li>

    <li class="admin-nav-item">
      <a href="customers.php" class="admin-nav-link <?= $page === 'customers.php' ? 'active' : '' ?>">
        <i class="bi bi-people"></i>
        <span>Customers</span>
      </a>
    </li>

    <li class="admin-nav-item">
      <a href="settings.php" class="admin-nav-link <?= $page === 'settings.php' ? 'active' : '' ?>">
        <i class="bi bi-sliders"></i>
        <span>Kitchen Settings</span>
      </a>
    </li>
  </ul>

  <div class="admin-sidebar-footer">
    <a href="logout.php" class="admin-nav-link text-danger-emphasis py-2 px-3 bg-dark bg-opacity-25 rounded-3 d-flex align-items-center justify-content-between">
      <span><i class="bi bi-box-arrow-right me-2 text-danger"></i> Log Out</span>
      <i class="bi bi-chevron-right small text-muted"></i>
    </a>
  </div>
</aside>
