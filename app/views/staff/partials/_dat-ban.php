<section class="staff-section" id="section-dat-ban">

    <div class="section-intro">
        <div>
            <p class="eyebrow">Lịch khách đặt trước</p>
            <h3>Quản lý đặt bàn</h3>
            <span>Hệ thống tự động gán bàn phù hợp khi khách đặt. Nhân viên xem lại và xác nhận hoặc điều chỉnh nếu cần.</span>
        </div>
        <div style="display:-webkit-box;display:-ms-flexbox;display:flex;gap:8px;-ms-flex-wrap:wrap;flex-wrap:wrap">
            <button class="btn secondary btn-icon btn-sm" type="button" onclick="StaffReservations.load()">&#8635; Làm mới</button>
        </div>
    </div>

    <!-- Badge đếm chờ duyệt -->
    <div id="pendingApprovalBanner" class="pending-banner" style="display:none">
        <span class="pending-banner-ico">&#9888;</span>
        <div>
            <strong id="pendingApprovalCount">0</strong> đặt bàn có bàn hệ thống tự gán — cần nhân viên xác nhận.
        </div>
        <button type="button" class="btn btn-sm" onclick="StaffReservations.setStatus('','',true)">Xem ngay</button>
    </div>

    <div class="panel">
        <div class="panel-head panel-head-wrap">
            <div>
                <h2>Danh sách đặt bàn</h2>
                <p class="panel-sub">Xác nhận bàn tự động gán, đổi bàn thủ công, hoặc cập nhật trạng thái đặt bàn.</p>
            </div>
        </div>
        <div class="panel-body">

            <!-- Bộ lọc -->
            <div class="db-filter-row">
                <input id="reservationSearch" class="search db-search" type="search"
                       placeholder="&#128269; Tìm tên, SĐT, mã đặt bàn..." autocomplete="off">
                <select id="reservationStatus" class="select db-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="cho_xac_nhan">Chờ xác nhận</option>
                    <option value="da_xac_nhan">Đã xác nhận</option>
                    <option value="da_huy">Đã hủy</option>
                    <option value="hoan_thanh">Hoàn thành</option>
                </select>
            </div>

            <!-- Tab nhanh -->
            <div class="reservation-tabs">
                <button type="button" class="chip active" data-status="" data-duyet="">Tất cả</button>
                <button type="button" class="chip chip-alert" data-status="" data-duyet="1">&#9888; Chờ duyệt bàn</button>
                <button type="button" class="chip chip-warn" data-status="cho_xac_nhan" data-duyet="">&#9679; Chờ xác nhận</button>
                <button type="button" class="chip chip-ok" data-status="da_xac_nhan" data-duyet="">&#9679; Đã xác nhận</button>
                <button type="button" class="chip chip-danger" data-status="da_huy" data-duyet="">&#9679; Đã hủy</button>
                <button type="button" class="chip chip-muted" data-status="hoan_thanh" data-duyet="">&#9679; Hoàn thành</button>
            </div>

            <div id="reservationList" class="reservation-list">
                <div class="empty-state">Đang tải danh sách đặt bàn...</div>
            </div>

        </div>
    </div>
</section>
