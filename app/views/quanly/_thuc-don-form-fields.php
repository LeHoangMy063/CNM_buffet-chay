<div class="grid2">
    <label>Tên món
        <input class="input" type="text" name="ten" id="add_ten" required>
    </label>
    <label>Danh mục
        <select class="select" name="danh_muc" id="add_danh_muc" required>
            <?php echo managerCategoryOptions(); ?>
        </select>
    </label>
</div>
<label>Mô tả
    <textarea name="mo_ta" rows="3"></textarea>
</label>
<label>URL ảnh
    <input class="input" type="url" name="anh_url">
</label>
<div class="checks">
    <label><input type="checkbox" name="con_mon" value="1" checked> Hiển thị/còn món</label>
    <label><input type="checkbox" name="noi_bat" value="1"> Nổi bật</label>
</div>
