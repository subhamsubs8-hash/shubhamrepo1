<?php
/**
 * PowerPrep Kitchen - Logout Handler
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash_message('info', 'You have been safely logged out.');
redirect('login.php');
