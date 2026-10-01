<?php
/**
 * Shopping Cart Management Module
 * PaushtikAahar Kitchen - High-Protein Meal Ordering System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function init_cart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// Add meal to cart with availability validation
function add_to_cart($meal_id, $quantity = 1) {
    global $pdo;
    init_cart();

    $meal_id = (int)$meal_id;
    $quantity = max(1, (int)$quantity);

    // Verify meal in database
    $stmt = $pdo->prepare("SELECT id, name, is_available FROM meals WHERE id = ?");
    $stmt->execute([$meal_id]);
    $meal = $stmt->fetch();

    if (!$meal) {
        return ['success' => false, 'message' => 'Meal not found.'];
    }

    if (!$meal['is_available']) {
        return ['success' => false, 'message' => 'Sorry, "' . $meal['name'] . '" is currently unavailable.'];
    }

    if (isset($_SESSION['cart'][$meal_id])) {
        $_SESSION['cart'][$meal_id] += $quantity;
    } else {
        $_SESSION['cart'][$meal_id] = $quantity;
    }

    return ['success' => true, 'message' => '"' . $meal['name'] . '" added to your meal prep cart!'];
}

// Update quantity for a specific meal
function update_cart_quantity($meal_id, $quantity) {
    init_cart();
    $meal_id = (int)$meal_id;
    $quantity = (int)$quantity;

    if ($quantity <= 0) {
        remove_from_cart($meal_id);
    } else {
        $_SESSION['cart'][$meal_id] = min(50, $quantity);
    }
}

// Remove item from cart
function remove_from_cart($meal_id) {
    init_cart();
    $meal_id = (int)$meal_id;
    if (isset($_SESSION['cart'][$meal_id])) {
        unset($_SESSION['cart'][$meal_id]);
    }
}

// Clear whole cart
function clear_cart() {
    $_SESSION['cart'] = [];
}

// Total distinct items / units count
function get_cart_count() {
    init_cart();
    $total_units = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $total_units += (int)$qty;
    }
    return $total_units;
}

// Retrieve enriched cart items from database
function get_cart_items() {
    global $pdo;
    init_cart();

    if (empty($_SESSION['cart'])) {
        return [];
    }

    $meal_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($meal_ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM meals WHERE id IN ($placeholders)");
    $stmt->execute($meal_ids);
    $meals = $stmt->fetchAll();

    $cart_items = [];
    foreach ($meals as $meal) {
        $id = $meal['id'];
        $qty = $_SESSION['cart'][$id] ?? 0;
        if ($qty > 0) {
            $subtotal = (float)$meal['price'] * $qty;
            $cart_items[] = [
                'id'          => $meal['id'],
                'name'        => $meal['name'],
                'slug'        => $meal['slug'],
                'image'       => $meal['image'],
                'price'       => (float)$meal['price'],
                'calories'    => (int)$meal['calories'],
                'protein'     => (int)$meal['protein'],
                'carbs'       => (int)$meal['carbs'],
                'fat'         => (int)$meal['fat'],
                'is_available'=> (bool)$meal['is_available'],
                'quantity'    => $qty,
                'subtotal'    => $subtotal,
            ];
        }
    }

    return $cart_items;
}

// Get subtotal
function get_cart_subtotal() {
    $items = get_cart_items();
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
    }
    return $subtotal;
}

// Get delivery fee
function get_cart_delivery_fee() {
    $items = get_cart_items();
    if (empty($items)) {
        return 0.00;
    }
    return (float)get_setting('delivery_fee', '3.99');
}

// Get final total
function get_cart_total() {
    $subtotal = get_cart_subtotal();
    if ($subtotal <= 0) return 0.00;
    $delivery_fee = get_cart_delivery_fee();
    return $subtotal + $delivery_fee;
}
