<?php

/** @var array $items */
/** @var string $tuKhoa */

$nguoiDung = isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : null;
$laKhach = $nguoiDung
    && isset($nguoiDung['vai_tro'])
    && $nguoiDung['vai_tro'] === 'khach'
    && isset($nguoiDung['dang_hoat_dong'])
    && $nguoiDung['dang_hoat_dong'] == 1;

function thucDonChuanHoaDanhMuc($danhMuc)
{
    $danhMuc = trim((string)$danhMuc);
    $key = function_exists('mb_strtolower')
        ? mb_strtolower($danhMuc, 'UTF-8')
        : strtolower($danhMuc);

    $map = array(
        'khai vi' => 'Khai vi',
        'khai vị' => 'Khai vi',
        'mon chinh' => 'Mon chinh',
        'món chính' => 'Mon chinh',
        'nuoc lau' => 'Nuoc lau',
        'nước lẩu' => 'Nuoc lau',
        'topping' => 'Topping',
        'rau' => 'Rau',
        'do uong' => 'Do uong',
        'đồ uống' => 'Do uong',
    );

    return isset($map[$key]) ? $map[$key] : $danhMuc;
}

function thucDonTenDanhMuc($danhMuc)
{
    $labels = array(
        'Khai vi' => 'Khai vị',
        'Mon chinh' => 'Món chính',
        'Nuoc lau' => 'Nước lẩu',
        'Topping' => 'Topping',
        'Rau' => 'Rau',
        'Do uong' => 'Đồ uống',
        'Khac' => 'Khác',
    );

    return isset($labels[$danhMuc]) ? $labels[$danhMuc] : $danhMuc;
}

// Nhóm món ăn theo danh mục
$theoDanhMuc = array();
if (!empty($items)) {
    foreach ($items as $mon) {
        $dm = isset($mon['danh_muc']) && $mon['danh_muc'] ? $mon['danh_muc'] : 'Khác';
        if (!isset($theoDanhMuc[$dm])) {
            $theoDanhMuc[$dm] = array();
        }
        $theoDanhMuc[$dm][] = $mon;
    }
}

