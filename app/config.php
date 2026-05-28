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
    $baseUrl = getenv('BASE_URL');
    $requestHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    if ($requestHost !== '' && strpos($baseUrl, 'localhost') !== false && strpos($requestHost, 'localhost') === false && strpos($requestHost, '127.0.0.1') === false) {
        $parts = parse_url($baseUrl);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'http';
        $path = isset($parts['path']) ? rtrim($parts['path'], '/') : '';
        $baseUrl = $scheme . '://' . $requestHost . $path;
    }
    define('BASE_URL', $baseUrl);
} else {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $basePath = rtrim(dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''), '/\\');
    // If basePath is root, keep empty string
    $basePath = $basePath === '/' ? '' : $basePath;
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}

define('PUBLIC_BASE_URL', getenv('PUBLIC_BASE_URL') ? rtrim(getenv('PUBLIC_BASE_URL'), '/') : BASE_URL);

define('PRICE_ADULT', 199000);
define('PRICE_CHILD', 0);
define('RESTAURANT_CAPACITY', 40);
define('BUFFET_SESSION_MINUTES', 90);

// VietQR direct bank transfer settings
define('VIETQR_ENABLED', getenv('VIETQR_ENABLED') ? getenv('VIETQR_ENABLED') : '0');
define('VIETQR_BANK_ID', getenv('VIETQR_BANK_ID') ? getenv('VIETQR_BANK_ID') : '');
define('VIETQR_ACCOUNT_NO', getenv('VIETQR_ACCOUNT_NO') ? getenv('VIETQR_ACCOUNT_NO') : '');
define('VIETQR_ACCOUNT_NAME', getenv('VIETQR_ACCOUNT_NAME') ? getenv('VIETQR_ACCOUNT_NAME') : '');
define('VIETQR_TEMPLATE', getenv('VIETQR_TEMPLATE') ? getenv('VIETQR_TEMPLATE') : 'compact2');

define('SESSION_LIFETIME', 28800); // 8 gio

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hien thi loi (tat tren production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
