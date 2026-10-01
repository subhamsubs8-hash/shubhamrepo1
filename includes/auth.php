<?php
/**
 * Authentication & Authorization Module
 * PaushtikAahar Kitchen - High-Protein Meal Ordering System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if any user is logged in
function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

// Check if current user is admin
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user data
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'customer'
    ];
}

// Guard: Require customer / user login
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        // Store intended destination
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        set_flash_message('warning', 'Please log in to continue.');
        redirect($redirect_url);
    }
}

// Guard: Require admin privileges
function require_admin($redirect_url = '../admin/login.php') {
    if (!is_admin()) {
        set_flash_message('danger', 'Access denied. Administrator privileges required.');
        redirect($redirect_url);
    }
}

// Set login session
function login_user($user) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'] ?? '';
    $_SESSION['user_role']  = $user['role'];
}

// Destroy session on logout
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