$danhSachDanhMuc = array_keys($theoDanhMuc);
$tuKhoa = isset($tuKhoa) ? $tuKhoa : '';
$dangTimKiem = $tuKhoa !== '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực Đơn - <?php echo APP_NAME ?></title>
    <link rel="manifest" href="<?php echo BASE_URL; ?>/public/manifest.webmanifest">
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" type="image/svg+xml">
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
            padding: 3rem 3rem 4rem;
        }

        /* THANH TÌM KIẾM */
        .search-bar {
            display: flex;
            gap: .75rem;
            margin-bottom: 2rem;
        }

        .search-bar input {
            flex: 1;
            padding: .75rem 1.25rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            color: var(--ink);
            background: #fff;
            outline: none;
            transition: border-color .2s;
        }

        .search-bar input:focus {
            border-color: var(--sage);
        }

        .search-bar button {
            padding: .75rem 1.5rem;
            background: var(--sage);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            cursor: pointer;
            transition: background .2s;
        }

        .search-bar button:hover {
            background: var(--sage-d);
        }

        .btn-xoa {
            padding: .75rem 1.25rem;
            background: transparent;
            color: var(--muted);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: border-color .2s, color .2s;
        }

        .btn-xoa:hover {
            border-color: var(--sage);
            color: var(--sage);
        }

        .ket-qua-tim-kiem {
            font-size: .9rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .ket-qua-tim-kiem strong {
            color: var(--ink);
        }

        /* TABS DANH MỤC */
        html {
            scroll-behavior: smooth;
        }

        .danh-muc-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 2.5rem;
            position: sticky;
            top: 0;
            z-index: 99;
            background: #f9f7f4;
            padding: .75rem 0;
            border-bottom: 1px solid var(--border);
        }

        .danh-muc-tab {
            padding: .5rem 1.25rem;
            border-radius: 999px;
            border: 1.5px solid var(--border);
            background: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem;
            color: var(--ink);
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
        }

        .danh-muc-tab:hover,
        .danh-muc-tab.active {
            background: var(--sage);
            border-color: var(--sage);
            color: #fff;
        }

        .danh-muc-block {
            scroll-margin-top: 80px;
        }

        /* GRID MÓN ĂN */
        .danh-muc-block {
            margin-bottom: 3.5rem;
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

        .khong-co-ket-qua {
            text-align: center;
            color: var(--muted);
            padding: 4rem 0;
            font-size: 1rem;
        }

        .khong-co-ket-qua span {
            display: block;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .tro-ve {
            margin-bottom: 2rem;
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
                <img class="nav-brand-icon" src="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" alt="<?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?>">
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
            Tất cả món trong <?php echo number_format(PRICE_ADULT, 0, ',', '.') ?>đ/người —
            ăn không giới hạn, không phụ thu
        </p>
    </div>

    <div class="thuc-don-wrap">

        <div class="tro-ve">
            <a href="<?php echo BASE_URL ?>">Về trang chủ</a>
        </div>

        <!-- Ô TÌM KIẾM -->
        <form class="search-bar" method="GET" action="<?php echo BASE_URL ?>/thuc-don">
            <input
                type="text"
                name="tim"
                placeholder="Tìm kiếm món ăn..."
                value="<?php echo htmlspecialchars($tuKhoa) ?>"
                autocomplete="off">
            <button type="submit">Tìm kiếm</button>
            <?php if ($dangTimKiem): ?>
                <a class="btn-xoa" href="<?php echo BASE_URL ?>/thuc-don">✕ Xóa</a>
            <?php endif; ?>
        </form>

        <?php if ($dangTimKiem): ?>
            <div class="ket-qua-tim-kiem">
                Kết quả tìm kiếm cho: <strong>"<?php echo htmlspecialchars($tuKhoa) ?>"</strong>
                — tìm thấy <strong><?php echo count($items) ?></strong> món
            </div>
        <?php endif; ?>

        <?php if (empty($theoDanhMuc)): ?>
            <div class="khong-co-ket-qua">
                <span>🥗</span>
                <?php if ($dangTimKiem): ?>
                    Không tìm thấy món ăn phù hợp với "<strong><?php echo htmlspecialchars($tuKhoa) ?></strong>".<br><br>
                    <a href="<?php echo BASE_URL ?>/thuc-don" style="color:var(--sage)">Xem toàn bộ thực đơn</a>
                <?php else: ?>
                    Chưa có món ăn nào. Vui lòng quay lại sau.
                <?php endif; ?>
            </div>
        <?php else: ?>

            <?php if (!$dangTimKiem && count($danhSachDanhMuc) > 1): ?>
                <!-- TABS DANH MỤC -->
                <div class="danh-muc-tabs" id="danhMucTabs">
                    <button class="danh-muc-tab active" onclick="cuonDenDanhMuc(0, this)">Tất cả</button>
                    <?php foreach ($danhSachDanhMuc as $i => $dm): ?>
                        <button
                            class="danh-muc-tab"
                            onclick="cuonDenDanhMuc(<?php echo intval($i) + 1 ?>, this)">
                            <?php echo htmlspecialchars($dm) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- DANH SÁCH MÓN THEO DANH MỤC -->
            <div id="danhSachMon">
                <?php $dmIdx = 1;
                foreach ($theoDanhMuc as $danhMuc => $danhSach): ?>
                    <div class="danh-muc-block" id="dm-block-<?php echo $dmIdx++ ?>">
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

            </div><!-- /#danhSachMon -->

        <?php endif; ?>

    </div>

    <footer>
        <div class="footer-brand"><?php echo APP_NAME ?></div>
        <p style="margin-bottom:.5rem">Ẩm thực thuần chay tươi lành - Mở cửa 10:00-21:00 hằng ngày</p>
        <p><a href="<?php echo BASE_URL ?>/dang-nhap">Quản trị viên</a></p>
    </footer>

    <script>
        function cuonDenDanhMuc(index, btn) {
            document.querySelectorAll('.danh-muc-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            if (index === 0) {
                const top = document.getElementById('danhSachMon').getBoundingClientRect().top + window.scrollY - 80;
                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });
                return;
            }

            const block = document.getElementById('dm-block-' + index);
            if (block) {
                const top = block.getBoundingClientRect().top + window.scrollY - 80;
                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });
            }
        }

        // Tự động highlight tab active khi scroll
        window.addEventListener('scroll', function() {
            const blocks = document.querySelectorAll('.danh-muc-block');
            const tabs = document.querySelectorAll('.danh-muc-tab');
            let activeIdx = 0;

            blocks.forEach((b, i) => {
                const top = b.getBoundingClientRect().top;
                if (top <= 120) activeIdx = i + 1;
            });

            tabs.forEach((t, i) => {
                t.classList.toggle('active', i === activeIdx);
            });
        });
    </script>

</body>

</html>
