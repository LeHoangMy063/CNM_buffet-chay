<?php
$nguoiDung = isset($nguoiDung) ? $nguoiDung : array();
$tenQuanLy = isset($nguoiDung['ho_ten']) && $nguoiDung['ho_ten'] !== ''
    ? $nguoiDung['ho_ten']
    : (isset($nguoiDung['ten_dang_nhap']) ? $nguoiDung['ten_dang_nhap'] : 'Quản lý');
$vaiTroQuanLy = isset($nguoiDung['vai_tro']) ? $nguoiDung['vai_tro'] : '';
$danhSachMon = isset($danhSachMon) ? $danhSachMon : array();
$danhSachNhanVien = isset($danhSachNhanVien) ? $danhSachNhanVien : array();
$bangDangMo = isset($bangDangMo) ? $bangDangMo : 'thuc-don';
$laTrangNhanVien = $bangDangMo === 'nhan-vien';
$laTrangBaoCao = $bangDangMo === 'bao-cao';

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

function managerText($value)
{
    if (!is_string($value)) {
        return $value;
    }

    if (!preg_match('//u', $value)) {
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        } else {
            $value = utf8_encode($value);
        }
    }

    if (!function_exists('iconv')) {
        return $value;
    }

    for ($i = 0; $i < 3 && preg_match('/Ãƒ|Ã‚|Ã¡Âº|Ã¡Â»|Ã„|Ã†/u', $value); $i++) {
        $bytes = @iconv('UTF-8', 'Windows-1252//IGNORE', $value);
        if ($bytes === false || $bytes === '' || !preg_match('//u', $bytes)) {
            break;
        }
        $value = $bytes;
    }

    return $value;
}

function managerCleanJsonData($value)
{
    if (is_array($value)) {
        $clean = array();
        foreach ($value as $key => $child) {
            $clean[$key] = managerCleanJsonData($child);
        }
        return $clean;
    }

    return is_string($value) ? managerText($value) : $value;
}

function managerCategoryText($value)
{
    $labels = array(
        'Khai vi' => 'Khai vị',
        'Mon chinh' => 'Món chính',
        'Nuoc lau' => 'Nước lẩu',
        'Do uong' => 'Đồ uống',
    );

    return isset($labels[$value]) ? $labels[$value] : managerText($value);
}

function managerCategoryOptions($selected = '')
{
    $categories = array(
        'Khai vi' => 'Khai vị',
        'Mon chinh' => 'Món chính',
        'Nuoc lau' => 'Nước lẩu',
        'Topping' => 'Topping',
        'Rau' => 'Rau',
        'Do uong' => 'Đồ uống',
    );

    $html = '';
    foreach ($categories as $value => $label) {
        $isSelected = (string)$selected === (string)$value ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $isSelected . '>';
        $html .= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
}

function managerRoleText($value)
{
    $labels = array(
        'quanly' => 'Quản lý',
        'nhanvien' => 'Nhân viên',
        'bep' => 'Bếp'
    );
    return isset($labels[$value]) ? $labels[$value] : managerText($value);
}

$jsonMon = json_encode(managerCleanJsonData($danhSachMon), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$jsonNhanVien = json_encode(managerCleanJsonData($danhSachNhanVien), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$managerViewPath = dirname(__FILE__);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $laTrangBaoCao ? 'Báo cáo doanh thu' : ($laTrangNhanVien ? 'Quản lý nhân viên' : 'Quản lý thực đơn'); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/quanly/thuc-don.css">
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
                <a class="side-link <?php echo $laTrangBaoCao ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/quan-ly/bao-cao">
                    <span class="side-icon">&#128200;</span>
                    <span>Báo cáo doanh thu</span>
                </a>
                <a class="nav-parent side-link <?php echo (!$laTrangNhanVien && !$laTrangBaoCao) ? 'active open' : ''; ?>" href="<?php echo BASE_URL; ?>/quan-ly/thuc-don">
                    <span class="side-icon">&#127858;</span>
                    <span>Quản lý thực đơn</span>
                </a>
                <a class="side-link <?php echo $laTrangNhanVien ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/quan-ly/nhan-vien">
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
                    <h2><?php echo $laTrangBaoCao ? 'Báo cáo doanh thu' : ($laTrangNhanVien ? 'Quản lý nhân viên' : 'Quản lý thực đơn'); ?></h2>
                </div>
            </div>

            <?php if ($laTrangBaoCao) : ?>
                <?php include $managerViewPath . '/bao-cao.php'; ?>
            <?php elseif ($laTrangNhanVien) : ?>
                <?php include $managerViewPath . '/nhan-vien/danh-sach.php'; ?>
            <?php else : ?>
                <?php include $managerViewPath . '/thuc-don/_stats.php'; ?>
                <?php include $managerViewPath . '/thuc-don/danh-sach.php'; ?>
                <?php include $managerViewPath . '/thuc-don/them-mon.php'; ?>
            <?php endif; ?>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        var BASE_URL = '<?php echo BASE_URL; ?>';
        var ITEM_COUNT = <?php echo (int)$tongMon; ?>;
        var ITEMS = <?php echo $jsonMon ? $jsonMon : '[]'; ?>;
        var STAFF_ITEMS = <?php echo $jsonNhanVien ? $jsonNhanVien : '[]'; ?>;
        var MANAGER_SECTION = '<?php echo $laTrangBaoCao ? 'bao-cao' : ($laTrangNhanVien ? 'nhan-vien' : 'thuc-don'); ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/quanly-thuc-don.js?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/js/quanly-thuc-don.js'); ?>"></script>
</body>

</html>