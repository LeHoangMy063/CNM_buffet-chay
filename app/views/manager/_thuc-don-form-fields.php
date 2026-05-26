<div class="grid2">
    <label>Tên món
        <input class="input" type="text" name="ten" required>
    </label>
    <label>Danh mục
        <input class="input" type="text" name="danh_muc" list="categoryOptions" required>
    </label>
</div>
<label>Mô tả
    <textarea name="mo_ta" rows="3"></textarea>
</label>
<label>URL ảnh
    <input class="input" type="url" name="anh_url">
</label>
<div class="grid2">
    <label>Giá
        <input class="input" type="number" min="0" step="1000" name="gia" value="0">
    </label>
    <label>Thứ tự
        <input class="input" type="number" name="thu_tu" value="0">
    </label>
</div>
<div class="checks">
    <label><input type="checkbox" name="con_mon" value="1" checked> Hiển thị/còn món</label>
    <label><input type="checkbox" name="noi_bat" value="1"> Nổi bật</label>
</div>
