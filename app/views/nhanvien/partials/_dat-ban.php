<?php $laBep = isset($laBep) ? $laBep : (isset($vaiTro) && $vaiTro === 'bep'); ?>
<section class="staff-section<?php echo $laBep ? ' active' : ''; ?>" id="section-dat-ban">

    <?php if (!$laBep): ?>
    <div class="table-work-pane active" id="pane-xac-nhan-dat-ban">
        <div class="panel">
            <div class="reservation-panel-head">
                <div>
                    <p class="eyebrow">Buffet Chay</p>
                    <h2>Duyệt đặt bàn</h2>
                    <p>Duyệt bàn tự gán, đổi bàn nếu cần, hoặc hủy đặt bàn.</p>
                </div>
                <div id="pendingApprovalBanner" class="pending-banner compact-pending" style="display:none">
                    <strong id="pendingApprovalCount">0</strong>
                    <span>chờ duyệt bàn</span>
                    <button type="button" onclick="StaffReservations.setStatus('cho_xac_nhan','',false)">Xem</button>
                </div>
            </div>
            <div class="panel-body">
                <div class="reservation-calendar-panel">
                    <div class="reservation-calendar-head">
                        <button type="button" class="calendar-nav-btn" onclick="StaffReservations.changeMonth(-1)">Trước</button>
                        <strong id="reservationCalendarTitle">Lịch đặt bàn</strong>
                        <button type="button" class="calendar-nav-btn" onclick="StaffReservations.changeMonth(1)">Sau</button>
                    </div>
                    <div class="reservation-calendar-week">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div id="reservationCalendar" class="reservation-calendar-grid">
                        <div class="empty-state">Đang tải lịch đặt bàn...</div>
                    </div>
                </div>

                <div class="db-filter-row">
                    <input id="reservationSearch" class="search db-search" type="search"
                        placeholder="Tìm tên, SĐT, mã đặt bàn..." autocomplete="off">
                    <select id="reservationStatus" class="select db-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="cho_xac_nhan">Chờ duyệt bàn</option>
                        <option value="da_xac_nhan">Đã xác nhận</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>

                <div class="reservation-tabs">
                    <button type="button" class="chip active" data-status="" data-duyet="">Tất cả</button>
                    <button type="button" class="chip chip-alert" data-status="cho_xac_nhan" data-duyet="">Chờ duyệt bàn</button>
                    <button type="button" class="chip chip-ok" data-status="da_xac_nhan" data-duyet="">Đã xác nhận</button>
                    <button type="button" class="chip chip-danger" data-status="cancelled" data-duyet="">Đã hủy</button>
                </div>

                <div id="reservationList" class="reservation-list">
                    <div class="empty-state">Đang tải danh sách đặt bàn...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-work-pane" id="pane-cap-nhat-trang-thai-ban" style="display:none">
        <div class="panel">
            <div class="panel-head panel-head-wrap">
                <div>
                    <h2>Điều phối bàn</h2>
                    <p class="panel-sub">Theo dõi bàn trống, sức chứa, mã bàn và xử lý thanh toán để trả bàn.</p>
                </div>
                <button class="btn secondary btn-sm" type="button" onclick="StaffTableStatus.load()">Làm mới</button>
            </div>
            <div class="panel-body">
                <div class="db-filter-row table-status-filter">
                    <input id="tableStatusSearch" class="search" type="search"
                        placeholder="Tìm số bàn, mã truy cập..." autocomplete="off">
                    <select id="tableStatusFilter" class="select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="trong">Trống</option>
                        <option value="dang_dung">Đang dùng</option>
                    </select>
                </div>
                <div id="tableStatusList" class="table-status-grid">
                    <div class="empty-state">Đang tải danh sách bàn...</div>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <?php require dirname(__FILE__) . '/_goi-mon.php'; ?>
</section>
