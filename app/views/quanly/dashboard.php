<?php
$tuNgay = isset($tuNgay) ? $tuNgay : date('Y-m-01');
$denNgay = isset($denNgay) ? $denNgay : date('Y-m-d');
$tongQuanDoanhThu = isset($tongQuanDoanhThu) ? $tongQuanDoanhThu : array();
$topMonBanChay = isset($topMonBanChay) ? $topMonBanChay : array();
$monCanDay = isset($monCanDay) ? $monCanDay : array();
$goiYMonAi = isset($goiYMonAi) ? $goiYMonAi : array();

if (!function_exists('managerMoney')) {
    function managerMoney($value)
    {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

$tongDoanhThu = isset($tongQuanDoanhThu['doanh_thu']) ? (float)$tongQuanDoanhThu['doanh_thu'] : 0;
$soPhien = isset($tongQuanDoanhThu['so_phien']) ? (int)$tongQuanDoanhThu['so_phien'] : 0;
$tongKhach = isset($tongQuanDoanhThu['tong_khach']) ? (int)$tongQuanDoanhThu['tong_khach'] : 0;
$khachMoiPhien = $soPhien > 0 ? round($tongKhach / $soPhien, 1) : 0;
?>

<section class="dashboard-filter">
    <form class="toolbar report-toolbar" method="GET" action="<?php echo BASE_URL; ?>/quan-ly/dashboard">
        <input class="input" type="date" name="tu_ngay" value="<?php echo htmlspecialchars($tuNgay, ENT_QUOTES, 'UTF-8'); ?>">
        <input class="input" type="date" name="den_ngay" value="<?php echo htmlspecialchars($denNgay, ENT_QUOTES, 'UTF-8'); ?>">
        <button class="btn" type="submit">Cập nhật</button>
    </form>
</section>

<section class="stats revenue-stats dashboard-stats">
    <div class="stat stat-visible">
        <span class="stat-icon">&#128176;</span>
        <strong><?php echo managerMoney($tongDoanhThu); ?></strong>
        <span>Tổng doanh thu</span>
    </div>
    <div class="stat">
        <span class="stat-icon">&#129534;</span>
        <strong><?php echo $soPhien; ?></strong>
        <span>Lượt thanh toán</span>
    </div>
    <div class="stat stat-featured">
        <span class="stat-icon">&#128101;</span>
        <strong><?php echo $tongKhach; ?></strong>
        <span>Lượt khách</span>
    </div>
    <div class="stat stat-accent">
        <span class="stat-icon">&#128200;</span>
        <strong><?php echo $khachMoiPhien; ?></strong>
        <span>Khách / phiên buffet</span>
    </div>
</section>

<section class="dashboard-grid">
    <div class="panel active chart-panel chart-panel-wide">
        <div class="panel-head">
            <div>
                <h3>Lượt khách buffet theo ngày</h3>
                <p>Theo dõi sức chứa và nhịp phục vụ trong khoảng đã chọn.</p>
            </div>
        </div>
        <div class="panel-body chart-body">
            <canvas id="revenueCanvas" width="980" height="320"></canvas>
        </div>
    </div>

    <div class="panel active chart-panel">
        <div class="panel-head">
            <div>
                <h3>Món được gọi nhiều</h3>
                <p>Những món tạo nhiều lượt ra món cho bếp.</p>
            </div>
        </div>
        <div class="panel-body chart-body">
            <canvas id="topDishCanvas" width="520" height="320"></canvas>
        </div>
    </div>

    <div class="panel active chart-panel">
        <div class="panel-head">
            <div>
                <h3>Mức tiêu thụ theo nhóm món</h3>
                <p>Nhóm nguyên liệu cần chuẩn bị nhiều hơn.</p>
            </div>
        </div>
        <div class="panel-body chart-body">
            <canvas id="categoryCanvas" width="520" height="320"></canvas>
        </div>
    </div>

    <div class="panel active chart-panel">
        <div class="panel-head">
            <div>
                <h3>Giờ cao điểm gọi món</h3>
                <p>Số lượt gọi món theo giờ để điều phối bếp.</p>
            </div>
        </div>
        <div class="panel-body chart-body">
            <canvas id="hourCanvas" width="520" height="320"></canvas>
        </div>
    </div>

    <div class="panel active ai-panel">
        <div class="panel-head">
            <div>
                <h3>AI gợi ý vận hành buffet</h3>
                <p>Ưu tiên chuẩn bị món, nhóm nguyên liệu và cách sắp xếp màn hình gọi món.</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="ai-list">
                <?php foreach ($goiYMonAi as $goiY) : ?>
                    <article class="ai-card">
                        <span><?php echo htmlspecialchars($goiY['nhan'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h4><?php echo htmlspecialchars($goiY['tieu_de'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p><?php echo htmlspecialchars($goiY['mo_ta'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <strong><?php echo htmlspecialchars($goiY['hanh_dong'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="panel active report-detail-panel">
    <div class="panel-head">
        <div>
            <h3>Món ít được chọn</h3>
            <p>Các món còn phục vụ nhưng lượng gọi thấp trong khoảng ngày đã chọn.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Món</th>
                        <th>Danh mục</th>
                        <th>Lượt gọi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($monCanDay)) : ?>
                        <tr>
                            <td colspan="3" class="empty">Chưa có đủ dữ liệu món cần đẩy.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($monCanDay as $mon) : ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars(managerText($mon['ten']), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars(managerCategoryText($mon['danh_muc']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$mon['tong_ban']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
