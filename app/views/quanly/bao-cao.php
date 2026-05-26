<?php
$tuNgay = isset($tuNgay) ? $tuNgay : date('Y-m-01');
$denNgay = isset($denNgay) ? $denNgay : date('Y-m-d');
$tongQuanDoanhThu = isset($tongQuanDoanhThu) ? $tongQuanDoanhThu : array();
$baoCaoDoanhThu = isset($baoCaoDoanhThu) ? $baoCaoDoanhThu : array();
$chiTietDoanhThu = isset($chiTietDoanhThu) ? $chiTietDoanhThu : array();

function managerMoney($value)
{
    return number_format((float)$value, 0, ',', '.') . 'đ';
}

$tongDoanhThu = isset($tongQuanDoanhThu['doanh_thu']) ? (float)$tongQuanDoanhThu['doanh_thu'] : 0;
$soPhien = isset($tongQuanDoanhThu['so_phien']) ? (int)$tongQuanDoanhThu['so_phien'] : 0;
$tongKhach = isset($tongQuanDoanhThu['tong_khach']) ? (int)$tongQuanDoanhThu['tong_khach'] : 0;
?>

<section class="stats revenue-stats">
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
        </form>

        <div class="table-wrap report-table">
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
                    <?php if (empty($baoCaoDoanhThu)) : ?>
                        <tr>
                            <td colspan="4" class="empty">Chưa có doanh thu trong khoảng ngày này.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($baoCaoDoanhThu as $dong) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dong['ngay'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$dong['so_phien']; ?></td>
                                <td><?php echo (int)$dong['so_khach']; ?></td>
                                <td><strong><?php echo managerMoney($dong['doanh_thu']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
                        <th>Nguồn dữ liệu</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($chiTietDoanhThu)) : ?>
                        <tr>
                            <td colspan="5" class="empty">Chưa có dữ liệu thanh toán.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($chiTietDoanhThu as $phien) : ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($phien['ngay'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo (int)$phien['so_phien']; ?></td>
                                <td><?php echo (int)$phien['so_khach']; ?></td>
                                <td><?php echo $phien['nguon'] === 'du_lieu_mau' ? 'Dữ liệu tháng 4 mẫu' : 'Hệ thống'; ?></td>
                                <td><strong><?php echo managerMoney($phien['doanh_thu']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
