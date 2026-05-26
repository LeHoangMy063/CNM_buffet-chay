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
</head>

<body>
    <div class="app-shell">

        <!-- SIDEBAR -->
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
                <a class="side-link" href="<?php echo BASE_URL; ?>/nhan-vien/tong-quan">
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

        <!-- MAIN -->
        <main class="main-area">
            <section class="staff-section active">

                <div class="welcome-panel" style="margin-bottom:24px">
                    <div>
                        <p class="eyebrow">Tích điểm</p>
                        <h3>Tra cứu & cộng điểm khách hàng &#11088;</h3>
                        <p>Tìm theo số điện thoại để tra cứu và cộng điểm thành viên.</p>
                    </div>
                </div>

                <div class="home-grid" style="grid-template-columns: 320px 1fr; align-items: start;">

                    <!-- TÌM KIẾM -->
                    <div class="feature-card" style="cursor:default; display:block; text-align:left;">
                        <div class="feature-icon">&#128269;</div>
                        <strong>Tìm khách hàng</strong>
                        <form method="GET" action="" id="searchForm" style="margin-top:14px">
                            <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Số điện thoại</label>
                            <input type="text" name="sdt" id="searchInput"
                                value="<?php echo isset($sdt) ? htmlspecialchars($sdt, ENT_QUOTES, 'UTF-8') : '' ?>"
                                placeholder="Nhập số điện thoại..."
                                style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;margin-bottom:10px">
                            <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Ngày hóa đơn</label>
                            <input type="date" name="ngay" id="dateInput"
                                value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>"
                                style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;margin-bottom:10px">
                            <div style="display:flex;gap:8px">
                                <button type="submit" class="btn-admin btn-green" style="flex:1">Tìm kiếm</button>
                                <button type="button" id="clearSearch" class="btn-admin" style="flex:1">Xóa</button>
                            </div>
                        </form>
                        <hr style="margin:16px 0; border:none; border-top:1px solid #eee">
                        <small style="color:#888; line-height:1.7; display:block">
                            • Nhập SĐT rồi bấm Tìm kiếm<br>
                            • Dùng nút +5 / +10 / +20 để cộng nhanh<br>
                            • 1 điểm = 10.000₫
                        </small>
                    </div>

                    <!-- KẾT QUẢ -->
                    <div>
                        <?php if (isset($sdt) && $sdt !== '' && empty($khach)): ?>
                            <div class="feature-card" style="cursor:default; text-align:center; color:#a00; background:#fdecea;">
                                <div class="feature-icon">&#10060;</div>
                                <strong>Không tìm thấy</strong>
                                <small>Không có khách hàng với: <?php echo htmlspecialchars($sdt, ENT_QUOTES, 'UTF-8') ?></small>
                            </div>

                            <?php if ($tongTienHoaDon > 0): ?>
                                <div class="feature-card" style="cursor:default; display:block; margin-top:16px; text-align:left;">
                                    <div class="feature-icon">&#128179;</div>
                                    <strong>Có hóa đơn chưa tích điểm</strong>
                                    <p style="margin:10px 0 0; color:#555">
                                        Ngày <?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?> có <?php echo $soHoaDon; ?> hóa đơn,
                                        tổng tiền <strong><?php echo number_format($tongTienHoaDon, 0, ',', '.'); ?>đ</strong>,
                                        quy đổi <strong><?php echo $diemHoaDon; ?> điểm</strong>.
                                    </p>
                                    <form id="form-tich-diem" class="form-tich-diem" style="margin-top:12px">
                                        <input type="hidden" name="tai_khoan_id" value="0">
                                        <input type="hidden" name="bang_tich_diem" value="khach_tai_khoan">
                                        <input type="hidden" name="sdt" value="<?php echo htmlspecialchars(isset($sdt) ? $sdt : '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="tu_hoa_don" value="1">
                                        <input type="hidden" name="diem" id="diemInput" value="<?php echo $diemHoaDon; ?>">
                                        <input type="hidden" id="amountInput" value="<?php echo $tongTienHoaDon; ?>">
                                        <button type="button" id="btnTichDiem" class="btn-admin btn-green btnTichDiem">&#11088; Tạo tài khoản & cộng điểm</button>
                                        <span id="resultMsg" style="display:none; margin-left:8px; padding:7px 12px; border-radius:7px; background:#e8f5e9; color:#1a6b3c; font-size:14px"></span>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="feature-card" style="cursor:default; display:block; margin-top:16px; text-align:left;">
                                    <div class="feature-icon">&#128100;</div>
                                    <strong>Tạo tài khoản khách mới</strong>
                                    <small style="display:block; color:#666; margin-top:4px">Không thấy hóa đơn tự động. Nhập số tiền hoặc điểm từ bill để tạo tài khoản và cộng điểm.</small>

                                    <form id="form-tich-diem" class="form-tich-diem" style="margin-top:12px">
                                        <input type="hidden" name="tai_khoan_id" value="0">
                                        <input type="hidden" name="bang_tich_diem" value="khach_tai_khoan">
                                        <input type="hidden" name="sdt" value="<?php echo htmlspecialchars(isset($sdt) ? $sdt : '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="tu_hoa_don" value="0">

                                        <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Tên khách</label>
                                        <input type="text" name="ten_khach" placeholder="Tên khách"
                                            style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;margin-bottom:10px">

                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px">
                                            <div>
                                                <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Số tiền bill</label>
                                                <input type="number" id="amountInput" min="0" placeholder="VND"
                                                    style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box">
                                            </div>
                                            <div>
                                                <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Điểm</label>
                                                <input type="number" name="diem" id="diemInput" min="1" placeholder="Điểm"
                                                    style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box">
                                            </div>
                                        </div>

                                        <button type="button" id="btnTichDiem" class="btn-admin btn-green btnTichDiem">&#11088; Tạo tài khoản & cộng điểm</button>
                                        <span id="resultMsg" style="display:none; margin-left:8px; padding:7px 12px; border-radius:7px; background:#e8f5e9; color:#1a6b3c; font-size:14px"></span>
                                    </form>
                                </div>
                            <?php endif; ?>

                        <?php elseif (empty($khach)): ?>
                            <div class="feature-card" style="cursor:default; text-align:center;">
                                <div class="feature-icon">&#128100;</div>
                                <strong>Chưa có khách được chọn</strong>
                                <small>Nhập SĐT và bấm Tìm kiếm để bắt đầu.</small>
                            </div>

                        <?php else: ?>
                            <div class="feature-card" style="cursor:default; display:block; margin-bottom:16px">
                                <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px">
                                    <div style="font-size:40px">&#128100;</div>
                                    <div style="flex:1">
                                        <strong style="font-size:17px"><?php echo htmlspecialchars($khach['ho_ten'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                        <small style="color:#666"><?php echo htmlspecialchars($khach['so_dien_thoai'], ENT_QUOTES, 'UTF-8') ?> &nbsp;•&nbsp; ID: <?php echo intval($khach['id']) ?></small>
                                    </div>
                                    <div style="text-align:right">
                                        <div id="currentPoints" style="font-size:32px; font-weight:800; color:var(--clr-green, #1a6b3c)"><?php echo intval($khach['diem_tich_luy']) ?></div>
                                        <small style="color:#888">điểm hiện có</small>
                                    </div>
                                </div>

                                <hr style="margin:0 0 16px; border:none; border-top:1px solid #eee">

                                <?php if ($tongTienHoaDon > 0): ?>
                                    <div style="border:1px solid #d8eadf; background:#f2fbf5; border-radius:8px; padding:12px 14px; margin-bottom:16px">
                                        <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start">
                                            <div>
                                                <strong style="font-size:15px">Hóa đơn ngày <?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?></strong><br>
                                                <small style="color:#666"><?php echo $soHoaDon; ?> hóa đơn chưa tích điểm</small>
                                            </div>
                                            <div style="text-align:right">
                                                <strong style="font-size:18px; color:var(--clr-green, #1a6b3c)"><?php echo number_format($tongTienHoaDon, 0, ',', '.'); ?>đ</strong><br>
                                                <small style="color:#666">= <?php echo $diemHoaDon; ?> điểm</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif (isset($sdt) && $sdt !== ''): ?>
                                    <div style="border:1px solid #eee; background:#fafafa; border-radius:8px; padding:12px 14px; margin-bottom:16px; color:#777">
                                        Chưa có hóa đơn chưa tích điểm cho ngày <?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>.
                                    </div>
                                <?php endif; ?>

                                <form id="form-tich-diem" class="form-tich-diem">
                                    <input type="hidden" name="tai_khoan_id" value="<?php echo intval($khach['id']) ?>">
                                    <input type="hidden" name="bang_tich_diem" value="<?php echo htmlspecialchars(isset($khach['_bang_tich_diem']) ? $khach['_bang_tich_diem'] : 'khach_tai_khoan', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="sdt" value="<?php echo htmlspecialchars(isset($sdt) ? $sdt : '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="tu_hoa_don" value="<?php echo $diemHoaDon > 0 ? '1' : '0'; ?>">

                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px">
                                        <div>
                                            <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Thêm điểm (số)</label>
                                            <input type="number" name="diem" id="diemInput" min="1" placeholder="Số điểm"
                                                value="<?php echo $diemHoaDon > 0 ? $diemHoaDon : ''; ?>"
                                                <?php echo $diemHoaDon > 0 ? 'readonly' : ''; ?>
                                                style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box">
                                            <div style="margin-top:8px; display:<?php echo $diemHoaDon > 0 ? 'none' : 'flex'; ?>; gap:6px">
                                                <button type="button" class="btn-admin" data-add="5">+5</button>
                                                <button type="button" class="btn-admin" data-add="10">+10</button>
                                                <button type="button" class="btn-admin" data-add="20">+20</button>
                                            </div>
                                        </div>
                                        <div>
                                            <label style="font-size:13px; color:#555; display:block; margin-bottom:5px">Tính từ tiền (₫)</label>
                                            <input type="number" id="amountInput" min="0" placeholder="Số tiền VND"
                                                value="<?php echo $tongTienHoaDon > 0 ? $tongTienHoaDon : ''; ?>"
                                                <?php echo $tongTienHoaDon > 0 ? 'readonly' : ''; ?>
                                                style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box">
                                            <small style="color:#888; display:block; margin-top:6px">Tỉ lệ: 1 điểm = 10.000₫</small>
                                        </div>
                                    </div>

                                    <div style="display:flex; align-items:center; gap:10px">
                                        <button type="button" id="btnTichDiem" class="btn-admin btn-green btnTichDiem">&#11088; Cộng điểm</button>
                                        <button type="button" id="btnCancel" class="btn-admin">Huỷ</button>
                                        <span id="resultMsg" style="display:none; padding:7px 12px; border-radius:7px; background:#e8f5e9; color:#1a6b3c; font-size:14px"></span>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($hoaDonChuaSdt)): ?>
                            <div class="feature-card" style="cursor:default; display:block; margin-top:16px; text-align:left;">
                                <div class="feature-icon">&#128221;</div>
                                <strong>Hóa đơn chưa có SĐT</strong>
                                <small style="display:block; color:#666; margin-top:4px">Nhập thông tin khách để tạo tài khoản và tích điểm ngay.</small>

                                <?php foreach ($hoaDonChuaSdt as $hd): ?>
                                    <?php
                                    $hdTongTien = isset($hd['tong_tien']) ? (int)$hd['tong_tien'] : 0;
                                    $hdDiem = isset($hd['diem_quy_doi']) ? (int)$hd['diem_quy_doi'] : (int)floor($hdTongTien / 10000);
                                    ?>
                                    <form class="form-tich-diem" style="border-top:1px solid #eee; margin-top:12px; padding-top:12px">
                                        <input type="hidden" name="tai_khoan_id" value="0">
                                        <input type="hidden" name="bang_tich_diem" value="khach_tai_khoan">
                                        <input type="hidden" name="ngay" value="<?php echo htmlspecialchars($ngayTraCuu, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="tu_hoa_don" value="1">
                                        <input type="hidden" name="hoa_don_id" value="<?php echo intval($hd['id']); ?>">
                                        <input type="hidden" name="diem" value="<?php echo $hdDiem; ?>">

                                        <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:10px">
                                            <div>
                                                <strong style="font-size:14px">Hóa đơn #<?php echo intval($hd['id']); ?></strong>
                                                <small style="display:block; color:#777">
                                                    <?php echo !empty($hd['so_ban']) ? 'Bàn ' . htmlspecialchars($hd['so_ban'], ENT_QUOTES, 'UTF-8') . ' · ' : ''; ?>
                                                    <?php echo htmlspecialchars(isset($hd['ngay_tao']) ? $hd['ngay_tao'] : '', ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </div>
                                            <div style="text-align:right">
                                                <strong style="color:var(--clr-green, #1a6b3c)"><?php echo number_format($hdTongTien, 0, ',', '.'); ?>đ</strong>
                                                <small style="display:block; color:#777">= <?php echo $hdDiem; ?> điểm</small>
                                            </div>
                                        </div>

                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px">
                                            <input type="text" name="ten_khach"
                                                value="<?php echo htmlspecialchars(isset($hd['ten_khach']) ? $hd['ten_khach'] : '', ENT_QUOTES, 'UTF-8') ?>"
                                                placeholder="Tên khách"
                                                style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box">
                                            <input type="text" name="sdt"
                                                placeholder="Số điện thoại"
                                                style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box">
                                        </div>

                                        <button type="button" class="btn-admin btn-green btnTichDiem">&#11088; Tạo tài khoản & cộng điểm</button>
                                        <span class="resultMsg" style="display:none; margin-left:8px; padding:7px 12px; border-radius:7px; background:#e8f5e9; color:#1a6b3c; font-size:14px"></span>
                                    </form>
                                <?php endforeach; ?>
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
                while (el && el.tagName !== 'FORM') {
                    el = el.parentNode;
                }
                return el;
            }

            function formValue(form, name) {
                var el = form ? form.querySelector('[name="' + name + '"]') : null;
                return el ? el.value : '';
            }

            var quicks = document.querySelectorAll('[data-add]');
            for (var i = 0; i < quicks.length; i++) {
                quicks[i].addEventListener('click', function() {
                    var form = closestForm(this);
                    var pointsInput = form ? form.querySelector('[name="diem"]') : null;
                    if (pointsInput) pointsInput.value = safeInt(pointsInput.value) + safeInt(this.getAttribute('data-add'));
                });
            }

            var buttons = document.querySelectorAll('.btnTichDiem');
            for (var b = 0; b < buttons.length; b++) {
                buttons[b].addEventListener('click', function() {
                var btn = this;
                var form = closestForm(btn);
                var pointsInput = form ? form.querySelector('[name="diem"]') : null;
                var amountInput = form ? form.querySelector('#amountInput') : null;
                var resultMsg = form ? (form.querySelector('.resultMsg') || form.querySelector('#resultMsg')) : null;
                var diem = safeInt(pointsInput ? pointsInput.value : 0);
                var amt = safeInt(amountInput ? amountInput.value : 0);
                var sdt = formValue(form, 'sdt');
                var fromInvoice = formValue(form, 'tu_hoa_don') === '1';
                if (sdt === '') {
                    alert('Vui lòng nhập số điện thoại khách hàng');
                    return;
                }
                if (diem <= 0 && amt <= 0) {
                    alert('Vui lòng nhập điểm hoặc số tiền');
                    return;
                }
                if (diem <= 0) diem = Math.floor(amt / RATE);
                if (diem <= 0) {
                    alert('Số điểm được tính là 0, không thể cộng');
                    return;
                }
                if (!confirm('Cộng ' + diem + ' điểm cho khách?')) return;

                var fd = new FormData();
                fd.append('tai_khoan_id', formValue(form, 'tai_khoan_id'));
                fd.append('bang_tich_diem', formValue(form, 'bang_tich_diem'));
                fd.append('sdt', sdt);
                fd.append('ngay', formValue(form, 'ngay'));
                fd.append('tu_hoa_don', formValue(form, 'tu_hoa_don'));
                fd.append('hoa_don_id', formValue(form, 'hoa_don_id'));
                fd.append('ten_khach', formValue(form, 'ten_khach'));
                fd.append('diem', diem);

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
                            if (fromInvoice) {
                                btn.disabled = true;
                                btn.textContent = 'Đã tích điểm';
                            } else {
                                if (pointsInput) pointsInput.value = '';
                                if (amountInput) amountInput.value = '';
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
            }

            var cancelBtn = document.getElementById('btnCancel');
            if (cancelBtn) cancelBtn.addEventListener('click', function() {
                var form = closestForm(cancelBtn);
                var pointsInput = form ? form.querySelector('[name="diem"]') : null;
                var amountInput = form ? form.querySelector('#amountInput') : null;
                var resultMsg = form ? (form.querySelector('.resultMsg') || form.querySelector('#resultMsg')) : null;
                if (pointsInput) pointsInput.value = '';
                if (amountInput) amountInput.value = '';
                if (resultMsg) {
                    resultMsg.style.display = 'none';
                    resultMsg.textContent = '';
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
