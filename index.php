<?php
// Router chinh - PHP 5.3 compatible

session_start();

require_once dirname(__FILE__) . '/app/config.php';
require_once dirname(__FILE__) . '/app/quanly_config.php';

require_once dirname(__FILE__) . '/app/core/CosoDuLieu.php';
require_once dirname(__FILE__) . '/app/core/XacThuc.php';
require_once dirname(__FILE__) . '/app/middleware/KiemTraQuyenQuanLy.php';

require_once dirname(__FILE__) . '/app/models/MoHinhCo.php';
require_once dirname(__FILE__) . '/app/models/MoHinh.php';

require_once dirname(__FILE__) . '/app/controllers/BoieuKhienCo.php';
require_once dirname(__FILE__) . '/app/controllers/TrangChuController.php';
require_once dirname(__FILE__) . '/app/controllers/KhachHangController.php';
require_once dirname(__FILE__) . '/app/controllers/QuanLyController.php';
require_once dirname(__FILE__) . '/app/controllers/QuanTriController.php';
require_once dirname(__FILE__) . '/app/controllers/NhanVienController.php';
require_once dirname(__FILE__) . '/app/controllers/KhachHangNhanVienController.php';
require_once dirname(__FILE__) . '/app/controllers/XacThucController.php';
require_once dirname(__FILE__) . '/app/controllers/GoiYMonController.php';

// ===== PARSE URI =====
$requestUri = $_SERVER['REQUEST_URI'];
$basePath   = parse_url(BASE_URL, PHP_URL_PATH);

if ($basePath === null) {
    $basePath = '';
}

$pathInfo = parse_url($requestUri, PHP_URL_PATH);

if (strlen($basePath) > 0 && strpos($pathInfo, $basePath) === 0) {
    $pathInfo = substr($pathInfo, strlen($basePath));
}

$uri = '/' . trim($pathInfo, '/');

