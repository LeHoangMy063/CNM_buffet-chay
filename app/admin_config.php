<?php

// ============================================
// CẤU HÌNH XÁC THỰC ADMIN - PHP 5.3 COMPATIBLE
// ============================================

if (!defined('ADMIN_SESSION_KEY')) {
    define('ADMIN_SESSION_KEY', 'admin_user');
}

define('ROLE_ADMIN',    'admin');
define('ROLE_STAFF',    'staff');
define('ROLE_CUSTOMER', 'customer');

define('PASSWORD_MIN_LENGTH',   6);
// PHP 5.3 không có password_hash, dùng md5 (tương thích cũ)
define('PASSWORD_HASH_METHOD',  'md5');

// Session timeout (phút)
define('ADMIN_SESSION_TIMEOUT', 480); // 8 giờ

// URL chuyển hướng
define('ADMIN_LOGIN_URL',      BASE_URL . '/dang-nhap');
define('ADMIN_DASHBOARD_URL',  BASE_URL . '/quan-tri');
define('CUSTOMER_HOME_URL',    BASE_URL . '/');
