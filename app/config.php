<?php
// ============================================
// CAU HINH UNG DUNG - PHP 5.3 COMPATIBLE
// ============================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'buffet_chay');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8');

define('APP_NAME',   'Buffet Chay An Lac');
define('BASE_URL',   'http://localhost/buffet-chay');

define('PRICE_ADULT', 199000);
define('PRICE_CHILD', 0);
define('RESTAURANT_CAPACITY', 40);
define('BUFFET_SESSION_MINUTES', 90);

define('SESSION_LIFETIME', 28800); // 8 gio

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hien thi loi (tat tren production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
