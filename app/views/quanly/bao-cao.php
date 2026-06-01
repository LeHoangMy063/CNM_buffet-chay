<?php
$tuNgay = isset($tuNgay) ? $tuNgay : date('Y-m-d');
$denNgay = isset($denNgay) ? $denNgay : date('Y-m-d');
$tongQuanDoanhThu = isset($tongQuanDoanhThu) ? $tongQuanDoanhThu : array();
$chiTietDoanhThu = isset($chiTietDoanhThu) ? $chiTietDoanhThu : array();

if (!function_exists('managerMoney')) {
    function managerMoney($value)
    {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('managerFormatDateShort')) {
    function managerFormatDateShort($value)
    {
        if (!$value) {
            return '';
        }
        $time = strtotime($value);
        return $time ? date('d-m-y', $time) : $value;
    }
}

$tongDoanhThu = isset($tongQuanDoanhThu['doanh_thu']) ? (float)$tongQuanDoanhThu['doanh_thu'] : 0;
$soPhien = isset($tongQuanDoanhThu['so_phien']) ? (int)$tongQuanDoanhThu['so_phien'] : 0;
$tongKhach = isset($tongQuanDoanhThu['tong_khach']) ? (int)$tongQuanDoanhThu['tong_khach'] : 0;
?>

<section class="stats revenue-stats">
    <div class="stat stat-visible">
        <strong><?php echo managerMoney($tongDoanhThu); ?></strong>
        <span>Tổng doanh thu</span>
    </div>
    <div class="stat">
        <strong><?php echo $soPhien; ?></strong>
        <span>Lượt thanh toán</span>
    </div>
    <div class="stat stat-featured">
        <strong><?php echo $tongKhach; ?></strong>
        <span>Lượt khách</span>
    </div>
</section>

<section class="panel active">
    <div class="panel-head">
        <div>
            <h3>Báo cáo doanh thu</h3>
            <p>Doanh thu được ghi nhận khi nhân viên xác nhận bàn trống.</p>
        </div>
    </div>
    <div class="panel-body">
        <form class="toolbar report-toolbar" method="GET" action="<?php echo BASE_URL; ?>/quan-ly/bao-cao">
            <input class="input" type="date" name="tu_ngay" value="<?php echo htmlspecialchars($tuNgay, ENT_QUOTES, 'UTF-8'); ?>">
            <input class="input" type="date" name="den_ngay" value="<?php echo htmlspecialchars($denNgay, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="btn" type="submit">Xem báo cáo</button>
            <a class="btn secondary" href="#revenue-dashboard">Xem dashboard</a>
            <a class="btn secondary" href="<?php echo BASE_URL; ?>/quan-ly/bao-cao/xuat?tu_ngay=<?php echo urlencode($tuNgay); ?>&den_ngay=<?php echo urlencode($denNgay); ?>">Xuất Excel</a>
        </form>

        <div id="revenue-dashboard" class="report-dashboard">
            <div class="report-dashboard-head">
                <div>
                    <h3>Dashboard theo doanh thu đã chọn</h3>
                    <p>Biểu đồ bên dưới dùng cùng khoảng ngày trong bộ lọc doanh thu.</p>
                </div>
            </div>

            <div class="dashboard-grid report-dashboard-grid">
                <div class="panel active chart-panel chart-panel-wide">
                    <div class="panel-head">
                        <div>
                            <h3>Doanh thu theo ngày</h3>
                            <p>Theo dõi nhịp tăng giảm doanh thu trong khoảng đã chọn.</p>
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
                            <p>Các món tạo nhiều lượt ra món trong khoảng doanh thu này.</p>
                        </div>
                    </div>
                    <div class="panel-body chart-body">
                        <canvas id="topDishCanvas" width="520" height="320"></canvas>
                    </div>
                </div>

                <div class="panel active chart-panel">
                    <div class="panel-head">
                        <div>
                            <h3>Cơ cấu món theo nhóm</h3>
                            <p>So sánh lượng món đã phục vụ theo từng nhóm trong khoảng đã chọn.</p>
                        </div>
                    </div>
                    <div class="panel-body chart-body">
                        <canvas id="categoryCanvas" width="520" height="320"></canvas>
                    </div>
                </div>

                <div class="panel active chart-panel chart-panel-wide">
                    <div class="panel-head">
                        <div>
                            <h3>Giờ cao điểm gọi món</h3>
                            <p>Xem bếp bận nhất vào khung giờ nào trong khoảng doanh thu này.</p>
                        </div>
                    </div>
                    <div class="panel-body chart-body">
                        <canvas id="hourCanvas" width="980" height="320"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel active report-detail-panel">
    <div class="panel-head">
        <div>
            <h3>Chi tiết khách thanh toán theo ngày</h3>
            <p>Dữ liệu doanh thu theo đầu người, gồm số khách đã thanh toán mỗi ngày.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Lượt thanh toán</th>
                        <th>Số khách thanh toán</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($chiTietDoanhThu)) : ?>
                        <tr>
                            <td colspan="4" class="empty">Chưa có dữ liệu thanh toán.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($chiTietDoanhThu as $phien) : ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars(managerFormatDateShort($phien['ngay']), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo (int)$phien['so_phien']; ?></td>
                                <td><?php echo (int)$phien['so_khach']; ?></td>
                                <td><strong><?php echo managerMoney($phien['doanh_thu']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
