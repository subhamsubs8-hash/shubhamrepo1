# PaushtikAahar Kitchen - High-Protein Meal Ordering Web Application

A complete, modern, responsive high-protein meal-prep ordering platform built for personal cooks and meal-prep businesses using **PHP 8+**, **MySQL (PDO)**, **HTML5**, **Vanilla CSS3**, and **Vanilla JavaScript**.

---

## 🌟 Key Features

### 1. Customer & Public Experience
- **Modern Fitness Branding**: Built with an energetic health aesthetic (`#173F35` Forest Green, `#2E7D5B` Fresh Green, `#F59E0B` Orange Accent).
- **Hero & Value Highlights**: Displays chef credentials, protein targets, and 3-step meal prep process.
- **Dynamic Meals Catalog (`meals.php`)**: Instant client-side search, category filter tabs (Breakfast, Lunch, Dinner, High Protein, Low Carb, Vegetarian), and sorting by protein, calories, and price.
- **Detailed Nutritional Profile (`meal.php`)**:
  - Exact calorie count and macro breakdown (Protein, Carbs, Fat, Fiber).
  - Visual color-coded progress bars for each macro.
  - Whole-food ingredients list and allergen warning tags.
- **Session-Based Cart (`cart.php`)**: Real-time quantity increment/decrement, live line item subtotals, and automatic delivery fee calculation.
- **Streamlined Checkout (`checkout.php`)**: Form for customer contact, delivery address, notes for the cook, and multiple payment methods (Cash on Delivery / Online Simulation).
- **Instant Order Confirmation (`order-success.php`)**: Itemized receipt and direct tracking link.
- **Visual Order Tracking Timeline (`orders.php`)**:
  - Live progress stepper: `Placed` → `Confirmed` → `Preparing Fresh` → `Packaged Ready` → `Out for Delivery` → `Delivered`.
  - Direct order lookup for guest buyers without requiring prior login.
- **Customer Account Portal (`account.php`)**: Order history, total fitness investment metrics, and profile manager.

### 2. Chef / Admin Dashboard (`/admin`)
- **KPI Metrics Overview**: Total orders, pending prep orders, total revenue, customer count, and total catalog meals.
- **Real-Time Order Queue (`admin/orders.php` & `admin/order-details.php`)**: Filter by status, change cooking/delivery status with instant sync to customer tracking, view delivery notes and customer phone.
- **Meal Inventory & Recipe Management (`admin/meals.php`, `admin/add-meal.php`, `admin/edit-meal.php`)**:
  - Add and update dishes with photo upload (`uploads/meals/`).
  - Configure calories, protein (g), carbohydrates (g), fats (g), and dietary fiber.
  - One-click availability toggle (Dishes marked unavailable cannot be ordered).
- **Customer Directory (`admin/customers.php`)**: Lifetime orders and spend metrics.
- **Kitchen Settings (`admin/settings.php`)**: Cook name, delivery fee, operating hours, and contact details.

---

## 🚀 Setup Instructions for XAMPP (Localhost)

### Step 1: Install & Launch XAMPP
1. Download and install [XAMPP](https://www.apachefriends.org/) (PHP 8.0+).
2. Open the **XAMPP Control Panel**.
3. Click **Start** next to **Apache** and **MySQL**.

### Step 2: Place the Project in `htdocs`
Make sure the project files are located inside:
```text
C:\xampp\htdocs\subham\
```

### Step 3: Create & Import the Database
1. Open your browser and navigate to **phpMyAdmin**:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click **New** on the left sidebar and create a database named:
   ```text
   high_protein_meals
   ```
3. Click on the **Import** tab.
4. Choose the file `database.sql` located at `C:\xampp\htdocs\subham\database.sql`.
5. Click **Import** (or **Go**). All tables and seed dishes will be populated.

*(Alternatively, run via command prompt: `mysql -u root -p < c:\xampp\htdocs\subham\database.sql`)*

### Step 4: Verify Database Configuration
The database connection is defined in `config/database.php`. The default XAMPP settings are:
```php
$db_host = 'localhost';
$db_name = 'high_protein_meals';
$db_user = 'root';
$db_pass = '';
```

### Step 5: Open the Application
Visit the public website in your web browser:
```text
http://localhost/subham/
```

---

## 🔑 Demo Login Accounts

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Chef / Admin** | `admin@mealprep.com` | `admin123` | `http://localhost/subham/admin/login.php` |
| **Customer** | `customer@example.com` | `customer123` | `http://localhost/subham/login.php` |

*(You can also register a new customer account at `http://localhost/subham/register.php`)*

---

## 📁 Project File Structure

```text
subham/
├── index.php                 # Landing page (Hero, Benefits, Featured Meals, Process)
├── meals.php                 # Searchable & filterable meals menu
├── meal.php                  # Single meal details with macro breakdown bars
├── cart.php                  # Shopping cart management
├── checkout.php              # Checkout form & order creation
├── order-success.php         # Order confirmation & receipt
├── orders.php                # Visual order tracking timeline & history
├── account.php               # Customer profile & account metrics
├── login.php                 # User login
├── register.php              # User registration
├── logout.php                # Logout handler
├── database.sql              # Database schema & sample high-protein dishes
├── README.md                 # Setup & documentation
│
├── config/
│   └── database.php          # PDO database connection & error handling
│
├── includes/
│   ├── header.php            # Global navigation, mobile menu & cart badge
│   ├── footer.php            # Global footer & script includes
│   ├── auth.php              # Session auth & role guards (require_login, require_admin)
│   ├── functions.php         # Helpers (sanitization, CSRF tokens, flash alerts, image upload)
│   └── cart-functions.php    # Session shopping cart helpers
│
├── assets/
│   ├── css/
│   │   ├── style.css         # Modern fitness design system & macro progress bars
│   │   └── admin.css         # Admin dashboard styles & status pills
│   ├── js/
│   │   ├── script.js         # Live search, category filters, sorting, quantity toggles
│   │   └── admin.js          # Image upload preview & confirmations
│   └── images/               # Brand visuals and hero images
│
├── uploads/
│   └── meals/                # High quality meal photos
│
└── admin/
    ├── index.php             # Admin Dashboard overview (KPIs, Recent Orders, Popular Meals)
    ├── login.php             # Dedicated Admin Login
    ├── logout.php            # Admin session logout
    ├── meals.php             # Meal Catalog & quick availability toggle
    ├── add-meal.php          # Add new dish form with image upload
    ├── edit-meal.php         # Edit dish & macros
    ├── delete-meal.php       # Safe meal deletion
    ├── orders.php            # Orders queue with status filter
    ├── order-details.php     # Detailed order view & fulfillment updater
    ├── customers.php         # Registered customer directory
    ├── settings.php          # Cook profile & delivery fee settings
    └── includes/
        ├── header.php        # Admin top navigation
        ├── sidebar.php       # Admin collapsible sidebar
        └── footer.php        # Admin footer
```

---

## 🔒 Security Practices
- **Prepared Statements**: 100% of database queries use PDO prepared statements to protect against SQL Injection.
- **Password Security**: Passwords hashed with standard `password_hash()` and verified with `password_verify()`.
- **Output Escaping**: All user input rendered using `htmlspecialchars()` via helper `e()`.
- **CSRF Protection**: Critical forms include CSRF tokens verified on submit.
- **Secure File Uploads**: Image uploads are validated by MIME type, extension whitelist (`jpg`, `jpeg`, `png`, `webp`), size limits (max 5MB), and verified with `getimagesize()`.
