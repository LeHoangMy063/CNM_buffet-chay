<?php
$nguoiDung = isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : null;
$laKhach = $nguoiDung
    && isset($nguoiDung['vai_tro'])
    && $nguoiDung['vai_tro'] === 'khach'
    && isset($nguoiDung['dang_hoat_dong'])
    && $nguoiDung['dang_hoat_dong'] == 1;

// Nhóm món ăn theo danh mục
$theoDanhMuc = array();
if (!empty($items)) {
    foreach ($items as $mon) {
        $dm = isset($mon['danh_muc']) ? $mon['danh_muc'] : '';
        if (!isset($theoDanhMuc[$dm])) {
            $theoDanhMuc[$dm] = array();
        }
        $theoDanhMuc[$dm][] = $mon;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực Đơn - <?php echo APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/base/trang-chu.css">
    <style>
        .page-header {
            background: var(--ink);
            padding: 7rem 3rem 4rem;
            text-align: center;
        }

        .page-header .section-eyebrow {
            color: var(--gold);
        }

        .page-header .section-title {
            color: #fff;
            margin-bottom: .5rem;
        }

        .page-header .section-sub {
            color: rgba(255, 255, 255, .55);
            margin: 0 auto;
        }

        .thuc-don-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 3rem;
        }

        .danh-muc-block {
            margin-bottom: 4rem;
        }

        .danh-muc-tieu-de {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            color: var(--ink);
            border-bottom: 2px solid var(--gold);
            padding-bottom: .5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .mon-an-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.25rem;
        }

        .mon-an-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            transition: transform .2s, box-shadow .2s;
        }

        .mon-an-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, .1);
        }

        .mon-an-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: var(--warm);
            display: block;
        }

        .mon-an-body {
            padding: 1rem 1.25rem 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .mon-an-ten {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .3rem;
        }

        .mon-an-mo-ta {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.6;
            flex: 1;
            margin-bottom: .75rem;
        }

        .mon-an-gia {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            color: var(--gold);
            font-weight: 600;
        }

        .badge-buffet {
            display: inline-block;
            background: var(--sage);
            color: #fff;
            font-size: .7rem;
            padding: .2rem .6rem;
            border-radius: 999px;
            margin-left: .4rem;
            vertical-align: middle;
            font-family: 'DM Sans', sans-serif;
        }

        .tro-ve {
            margin-bottom: 2.5rem;
        }

        .tro-ve a {
            color: var(--sage);
            text-decoration: none;
            font-size: .9rem;
            border-bottom: 1px solid var(--sage);
            padding-bottom: 2px;
            transition: color .2s;
        }

        .tro-ve a:hover {
            color: var(--sage-d);
        }
    </style>
</head>

<body>

    <nav>
        <div class="nav-left">
            <a class="nav-brand" href="<?php echo BASE_URL ?>">
                <div class="nav-brand-icon">&#127807;</div>
                <span class="nav-brand-text"><?php echo APP_NAME ?></span>
            </a>
            <?php if ($laKhach): ?>
                <span class="nav-user"><?php echo htmlspecialchars($nguoiDung['ho_ten'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-links">
            <a href="<?php echo BASE_URL ?>/thuc-don">Thực Đơn</a>
            <a href="<?php echo BASE_URL ?>/#price">Giá Buffet</a>
            <a href="<?php echo BASE_URL ?>/#order">Gọi Món</a>
            <?php if ($laKhach): ?>
                <a href="<?php echo BASE_URL ?>/dang-xuat">Đăng xuất</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL ?>/khach/dang-nhap">Đăng nhập</a>
                <a href="<?php echo BASE_URL ?>/khach/dang-ky">Đăng ký</a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL ?>/#" class="nav-cta">Đặt Bàn</a>
        </div>
    </nav>

    <div class="page-header">
        <div class="section-eyebrow">Buffet Thuần Chay</div>
        <h1 class="section-title">Toàn Bộ Thực Đơn</h1>
        <p class="section-sub">
            Tất cả món trong <?php echo number_format(PRICE_ADULT, 0, ',', '.') ?>đ/người -
            ăn không giới hạn, không phụ thu
        </p>
    </div>

    <div class="thuc-don-wrap">

        <div class="tro-ve">
            <a href="<?php echo BASE_URL ?>">&#8592; Về trang chủ</a>
        </div>

        <?php if (empty($theoDanhMuc)): ?>
            <p style="text-align:center;color:var(--muted);padding:3rem 0">
                Chưa có món ăn nào. Vui lòng quay lại sau.
            </p>
        <?php else: ?>
            <?php foreach ($theoDanhMuc as $danhMuc => $danhSach): ?>
                <div class="danh-muc-block">
                    <div class="danh-muc-tieu-de"><?php echo htmlspecialchars($danhMuc) ?></div>
                    <div class="mon-an-grid">
                        <?php foreach ($danhSach as $mon): ?>
                            <div class="mon-an-card">
                                <?php
                                $anh = isset($mon['anh_url']) && $mon['anh_url']
                                    ? $mon['anh_url']
                                    : 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80';
                                ?>
                                <img class="mon-an-img"
                                    src="<?php echo htmlspecialchars($anh) ?>"
                                    alt="<?php echo htmlspecialchars($mon['ten']) ?>"
                                    loading="lazy">
                                <div class="mon-an-body">
                                    <div class="mon-an-ten"><?php echo htmlspecialchars($mon['ten']) ?></div>
                                    <div class="mon-an-mo-ta">
                                        <?php echo htmlspecialchars(isset($mon['mo_ta']) && $mon['mo_ta'] ? $mon['mo_ta'] : '') ?>
                                    </div>
                                    <div class="mon-an-gia">
                                        Buffet <?php echo number_format(PRICE_ADULT, 0, ',', '.') ?>đ
                                        <span class="badge-buffet">Ăn thoải mái</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <footer>
        <div class="footer-brand">&#127807; <?php echo APP_NAME ?></div>
        <p style="margin-bottom:.5rem">Ẩm thực thuần chay tươi lành - Mở cửa 10:00-21:00 hằng ngày</p>
        <p><a href="<?php echo BASE_URL ?>/dang-nhap">Quản trị viên</a></p>
    </footer>

</body>

</html>
