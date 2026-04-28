<?php $pageTitle = 'Dashboard';
require(dirname(__FILE__) . '/_header.php'); ?>

<div class="stat-grid">
    <div class="stat-card">
        <h3>Dat ban hom nay</h3>
        <div class="val"><?php echo isset($todayStats['total']) ? $todayStats['total'] : 0 ?></div>
        <div class="sub"><?php echo isset($todayStats['total_adults']) ? $todayStats['total_adults'] : 0 ?> nguoi lon &middot; <?php echo isset($todayStats['total_children']) ? $todayStats['total_children'] : 0 ?> tre em</div>
    </div>
    <div class="stat-card">
        <h3>Doanh thu hom nay</h3>
        <div class="val" style="font-size:1.5rem"><?php echo number_format(isset($todayStats['revenue']) ? $todayStats['revenue'] : 0, 0, ',', '.') ?><span style="font-size:1rem">d</span></div>
        <div class="sub">Tu buffet nguoi lon</div>
    </div>
    <div class="stat-card">
        <h3>Luot goi mon hom nay</h3>
        <div class="val"><?php echo $orderCount ?></div>
        <div class="sub">Tong mon da goi</div>
    </div>
    <div class="stat-card">
        <h3>Tong so ban</h3>
        <div class="val"><?php echo $totalTables ?></div>
        <div class="sub"><?php echo $menuCount ?> mon trong thuc don</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <div class="card">
        <div class="card-header">
            <h3>Dat ban gan day</h3>
            <a href="<?php echo BASE_URL ?>/admin/reservations" style="font-size:0.8rem;color:#2d6a4f;text-decoration:none">Xem tat ca &rarr;</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Khach</th>
                    <th>Ngay</th>
                    <th>Trang thai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentReservations)) { ?>
                    <tr>
                        <td colspan="3" style="text-align:center;color:#6b7280">Chua co dat ban</td>
                    </tr>
                    <?php } else {
                    foreach ($recentReservations as $r) { ?>
                        <tr>
                            <td>
                                <div style="font-weight:600"><?php echo htmlspecialchars($r['customer_name']) ?></div>
                                <div style="font-size:0.75rem;color:#6b7280"><?php echo $r['adult_count'] ?> NL<?php echo $r['child_count'] ? ' + ' . $r['child_count'] . ' TE' : '' ?></div>
                            </td>
                            <td><?php echo date('d/m', strtotime($r['reservation_date'])) ?><br><span style="color:#6b7280;font-size:0.75rem"><?php echo $r['reservation_time'] ?></span></td>
                            <td><?php
                                $statusMap = array('pending' => 'Cho', 'confirmed' => 'Xac nhan', 'cancelled' => 'Huy', 'completed' => 'Hoan thanh');
                                echo '<span class="badge badge-' . $r['status'] . '">' . (isset($statusMap[$r['status']]) ? $statusMap[$r['status']] : $r['status']) . '</span>';
                                ?></td>
                        </tr>
                <?php }
                } ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Goi mon gan day</h3>
            <a href="<?php echo BASE_URL ?>/admin/orders" style="font-size:0.8rem;color:#2d6a4f;text-decoration:none">Xem tat ca &rarr;</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Ban</th>
                    <th>Mon</th>
                    <th>Trang thai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)) { ?>
                    <tr>
                        <td colspan="3" style="text-align:center;color:#6b7280">Chua co goi mon</td>
                    </tr>
                    <?php } else {
                    foreach ($recentOrders as $o) { ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($o['table_number']) ?></strong></td>
                            <td><?php echo $o['quantity'] ?>x <?php echo htmlspecialchars($o['item_name']) ?></td>
                            <td><?php
                                $oMap = array('pending' => 'Cho', 'preparing' => 'Dang lam', 'served' => 'Phuc vu', 'cancelled' => 'Huy');
                                echo '<span class="badge badge-' . $o['status'] . '">' . (isset($oMap[$o['status']]) ? $oMap[$o['status']] : $o['status']) . '</span>';
                                ?></td>
                        </tr>
                <?php }
                } ?>
            </tbody>
        </table>
    </div>

</div>

<?php require __DIR__ . '/_footer.php'; ?>