// ===== DINH NGHIA ROUTE =====
// Format: array(PHUONG_THUC, DUONG_DAN, TEN_CLASS, TEN_PHUONG_THUC)
$routes = array(

    // TRANG CHU
    array('GET',  '/',         'TrangChuController', 'index'),
    array('GET',  '/thuc-don', 'TrangChuController', 'thucDon'),

    // DANG NHAP / DANG KY / DANG XUAT
    array('GET',  '/dang-nhap',       'XacThucController', 'hienThiDangNhap'),
    array('POST', '/dang-nhap/xu-ly', 'XacThucController', 'xuLyDangNhap'),
    array('GET',  '/quanly/dang-nhap',     'XacThucController', 'hienThiDangNhap'),
    array('POST', '/loginSubmit',     'XacThucController', 'xuLyDangNhap'),
    array('GET',  '/dang-ky',         'XacThucController', 'hienThiDangKy'),
    array('POST', '/dang-ky/xu-ly',   'XacThucController', 'xuLyDangKy'),
    array('GET',  '/dang-xuat',       'XacThucController', 'dangXuat'),

    // KHACH HANG - GOI MON
    array('GET',  '/goi-mon',             'KhachHangController', 'trangGoiMon'),
    array('GET',  '/goi-y-mon',                'GoiYMonController', 'layGoiY'),
    array('POST', '/goi-y-mon/hanh-vi',        'GoiYMonController', 'ghiHanhVi'),
    array('GET',  '/goi-y-mon/cap-nhat-batch', 'GoiYMonController', 'capNhatBatch'),
    array('POST', '/goi-mon/dat',         'KhachHangController', 'datMon'),
    array('POST', '/goi-mon/huy',         'KhachHangController', 'huyMon'),
    array('GET',  '/goi-mon/danh-sach',   'KhachHangController', 'layDonHienTai'),
    array('POST', '/goi-mon/ket-thuc',    'KhachHangController', 'ketThucGoiMon'),
    array('POST', '/order/place',         'KhachHangController', 'datMon'),
    array('POST', '/order/cancel',        'KhachHangController', 'huyMon'),
    array('GET',  '/order/list',          'KhachHangController', 'layDonHienTai'),

    // KHACH HANG - DAT BAN
    array('GET',  '/dat-ban',       'KhachHangController', 'trangDatBan'),
    array('POST', '/dat-ban/xu-ly', 'KhachHangController', 'xuLyDatBan'),
    array('POST', '/reservation/submit', 'KhachHangController', 'xuLyDatBan'),

    // KHACH HANG - TAI KHOAN
    array('POST', '/tai-khoan/cap-nhat',    'KhachHangController', 'capNhatThongTin'),
    array('POST', '/tai-khoan/doi-mat-khau', 'KhachHangController', 'doiMatKhau'),
    array('GET',  '/khach/dang-nhap',       'XacThucController', 'hienThiDangNhapKhach'),
    array('POST', '/khach/dang-nhap/xu-ly', 'XacThucController', 'xuLyDangNhapKhach'),
    array('GET',  '/khach/dang-ky',         'XacThucController', 'hienThiDangKy'),
    array('POST', '/khach/dang-ky/xu-ly',   'XacThucController', 'xuLyDangKy'),
    array('GET',  '/khach/quen-mat-khau',   'XacThucController', 'hienThiQuenMatKhau'),
    array('POST', '/khach/quen-mat-khau/gui', 'XacThucController', 'guiLienKetDatLaiMatKhau'),
    array('GET',  '/khach/dat-lai-mat-khau', 'XacThucController', 'hienThiDatLaiMatKhau'),
    array('POST', '/khach/dat-lai-mat-khau/xu-ly', 'XacThucController', 'xuLyDatLaiMatKhau'),
    array('POST', '/danh-gia',              'KhachHangController', 'danhGia'),

    // QUAN LY
    array('GET',  '/quan-ly',              'QuanLyController', 'trangChu'),
    array('GET',  '/quan-ly/dashboard',    'QuanLyController', 'dashboard'),
    array('GET',  '/quan-ly/thuc-don',     'QuanLyController', 'thucDon'),
    array('POST', '/quan-ly/thuc-don/luu', 'QuanLyController', 'luuMon'),
    array('POST', '/quan-ly/thuc-don/xoa', 'QuanLyController', 'xoaMon'),
    array('GET',  '/quan-ly/nhan-vien',     'QuanLyController', 'nhanVien'),
    array('POST', '/quan-ly/nhan-vien/luu', 'QuanLyController', 'luuNhanVien'),
    array('POST', '/quan-ly/nhan-vien/xoa', 'QuanLyController', 'xoaNhanVien'),
    array('GET',  '/quan-ly/bao-cao',       'QuanLyController', 'baoCaoDoanhThu'),
    array('GET',  '/quan-ly/bao-cao/xuat',  'QuanLyController', 'xuatBaoCaoDoanhThu'),

    // QUAN TRI
    array('GET', '/quan-tri',           'QuanTriController', 'tongQuan'),
    array('GET', '/quan-tri/tong-quan', 'QuanTriController', 'tongQuan'),

    array('GET',  '/quan-tri/ban',                     'QuanTriController', 'quanLyBan'),
    array('POST', '/quan-tri/ban/cap-nhat-trang-thai', 'QuanTriController', 'capNhatTrangThaiBan'),

    array('GET',  '/quan-tri/dat-ban',                     'QuanTriController', 'quanLyDatBan'),
    array('POST', '/quan-tri/dat-ban/cap-nhat-trang-thai', 'QuanTriController', 'capNhatTrangThaiDatBan'),

    array('GET',  '/quan-tri/thuc-don',      'QuanTriController', 'quanLyThucDon'),
    array('POST', '/quan-tri/thuc-don/luu',  'QuanTriController', 'luuMonAn'),
    array('POST', '/quan-tri/thuc-don/xoa',  'QuanTriController', 'xoaMonAn'),

    array('GET',  '/quan-tri/don-mon',                     'QuanTriController', 'quanLyDonMon'),
    array('POST', '/quan-tri/don-mon/cap-nhat-trang-thai', 'QuanTriController', 'capNhatTrangThaiDon'),

    array('GET', '/quan-tri/bao-cao', 'QuanTriController', 'baoCaoDoanThu'),

    array('GET',  '/quan-tri/tai-khoan',                     'QuanTriController', 'quanLyTaiKhoan'),
    array('POST', '/quan-tri/tai-khoan/them-nhan-vien',      'QuanTriController', 'themNhanVien'),
    array('POST', '/quan-tri/tai-khoan/cap-nhat-trang-thai', 'QuanTriController', 'capNhatTrangThaiTaiKhoan'),

    // NHAN VIEN
    array('GET', '/nhan-vien',           'NhanVienController', 'tongQuan'),
    array('GET', '/nhan-vien/tong-quan', 'NhanVienController', 'tongQuan'),
    array('GET', '/nhan-vien/xem-don',   'NhanVienController', 'xemDon'),

    array('GET',  '/nhan-vien/danh-sach-ban', 'NhanVienController', 'layDanhSachBan'),
    array('POST', '/nhan-vien/cap-nhat-trang-thai-ban', 'NhanVienController', 'capNhatTrangThaiBan'),
    array('GET',  '/nhan-vien/don-theo-ban',  'NhanVienController', 'layDonTheoBan'),
    array('GET',  '/nhan-vien/don-mon/su-kien', 'NhanVienController', 'suKienDonMon'),
    array('GET',  '/nhan-vien/dat-ban/danh-sach', 'NhanVienController', 'layDanhSachDatBan'),
    array('GET',  '/nhan-vien/dat-ban/lich', 'NhanVienController', 'layLichDatBan'),
    array('POST', '/nhan-vien/xac-nhan-mon',  'NhanVienController', 'xacNhanMon'),
    array('POST', '/nhan-vien/xac-nhan-tat-ca', 'NhanVienController', 'xacNhanTatCa'),
    array('POST', '/nhan-vien/xac-nhan-ban',  'NhanVienController', 'xacNhanBanTrong'),

    array('POST', '/nhan-vien/cap-nhat-dat-ban', 'NhanVienController', 'capNhatDatBan'),
    array('POST', '/nhan-vien/dat-ban/gan-ban', 'NhanVienController', 'ganBanDatBanTheoSucChuaNhaHang'),
    array('POST', '/nhan-vien/dat-ban/xac-nhan-gan-ban', 'NhanVienController', 'xacNhanGanBan'),

    array('GET',  '/nhan-vien/khach-hang', 'KhachHangNhanVienController', 'index'),
    array('GET',  '/nhan-vien/khach-hang/chi-tiet/{id}', 'KhachHangNhanVienController', 'chiTiet'),

    array('GET',  '/nhan-vien/tich-diem',      'NhanVienController', 'tichDiem'),
    array('POST', '/nhan-vien/tich-diem/xu-ly', 'NhanVienController', 'xuLyTichDiem'),
);

