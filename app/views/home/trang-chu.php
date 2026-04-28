<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME ?> — Tinh Tuý Ẩm Thực Chay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/trang-chu.css">
</head>

<body>
    <?php
    $nguoiDungHienTai = isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : null;
    $laKhachDangNhap = $nguoiDungHienTai
        && isset($nguoiDungHienTai['vai_tro'])
        && $nguoiDungHienTai['vai_tro'] === 'khach'
        && isset($nguoiDungHienTai['dang_hoat_dong'])
        && $nguoiDungHienTai['dang_hoat_dong'] == 1;
    $tenKhachHang = $laKhachDangNhap && isset($nguoiDungHienTai['ho_ten'])
        ? $nguoiDungHienTai['ho_ten']
        : '';
    ?>

    <nav>
        <div class="nav-left">
            <a class="nav-brand" href="#">
                <div class="nav-brand-icon">🌿</div>
                <span class="nav-brand-text"><?php echo APP_NAME ?></span>
            </a>
            <?php if ($laKhachDangNhap): ?>
                <span class="nav-user"><?php echo htmlspecialchars($tenKhachHang, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-links">
            <a href="<?php echo BASE_URL ?>/thuc-don">Thực Đơn</a>
            <a href="#price">Giá Buffet</a>
            <a href="#order">Gọi Món</a>
            <?php if ($laKhachDangNhap): ?>
                <a href="<?php echo BASE_URL ?>/dang-xuat">Đăng xuất</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL ?>/khach/dang-nhap">Đăng nhập</a>
                <a href="<?php echo BASE_URL ?>/khach/dang-ky">Đăng ký</a>
            <?php endif; ?>
            <a href="#" onclick="openModal(); return false;" class="nav-cta">Đặt Bàn</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-inner">
            <div class="hero-tag">✦ Thuần Chay · Hữu Cơ · Tươi Mỗi Ngày ✦</div>
            <h1>Bữa Tiệc<br><em>Thuần Chay</em><br>Tinh Tuý Nhất</h1>
            <p class="hero-desc">Hơn 20 món chay đặc sắc từ nguyên liệu hữu cơ tươi sạch, không màu nhân tạo, không bột ngọt — trải nghiệm ẩm thực lành mạnh đích thực giữa lòng thành phố.</p>
            <div class="hero-actions">
                <a class="btn-gold" href="#" onclick="openModal(); return false;">📅 Đặt Bàn Ngay</a>
                <a class="btn-ghost" href="#order">🍜 Gọi Món Tại Bàn</a>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-num">22+</div>
            <div class="stat-lbl">Món thuần chay</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">199K</div>
            <div class="stat-lbl">Đồng / người lớn</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">100%</div>
            <div class="stat-lbl">Nguyên liệu hữu cơ</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">Miễn Phí</div>
            <div class="stat-lbl">Trẻ em dưới 1m3</div>
        </div>
    </div>

    <!-- FEATURED MENU -->
    <div id="menu">
        <div class="section">
            <div class="section-head">
                <div>
                    <div class="section-eyebrow">Thực Đơn Nổi Bật</div>
                    <h2 class="section-title">Những Món Được<br>Yêu Thích Nhất</h2>
                </div>
                <a href="<?php echo BASE_URL ?>/thuc-don" class="see-all">Xem tất cả →</a>
            </div>
            <div class="menu-grid">
                <?php if (!empty($monNoiBat)) foreach ($monNoiBat as $item) { ?>
                    <div class="menu-card">
                        <img class="menu-card-img"
                            src="<?php echo htmlspecialchars($item['anh_url'] ? $item['anh_url'] : 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80') ?>"
                            alt="<?php echo htmlspecialchars($item['ten']) ?>"
                            loading="lazy">
                        <div class="menu-card-body">
                            <div class="menu-card-cat"><?php echo htmlspecialchars($item['danh_muc']) ?></div>
                            <div class="menu-card-name"><?php echo htmlspecialchars($item['ten']) ?></div>
                            <div class="menu-card-desc"><?php echo htmlspecialchars($item['mo_ta']) ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- PRICE -->
    <section class="price-section" id="price">
        <div class="price-inner">
            <div class="section-eyebrow">Giá Buffet</div>
            <h2 class="section-title">Trọn Gói — Ăn Không Giới Hạn</h2>
            <p class="section-sub">Một mức giá duy nhất — thưởng thức toàn bộ thực đơn thoải mái, không tính thêm bất kỳ khoản phụ phí nào.</p>
            <div class="price-cards">
                <div class="price-card featured">
                    <div class="price-amt"><?php echo number_format(PRICE_ADULT, 0, ',', '.') ?>đ</div>
                    <div class="price-unit">👤 Người lớn</div>
                    <div class="price-note">Trên 1m3</div>
                </div>
                <div class="price-card">
                    <div class="price-amt" style="font-size:2rem">Miễn Phí</div>
                    <div class="price-unit">👶 Trẻ em</div>
                    <div class="price-note">Dưới 1m3</div>
                </div>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section class="gallery-section">
        <div class="gallery-inner">
            <div class="section-eyebrow">Không Gian</div>
            <h2 class="section-title">Không Gian Xanh<br>Thanh Tịnh</h2>
            <div class="gallery-grid">
                <img class="g-main" src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80" alt="Nhà hàng" loading="lazy">
                <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80" alt="Món ăn" loading="lazy">
                <img src="https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80" alt="Món chay" loading="lazy">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80" alt="Salad" loading="lazy">
                <img src="https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=600&q=80" alt="Thực đơn" loading="lazy">
            </div>
        </div>
    </section>

    <!-- ORDER CTA -->
    <section class="order-section" id="order">
        <div class="order-inner">
            <div class="section-eyebrow">Gọi Món Tại Bàn</div>
            <h2 class="section-title">Nhập Mã Bàn<br>Để Bắt Đầu</h2>
            <p class="section-sub">Mã bàn được in trên biển hiệu tại bàn của bạn. Nhập mã để gọi món trực tiếp — không cần đợi phục vụ.</p>
            <div class="code-wrap">
                <input type="text" id="codeInput" placeholder="VD: BAN-A1" maxlength="10">
                <button onclick="goOrder()">Vào Gọi Món →</button>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-brand">🌿 <?php echo APP_NAME ?></div>
        <p style="margin-bottom:.5rem">Ẩm thực thuần chay tươi lành · Mở cửa 10:00–21:00 hàng ngày</p>
        <p><a href="<?php echo BASE_URL ?>/admin/login">Quản trị viên</a></p>
    </footer>

    <!-- RESERVATION MODAL -->
    <div class="overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-head">
                <h3 class="modal-title">🌿 Đặt Bàn</h3>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div id="reservationConfirmation" class="reservation-confirmation" style="display:none">
                <div class="confirmation-title">Đặt bàn thành công!</div>
                <div class="confirmation-text">Cảm ơn quý khách, thông tin đặt bàn đã được ghi nhận.</div>
                <div class="confirmation-row"><strong>Mã đặt bàn:</strong> <span id="confirmCode"></span></div>
                <div class="confirmation-row"><strong>Ngày:</strong> <span id="confirmDate"></span></div>
                <div class="confirmation-row"><strong>Giờ:</strong> <span id="confirmTime"></span></div>
                <div class="confirmation-row"><strong>Người lớn:</strong> <span id="confirmAdults"></span></div>
                <div class="confirmation-row"><strong>Trẻ em:</strong> <span id="confirmChildren"></span></div>
                <div class="confirmation-row"><strong>Tổng tiền:</strong> <span id="confirmTotal"></span></div>
                <div class="confirmation-row"><strong>Ghi chú:</strong> <span id="confirmNotes"></span></div>
                <div class="confirmation-note">Vui lòng lưu lại mã đặt bàn để gọi món hoặc tra cứu thông tin sau này.</div>
                <button type="button" class="btn-gold" style="width:100%;justify-content:center" onclick="closeModal()">Đóng</button>
            </div>
            <form id="resForm">
                <div class="form-row">
                    <div class="field">
                        <label>Họ tên *</label>
                        <input type="text" name="customer_name" placeholder="Nguyễn Văn A" required>
                    </div>
                    <div class="field">
                        <label>Điện thoại *</label>
                        <input type="tel" name="customer_phone" placeholder="0901234567" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Ngày *</label>
                        <input type="date" name="reservation_date" required>
                    </div>
                    <div class="field">
                        <label>Giờ *</label>
                        <select name="reservation_time" required></select>
                        <small style="display:block;margin-top:6px;color:#8a7d6b">Mỗi lượt buffet kéo dài 90 phút. Nhà hàng nhận tối đa 40 khách mỗi phiên.</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Người lớn *</label>
                        <input type="number" name="adult_count" value="2" min="1" max="40" onchange="calcTotal()">
                    </div>
                    <div class="field">
                        <label>Trẻ em (dưới 1m3)</label>
                        <input type="number" name="child_count" value="0" min="0" max="20">
                    </div>
                </div>
                <div class="field">
                    <label>Ghi chú</label>
                    <textarea name="notes" rows="2" placeholder="Yêu cầu đặc biệt, dị ứng thực phẩm..."></textarea>
                </div>
                <div class="price-est">
                    <strong id="totalDisplay"><?php echo number_format(PRICE_ADULT * 2, 0, ',', '.') ?>đ</strong>
                    <span>Tạm tính (người lớn × <?php echo number_format(PRICE_ADULT, 0, ',', '.') ?>đ)</span>
                </div>
                <button type="submit" class="btn-gold" style="width:100%;justify-content:center">✓ Xác Nhận Đặt Bàn</button>
            </form>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        var PRICE = <?php echo PRICE_ADULT ?>;
        var BASE = '<?php echo BASE_URL ?>';

        // Định dạng giờ (HH:MM)
        function padZero(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        function buildTimeSlots() {
            var slots = [];
            for (var minutes = 10 * 60; minutes <= 21 * 60 + 30; minutes += 30) {
                slots.push(padZero(Math.floor(minutes / 60)) + ':' + padZero(minutes % 60));
            }
            return slots;
        }

        function updateTimeConstraints() {
            var dateInput = document.querySelector('[name=reservation_date]');
            var timeInput = document.querySelector('[name=reservation_time]');
            var selectedDate = dateInput.value;
            var today = new Date().toISOString().split('T')[0];
            var now = new Date();
            var minMinutes = 0;
            if (selectedDate === today) {
                minMinutes = now.getHours() * 60 + now.getMinutes() + 30;
            }

            var html = '<option value="">Chọn khung giờ</option>';
            var firstAvailable = '';
            var slots = buildTimeSlots();
            for (var i = 0; i < slots.length; i++) {
                var parts = slots[i].split(':');
                var slotMinutes = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                var disabled = selectedDate === today && slotMinutes < minMinutes;
                if (!disabled && firstAvailable === '') firstAvailable = slots[i];
                html += '<option value="' + slots[i] + '"' + (disabled ? ' disabled' : '') + '>' + slots[i] + '</option>';
            }

            timeInput.innerHTML = html;
            timeInput.value = firstAvailable;
        }

        function openModal() {
            document.getElementById('modalOverlay').className = 'overlay open';
            resetReservationForm();
            var d = new Date().toISOString().split('T')[0];
            var di = document.querySelector('[name=reservation_date]');
            di.min = d;
            di.value = d;
            updateTimeConstraints();
            calcTotal();
        }

        function closeModal() {
            document.getElementById('modalOverlay').className = 'overlay';
        }

        function showReservationConfirmation(data, form) {
            var box = document.getElementById('reservationConfirmation');
            document.getElementById('confirmCode').textContent = data.ma_dat_ban || data.access_code || '';
            document.getElementById('confirmDate').textContent = form.querySelector('[name=reservation_date]').value;
            document.getElementById('confirmTime').textContent = form.querySelector('[name=reservation_time]').value;
            document.getElementById('confirmAdults').textContent = form.querySelector('[name=adult_count]').value;
            document.getElementById('confirmChildren').textContent = form.querySelector('[name=child_count]').value || '0';
            document.getElementById('confirmTotal').textContent = data.tong_tien ? data.tong_tien + 'đ' : '';
            document.getElementById('confirmNotes').textContent = form.querySelector('[name=notes]').value || '-';
            box.style.display = 'block';
            form.style.display = 'none';
        }

        function resetReservationForm() {
            var form = document.getElementById('resForm');
            var box = document.getElementById('reservationConfirmation');
            form.style.display = 'block';
            box.style.display = 'none';
            form.reset();
        }
        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Cập nhật giờ khi thay đổi ngày
        document.querySelector('[name=reservation_date]').addEventListener('change', updateTimeConstraints);

        function calcTotal() {
            var n = parseInt(document.querySelector('[name=adult_count]').value) || 0;
            document.getElementById('totalDisplay').textContent = (n * PRICE).toLocaleString('vi-VN') + 'đ';
        }

        function goOrder() {
            var c = document.getElementById('codeInput').value.trim().toUpperCase();
            if (!c) {
                toast('Vui lòng nhập mã bàn', 'err');
                return;
            }
            window.location.href = BASE + '/goi-mon?ma=' + encodeURIComponent(c);
        }
        document.getElementById('codeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') goOrder();
        });

        document.getElementById('resForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = e.target.querySelector('[type=submit]');
            btn.textContent = 'Đang gửi...';
            btn.disabled = true;
            var fd = new FormData(e.target);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', BASE + '/reservation/submit', true);
            xhr.onload = function() {
                try {
                    var d = JSON.parse(xhr.responseText);
                    if (d.success) {
                        showReservationConfirmation(d, e.target);
                        toast((d.thong_bao || 'Đặt bàn thành công') + ' - Mã gọi món: ' + (d.ma_dat_ban || ''), 'ok');
                    } else toast(d.thong_bao || 'Không thể đặt bàn, vui lòng thử lại', 'err');
                } catch (x) {
                    toast('Lỗi phản hồi server: ' + xhr.responseText.substring(0, 100), 'err');
                }
                btn.textContent = '✓ Xác Nhận Đặt Bàn';
                btn.disabled = false;
            };
            xhr.onerror = function() {
                toast('Kết nối thất bại, vui lòng kiểm tra mạng', 'err');
                btn.textContent = '✓ Xác Nhận Đặt Bàn';
                btn.disabled = false;
            };
            xhr.send(fd);
        });

        function toast(msg, type) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast show ' + (type || 'ok');
            setTimeout(function() {
                t.className = 'toast';
            }, 3800);
        }
    </script>
</body>

</html>
