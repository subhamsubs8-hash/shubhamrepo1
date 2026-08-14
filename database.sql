-- High-Protein Meal Prep Database Schema & Seed Data
-- Database Name: high_protein_meals

CREATE DATABASE IF NOT EXISTS `high_protein_meals` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `high_protein_meals`;

-- 1. Users Table
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `meals`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Meals Table
CREATE TABLE `meals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(180) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `ingredients` TEXT NOT NULL,
  `allergens` VARCHAR(255) DEFAULT 'None',
  `image` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `calories` INT NOT NULL,
  `protein` INT NOT NULL,
  `carbs` INT NOT NULL,
  `fat` INT NOT NULL,
  `fiber` INT NOT NULL DEFAULT 0,
  `preparation_time` VARCHAR(50) NOT NULL DEFAULT '20-25 mins',
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Orders Table
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(120) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `delivery_city` VARCHAR(80) NOT NULL,
  `delivery_instructions` TEXT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 3.99,
  `total` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
  `payment_status` ENUM('Pending', 'Paid') NOT NULL DEFAULT 'Pending',
  `status` ENUM('Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for Delivery', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Order Items Table
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `meal_id` INT NULL,
  `meal_name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`meal_id`) REFERENCES `meals`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Business Settings Table
CREATE TABLE `settings` (
  `key_name` VARCHAR(50) PRIMARY KEY,
  `key_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Settings
INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('site_name', 'PowerPrep Kitchen'),
('tagline', 'High-Protein Meals Made Fresh For Your Fitness Goals'),
('cook_name', 'Chef Alex Reynolds'),
('contact_email', 'alex@powerprepkitchen.com'),
('contact_phone', '+1 (555) 349-7890'),
('delivery_fee', '3.99'),
('currency_symbol', '$'),
('address', '742 Evergreen Fitness Blvd, Suite 101'),
('business_hours', 'Mon - Sat: 7:00 AM - 9:00 PM');

-- Insert Initial Users (Admin & Customer)
-- Passwords:
-- Admin: admin123
-- Customer: customer123
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('Head Chef Alex', 'admin@mealprep.com', '+1 555-0199', '$2y$10$cry6F9jCdanhlmw/ZBz4s.3H4lZ1GAgVvSHCmmcuRa4.8vwBPRe/2', 'admin'),
('John Doe', 'customer@example.com', '+1 555-0144', '$2y$10$OhG9suqUbWgT.SUKqN/zTuov883LWi/xIdeh1or5Guy.ap71cc28u', 'customer');

-- Insert 8 Realistic High-Protein Meals
INSERT INTO `meals` (`name`, `slug`, `description`, `category`, `ingredients`, `allergens`, `image`, `price`, `calories`, `protein`, `carbs`, `fat`, `fiber`, `preparation_time`, `is_available`, `is_featured`) VALUES
(
  'Grilled Chicken Power Bowl',
  'grilled-chicken-power-bowl',
  'Tender herb-marinated chicken breast served over fluffy brown rice, steamed broccoli florets, sweet corn, and drizzled with our signature lemon garlic dressing.',
  'Lunch',
  'Herb-marinated chicken breast (200g), Organic brown jasmine rice, Steamed broccoli florets, Sweet roasted corn, Olive oil, Lemon zest, Garlic herbs',
  'None',
  'grilled_chicken_bowl.jpg',
  12.99,
  620,
  55,
  52,
  16,
  8,
  '20 mins',
  1,
  1
),
(
  'High-Protein Beef & Jasmine Rice Bowl',
  'high-protein-beef-jasmine-rice-bowl',
  'Lean 93/7 grass-fed ground beef seasoned with smoked paprika and cumin, served with steamed jasmine rice, roasted bell peppers, and avocado wedges.',
  'Dinner',
  'Lean grass-fed ground beef (220g), Steamed white jasmine rice, Red and yellow bell peppers, Hass avocado, House seasoning blend, Fresh cilantro',
  'None',
  'beef_rice_bowl.jpg',
  14.49,
  680,
  58,
  56,
  20,
  7,
  '25 mins',
  1,
  1
),
(
  'Chicken Teriyaki Clean Meal Prep',
  'chicken-teriyaki-clean-meal-prep',
  'Glazed chicken breast with low-sodium homemade teriyaki sauce, sesame seeds, steamed edamame, shredded carrots, and nutrient-dense tri-color quinoa.',
  'Dinner',
  'Free-range chicken breast, Housemade low-sodium teriyaki glaze (honey, ginger, tamari), Tri-color quinoa, Shelled edamame, Carbs-light stir-fry veggies, Toasted sesame',
  'Soy, Sesame',
  'chicken_teriyaki_prep.jpg',
  13.49,
  590,
  52,
  48,
  14,
  9,
  '20 mins',
  1,
  1
),
(
  'Turkey & Sweet Potato Muscle Bowl',
  'turkey-sweet-potato-muscle-bowl',
  'Spiced extra-lean ground turkey breast simmered with roasted sweet potato cubes, black beans, baby spinach, and charred zucchini.',
  'High Protein',
  'Extra lean ground turkey (99% fat free), Cinnamon roasted sweet potatoes, Black beans, Baby spinach, Charred zucchini rounds, Himalayan pink salt',
  'None',
  'turkey_sweet_potato.jpg',
  13.99,
  560,
  50,
  54,
  12,
  10,
  '20 mins',
  1,
  1
),
(
  'Creamy Protein Pasta with Chicken',
  'creamy-protein-pasta-with-chicken',
  'Chickpea/pea protein high-fiber penne smothered in a high-protein cottage cheese Alfredo sauce, topped with grilled chicken breast and fresh parsley.',
  'Dinner',
  'Chickpea protein penne pasta, Grilled sliced chicken breast, High-protein whipped cottage cheese Alfredo, Garlic, Grana Padano, Baby spinach, Cracked black pepper',
  'Dairy, Wheat (in trace)',
  'protein_pasta_chicken.jpg',
  14.99,
  640,
  56,
  50,
  18,
  11,
  '25 mins',
  1,
  1
),
(
  'Wild Salmon Rice & Green Beans',
  'wild-salmon-rice-green-beans',
  'Pan-seared Atlantic salmon fillet with dill and sea salt, accompanied by fluffy basmati rice, tender French green beans, and lemon butter drizzle.',
  'Lunch',
  'Pan-seared Atlantic wild salmon fillet (180g), Basmati rice, French green beans, Grass-fed ghee butter, Fresh dill, Lemon wedge',
  'Fish, Dairy',
  'salmon_rice_beans.jpg',
  15.99,
  610,
  46,
  44,
  24,
  6,
  '20 mins',
  1,
  1
),
(
  'Egg & Chicken Sausage Muscle Breakfast',
  'egg-chicken-sausage-muscle-breakfast',
  'Fluffy pasture-raised whole eggs and egg whites scramble with house-made lean chicken breakfast sausages, roasted red skin potatoes, and grilled cherry tomatoes.',
  'Breakfast',
  'Pasture-raised eggs & egg whites, House lean chicken sausage patties, Rosemary roasted red potatoes, Roasted vine tomatoes, Baby arugula',
  'Eggs',
  'egg_chicken_breakfast.jpg',
  11.99,
  520,
  48,
  38,
  16,
  5,
  '15 mins',
  1,
  1
),
(
  'High-Protein Paneer Tikka Power Bowl',
  'high-protein-paneer-tikka-power-bowl',
  'Tandoori spiced low-fat cottage cheese (paneer) cubes, roasted bell peppers, spiced chickpeas, baby kale, and jeera brown rice with mint yogurt dip.',
  'Vegetarian',
  'Low-fat artisanal paneer (cottage cheese), Spiced chickpeas, Roasted peppers and red onions, Jeera brown basmati rice, Greek yogurt mint raita',
  'Dairy',
  'paneer_tikka_bowl.jpg',
  12.49,
  580,
  42,
  58,
  18,
  9,
  '20 mins',
  1,
  0
),
(
  'Keto Bacon & Avocado Beef Skillet',
  'keto-bacon-avocado-beef-skillet',
  'Ultra low-carb high-protein meal: Seasoned Angus ground beef sautéed with crispy uncured turkey bacon, sliced avocado, sautéed mushrooms, and cheddar.',
  'Low Carb',
  'Angus ground beef, Crispy turkey bacon, Fresh avocado, Sautéed cremini mushrooms, Sharp cheddar cheese, Garlic butter',
  'Dairy',
  'keto_beef_skillet.jpg',
  14.99,
  670,
  54,
  8,
  36,
  5,
  '20 mins',
  1,
  0
);

-- Insert Sample Orders for Demo
INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `delivery_city`, `delivery_instructions`, `subtotal`, `delivery_fee`, `total`, `payment_method`, `payment_status`, `status`, `created_at`) VALUES
(1001, 2, 'John Doe', 'customer@example.com', '+1 555-0144', '123 Iron Street, Apt 4B', 'Metro City', 'Please ring the doorbell and leave at door.', 26.48, 3.99, 30.47, 'Cash on Delivery', 'Pending', 'Preparing', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1002, 2, 'John Doe', 'customer@example.com', '+1 555-0144', '123 Iron Street, Apt 4B', 'Metro City', 'Call before arrival', 14.99, 3.99, 18.98, 'Online Payment', 'Paid', 'Delivered', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO `order_items` (`order_id`, `meal_id`, `meal_name`, `price`, `quantity`, `subtotal`) VALUES
(1001, 1, 'Grilled Chicken Power Bowl', 12.99, 1, 12.99),
(1001, 3, 'Chicken Teriyaki Clean Meal Prep', 13.49, 1, 13.49),
(1002, 5, 'Creamy Protein Pasta with Chicken', 14.99, 1, 14.99);
