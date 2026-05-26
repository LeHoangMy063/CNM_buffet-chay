<section class="panel" id="panel-delete">
    <div class="panel-head">
        <div>
            <h3>Xóa món ăn</h3>
            <p>Xóa món khỏi hệ thống khi không còn sử dụng.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="danger-zone">Lưu ý: xóa món có thể ảnh hưởng dữ liệu đơn món cũ nếu món đã từng được gọi.</div>
        <div class="toolbar">
            <input class="input" id="deleteSearch" type="search" placeholder="Tìm món cần xóa..." oninput="renderDeleteList()">
            <select class="select" id="deleteCategory" onchange="renderDeleteList()"></select>
        </div>
        <div class="table-wrap">
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
                <tbody id="deleteRows"></tbody>
            </table>
        </div>
    </div>
</section>
