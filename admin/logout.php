<?php
/**
 * PowerPrep Kitchen - Admin Logout
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logout_user();
set_flash_message('info', 'Admin logged out successfully.');
redirect('login.php');
