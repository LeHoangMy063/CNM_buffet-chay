<section class="staff-section" id="section-goi-mon">

    <div class="section-intro">
        <div>
            <p class="eyebrow">Phục vụ tại bàn</p>
            <h3>Gọi món theo bàn</h3>
            <span>Chọn bàn để xem danh sách món đang chờ phục vụ và xác nhận từng món khi đã mang ra.</span>
        </div>
        <button class="btn btn-icon" type="button" onclick="StaffOrders.loadTables()">
            &#8635; Làm mới
        </button>
    </div>

    <!-- Thống kê nhanh -->
    <div class="gm-stats">
        <div class="gm-stat gm-stat-total">
            <strong id="statTotal">0</strong>
            <span>Tổng bàn</span>
        </div>
        <div class="gm-stat gm-stat-busy">
            <strong id="statBusy">0</strong>
            <span>Đang có đơn</span>
        </div>
        <div class="gm-stat gm-stat-empty">
            <strong id="statEmpty">0</strong>
            <span>Bàn trống</span>
        </div>
        <div class="gm-stat gm-stat-orders">
            <strong id="statOrders">0</strong>
            <span>Món chờ phục vụ</span>
        </div>
    </div>

    <div class="gm-layout">

        <!-- Cột trái: danh sách bàn -->
        <div class="gm-col-tables">
            <div class="panel">
                <div class="panel-head">
                    <h2>Danh sách bàn</h2>
                    <span class="badge muted">Tự động cập nhật</span>
                </div>
                <div class="panel-body">
                    <input id="tableSearch" class="search" type="search"
                           placeholder="Tìm số bàn, mã truy cập..." autocomplete="off">
                    <div class="table-legend">
                        <span class="legend-dot legend-busy"></span><span>Đang có đơn</span>
                        <span class="legend-dot legend-empty"></span><span>Trống</span>
                    </div>
                    <div id="tables" class="table-grid">
                        <div class="empty-state">Đang tải danh sách bàn...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: chi tiết đơn của bàn đã chọn -->
        <div class="gm-col-orders">
            <div class="panel">
                <div class="panel-head">
                    <div class="order-head-info">
                        <h2 id="selectedTableTitle">Chưa chọn bàn</h2>
                        <small id="selectedTableMeta" class="panel-sub">Chọn một bàn bên trái để xem món đang chờ.</small>
                    </div>
                    <div class="order-head-actions">
                        <span id="lastUpdated" class="badge muted">Chưa tải</span>
                        <button id="refreshOrderBtn" class="btn secondary btn-sm" type="button"
                                onclick="StaffOrders.loadOrders()" disabled>&#8635;</button>
                    </div>
                </div>

                <div class="panel-body">
                    <!-- Thanh hành động -->
                    <div class="order-action-bar" id="orderActionBar" style="display:none">
                        <div>
                            <span id="orderCountLabel" class="badge warn">0 món đang chờ</span>
                        </div>
                        <div class="order-action-right">
                            <button id="confirmAllBtn" class="btn btn-sm" type="button"
                                    onclick="StaffOrders.confirmAll()" style="display:none">
                                &#10003; Xác nhận tất cả
                            </button>
                            <button id="clearTableBtn" class="btn danger btn-sm" type="button"
                                    onclick="StaffOrders.markTableEmpty()" disabled>
                                Xác nhận bàn trống
                            </button>
                        </div>
                    </div>

                    <div id="orders" class="orders">
                        <div class="empty-state">
                            <div style="font-size:32px;margin-bottom:8px">&#127860;</div>
                            <div>Chọn bàn để xem danh sách món.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
