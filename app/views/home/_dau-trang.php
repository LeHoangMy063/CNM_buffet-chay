<?php // Admin shared layout - include at top of each admin view 
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin' ?> - <?php echo APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/style.css">
</head>

<body class="page-admin">
    <aside class="sidebar">
        <div class="sb-logo">
            <h2>&#127807; <?php echo APP_NAME ?></h2>
            <span>Quan tri he thong</span>
        </div>
        <nav>
            <?php
            $cur = isset($_GET['_page']) ? $_GET['_page'] : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            ?>
            <a href="<?php echo BASE_URL ?>/admin/dashboard" <?php echo (strpos($cur, 'dashboard') !== false) ? 'class="active"' : '' ?>><span>&#128202;</span> Dashboard</a>
            <a href="<?php echo BASE_URL ?>/admin/tables" <?php echo (strpos($cur, 'tables') !== false)    ? 'class="active"' : '' ?>><span>&#129681;</span> Quan ly ban</a>
            <a href="<?php echo BASE_URL ?>/admin/reservations" <?php echo (strpos($cur, 'reservations') !== false) ? 'class="active"' : '' ?>><span>&#128197;</span> Dat ban</a>
            <a href="<?php echo BASE_URL ?>/admin/menu" <?php echo (strpos($cur, 'menu') !== false)     ? 'class="active"' : '' ?>><span>&#127869;</span> Thuc don</a>
            <a href="<?php echo BASE_URL ?>/admin/orders" <?php echo (strpos($cur, 'orders') !== false)   ? 'class="active"' : '' ?>><span>&#128203;</span> Goi mon</a>
        </nav>
        <div class="sb-footer">
            <a href="<?php echo BASE_URL ?>">&#8592; Ve trang chu</a>
            <a href="<?php echo BASE_URL ?>/logout" style="margin-top:0.5rem;color:rgba(255,100,100,0.7)">Dang xuat</a>
        </div>
    </aside>
    <div class="main">
        <div class="topbar">
            <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>

            <span class="admin-badge">
                &#128100;
                <?php
                if (isset($_SESSION['user'])) {
                    $user = $_SESSION['user'];

                    if (isset($user['full_name']) && $user['full_name'] != '') {
                        echo htmlspecialchars($user['full_name']);
                    } else {
                        echo htmlspecialchars($user['username']);
                    }

                    if (isset($user['role'])) {
                        echo ' (' . $user['role'] . ')';
                    }
                } else {
                    echo 'Guest';
                }
                ?>
            </span>
        </div>
        <div class="content">