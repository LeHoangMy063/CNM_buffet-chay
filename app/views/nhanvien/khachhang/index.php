<?php
$nhanVien = isset($nhanVien) ? $nhanVien : (isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array());
$tenNhanVien = isset($nhanVien['ho_ten']) && $nhanVien['ho_ten'] !== ''
    ? $nhanVien['ho_ten']
    : (isset($nhanVien['ten_dang_nhap']) ? $nhanVien['ten_dang_nhap'] : 'Nhân viên');
$vaiTro = isset($nhanVien['vai_tro']) ? $nhanVien['vai_tro'] : '';
$tuKhoa = isset($tuKhoa) ? $tuKhoa : '';
$trangThai = isset($trangThai) ? $trangThai : '';
$danhSachKhachHang = isset($danhSachKhachHang) && is_array($danhSachKhachHang) ? $danhSachKhachHang : array();

function khnv_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function khnv_ngay($value)
{
    if (!$value) {
        return '-';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : khnv_e($value);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Quản lý khách hàng - <?php echo APP_NAME; ?></title>
    <link rel="manifest" href="<?php echo BASE_URL; ?>/public/manifest.webmanifest">
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/nhanvien/dashboard.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../../public/assets/css/nhanvien/dashboard.css'); ?>">
    <style>
        .customer-toolbar {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) 190px auto auto;
            gap: 10px;
            align-items: center;
        }

        .customer-table-wrap {
            overflow-x: auto;
        }

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
        }

        .customer-table th,
        .customer-table td {
            border-bottom: 1px solid var(--line);
            padding: 12px 14px;
            text-align: left;
            vertical-align: middle;
        }

        .customer-table th {
            background: var(--surface-soft);
            color: var(--text-soft);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .point-pill {
            display: inline-flex;
            min-width: 66px;
            justify-content: center;
            border-radius: var(--radius-sm);
            padding: 6px 9px;
            background: var(--green-soft);
            color: var(--green);
            font-weight: 800;
        }

        .muted-text {
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .customer-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img class="brand-mark" src="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" alt="<?php echo khnv_e(APP_NAME); ?>">
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
                    <strong><?php echo khnv_e($tenNhanVien); ?></strong>
                    <?php if ($vaiTro !== ''): ?>
                        <small><?php echo khnv_e($vaiTro); ?></small>
                    <?php endif; ?>
                </div>
                <a class="logout-link" href="<?php echo BASE_URL; ?>/dang-xuat">Đăng xuất</a>
            </div>
        </aside>

        <main class="main-area">
            <section class="staff-section active">
                <div class="welcome-panel">
                    <div>
                        <p class="eyebrow">Khách hàng</p>
                        <h3>Quản lý khách hàng</h3>
                        <p>Xem tài khoản khách hàng, trạng thái hoạt động và điểm tích lũy.</p>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head panel-head-wrap">
                        <div>
                            <h2>Danh sách khách hàng</h2>
                            <p class="panel-sub"><?php echo count($danhSachKhachHang); ?> khách hàng phù hợp</p>
                        </div>
                    </div>
                    <div class="panel-body">
                        <form class="customer-toolbar" method="GET" action="<?php echo BASE_URL; ?>/nhan-vien/khach-hang">
                            <input class="search" type="text" name="tim" value="<?php echo khnv_e($tuKhoa); ?>" placeholder="Tìm theo mã khách hàng, họ tên hoặc email">
                            <select class="select" name="trang_thai">
                                <option value="" <?php echo $trangThai === '' ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="dang_hoat_dong" <?php echo $trangThai === 'dang_hoat_dong' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                <option value="ngung_hoat_dong" <?php echo $trangThai === 'ngung_hoat_dong' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                            </select>
                            <button class="btn" type="submit">Tìm kiếm</button>
                            <a class="btn secondary" href="<?php echo BASE_URL; ?>/nhan-vien/khach-hang">Xóa lọc</a>
                        </form>
                    </div>

                    <?php if (empty($danhSachKhachHang)): ?>
                        <div class="panel-body">
                            <div class="empty-state">Không tìm thấy khách hàng phù hợp.</div>
                        </div>
                    <?php else: ?>
                        <div class="customer-table-wrap">
                            <table class="customer-table">
                                <thead>
                                    <tr>
                                        <th>Mã khách hàng</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Điểm tích lũy</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachKhachHang as $khach): ?>
                                        <tr>
                                            <td><strong><?php echo khnv_e($khach['id_khach_tai_khoan']); ?></strong></td>
                                            <td><?php echo khnv_e($khach['ho_ten']); ?></td>
                                            <td class="muted-text"><?php echo !empty($khach['email']) ? khnv_e($khach['email']) : '-'; ?></td>
                                            <td><span class="point-pill"><?php echo number_format((int)$khach['diem_tich_luy'], 0, ',', '.'); ?></span></td>
                                            <td>
                                                <?php if ((int)$khach['dang_hoat_dong'] === 1): ?>
                                                    <span class="badge ok">Đang hoạt động</span>
                                                <?php else: ?>
                                                    <span class="badge muted">Ngừng hoạt động</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo khnv_ngay($khach['ngay_tao']); ?></td>
                                            <td>
                                                <a class="btn btn-sm" href="<?php echo BASE_URL; ?>/nhan-vien/khach-hang/chi-tiet/<?php echo rawurlencode($khach['id_khach_tai_khoan']); ?>">Xem chi tiết</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
