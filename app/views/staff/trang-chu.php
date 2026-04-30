<?php
$nhanVien    = isset($nhanVien) ? $nhanVien : (isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array());
$tenNhanVien = isset($nhanVien['ho_ten']) && $nhanVien['ho_ten'] !== ''
    ? $nhanVien['ho_ten']
    : (isset($nhanVien['ten_dang_nhap']) ? $nhanVien['ten_dang_nhap'] : 'Nhân viên');
$vaiTro = isset($nhanVien['vai_tro']) ? $nhanVien['vai_tro'] : '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Trang nhân viên - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/staff/dashboard.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/staff/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/staff/orders.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/staff/orders.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/staff/reservations.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/staff/reservations.css'); ?>">
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark">&#127807;</div>
                <div>
                    <h1><?php echo APP_NAME; ?></h1>
                    <span>Khu vực nhân viên</span>
                </div>
            </div>

            <nav class="side-nav">
                <button type="button" class="side-link active" data-section="home" onclick="StaffTabs.show('home')">
                    <span class="side-icon">&#8962;</span>
                    <span>Trang chủ</span>
                </button>
                <button type="button" class="side-link" data-section="dat-ban" onclick="StaffTabs.show('dat-ban')">
                    <span class="side-icon">&#129681;</span>
                    <span>Quản lý bàn</span>
                </button>
                <div class="side-subnav" id="tableSubnav" style="display:none">
                    <button type="button" class="side-sub-link active" data-pane="xac-nhan-dat-ban" onclick="StaffTableManager.showPane('xac-nhan-dat-ban')">
                        Xác nhận đặt bàn
                    </button>
                    <button type="button" class="side-sub-link" data-pane="cap-nhat-trang-thai-ban" onclick="StaffTableManager.showPane('cap-nhat-trang-thai-ban')">
                        Cập nhật trạng thái bàn
                    </button>
                    <button type="button" class="side-sub-link" data-pane="xac-nhan-mon" onclick="StaffTableManager.showPane('xac-nhan-mon')">
                        Xác nhận món theo bàn
                    </button>
                </div>
                <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem">
                    <span class="side-icon">&#11088;</span>
                    <span>Tích điểm</span>
                </a>
                <?php if ($vaiTro === 'admin'): ?>
                    <a class="side-link" href="<?php echo BASE_URL; ?>/quan-tri/tong-quan">
                        <span class="side-icon">&#9881;</span>
                        <span>Quản trị</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-card">
                    <span>Đang đăng nhập</span>
                    <strong><?php echo htmlspecialchars($tenNhanVien, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if ($vaiTro !== ''): ?>
                        <small><?php echo htmlspecialchars($vaiTro, ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
                </div>
                <a class="logout-link" href="<?php echo BASE_URL; ?>/dang-xuat">Đăng xuất</a>
            </div>
        </aside>

        <main class="main-area">
            <h2 id="pageTitle" style="display:none">Trang chủ nhân viên</h2>

            <section class="staff-section active" id="section-home">
                <div class="welcome-panel">
                    <div>
                        <p class="eyebrow">Xin chào</p>
                        <h3><?php echo htmlspecialchars($tenNhanVien, ENT_QUOTES, 'UTF-8'); ?> &#128075;</h3>
                        <p>Chào mừng trở lại. Hôm nay là <?php echo date('d/m/Y'); ?>.</p>
                    </div>
                </div>

                <?php require dirname(__FILE__) . '/partials/_thong-ke.php'; ?>

                <div class="home-grid">
                    <button type="button" class="feature-card" onclick="StaffTabs.show('dat-ban')">
                        <div class="feature-icon">&#129681;</div>
                        <strong>Quản lý bàn</strong>
                        <small>Xác nhận đặt bàn, cập nhật trạng thái bàn và xác nhận món theo bàn trong cùng một màn hình.</small>
                    </button>
                    <a class="feature-card" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem">
                        <div class="feature-icon">&#11088;</div>
                        <strong>Tích điểm</strong>
                        <small>Tra cứu và cộng điểm thành viên cho khách sau khi dùng bữa.</small>
                    </a>
                </div>
            </section>

            <?php require dirname(__FILE__) . '/partials/_dat-ban.php'; ?>
        </main>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        var BASE_URL = '<?php echo BASE_URL; ?>';
        var RESTAURANT_CAPACITY = <?php echo defined('RESTAURANT_CAPACITY') ? (int)RESTAURANT_CAPACITY : 40; ?>;
    </script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/staff-dashboard.js?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/js/staff-dashboard.js'); ?>"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/staff-reservations.js?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/js/staff-reservations.js'); ?>"></script>
</body>

</html>
