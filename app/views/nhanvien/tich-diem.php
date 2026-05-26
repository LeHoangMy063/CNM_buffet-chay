<?php
$nhanVien    = isset($nhanVien) ? $nhanVien : (isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array());
$tenNhanVien = isset($nhanVien['ho_ten']) && $nhanVien['ho_ten'] !== ''
    ? $nhanVien['ho_ten']
    : (isset($nhanVien['ten_dang_nhap']) ? $nhanVien['ten_dang_nhap'] : 'Nhân viên');
$vaiTro = isset($nhanVien['vai_tro']) ? $nhanVien['vai_tro'] : '';
$ngayTraCuu = isset($ngay) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay) ? $ngay : date('Y-m-d');
$hoaDonTichDiem = isset($hoaDonTichDiem) && is_array($hoaDonTichDiem) ? $hoaDonTichDiem : null;
$tongTienHoaDon = $hoaDonTichDiem && isset($hoaDonTichDiem['tong_tien']) ? (int)$hoaDonTichDiem['tong_tien'] : 0;
$diemHoaDon = $hoaDonTichDiem && isset($hoaDonTichDiem['diem_quy_doi']) ? (int)$hoaDonTichDiem['diem_quy_doi'] : 0;
$soHoaDon = $hoaDonTichDiem && isset($hoaDonTichDiem['so_hoa_don']) ? (int)$hoaDonTichDiem['so_hoa_don'] : 0;
$hoaDonChuaSdt = isset($hoaDonChuaSdt) && is_array($hoaDonChuaSdt) ? $hoaDonChuaSdt : array();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tích Điểm - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/nhanvien/dashboard.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../../public/assets/css/nhanvien/dashboard.css'); ?>">
    <style>
        .td-input {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            background: #fff;
            color: var(--text);
            font: inherit;
            font-size: 13px;
            transition: border-color .16s, box-shadow .16s;
        }

        .td-input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(47, 107, 79, .12);
        }

        .td-input[readonly] {
            background: var(--surface-soft);
            color: var(--text-soft);
        }

        .td-label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-soft);
            font-size: 12px;
            font-weight: 700;
        }

        .td-field {
            margin-bottom: 12px;
        }

        .invoice-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid #b7e4c7;
            border-radius: var(--radius);
            padding: 14px 16px;
            background: var(--green-soft);
            margin-bottom: 18px;
        }

        .invoice-banner .amt {
            font-size: 20px;
            font-weight: 800;
            color: var(--green);
            white-space: nowrap;
        }

        .invoice-banner small {
            color: var(--text-soft);
            font-size: 12px;
        }

        .no-invoice-banner {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 12px 14px;
            background: var(--surface-soft);
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 18px;
        }

        .quick-add {
            display: flex;
            gap: 6px;
            margin-top: 8px;
        }

        .result-ok {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            background: var(--green-soft);
            color: var(--green);
            font-size: 13px;
            font-weight: 700;
        }

        .result-ok::before {
            content: "✓";
            font-weight: 800;
        }

        .td-divider {
            border: none;
            border-top: 1px solid var(--line);
            margin: 18px 0;
        }

        .customer-hero {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow-soft);
            margin-bottom: 16px;
        }

        .customer-avatar {
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--green-soft);
            font-size: 24px;
            flex-shrink: 0;
        }

        .customer-points {
            margin-left: auto;
            text-align: right;
        }

        .customer-points .big {
            font-size: 34px;
            font-weight: 800;
            color: var(--green);
            line-height: 1;
        }

        .customer-points small {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
    </style>
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
                <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tong-quan">
                    <span class="side-icon">&#8962;</span>
                    <span>Trang chủ</span>
                </a>
                <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tong-quan?tab=dat-ban">
                    <span class="side-icon">&#129681;</span>
                    <span>Quản lý bàn</span>
                </a>
                <a class="side-link active" href="<?php echo BASE_URL; ?>/nhan-vien/tich-diem">
                    <span class="side-icon">&#11088;</span>
                    <span>Tích điểm</span>
                </a>
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
            <section class="staff-section active">

                <div class="welcome-panel">
                    <div>
                        <p class="eyebrow">Tích điểm thành viên</p>
                        <h3>Tra cứu & cộng điểm &#11088;</h3>
                        <p>Tìm theo số điện thoại để tra cứu và cộng điểm.</p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:300px 1fr; gap:18px; align-items:start; margin-top:20px;">

                    <!-- CỘT TRÁI -->
                    <div class="panel">
                        <div class="panel-head">
                            <h2>&#128269; Tìm khách hàng</h2>
                        </div>
                        <div class="panel-body">
                            <form method="GET" action="" id="searchForm">
                                <div class="td-field">
                                    <label class="td-label">Số điện thoại</label>
                                    <input class="td-input" type="text" name="sdt" id="searchInput"
                                        value="<?php echo isset($sdt) ? htmlspecialchars($sdt, ENT_QUOTES, 'UTF-8') : '' ?>"
                                        placeholder="Nhập số điện thoại...">
                                </div>
                                <div class="td-field">
                                    <label class="td-label">Ngày hóa đơn</label>
                                    <input class="td-input" type="date" name="ngay" id="dateInput"
                                        value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div style="display:flex; gap:8px">
                                    <button type="submit" class="btn" style="flex:1">Tìm kiếm</button>
                                    <button type="button" id="clearSearch" class="btn secondary" style="flex:1">Xóa</button>
                                </div>
                            </form>
                            <hr class="td-divider">
                            <div style="color:var(--muted); font-size:12px; line-height:1.8">
                                <div>• Nhập SĐT rồi bấm Tìm kiếm</div>
                                <div>• Nhập số tiền bill, hệ thống tự quy đổi điểm</div>
                                <div>• Tỉ lệ: 1 điểm = 10.000₫</div>
                            </div>
                        </div>
                    </div>

                    <!-- CỘT PHẢI -->
                    <div>
                        <?php if (isset($sdt) && $sdt !== '' && empty($khach)): ?>
                            <div class="panel" style="margin-bottom:16px">
                                <div class="panel-head">
                                    <h2>&#10060; Không tìm thấy khách hàng</h2>
                                </div>
                                <div class="panel-body">
                                    <p style="color:var(--muted); margin-bottom:16px">
                                        Không có tài khoản với SĐT: <strong><?php echo htmlspecialchars($sdt, ENT_QUOTES, 'UTF-8') ?></strong>
                                    </p>
                                    <?php if ($tongTienHoaDon > 0): ?>
                                        <div class="invoice-banner">
                                            <div>
                                                <div style="font-weight:800; font-size:14px">Có hóa đơn chưa tích điểm</div>
                                                <small><?php echo $soHoaDon; ?> hóa đơn ngày <?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?></small>
                                            </div>
                                            <div style="text-align:right">
                                                <div class="amt"><?php echo number_format($tongTienHoaDon, 0, ',', '.'); ?>đ</div>
                                                <small>= <?php echo $diemHoaDon; ?> điểm</small>
                                            </div>
                                        </div>
                                        <form id="form-tich-diem" class="form-tich-diem">
                                            <input type="hidden" name="tai_khoan_id" value="0">
                                            <input type="hidden" name="bang_tich_diem" value="khach_tai_khoan">
                                            <input type="hidden" name="sdt" value="<?php echo htmlspecialchars(isset($sdt) ? $sdt : '', ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="tu_hoa_don" value="1">
                                            <input type="hidden" name="diem" id="diemInput" value="<?php echo $diemHoaDon; ?>">
                                            <input type="hidden" id="amountInput" value="<?php echo $tongTienHoaDon; ?>">
                                            <div style="display:flex; align-items:center; gap:10px">
                                                <button type="button" class="btn btnTichDiem">&#11088; Tạo tài khoản & cộng <?php echo $diemHoaDon; ?> điểm</button>
                                                <span id="resultMsg" class="result-ok" style="display:none"></span>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <form id="form-tich-diem" class="form-tich-diem">
                                            <input type="hidden" name="tai_khoan_id" value="0">
                                            <input type="hidden" name="bang_tich_diem" value="khach_tai_khoan">
                                            <input type="hidden" name="sdt" value="<?php echo htmlspecialchars(isset($sdt) ? $sdt : '', ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="tu_hoa_don" value="0">
                                            <div class="td-field">
                                                <label class="td-label">Tên khách</label>
                                                <input class="td-input" type="text" name="ten_khach" placeholder="Tên khách">
                                            </div>
                                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px">
                                                <div>
                                                    <label class="td-label">Số tiền bill</label>
                                                    <input class="td-input" type="number" id="amountInput" min="0" placeholder="VND">
                                                </div>
                                                <div>
                                                    <label class="td-label">Điểm tự quy đổi</label>
                                                    <input class="td-input" type="number" name="diem" id="diemInput" min="1" placeholder="Tự tính từ tiền" readonly>
                                                </div>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:10px">
                                                <button type="button" class="btn btnTichDiem">&#11088; Tạo tài khoản & cộng điểm</button>
                                                <span id="resultMsg" class="result-ok" style="display:none"></span>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif (empty($khach)): ?>
                            <div class="empty-state" style="padding:48px 20px">
                                <div style="font-size:36px; margin-bottom:12px">&#128100;</div>
                                <div style="font-weight:800; font-size:15px; margin-bottom:6px">Chưa có khách được chọn</div>
                                <div>Nhập SĐT và bấm Tìm kiếm để bắt đầu.</div>
                            </div>

                        <?php else: ?>
                            <div class="customer-hero">
                                <div class="customer-avatar">&#128100;</div>
                                <div>
                                    <div style="font-weight:800; font-size:16px"><?php echo htmlspecialchars($khach['ho_ten'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="color:var(--muted); font-size:13px; margin-top:2px">
                                        <?php echo htmlspecialchars($khach['so_dien_thoai'], ENT_QUOTES, 'UTF-8') ?>
                                        <span style="margin:0 6px">·</span>
                                        ID: <?php echo intval($khach['id']) ?>
                                    </div>
                                </div>
                                <div class="customer-points">
                                    <div class="big" id="currentPoints"><?php echo intval($khach['diem_tich_luy']) ?></div>
                                    <small>điểm hiện có</small>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-head">
                                    <h2>&#11088; Cộng điểm</h2>
                                </div>
                                <div class="panel-body">
                                    <?php if ($tongTienHoaDon > 0): ?>
                                        <div class="invoice-banner">
                                            <div>
                                                <div style="font-weight:800; font-size:14px">Hóa đơn ngày <?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?></div>
                                                <small><?php echo $soHoaDon; ?> hóa đơn chưa tích điểm</small>
                                            </div>
                                            <div style="text-align:right">
                                                <div class="amt"><?php echo number_format($tongTienHoaDon, 0, ',', '.'); ?>đ</div>
                                                <small>= <?php echo $diemHoaDon; ?> điểm</small>
                                            </div>
                                        </div>
                                    <?php elseif (isset($sdt) && $sdt !== ''): ?>
                                        <div class="no-invoice-banner">
                                            Chưa có hóa đơn chưa tích điểm cho ngày <?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>.
                                        </div>
                                    <?php endif; ?>

                                    <form id="form-tich-diem" class="form-tich-diem">
                                        <input type="hidden" name="tai_khoan_id" value="<?php echo intval($khach['id']) ?>">
                                        <input type="hidden" name="bang_tich_diem" value="<?php echo htmlspecialchars(isset($khach['_bang_tich_diem']) ? $khach['_bang_tich_diem'] : 'khach_tai_khoan', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="sdt" value="<?php echo htmlspecialchars(isset($sdt) ? $sdt : '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="tu_hoa_don" value="<?php echo $diemHoaDon > 0 ? '1' : '0'; ?>">

                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px">
                                            <div>
                                                <label class="td-label">Điểm tự quy đổi</label>
                                                <input class="td-input" type="number" name="diem" id="diemInput" min="1" placeholder="Số điểm"
                                                    value="<?php echo $diemHoaDon > 0 ? $diemHoaDon : ''; ?>"
                                                    readonly>
                                            </div>
                                            <div>
                                                <label class="td-label">Tính từ tiền (₫)</label>
                                                <input class="td-input" type="number" id="amountInput" min="0" placeholder="Số tiền VND"
                                                    value="<?php echo $tongTienHoaDon > 0 ? $tongTienHoaDon : ''; ?>"
                                                    <?php echo $tongTienHoaDon > 0 ? 'readonly' : ''; ?>>
                                                <div style="color:var(--muted); font-size:12px; margin-top:6px">1 điểm = 10.000₫</div>
                                            </div>
                                        </div>

                                        <div style="display:flex; align-items:center; gap:10px">
                                            <button type="button" id="btnTichDiem" class="btn btnTichDiem">&#11088; Cộng điểm</button>
                                            <button type="button" id="btnCancel" class="btn secondary">Huỷ</button>
                                            <span id="resultMsg" class="result-ok" style="display:none"></span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($hoaDonChuaSdt)): ?>
                            <div class="panel" style="margin-top:18px">
                                <div class="panel-head">
                                    <div>
                                        <h2>&#128221; Hóa đơn chưa có SĐT</h2>
                                        <span class="panel-sub">Nhập thông tin khách để tạo tài khoản và tích điểm</span>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <?php foreach ($hoaDonChuaSdt as $idx => $hd): ?>
                                        <?php
                                        $hdTongTien = isset($hd['tong_tien']) ? (int)$hd['tong_tien'] : 0;
                                        $hdDiem = isset($hd['diem_quy_doi']) ? (int)$hd['diem_quy_doi'] : (int)floor($hdTongTien / 10000);
                                        ?>
                                        <form class="form-tich-diem" style="<?php echo $idx > 0 ? 'border-top:1px solid var(--line); padding-top:16px; margin-top:16px' : '' ?>">
                                            <input type="hidden" name="tai_khoan_id" value="0">
                                            <input type="hidden" name="bang_tich_diem" value="khach_tai_khoan">
                                            <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="tu_hoa_don" value="1">
                                            <input type="hidden" name="hoa_don_id" value="<?php echo intval($hd['id']); ?>">
                                            <input type="hidden" name="diem" value="<?php echo $hdDiem; ?>">

                                            <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:12px">
                                                <div>
                                                    <div style="font-weight:800">Hóa đơn #<?php echo intval($hd['id']); ?></div>
                                                    <div style="color:var(--muted); font-size:12px">
                                                        <?php echo !empty($hd['so_ban']) ? 'Bàn ' . htmlspecialchars($hd['so_ban'], ENT_QUOTES, 'UTF-8') . ' · ' : ''; ?>
                                                        <?php echo htmlspecialchars(isset($hd['ngay_tao']) ? $hd['ngay_tao'] : '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                                <div style="text-align:right">
                                                    <div style="font-size:17px; font-weight:800; color:var(--green)"><?php echo number_format($hdTongTien, 0, ',', '.'); ?>đ</div>
                                                    <div style="color:var(--muted); font-size:12px">= <?php echo $hdDiem; ?> điểm</div>
                                                </div>
                                            </div>

                                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px">
                                                <div>
                                                    <label class="td-label">Tên khách</label>
                                                    <input class="td-input" type="text" name="ten_khach"
                                                        value="<?php echo htmlspecialchars(isset($hd['ten_khach']) ? $hd['ten_khach'] : '', ENT_QUOTES, 'UTF-8') ?>"
                                                        placeholder="Tên khách">
                                                </div>
                                                <div>
                                                    <label class="td-label">Số điện thoại</label>
                                                    <input class="td-input" type="text" name="sdt" placeholder="Số điện thoại">
                                                </div>
                                            </div>

                                            <div style="display:flex; align-items:center; gap:10px">
                                                <button type="button" class="btn btnTichDiem">&#11088; Tạo tài khoản & cộng điểm</button>
                                                <span class="result-ok resultMsg" style="display:none"></span>
                                            </div>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        (function() {
            var currentEl = document.getElementById('currentPoints');
            var RATE = 10000;

            function safeInt(v) {
                v = parseInt(v, 10);
                return isNaN(v) ? 0 : v;
            }

            function closestForm(el) {
                while (el && el.tagName !== 'FORM') el = el.parentNode;
                return el;
            }

            function formVal(form, name) {
                var el = form ? form.querySelector('[name="' + name + '"]') : null;
                return el ? el.value : '';
            }

            function syncPointsFromAmount(form) {
                var diemInp = form ? form.querySelector('[name="diem"]') : null;
                var amtInp = form ? form.querySelector('#amountInput') : null;
                if (!diemInp || !amtInp || formVal(form, 'tu_hoa_don') === '1') return safeInt(diemInp ? diemInp.value : 0);
                var diem = Math.floor(safeInt(amtInp.value) / RATE);
                diemInp.value = diem > 0 ? diem : '';
                return diem;
            }

            document.querySelectorAll('#amountInput').forEach(function(inp) {
                var form = closestForm(inp);
                syncPointsFromAmount(form);
                inp.addEventListener('input', function() {
                    syncPointsFromAmount(closestForm(this));
                });
            });

            document.querySelectorAll('.btnTichDiem').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var form = closestForm(this);
                    var diemInp = form ? form.querySelector('[name="diem"]') : null;
                    var amtInp = form ? form.querySelector('#amountInput') : null;
                    var resultMsg = form ? (form.querySelector('.resultMsg') || form.querySelector('#resultMsg')) : null;
                    var diem = safeInt(diemInp ? diemInp.value : 0);
                    var amt = safeInt(amtInp ? amtInp.value : 0);
                    var sdt = formVal(form, 'sdt');
                    var fromInvoice = formVal(form, 'tu_hoa_don') === '1';

                    if (!sdt) {
                        alert('Vui lòng nhập số điện thoại khách hàng');
                        return;
                    }
                    if (!fromInvoice && amt <= 0) {
                        alert('Vui lòng nhập số tiền bill');
                        return;
                    }
                    if (!fromInvoice) {
                        diem = Math.floor(amt / RATE);
                        if (diemInp) diemInp.value = diem > 0 ? diem : '';
                    }
                    if (diem <= 0) {
                        alert('Số tiền chưa đủ để quy đổi điểm');
                        return;
                    }
                    if (!confirm('Cộng ' + diem + ' điểm cho khách?')) return;

                    var fd = new FormData();
                    ['tai_khoan_id', 'bang_tich_diem', 'sdt', 'ngay', 'tu_hoa_don', 'hoa_don_id', 'ten_khach'].forEach(function(k) {
                        fd.append(k, formVal(form, k));
                    });
                    fd.append('diem', diem);
                    fd.append('so_tien', amt);

                    var self = this;
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo BASE_URL ?>/nhan-vien/tich-diem/xu-ly', true);
                    xhr.onload = function() {
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                if (resultMsg) {
                                    resultMsg.style.display = '';
                                    resultMsg.textContent = res.thong_bao || 'Cộng điểm thành công';
                                }
                                if (currentEl) currentEl.textContent = safeInt(currentEl.textContent) + diem;
                                if (formVal(form, 'tu_hoa_don') === '1') {
                                    self.disabled = true;
                                    self.textContent = '✓ Đã tích điểm';
                                } else {
                                    if (diemInp) diemInp.value = '';
                                    if (amtInp && !amtInp.readOnly) amtInp.value = '';
                                }
                            } else {
                                alert(res.thong_bao || 'Lỗi');
                            }
                        } catch (e) {
                            alert('Phản hồi không hợp lệ');
                        }
                    };
                    xhr.onerror = function() {
                        alert('Không thể kết nối tới máy chủ');
                    };
                    xhr.send(fd);
                });
            });

            var cancelBtn = document.getElementById('btnCancel');
            if (cancelBtn) cancelBtn.addEventListener('click', function() {
                var form = closestForm(this);
                var diemInp = form ? form.querySelector('[name="diem"]') : null;
                var amtInp = form ? form.querySelector('#amountInput') : null;
                var msg = form ? form.querySelector('#resultMsg') : null;
                if (diemInp) diemInp.value = '';
                if (amtInp && !amtInp.readOnly) amtInp.value = '';
                if (msg) {
                    msg.style.display = 'none';
                    msg.textContent = '';
                }
            });

            var clearBtn = document.getElementById('clearSearch');
            if (clearBtn) clearBtn.addEventListener('click', function() {
                var inp = document.getElementById('searchInput');
                if (inp) inp.value = '';
                var dateInp = document.getElementById('dateInput');
                if (dateInp) dateInp.value = '<?php echo date('Y-m-d'); ?>';
            });
        })();
    </script>
</body>

</html>
