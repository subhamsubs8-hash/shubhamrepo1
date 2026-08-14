<?php
/**
 * PowerPrep Kitchen - Admin Delete Meal Handler
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin('login.php');

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    // Delete meal (Foreign key constraints in order_items will set meal_id = NULL so orders are preserved)
    $stmt = $pdo->prepare("DELETE FROM meals WHERE id = ?");
    $stmt->execute([$id]);
    set_flash_message('success', 'Meal deleted successfully from catalog.');
} else {
    set_flash_message('danger', 'Invalid meal ID provided.');
}

redirect('meals.php');
