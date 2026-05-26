<?php
// Ensure variables exist to avoid undefined index notices and escape outputs
$tenNhanVien = isset($tenNhanVien) ? $tenNhanVien : '';
$vaiTro = isset($vaiTro) ? $vaiTro : '';
?>

<header class="topbar">
    <div class="brand">
        <div class="brand-mark">&#127807;</div>
        <div>
            <h1>Màn hình nhân viên</h1>
            <span><?php echo htmlspecialchars(defined('APP_NAME') ? APP_NAME : '', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
    <div class="top-actions">
        <span class="user-pill">
            <?php echo htmlspecialchars($tenNhanVien, ENT_QUOTES, 'UTF-8'); ?>
            <?php if ($vaiTro !== ''): ?>
                (<?php echo htmlspecialchars($vaiTro, ENT_QUOTES, 'UTF-8'); ?>)
            <?php endif; ?>
        </span>
        <a class="top-link" href="<?php echo BASE_URL ?>/nhan-vien/tich-diem">Tích điểm</a>
        <?php if ($vaiTro === 'quanly'): ?>
            <a class="top-link" href="<?php echo BASE_URL ?>/quan-tri/tong-quan">Quản trị</a>
        <?php endif; ?>
        <a class="top-link" href="<?php echo BASE_URL ?>/dang-xuat">Đăng xuất</a>
    </div>
</header>