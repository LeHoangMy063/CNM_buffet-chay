<?php
// View: Trang tích điểm khách hàng (Usecase 104)
// Liên kết với: NhanVienController::tichDiem() và xuLyTichDiem()
$pageTitle = 'Tích Điểm Khách Hàng';
require dirname(__FILE__) . '/../home/_dau-trang.php';
?>

<h2>Tích điểm khách hàng</h2>

<form method="GET" action="">
    <input type="text" name="sdt"
           value="<?php echo isset($sdt) ? htmlspecialchars($sdt) : '' ?>"
           placeholder="Nhập số điện thoại khách hàng">
    <button type="submit" class="btn-admin btn-green">Tìm kiếm</button>
</form>

<?php if (isset($sdt) && $sdt !== '' && empty($khach)) { ?>
    <p style="color:red;margin-top:1rem">
        Không tìm thấy khách hàng với SĐT: <?php echo htmlspecialchars($sdt) ?>
    </p>
<?php } ?>

<?php if (!empty($khach)) { ?>
<div class="card" style="margin-top:1rem">
    <div class="card-header"><h3>Thông tin khách hàng</h3></div>
    <p>Họ tên: <?php echo htmlspecialchars($khach['ho_ten']) ?></p>
    <p>SĐT: <?php echo htmlspecialchars($khach['so_dien_thoai']) ?></p>
    <p>Điểm tích lũy hiện tại: <strong><?php echo intval($khach['diem_tich_luy']) ?></strong></p>

    <form id="form-tich-diem" style="margin-top:1rem">
        <input type="hidden" name="tai_khoan_id" value="<?php echo intval($khach['id']) ?>">
        <input type="number" name="diem" min="1" placeholder="Số điểm cần cộng" required>
        <button type="submit" class="btn-admin btn-green">Cộng điểm</button>
    </form>
</div>
<script>
document.getElementById('form-tich-diem').onsubmit = function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL ?>/nhan-vien/tich-diem/xu-ly', true);
    xhr.onload = function() {
        try {
            var res = JSON.parse(xhr.responseText);
            alert(res.thong_bao || res.message);
            if (res.success) { location.reload(); }
        } catch(ex) { alert('Lỗi kết nối'); }
    };
    xhr.send(fd);
};
</script>
<?php } ?>

<?php require dirname(__FILE__) . '/../home/_cuoi-trang.php'; ?>
