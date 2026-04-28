<?php $pageTitle = 'Quan Ly Ban'; require __DIR__ . '/_header.php'; ?>

<div class="card">
    <div class="card-header">
        <h3>Danh sach ban an</h3>
        <span style="font-size:0.8rem;color:#6b7280"><?php echo count($tables) ?> ban</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>So ban</th><th>Suc chua</th><th>Ma truy cap</th>
                <th>Dat ban</th><th>Goi mon</th><th>Trang thai</th><th>Thao tac</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tables as $t) { ?>
        <tr>
            <td><strong style="font-size:1rem">Ban <?php echo htmlspecialchars($t['table_number']) ?></strong></td>
            <td><?php echo $t['capacity'] ?> nguoi</td>
            <td>
                <code style="background:#f3f4f6;padding:0.2rem 0.5rem;border-radius:4px;font-size:0.8rem;letter-spacing:0.05em">
                    <?php echo htmlspecialchars($t['access_code']) ?>
                </code>
            </td>
            <td><?php echo $t['total_reservations'] ?> lan</td>
            <td><?php echo $t['total_orders'] ?> mon</td>
            <td>
                <select class="inline" onchange="updateTableStatus(<?php echo $t['id'] ?>, this.value)">
                    <?php
                    $tOpts = array('available' => 'Trong', 'occupied' => 'Dang dung', 'reserved' => 'Da dat');
                    foreach ($tOpts as $val => $lbl) {
                        echo '<option value="'.$val.'"'.($t['status']===$val?' selected':'').'>'.$lbl.'</option>';
                    }
                    ?>
                </select>
            </td>
            <td>
                <a href="<?php echo BASE_URL ?>/order?code=<?php echo urlencode($t['access_code']) ?>" target="_blank"
                   class="btn-admin btn-sm btn-gray">Xem</a>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
function updateTableStatus(id, status) {
    apiPost('<?php echo BASE_URL ?>/admin/tables/update-status', { id: id, status: status })
        .then(function(data) {
            if (data.success) showToast('Da cap nhat trang thai ban');
            else showToast(data.message, true);
        });
}
</script>

<?php require __DIR__ . '/_footer.php'; ?>
