<section class="panel" id="panel-add">
    <div class="panel-head">
        <div>
            <h3>Thêm món mới</h3>
            <p>Tạo món ăn mới cho thực đơn khách hàng.</p>
        </div>
    </div>
    <div class="panel-body">
        <form class="form-grid" id="addForm">
            <input type="hidden" name="id" value="">
            <?php include dirname(__FILE__) . '/../_thuc-don-form-fields.php'; ?>
            <div class="form-actions">
                <button class="btn secondary" type="reset">Làm mới</button>
                <button class="btn" type="submit">Thêm món</button>
            </div>
        </form>
    </div>
</section>
