<?php

// ============================================
// CẤU HÌNH XÁC THỰC QUẢN LÝ - PHP 5.3 COMPATIBLE
// ============================================

if (!defined('QUANLY_SESSION_KEY')) {
    define('QUANLY_SESSION_KEY', 'quanly_user');
}

define('ROLE_QUANLY', 'quanly');
define('ROLE_NHANVIEN', 'nhanvien');
define('ROLE_CUSTOMER', 'customer');

define('PASSWORD_MIN_LENGTH',   6);
define('PASSWORD_HASH_METHOD',  'password_hash');

// Session timeout (phút)
define('QUANLY_SESSION_TIMEOUT', 480); // 8 giờ

// URL chuyển hướng
define('QUANLY_LOGIN_URL',      BASE_URL . '/dang-nhap');
define('QUANLY_DASHBOARD_URL',  BASE_URL . '/quan-tri');
define('CUSTOMER_HOME_URL',    BASE_URL . '/');
