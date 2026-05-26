<?php
// ============================================
// CAU HINH UNG DUNG - PHP 5.3 COMPATIBLE
// ============================================

define('DB_HOST',    getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost');
define('DB_NAME',    getenv('DB_NAME') ? getenv('DB_NAME') : 'buffet_chay');
define('DB_USER',    getenv('DB_USER') ? getenv('DB_USER') : 'root');
define('DB_PASS',    getenv('DB_PASS') ? getenv('DB_PASS') : '');
define('DB_CHARSET', getenv('DB_CHARSET') ? getenv('DB_CHARSET') : 'utf8');

define('APP_NAME',   'Buffet Chay An Lac');

// BASE_URL: prefer environment variable, otherwise derive from current request
if (getenv('BASE_URL')) {
    define('BASE_URL', getenv('BASE_URL'));
} else {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $basePath = rtrim(dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''), '/\\');
    // If basePath is root, keep empty string
    $basePath = $basePath === '/' ? '' : $basePath;
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}

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
