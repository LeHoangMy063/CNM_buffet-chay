<div class="modal" id="addModal" aria-hidden="true">
    <div class="modal-backdrop" onclick="hideAddForm()"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
        <div class="modal-head">
            <h3 id="addModalTitle">Thêm món</h3>
            <button class="modal-close" type="button" onclick="hideAddForm()" aria-label="Đóng">&times;</button>
        </div>
        <form class="form-grid" id="addForm">
            <input type="hidden" name="id" value="">
            <?php include dirname(__FILE__) . '/../_thuc-don-form-fields.php'; ?>
            <div class="form-actions">
                <button class="btn secondary" type="button" onclick="hideAddForm()">Hủy</button>
                <button class="btn" type="submit">Thêm món</button>
            </div>
        </form>
    </div>
</div>