// ===== DISPATCH =====
$phuongThuc = $_SERVER['REQUEST_METHOD'];

if ($phuongThuc === 'POST' && !empty($_POST['_phuong_thuc'])) {
    $phuongThuc = strtoupper($_POST['_phuong_thuc']);
}

$khop = false;

foreach ($routes as $route) {
    $routePhuongThuc = $route[0];
    $routeDuongDan   = $route[1];
    $tenClass        = $route[2];
    $tenPhuongThuc   = $route[3];
    $thamSoRoute      = array();

    if ($routePhuongThuc === $phuongThuc && $routeDuongDan === $uri) {
        $khop = true;
    } elseif ($routePhuongThuc === $phuongThuc && strpos($routeDuongDan, '{') !== false) {
        $pattern = preg_replace('/\\\{[^\/]+\\\}/', '([^/]+)', preg_quote($routeDuongDan, '#'));
        if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
            array_shift($matches);
            foreach ($matches as $match) {
                $thamSoRoute[] = rawurldecode($match);
            }
            $khop = true;
        }
    }

    if ($khop) {
        try {
            $ctrl = new $tenClass();
            call_user_func_array(array($ctrl, $tenPhuongThuc), $thamSoRoute);
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo '<h1>500 - Loi may chu</h1>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        }

        break;
    }
}

// ===== 404 =====
if (!$khop) {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>404 - Khong tim thay trang</h1>';
    echo '<p>Duong dan: <code>' . htmlspecialchars($uri) . '</code></p>';
}
if (!$khop) {
    die('404 - URI: [' . $uri . '] - Method: [' . $phuongThuc . ']');
}
