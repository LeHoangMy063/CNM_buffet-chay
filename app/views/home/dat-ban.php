<?php $pageTitle = 'Quan Ly Dat Ban'; require __DIR__ . '/_header.php'; ?>

<div style="margin-bottom:1rem;display:flex;gap:0.5rem;flex-wrap:wrap">
    <?php
    $filters = array('' => 'Tat ca', 'pending' => 'Cho xac nhan', 'confirmed' => 'Da xac nhan', 'cancelled' => 'Da huy', 'completed' => 'Hoan thanh');
    foreach ($filters as $val => $lbl) {
        $href = BASE_URL . '/admin/reservations' . ($val ? '?status=' . $val : '');
        $cls = ($filter === $val) ? 'btn-admin btn-green' : 'btn-admin btn-gray';
        echo '<a href="'.$href.'" class="'.$cls.'">'.$lbl.'</a>';
    }
    ?>
</div>

<div class="card">
    <div class="card-header">
        <h3>Danh sach dat ban</h3>
        <span style="font-size:0.8rem;color:#6b7280"><?php echo count($reservations) ?> ket qua</span>
    </div>
    <table>
        <thead>
            <tr><th>Khach hang</th><th>Lien he</th><th>Ban</th><th>Ngay / Gio</th><th>Khach</th><th>Tong tien</th><th>Trang thai</th></tr>
        </thead>
        <tbody>
        <?php if (empty($reservations)) { ?>
        <tr><td colspan="7" style="text-align:center;color:#6b7280;padding:2rem">Khong co du lieu</td></tr>
        <?php } else { foreach ($reservations as $r) { ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($r['customer_name']) ?></strong></td>
            <td style="color:#6b7280"><?php echo htmlspecialchars($r['customer_phone']) ?></td>
            <td><?php echo $r['table_number'] ? 'Ban '.$r['table_number'] : '-' ?></td>
            <td><?php echo date('d/m/Y', strtotime($r['reservation_date'])) ?><br><span style="color:#6b7280;font-size:0.8rem"><?php echo $r['reservation_time'] ?></span></td>
            <td><?php echo $r['adult_count'] ?> NL<?php echo $r['child_count'] ? ' + '.$r['child_count'].' TE' : '' ?></td>
            <td style="font-weight:600;color:#2d6a4f"><?php echo number_format($r['total_price'], 0, ',', '.') ?>d</td>
            <td>
                <select class="inline" onchange="updateResStatus(<?php echo $r['id'] ?>, this.value)">
                    <?php
                    $rMap = array('pending' => 'Cho', 'confirmed' => 'Xac nhan', 'cancelled' => 'Huy', 'completed' => 'Hoan thanh');
                    foreach ($rMap as $v => $l) {
                        echo '<option value="'.$v.'"'.($r['status']===$v?' selected':'').'>'.$l.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table>
</div>

<script>
function updateResStatus(id, status) {
    apiPost('<?php echo BASE_URL ?>/admin/reservations/update-status', { id: id, status: status })
        .then(function(data) {
            if (data.success) showToast('Da cap nhat dat ban #' + id);
            else showToast(data.message, true);
        });
}
</script>

<?php require __DIR__ . '/_footer.php'; ?>
