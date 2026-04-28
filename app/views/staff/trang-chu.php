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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/staff-dashboard.css">
</head>
<body>
<div class="app-shell">

    <!-- ========== SIDEBAR ========== -->
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
            <button type="button" class="side-link" data-section="goi-mon" onclick="StaffTabs.show('goi-mon')">
                <span class="side-icon">&#127860;</span>
                <span>Gọi món theo bàn</span>
            </button>
            <button type="button" class="side-link" data-section="dat-ban" onclick="StaffTabs.show('dat-ban')">
                <span class="side-icon">&#128197;</span>
                <span>Quản lý đặt bàn</span>
            </button>
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

    <!-- ========== MAIN ========== -->
    <main class="main-area">

        <div class="page-topbar">
            <div>
                <p class="eyebrow">Buffet Chay</p>
                <h2 id="pageTitle">Trang chủ nhân viên</h2>
            </div>
        </div>

        <!-- ---- TRANG CHỦ ---- -->
        <section class="staff-section active" id="section-home">
            <div class="welcome-panel">
                <div>
                    <p class="eyebrow">Xin chào</p>
                    <h3><?php echo htmlspecialchars($tenNhanVien, ENT_QUOTES, 'UTF-8'); ?> &#128075;</h3>
                    <p>Theo dõi bàn đang có đơn, xử lý món chờ phục vụ và cập nhật đặt bàn của khách trong cùng một màn hình.</p>
                </div>
                <div class="welcome-actions">
                    <button class="btn" type="button" onclick="StaffTabs.show('goi-mon')">&#127860; Xem đơn món</button>
                    <button class="btn secondary" type="button" onclick="StaffTabs.show('dat-ban')">&#128197; Xem đặt bàn</button>
                </div>
            </div>

            <?php require dirname(__FILE__) . '/partials/_thong-ke.php'; ?>

            <div class="home-grid">
                <button type="button" class="feature-card" onclick="StaffTabs.show('goi-mon')">
                    <div class="feature-icon">&#127860;</div>
                    <strong>Gọi món theo bàn</strong>
                    <small>Xem tất cả món đang chờ phục vụ, xác nhận từng món hoặc toàn bộ bàn nhanh chóng.</small>
                </button>
                <button type="button" class="feature-card" onclick="StaffTabs.show('dat-ban')">
                    <div class="feature-icon">&#128197;</div>
                    <strong>Quản lý đặt bàn</strong>
                    <small>Lọc, xác nhận, hủy hoặc hoàn thành đặt bàn. Gán bàn thủ công hoặc tự động.</small>
                </button>
                <a class="feature-card" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem">
                    <div class="feature-icon">&#11088;</div>
                    <strong>Tích điểm</strong>
                    <small>Tra cứu và cộng điểm thành viên cho khách sau khi dùng bữa.</small>
                </a>
            </div>
        </section>

        <!-- ---- GỌI MÓN THEO BÀN ---- -->
        <?php require dirname(__FILE__) . '/partials/_goi-mon.php'; ?>

        <!-- ---- QUẢN LÝ ĐẶT BÀN ---- -->
        <?php require dirname(__FILE__) . '/partials/_dat-ban.php'; ?>

    </main>
</div>

<div id="toast" class="toast"></div>

<script>
    var BASE_URL = '<?php echo BASE_URL; ?>';
    var RESTAURANT_CAPACITY = <?php echo defined('RESTAURANT_CAPACITY') ? (int)RESTAURANT_CAPACITY : 40; ?>;
</script>
<script src="<?php echo BASE_URL; ?>/public/assets/js/staff-dashboard.js"></script>
</body>
</html>
