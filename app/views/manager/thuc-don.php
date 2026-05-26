<?php
$nguoiDung = isset($nguoiDung) ? $nguoiDung : array();
$tenQuanLy = isset($nguoiDung['ho_ten']) && $nguoiDung['ho_ten'] !== ''
    ? $nguoiDung['ho_ten']
    : (isset($nguoiDung['ten_dang_nhap']) ? $nguoiDung['ten_dang_nhap'] : 'Quản lý');
$vaiTroQuanLy = isset($nguoiDung['vai_tro']) ? $nguoiDung['vai_tro'] : '';
$danhSachMon = isset($danhSachMon) ? $danhSachMon : array();

$tongMon = count($danhSachMon);
$tongConMon = 0;
$tongNoiBat = 0;
foreach ($danhSachMon as $monThongKe) {
    if (!empty($monThongKe['con_mon'])) {
        $tongConMon++;
    }
    if (!empty($monThongKe['noi_bat'])) {
        $tongNoiBat++;
    }
}

$jsonMon = json_encode($danhSachMon);
$managerViewPath = dirname(__FILE__);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Quản lý thực đơn - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/manager/thuc-don.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark">&#127807;</div>
            <div>
                <h1><?php echo APP_NAME; ?></h1>
                <span>Khu vực quản lý</span>
            </div>
        </div>

        <nav class="nav">
            <button class="nav-parent side-link active open" type="button" onclick="toggleMenu('menu-thuc-don', this)">
                <span class="side-icon">&#127858;</span>
                <span>Quản lý thực đơn</span>
                <span class="chevron"></span>
            </button>
            <div class="nav-sub open" id="menu-thuc-don">
                <button class="nav-action active" type="button" data-panel="panel-view" onclick="showPanel('panel-view')">Xem món</button>
                <button class="nav-action" type="button" data-panel="panel-add" onclick="showPanel('panel-add')">Thêm món</button>
                <button class="nav-action" type="button" data-panel="panel-edit" onclick="showPanel('panel-edit')">Sửa món</button>
                <button class="nav-action" type="button" data-panel="panel-delete" onclick="showPanel('panel-delete')">Xóa món</button>
            </div>

            <div class="nav-section">Chức năng khác</div>
            <a class="side-link disabled" href="#" onclick="toast('Chức năng báo cáo doanh thu sẽ làm sau');return false;">
                <span class="side-icon">&#128200;</span>
                <span>Báo cáo doanh thu</span>
            </a>
            <a class="side-link disabled" href="#" onclick="toast('Chức năng quản lý nhân viên sẽ làm sau');return false;">
                <span class="side-icon">&#128101;</span>
                <span>Quản lý nhân viên</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <span>Đang đăng nhập</span>
                <strong><?php echo htmlspecialchars($tenQuanLy, ENT_QUOTES, 'UTF-8'); ?></strong>
                <small><?php echo htmlspecialchars($vaiTroQuanLy, ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
            <a class="logout-link" href="<?php echo BASE_URL; ?>/dang-xuat">Đăng xuất</a>
        </div>
    </aside>

    <main class="main">
        <div class="welcome-panel">
            <div>
                <p class="eyebrow">Quản lý</p>
                <h2>Quản lý thực đơn</h2>
                <p>Chia riêng các thao tác xem món, thêm món, sửa món và xóa món.</p>
            </div>
        </div>

        <?php include $managerViewPath . '/thuc-don/_stats.php'; ?>
        <?php include $managerViewPath . '/thuc-don/xem-mon.php'; ?>
        <?php include $managerViewPath . '/thuc-don/them-mon.php'; ?>
        <?php include $managerViewPath . '/thuc-don/sua-mon.php'; ?>
        <?php include $managerViewPath . '/thuc-don/xoa-mon.php'; ?>
    </main>
</div>

<datalist id="categoryOptions"></datalist>
<div class="toast" id="toast"></div>

<script>
var BASE_URL = '<?php echo BASE_URL; ?>';
var ITEMS = <?php echo $jsonMon ? $jsonMon : '[]'; ?>;
</script>
<script src="<?php echo BASE_URL; ?>/public/assets/js/manager-thuc-don.js"></script>
</body>
</html>
