/**
 * PaushtikAahar Kitchen - Public JavaScript Module
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Quantity Adjuster Controls
  document.querySelectorAll('.quantity-control').forEach(control => {
    const minusBtn = control.querySelector('.qty-minus');
    const plusBtn = control.querySelector('.qty-plus');
    const input = control.querySelector('.qty-input');

    if (!input) return;

    if (minusBtn) {
      minusBtn.addEventListener('click', (e) => {
        e.preventDefault();
        let val = parseInt(input.value) || 1;
        if (val > 1) {
          input.value = val - 1;
          input.dispatchEvent(new Event('change'));
        }
      });
    }

    if (plusBtn) {
      plusBtn.addEventListener('click', (e) => {
        e.preventDefault();
        let val = parseInt(input.value) || 1;
        if (val < 50) {
          input.value = val + 1;
          input.dispatchEvent(new Event('change'));
        }
      });
    }
  });

  // 2. Client-side Instant Filter & Search on meals.php
  const searchInput = document.getElementById('mealSearchInput');
  const filterButtons = document.querySelectorAll('.filter-btn');
  const mealCards = document.querySelectorAll('.meal-grid-item');
  const sortSelect = document.getElementById('mealSortSelect');
  const noResultsEl = document.getElementById('noMealsFound');

  let currentCategory = 'all';
  let searchQuery = '';

  function filterMeals() {
    let visibleCount = 0;

    mealCards.forEach(card => {
      const name = (card.getAttribute('data-name') || '').toLowerCase();
      const desc = (card.getAttribute('data-desc') || '').toLowerCase();
      const category = (card.getAttribute('data-category') || '').toLowerCase();
      const isAvailable = card.getAttribute('data-available') === '1';

      const matchesSearch = name.includes(searchQuery) || desc.includes(searchQuery);
      let matchesCategory = false;

      if (currentCategory === 'all') {
        matchesCategory = true;
      } else if (currentCategory === 'high protein') {
        const protein = parseInt(card.getAttribute('data-protein')) || 0;
        matchesCategory = protein >= 50 || category.includes('high protein');
      } else if (currentCategory === 'low carb') {
        const carbs = parseInt(card.getAttribute('data-carbs')) || 0;
        matchesCategory = carbs <= 30 || category.includes('low carb');
      } else {
        matchesCategory = category.includes(currentCategory);
      }

      if (matchesSearch && matchesCategory) {
        card.style.display = 'block';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (noResultsEl) {
      noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.toLowerCase().trim();
      filterMeals();
    });
  }

  if (filterButtons.length > 0) {
    filterButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCategory = (btn.getAttribute('data-filter') || 'all').toLowerCase();
        filterMeals();
      });
    });
  }

  // 3. Sorting Meals
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      const container = document.getElementById('mealsContainer');
      if (!container) return;

      const itemsArray = Array.from(mealCards);
      const val = sortSelect.value;

      itemsArray.sort((a, b) => {
        if (val === 'protein-desc') {
          return (parseInt(b.getAttribute('data-protein')) || 0) - (parseInt(a.getAttribute('data-protein')) || 0);
        } else if (val === 'calories-asc') {
          return (parseInt(a.getAttribute('data-calories')) || 0) - (parseInt(b.getAttribute('data-calories')) || 0);
        } else if (val === 'price-asc') {
          return (parseFloat(a.getAttribute('data-price')) || 0) - (parseFloat(b.getAttribute('data-price')) || 0);
        } else if (val === 'price-desc') {
          return (parseFloat(b.getAttribute('data-price')) || 0) - (parseFloat(a.getAttribute('data-price')) || 0);
        }
        return 0;
      });

      itemsArray.forEach(item => container.appendChild(item));
    });
  }

  // 4. Auto-dismiss alerts after 5 seconds
  setTimeout(() => {
    document.querySelectorAll('.alert-dismissible').forEach(alertEl => {
      const alert = bootstrap.Alert.getOrCreateInstance(alertEl);
      if (alert) alert.close();
    });
  }, 5000);
});
