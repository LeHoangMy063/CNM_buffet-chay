<?php
$nhanVien = isset($nhanVien) ? $nhanVien : (isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array());
$tenNhanVien = isset($nhanVien['ho_ten']) && $nhanVien['ho_ten'] !== ''
    ? $nhanVien['ho_ten']
    : (isset($nhanVien['ten_dang_nhap']) ? $nhanVien['ten_dang_nhap'] : 'Nhân viên');
$vaiTro = isset($nhanVien['vai_tro']) ? $nhanVien['vai_tro'] : '';
$chiTiet = isset($chiTiet) && is_array($chiTiet) ? $chiTiet : null;
$khach = $chiTiet ? $chiTiet['khach'] : null;
$thongKe = $chiTiet ? $chiTiet['thongKe'] : array();
$lichSuDatBan = $chiTiet ? $chiTiet['lichSuDatBan'] : array();
$lichSuHoaDon = $chiTiet ? $chiTiet['lichSuHoaDon'] : array();

function khct_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function khct_ngay($value)
{
    if (!$value) {
        return '-';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : khct_e($value);
}

function khct_tien($value)
{
    return number_format((int)$value, 0, ',', '.') . 'đ';
}

function khct_trang_thai_dat_ban($value)
{
    $labels = array(
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan' => 'Đã xác nhận',
        'da_huy' => 'Đã hủy',
        'expired' => 'Quá hạn',
        'hoan_thanh' => 'Hoàn thành'
    );
    return isset($labels[$value]) ? $labels[$value] : $value;
}

function khct_trang_thai_hoa_don($value)
{
    $labels = array(
        'chua_thanh_toan' => 'Chưa thanh toán',
        'thanh_toan_mot_phan' => 'Thanh toán một phần',
        'da_thanh_toan' => 'Đã thanh toán',
        'da_huy' => 'Đã hủy'
    );
    return isset($labels[$value]) ? $labels[$value] : $value;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Chi tiết khách hàng - <?php echo APP_NAME; ?></title>
    <link rel="manifest" href="<?php echo BASE_URL; ?>/public/manifest.webmanifest">
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/nhanvien/dashboard.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../../public/assets/css/nhanvien/dashboard.css'); ?>">
    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 16px;
            align-items: start;
        }

        .info-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .info-item {
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            padding: 12px;
            background: var(--surface-soft);
        }

        .info-item span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info-item strong {
            display: block;
            margin-top: 3px;
            overflow-wrap: anywhere;
        }

        .points-box {
            border: 1px solid #b7e4c7;
            border-radius: var(--radius);
            padding: 18px;
            background: var(--green-soft);
        }

        .points-box strong {
            display: block;
            color: var(--green);
            font-size: 42px;
            line-height: 1;
            font-weight: 800;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .history-wrap {
            overflow-x: auto;
        }

        .history-table th,
        .history-table td {
            border-bottom: 1px solid var(--line);
            padding: 11px 12px;
            text-align: left;
            vertical-align: top;
        }

        .history-table th {
            background: var(--surface-soft);
            color: var(--text-soft);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .panel-gap {
            margin-top: 16px;
        }

        @media (max-width: 980px) {
            .detail-grid,
            .info-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img class="brand-mark" src="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" alt="<?php echo khct_e(APP_NAME); ?>">
                <div>
                    <h1><?php echo APP_NAME; ?></h1>
                    <span>Khu vực nhân viên</span>
                </div>
            </div>
            <nav class="side-nav">
                <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tong-quan?tab=dat-ban">
                    <span>Quản lý bàn</span>
                </a>
                <a class="side-link active" href="<?php echo BASE_URL; ?>/nhan-vien/khach-hang">
                    <span>Quản lý khách hàng</span>
                </a>
                <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem">
                    <span>Tích điểm</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-card">
                    <span>Đang đăng nhập</span>
                    <strong><?php echo khct_e($tenNhanVien); ?></strong>
                    <?php if ($vaiTro !== ''): ?>
                        <small><?php echo khct_e($vaiTro); ?></small>
                    <?php endif; ?>
                </div>
                <a class="logout-link" href="<?php echo BASE_URL; ?>/dang-xuat">Đăng xuất</a>
            </div>
        </aside>

        <main class="main-area">
            <section class="staff-section active">
                <div class="page-topbar">
                    <h2>Quản lý khách hàng</h2>
                    <a class="btn secondary" href="<?php echo BASE_URL; ?>/nhan-vien/khach-hang">Quay lại</a>
                </div>

                <?php if (!$chiTiet): ?>
                    <div class="panel">
                        <div class="panel-body">
                            <div class="empty-state">Không tìm thấy khách hàng <?php echo khct_e(isset($idKhachHang) ? $idKhachHang : ''); ?>.</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="detail-grid">
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Thông tin tài khoản khách hàng</h2>
                                <?php if ((int)$khach['dang_hoat_dong'] === 1): ?>
                                    <span class="badge ok">Đang hoạt động</span>
                                <?php else: ?>
                                    <span class="badge muted">Ngừng hoạt động</span>
                                <?php endif; ?>
                            </div>
                            <div class="panel-body">
                                <div class="info-list">
                                    <div class="info-item"><span>Mã khách hàng</span><strong><?php echo khct_e($khach['id_khach_tai_khoan']); ?></strong></div>
                                    <div class="info-item"><span>Họ tên</span><strong><?php echo khct_e($khach['ho_ten']); ?></strong></div>
                                    <div class="info-item"><span>Email</span><strong><?php echo !empty($khach['email']) ? khct_e($khach['email']) : '-'; ?></strong></div>
                                    <div class="info-item"><span>Ngày tạo</span><strong><?php echo khct_ngay($khach['ngay_tao']); ?></strong></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="points-box">
                                <span class="eyebrow">Tổng điểm tích lũy hiện tại</span>
                                <strong><?php echo number_format((int)$khach['diem_tich_luy'], 0, ',', '.'); ?></strong>
                            </div>
                            <div class="stats" style="grid-template-columns:repeat(3,minmax(0,1fr)); margin-top:16px;">
                                <div class="stat stat-orders"><strong><?php echo (int)$thongKe['so_lan_dat_ban']; ?></strong><span>Lần đặt bàn</span></div>
                                <div class="stat stat-empty"><strong><?php echo (int)$thongKe['so_lan_thanh_toan']; ?></strong><span>Lần thanh toán</span></div>
                                <div class="stat stat-busy"><strong><?php echo khct_tien($thongKe['tong_tien_da_thanh_toan']); ?></strong><span>Tổng đã thanh toán</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-gap">
                        <div class="panel-head">
                            <h2>Lịch sử đặt bàn gần đây</h2>
                        </div>
                        <?php if (empty($lichSuDatBan)): ?>
                            <div class="panel-body"><div class="empty-state">Chưa có lịch sử đặt bàn.</div></div>
                        <?php else: ?>
                            <div class="history-wrap">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Mã đặt bàn</th>
                                            <th>Ngày giờ</th>
                                            <th>Khách</th>
                                            <th>Số người</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lichSuDatBan as $datBan): ?>
                                            <tr>
                                                <td><strong><?php echo khct_e($datBan['ma_dat_ban']); ?></strong></td>
                                                <td><?php echo khct_e($datBan['ngay_dat']); ?> <?php echo khct_e(substr($datBan['gio_dat'], 0, 5)); ?></td>
                                                <td><?php echo khct_e($datBan['ten_khach']); ?></td>
                                                <td><?php echo (int)$datBan['so_nguoi_lon']; ?> người lớn, <?php echo (int)$datBan['so_tre_em']; ?> trẻ em</td>
                                                <td><span class="badge muted"><?php echo khct_e(khct_trang_thai_dat_ban($datBan['trang_thai'])); ?></span></td>
                                                <td><?php echo khct_ngay($datBan['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="panel panel-gap">
                        <div class="panel-head">
                            <h2>Lịch sử hóa đơn gần đây</h2>
                        </div>
                        <?php if (empty($lichSuHoaDon)): ?>
                            <div class="panel-body"><div class="empty-state">Chưa có lịch sử hóa đơn.</div></div>
                        <?php else: ?>
                            <div class="history-wrap">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Mã hóa đơn</th>
                                            <th>Phiên</th>
                                            <th>Tổng tiền</th>
                                            <th>Đã thanh toán</th>
                                            <th>Phương thức</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lichSuHoaDon as $hoaDon): ?>
                                            <?php
                                            $daThanhToan = (int)$hoaDon['tong_tien_da_thanh_toan'];
                                            if ($daThanhToan <= 0 && $hoaDon['trang_thai'] === 'da_thanh_toan') {
                                                $daThanhToan = (int)$hoaDon['thanh_tien'];
                                            }
                                            ?>
                                            <tr>
                                                <td><strong><?php echo khct_e($hoaDon['ma_hoa_don']); ?></strong></td>
                                                <td><?php echo khct_e($hoaDon['ma_phien']); ?><br><span class="panel-sub"><?php echo khct_ngay($hoaDon['gio_bat_dau']); ?></span></td>
                                                <td><?php echo khct_tien($hoaDon['thanh_tien']); ?></td>
                                                <td><?php echo khct_tien($daThanhToan); ?></td>
                                                <td><?php echo !empty($hoaDon['phuong_thuc_thanh_toan']) ? khct_e($hoaDon['phuong_thuc_thanh_toan']) : '-'; ?></td>
                                                <td><span class="badge muted"><?php echo khct_e(khct_trang_thai_hoa_don(!empty($hoaDon['trang_thai_thanh_toan']) ? $hoaDon['trang_thai_thanh_toan'] : $hoaDon['trang_thai'])); ?></span></td>
                                                <td><?php echo khct_ngay($hoaDon['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>

</html>
