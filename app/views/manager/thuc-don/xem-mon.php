<section class="panel active" id="panel-view">
    <div class="panel-head">
        <div>
            <h3>Xem món</h3>
            <p>Danh sách món đang có trong thực đơn.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="toolbar">
            <input class="input" id="viewSearch" type="search" placeholder="Tìm tên món, danh mục, mô tả..." oninput="renderViewList()">
            <select class="select" id="viewCategory" onchange="renderViewList()"></select>
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
                <tbody id="viewRows"></tbody>
            </table>
        </div>
    </div>
</section>
