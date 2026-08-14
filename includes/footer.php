  </main>

  <!-- Global Footer -->
  <footer class="footer-custom">
    <div class="container">
      <div class="row g-4">
        <!-- Brand Info -->
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="brand-icon" style="background: rgba(255,255,255,0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
              <i class="bi bi-lightning-charge-fill fs-5"></i>
            </div>
            <h4 class="text-white fw-bold mb-0">Power<span style="color: #f59e0b;">Prep</span></h4>
          </div>
          <p class="text-white-50 pe-lg-4">
            Chef-crafted, nutrient-dense, high-protein meal prep designed to help you build lean muscle, recover faster, and fuel your active lifestyle without sacrificing flavor.
          </p>
          <div class="d-flex gap-3 mt-3">
            <span class="badge bg-secondary-subtle text-success p-2 rounded-3 border border-success-subtle"><i class="bi bi-shield-check me-1"></i> 100% Fresh Daily</span>
            <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-3 border border-warning-subtle"><i class="bi bi-fire me-1"></i> 40g+ Protein/Meal</span>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-2 col-md-6 col-6">
          <h5>Menu Links</h5>
          <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
            <li><a href="meals.php"><i class="bi bi-chevron-right me-1 text-warning"></i> All Meals</a></li>
            <li><a href="meals.php?category=High%20Protein"><i class="bi bi-chevron-right me-1 text-warning"></i> High Protein</a></li>
            <li><a href="meals.php?category=Low%20Carb"><i class="bi bi-chevron-right me-1 text-warning"></i> Low Carb</a></li>
            <li><a href="meals.php?category=Breakfast"><i class="bi bi-chevron-right me-1 text-warning"></i> Breakfast</a></li>
            <li><a href="meals.php?category=Vegetarian"><i class="bi bi-chevron-right me-1 text-warning"></i> Vegetarian</a></li>
          </ul>
        </div>

        <!-- Customer Support -->
        <div class="col-lg-3 col-md-6 col-6">
          <h5>Customer Care</h5>
          <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
            <li><a href="orders.php"><i class="bi bi-truck me-2"></i> Track Orders</a></li>
            <li><a href="cart.php"><i class="bi bi-bag-check me-2"></i> View Cart</a></li>
            <li><a href="account.php"><i class="bi bi-person me-2"></i> My Account</a></li>
            <li><a href="index.php#how-it-works"><i class="bi bi-info-circle me-2"></i> How It Works</a></li>
            <li><a href="admin/login.php"><i class="bi bi-shield-lock me-2"></i> Chef Admin Portal</a></li>
          </ul>
        </div>

        <!-- Cook & Contact Info -->
        <div class="col-lg-3 col-md-6">
          <h5>Kitchen & Cook</h5>
          <p class="text-white-50 mb-2">
            <strong class="text-white"><?= e(get_setting('cook_name', 'Chef Alex Reynolds')) ?></strong><br>
            <?= e(get_setting('address', '742 Evergreen Fitness Blvd')) ?>
          </p>
          <p class="text-white-50 mb-2">
            <i class="bi bi-telephone-fill text-warning me-2"></i> <?= e(get_setting('contact_phone', '+1 (555) 349-7890')) ?>
          </p>
          <p class="text-white-50 mb-2">
            <i class="bi bi-envelope-fill text-warning me-2"></i> <?= e(get_setting('contact_email', 'alex@powerprepkitchen.com')) ?>
          </p>
          <p class="text-white-50 mb-0">
            <i class="bi bi-clock-fill text-warning me-2"></i> <?= e(get_setting('business_hours', 'Mon - Sat: 7am - 9pm')) ?>
          </p>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom text-center">
        <p class="mb-0">
          &copy; <?= date('Y') ?> <strong>PowerPrep Kitchen</strong>. All Rights Reserved. Crafted for high-performance healthy nutrition.
        </p>
      </div>
    </div>
  </footer>

  <!-- Bootstrap 5 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom Frontend JS -->
  <script src="assets/js/script.js"></script>
</body>
</html>
