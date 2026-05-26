<section class="panel" id="panel-edit">
    <div class="panel-head">
        <div>
            <h3>Sửa món ăn</h3>
            <p>Chọn món trong danh sách, chỉnh thông tin rồi lưu lại.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="toolbar">
            <input class="input" id="editSearch" type="search" placeholder="Tìm món cần sửa..." oninput="renderEditList()">
            <select class="select" id="editCategory" onchange="renderEditList()"></select>
        </div>
        <div class="table-wrap edit-table">
            <table>
                <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Món ăn</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
                </thead>
                <tbody id="editRows"></tbody>
            </table>
        </div>
        <form class="form-grid edit-form" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid2">
                <label>Tên món
                    <input class="input" type="text" name="ten" id="edit_ten" required>
                </label>
                <label>Danh mục
                    <input class="input" type="text" name="danh_muc" id="edit_danh_muc" list="categoryOptions" required>
                </label>
            </div>
            <label>Mô tả
                <textarea name="mo_ta" id="edit_mo_ta" rows="3"></textarea>
            </label>
            <label>URL ảnh
                <input class="input" type="url" name="anh_url" id="edit_anh_url">
            </label>
            <div class="grid2">
                <label>Giá
                    <input class="input" type="number" min="0" step="1000" name="gia" id="edit_gia">
                </label>
                <label>Thứ tự
                    <input class="input" type="number" name="thu_tu" id="edit_thu_tu">
                </label>
            </div>
            <div class="checks">
                <label><input type="checkbox" name="con_mon" id="edit_con_mon" value="1"> Hiển thị/còn món</label>
                <label><input type="checkbox" name="noi_bat" id="edit_noi_bat" value="1"> Nổi bật</label>
            </div>
            <div class="form-actions">
                <button class="btn secondary" type="button" onclick="hideEditForm()">Hủy sửa</button>
                <button class="btn" type="submit">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</section>
