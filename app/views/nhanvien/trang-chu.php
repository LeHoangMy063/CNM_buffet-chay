<?php
$nhanVien    = isset($nhanVien) ? $nhanVien : (isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array());
$tenNhanVien = isset($nhanVien['ho_ten']) && $nhanVien['ho_ten'] !== ''
    ? $nhanVien['ho_ten']
    : (isset($nhanVien['ten_dang_nhap']) ? $nhanVien['ten_dang_nhap'] : 'Nhân viên');
$vaiTro = isset($nhanVien['vai_tro']) ? $nhanVien['vai_tro'] : '';
$laBep = $vaiTro === 'bep';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $laBep ? 'Màn hình bếp' : 'Trang nhân viên'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/nhanvien/dashboard.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/nhanvien/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/nhanvien/orders.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/nhanvien/orders.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/nhanvien/reservations.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/nhanvien/reservations.css'); ?>">
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark">&#127807;</div>
                <div>
                    <h1><?php echo APP_NAME; ?></h1>
                    <span><?php echo $laBep ? 'Khu vực bếp' : 'Khu vực nhân viên'; ?></span>
                </div>
            </div>

            <nav class="side-nav">
                <?php if ($laBep): ?>
                    <button type="button" class="side-link active" data-section="dat-ban" onclick="StaffTableManager.showPane('xac-nhan-mon')">
                        <span class="side-icon">&#127860;</span>
                        <span>Xem đơn món</span>
                    </button>
                <?php else: ?>

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
                    <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem" onclick="window.location.href='<?php echo BASE_URL; ?>/nhan-vien/tich-diem'; return false;">
                        <span class="side-icon">&#11088;</span>
                        <span>Tích điểm</span>
                    </a>
                <?php endif; ?>

                <?php if ($vaiTro === 'quanly'): ?>
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
            <h2 id="pageTitle" style="display:none"><?php echo $laBep ? 'Xem đơn món' : 'Trang chủ nhân viên'; ?></h2>

            <?php if (!$laBep): ?>
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
                        <a class="feature-card" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem" onclick="window.location.href='<?php echo BASE_URL; ?>/nhan-vien/tich-diem'; return false;">
                            <div class="feature-icon">&#11088;</div>
                            <strong>Tích điểm</strong>
                            <small>Tra cứu và cộng điểm thành viên cho khách sau khi dùng bữa.</small>
                        </a>
                    </div>
                </section>
            <?php endif; ?>

            <?php require dirname(__FILE__) . '/partials/_dat-ban.php'; ?>
        </main>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        var BASE_URL = '<?php echo BASE_URL; ?>';
        var RESTAURANT_CAPACITY = <?php echo defined('RESTAURANT_CAPACITY') ? (int)RESTAURANT_CAPACITY : 40; ?>;
        var PRICE_ADULT = <?php echo defined('PRICE_ADULT') ? (int)PRICE_ADULT : 199000; ?>;
        var PRICE_CHILD = <?php echo defined('PRICE_CHILD') ? (int)PRICE_CHILD : 0; ?>;
        var STAFF_ROLE = '<?php echo htmlspecialchars($vaiTro, ENT_QUOTES, 'UTF-8'); ?>';
        var STAFF_DEFAULT_PANE = '<?php echo $laBep ? 'xac-nhan-mon' : 'xac-nhan-dat-ban'; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/nhanvien-dashboard.js?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/js/nhanvien-dashboard.js'); ?>"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/nhanvien-reservations.js?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/js/nhanvien-reservations.js'); ?>"></script>
</body>

</html>