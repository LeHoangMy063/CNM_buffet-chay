<?php
$orders = isset($orders) ? $orders : (isset($danhSachDon) ? $danhSachDon : array());
$pageTitle = 'Quan Ly Goi Mon';
require __DIR__ . '/_header.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <span style="color:#6b7280;font-size:0.85rem"><?php echo count($orders) ?> luot goi mon</span>
    <button class="btn-admin btn-gray" onclick="location.reload()">Lam moi</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Ban</th>
                <th>Mon an</th>
                <th>Danh muc</th>
                <th>So luong</th>
                <th>Ghi chu</th>
                <th>Thoi gian</th>
                <th>Trang thai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)) { ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:#6b7280;padding:2rem">Chua co goi mon</td>
                </tr>
                <?php } else {
                foreach ($orders as $o) { ?>
                    <tr id="ord-<?php echo $o['id'] ?>">
                        <td><strong>Ban <?php echo htmlspecialchars($o['table_number']) ?></strong></td>
                        <td><?php echo htmlspecialchars($o['item_name']) ?></td>
                        <td style="color:#6b7280"><?php echo htmlspecialchars($o['category']) ?></td>
                        <td style="font-weight:700;font-size:1rem"><?php echo $o['quantity'] ?></td>
                        <td style="color:#6b7280;font-size:0.8rem"><?php echo htmlspecialchars($o['note'] ? $o['note'] : '-') ?></td>
                        <td style="font-size:0.8rem;color:#6b7280"><?php echo date('H:i d/m', strtotime($o['created_at'])) ?></td>
                        <td>
                            <select class="inline" onchange="updateOrderStatus(<?php echo $o['id'] ?>, this.value)">
                                <?php
                $statusOpts = array(
                                    'cho_phuc_vu' => 'Cho',
                                    'dang_che_bien' => 'Dang lam',
                                    'da_phuc_vu' => 'Phuc vu',
                                    'da_huy' => 'Huy'
                                );
                                $currentStatus = isset($o['trang_thai']) ? $o['trang_thai'] : $o['status'];
                                foreach ($statusOpts as $v => $l) {
                                    echo '<option value="' . $v . '"' . ($currentStatus === $v ? ' selected' : '') . '>' . $l . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<script>
    function updateOrderStatus(id, status) {
        apiPost('<?php echo BASE_URL ?>/quan-tri/don-mon/cap-nhat-trang-thai', {
                id: id,
                trang_thai: status
            })
            .then(function(data) {
                if (data.success) showToast('Cap nhat mon #' + id);
                else showToast(data.message, true);
            });
    }
    setTimeout(function() {
        location.reload();
    }, 20000);
</script>

<?php require __DIR__ . '/_footer.php'; ?>
