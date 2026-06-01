<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ưu Đãi - <?php echo APP_NAME ?></title>
    <link rel="manifest" href="<?php echo BASE_URL; ?>/public/manifest.webmanifest">
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/base/trang-chu.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/base/uu-dai.css">
</head>

<body class="page-rewards">
    <?php
    $laKhachDangNhap = isset($laKhachDangNhap) ? $laKhachDangNhap : false;
    $tenKhachHang = isset($tenKhachHang) ? $tenKhachHang : '';
    $diemHienTai = isset($diemHienTai) ? (int)$diemHienTai : 0;
    ?>

    <nav>
        <div class="nav-left">
            <a class="nav-brand" href="<?php echo BASE_URL ?>">
                <img class="nav-brand-icon" src="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" alt="<?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="nav-brand-text"><?php echo APP_NAME ?></span>
            </a>
            <?php if ($laKhachDangNhap): ?>
                <span class="nav-user"><?php echo htmlspecialchars($tenKhachHang, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-links">
            <a href="<?php echo BASE_URL ?>/thuc-don">Thực Đơn</a>
            <a href="<?php echo BASE_URL ?>/uu-dai" class="active">Ưu Đãi</a>
            <a href="<?php echo BASE_URL ?>/dat-ban">Đặt Bàn</a>
            <a href="<?php echo BASE_URL ?>">Liên Hệ</a>
            <?php if ($laKhachDangNhap): ?>
                <a href="<?php echo BASE_URL ?>/dang-xuat">Đăng xuất</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL ?>/khach/dang-nhap">Đăng nhập</a>
                <a href="<?php echo BASE_URL ?>/khach/dang-ky">Đăng ký</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <section class="reward-hero">
            <div class="reward-hero-inner">
                <span class="eyebrow">Ưu Đãi Cho Thực Khách</span>
                <h1>Ưu đãi dành riêng cho thực khách An Lạc</h1>
                <p>Chương trình tích điểm thân thiện giúp bạn tích lũy điểm sau mỗi lần thanh toán. Đổi điểm lấy món đặc biệt, nhận quà chay chỉ dành cho khách hàng An Lạc.</p>
                <div class="hero-buttons">
                    <a class="btn btn-primary" href="<?php echo BASE_URL ?>/dat-ban">Đặt bàn ngay</a>
                    <a class="btn btn-outline" href="<?php echo BASE_URL ?>/thuc-don">Xem thực đơn</a>
                </div>
            </div>
        </section>

        <section class="reward-summary">
            <div class="container">
                <?php if ($laKhachDangNhap): ?>
                    <div class="account-box">
                        <div>
                            <div class="account-label">Xin chào, <?php echo htmlspecialchars($tenKhachHang, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="account-points"><strong><?php echo number_format($diemHienTai, 0, ',', '.') ?></strong> điểm hiện có</div>
                        </div>
                        <div class="account-note">Điểm sẽ được cập nhật ngay khi hóa đơn thanh toán thành công.</div>
                    </div>
                <?php else: ?>
                    <div class="account-box empty">
                        <div>
                            <div class="account-label">Đăng nhập để xem số điểm hiện có</div>
                            <div class="account-points">Đổi điểm món đặc biệt, nhận ưu đãi thêm mỗi lần dùng bữa.</div>
                        </div>
                        <a class="btn btn-primary" href="<?php echo BASE_URL ?>/khach/dang-nhap">Đăng nhập ngay</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="reward-info">
            <div class="container">
                <h2>Chương trình tích điểm An Lạc</h2>
                <p>Với mỗi 10.000đ thanh toán thành công, bạn sẽ được cộng 1 điểm vào tài khoản. Điểm chỉ được tính khi hóa đơn đã thanh toán và không thể quy đổi ra tiền mặt.</p>
                <div class="reward-grid">
                    <div class="reward-card small">
                        <h3>1 điểm = 10.000đ</h3>
                        <p>Điểm được cộng ngay khi hóa đơn đã thanh toán thành công.</p>
                    </div>
                    <div class="reward-card small">
                        <h3>Đổi điểm lấy món đặc biệt</h3>
                        <p>Điểm dùng để đổi các món ưu đãi riêng, không xuất hiện trong thực đơn gọi món thông thường.</p>
                    </div>
                    <div class="reward-card small">
                        <h3>Dễ dàng và thân thiện</h3>
                        <p>Chỉ cần đăng nhập, đủ điểm là đổi ngay. Nếu chưa đủ, hệ thống sẽ cho biết bạn còn thiếu bao nhiêu điểm.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="reward-items">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Món đổi điểm</span>
                        <h2>Danh sách ưu đãi đặc biệt</h2>
                        <p>Những món chỉ dành cho khách hàng tích điểm, không xuất hiện trong menu gọi món thông thường.</p>
                    </div>
                </div>
                <div class="reward-list">
                    <?php foreach ($monDoiDiem as $mon) :
                        $trangThai = isset($mon['trang_thai']) ? $mon['trang_thai'] : 'available';
                        $coTheDoi = $trangThai === 'available' && $laKhachDangNhap && $diemHienTai >= (int)$mon['diem_can_doi'];
                        $canThieu = max(0, (int)$mon['diem_can_doi'] - $diemHienTai);
                        $label = 'Có thể đổi';
                        if ($trangThai === 'sold_out') {
                            $label = 'Tạm hết';
                        } elseif ($trangThai === 'coming_soon') {
                            $label = 'Sắp ra mắt';
                        }
                    ?>
                        <article class="reward-card">
                            <?php if (!empty($mon['hinh_anh'])) : ?>
                                <div class="reward-image" style="background-image:url('<?php echo htmlspecialchars($mon['hinh_anh'], ENT_QUOTES, 'UTF-8'); ?>');"></div>
                            <?php endif; ?>
                            <div class="reward-body">
                                <div class="reward-badge">Chỉ đổi bằng điểm</div>
                                <div class="reward-title"><?php echo htmlspecialchars($mon['ten_mon'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="reward-meta"><strong><?php echo number_format($mon['diem_can_doi'], 0, ',', '.') ?> điểm</strong></div>
                                <p><?php echo htmlspecialchars($mon['mo_ta'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="reward-footer">
                                    <span class="reward-status <?php echo $trangThai; ?>"><?php echo $label; ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="reward-terms">
            <div class="container">
                <h2>Điều kiện áp dụng</h2>
                <ul>
                    <li>Điểm chỉ được cộng sau khi hóa đơn đã thanh toán thành công.</li>
                    <li>Món đổi điểm không quy đổi thành tiền mặt.</li>
                    <li>Món đổi điểm là món ưu đãi đặc biệt, không nằm trong menu gọi món thông thường.</li>
                    <li>Mỗi lần đổi điểm chỉ áp dụng cho một món hoặc một combo theo quy định nhà hàng.</li>
                    <li>Khách cần đăng nhập để sử dụng điểm.</li>
                    <li>Nhà hàng có thể thay đổi danh sách món đổi điểm theo từng thời điểm.</li>
                </ul>
            </div>
        </section>
    </main>

</body>

</html>