/* staff-dashboard.js
 * Compatible: IE9+, no ES5 Array extras assumed beyond forEach polyfill
 * PHP 5.2.6 project - no transpile needed, pure ES3-safe syntax
 */
(function () {

    /* ---- Helpers ---- */
    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toast(msg, type) {
        var el = document.getElementById('toast');
        if (!el) return;
        el.textContent = msg;
        el.className = 'toast show' + (type === 'err' ? ' err' : '');
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.className = 'toast'; }, 3400);
    }

    function postForm(url, data, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            try { cb(JSON.parse(xhr.responseText)); }
            catch (e) { cb({ success: false, thong_bao: 'Phản hồi server không hợp lệ' }); }
        };
        xhr.onerror = function () { cb({ success: false, thong_bao: 'Không kết nối được máy chủ' }); };
        var parts = [];
        for (var k in data) {
            if (data.hasOwnProperty(k))
                parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(data[k]));
        }
        xhr.send(parts.join('&'));
    }

    function getJson(url, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            try { cb(JSON.parse(xhr.responseText)); }
            catch (e) { cb({ success: false, thong_bao: 'Phản hồi server không hợp lệ' }); }
        };
        xhr.onerror = function () { cb({ success: false, thong_bao: 'Không kết nối được máy chủ' }); };
        xhr.send();
    }

    function el(id) { return document.getElementById(id); }
    function setText(id, v) { var e = el(id); if (e) e.textContent = v; }

    function fmtTime() {
        var d = new Date();
        var h = d.getHours();
        var m = d.getMinutes();
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    function statusInfo(s) {
        var map = {
            cho_xac_nhan: { text: 'Chờ xác nhận', badge: 'warn', bar: 'bar-cho_xac_nhan' },
            da_xac_nhan:  { text: 'Đã xác nhận',  badge: 'ok',   bar: 'bar-da_xac_nhan'  },
            da_huy:       { text: 'Đã hủy',        badge: 'danger', bar: 'bar-da_huy'     },
            hoan_thanh:   { text: 'Hoàn thành',    badge: 'muted',  bar: 'bar-hoan_thanh' }
        };
        return map[s] || { text: s || '-', badge: 'muted', bar: '' };
    }

    /* ================================================================
       StaffDashboard – điều phối chung
    ================================================================ */
    window.StaffDashboard = {
        currentSection: 'home'
    };

    /* ================================================================
       StaffTabs – chuyển màn hình
    ================================================================ */
    window.StaffTabs = {
        titles: {
            'home':    'Trang chủ nhân viên',
            'goi-mon': 'Gọi món theo bàn',
            'dat-ban': 'Quản lý đặt bàn'
        },
        show: function (name) {
            StaffDashboard.currentSection = name;

            var sections = document.querySelectorAll('.staff-section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].className = sections[i].className.replace(' active', '').replace('active', '');
            }
            var links = document.querySelectorAll('.side-link[data-section]');
            for (var j = 0; j < links.length; j++) {
                links[j].className = links[j].className.replace(' active', '');
            }

            var sec = el('section-' + name);
            if (sec) sec.className += ' active';

            var lnk = document.querySelector('.side-link[data-section="' + name + '"]');
            if (lnk) lnk.className += ' active';

            var titleEl = el('pageTitle');
            if (titleEl) titleEl.textContent = this.titles[name] || 'Màn hình nhân viên';

            if (name === 'goi-mon') StaffOrders.loadTables();
            if (name === 'dat-ban') StaffReservations.load();
        }
    };

    /* ================================================================
       StaffOrders – Gọi món theo bàn
    ================================================================ */
    window.StaffOrders = {
        tables: [],
        selectedTableId: 0,
        selectedTable: null,

        /* Cập nhật 4 stat ở đầu section gọi món */
        renderStats: function (pendingCount) {
            var total = this.tables.length;
            var busy  = 0;
            for (var i = 0; i < this.tables.length; i++) {
                if (this.tables[i].trang_thai === 'dang_dung') busy++;
            }
            setText('statTotal',  total);
            setText('statBusy',   busy);
            setText('statEmpty',  total - busy);
            setText('statOrders', pendingCount != null ? pendingCount : '-');

            /* Đồng bộ stat ở trang chủ (IDs khác) */
            setText('statTotalHome',  total);
            setText('statBusyHome',   busy);
            setText('statEmptyHome',  total - busy);
        },

        /* Vẽ lưới bàn bên trái */
        renderTables: function () {
            var box = el('tables');
            if (!box) return;

            var input   = el('tableSearch');
            var keyword = input ? input.value.toLowerCase() : '';
            var html    = '';
            var visible = 0;

            for (var i = 0; i < this.tables.length; i++) {
                var t    = this.tables[i];
                var name = 'Bàn ' + esc(t.so_ban);
                var code = t.ma_truy_cap || '';
                if (keyword && (name + ' ' + code).toLowerCase().indexOf(keyword) === -1) continue;

                visible++;
                var isBusy   = t.trang_thai === 'dang_dung';
                var isActive = this.selectedTableId == t.id;
                var cardCls  = 'table-card' + (isBusy ? ' busy' : ' empty') + (isActive ? ' active' : '');

                html += '<button type="button" class="' + cardCls + '" onclick="StaffOrders.selectTable(' + t.id + ')">';
                html += '<div class="table-num">';
                html += '<span class="table-status-dot"></span>' + name;
                html += '</div>';
                html += '<div class="table-info">Sức chứa: ' + esc(t.suc_chua || '-') + ' khách';
                if (code) html += '<br>Mã: ' + esc(code);
                html += '</div>';
                if (isBusy) {
                    html += '<span class="badge warn" style="margin-top:6px;display:inline-flex">Có đơn chờ</span>';
                } else {
                    html += '<span class="badge ok" style="margin-top:6px;display:inline-flex">Trống</span>';
                }
                html += '</button>';
            }

            if (!visible) html = '<div class="empty-state">Không tìm thấy bàn phù hợp.</div>';
            box.innerHTML = html;
            this.renderStats();
        },

        loadTables: function () {
            var self = this;
            getJson(BASE_URL + '/nhan-vien/danh-sach-ban', function (res) {
                if (!res.success) {
                    toast(res.thong_bao || 'Không tải được danh sách bàn', 'err');
                    return;
                }
                self.tables = res.du_lieu || [];
                /* Cập nhật lại selectedTable nếu vẫn đang chọn */
                if (self.selectedTableId) {
                    self.selectedTable = null;
                    for (var i = 0; i < self.tables.length; i++) {
                        if (self.tables[i].id == self.selectedTableId) {
                            self.selectedTable = self.tables[i];
                        }
                    }
                }
                self.renderTables();
            });
        },

        selectTable: function (id) {
            this.selectedTableId = id;
            this.selectedTable   = null;
            for (var i = 0; i < this.tables.length; i++) {
                if (this.tables[i].id == id) this.selectedTable = this.tables[i];
            }
            this.renderTables();
            this.loadOrders();

            /* Bật nút làm mới đơn */
            var refreshBtn = el('refreshOrderBtn');
            if (refreshBtn) refreshBtn.disabled = false;
        },

        loadOrders: function () {
            var self = this;
            if (!self.selectedTableId) return;

            var t     = self.selectedTable;
            var title = t ? ('Bàn ' + t.so_ban) : ('Bàn #' + self.selectedTableId);
            var meta  = t && t.ma_truy_cap ? ('Mã truy cập: ' + t.ma_truy_cap) : 'Đang tải đơn món...';

            setText('selectedTableTitle', title);
            setText('selectedTableMeta',  meta);

            var ordersBox = el('orders');
            if (ordersBox) ordersBox.innerHTML = '<div class="empty-state">Đang tải đơn món...</div>';

            getJson(BASE_URL + '/nhan-vien/don-theo-ban?ban_id=' + encodeURIComponent(self.selectedTableId), function (res) {
                if (!res.success) {
                    toast(res.thong_bao || 'Không tải được đơn món', 'err');
                    return;
                }
                self.renderOrders(res.du_lieu || []);
            });
        },

        renderOrders: function (orders) {
            var n          = orders.length;
            var actionBar  = el('orderActionBar');
            var clearBtn   = el('clearTableBtn');
            var confirmAll = el('confirmAllBtn');
            var countLabel = el('orderCountLabel');
            var lastUpd    = el('lastUpdated');

            if (lastUpd) lastUpd.textContent = 'Cập nhật lúc ' + fmtTime();
            this.renderStats(n);

            /* Hiện action bar khi đã chọn bàn */
            if (actionBar) actionBar.style.display = 'flex';

            if (clearBtn) {
                clearBtn.disabled    = n > 0;
                clearBtn.textContent = n > 0 ? 'Phục vụ hết món trước' : 'Xác nhận bàn trống';
            }
            if (confirmAll) confirmAll.style.display = n > 0 ? 'inline-flex' : 'none';
            if (countLabel) {
                countLabel.textContent = n > 0 ? (n + ' món đang chờ') : 'Không có món chờ';
                countLabel.className   = 'badge ' + (n > 0 ? 'warn' : 'ok');
            }

            if (!n) {
                el('orders').innerHTML = '<div class="empty-state"><div style="font-size:32px;margin-bottom:8px">&#127860;</div><div>Bàn này không có món nào đang chờ phục vụ.</div></div>';
                return;
            }

            var html = '';
            for (var i = 0; i < orders.length; i++) {
                var item = orders[i];
                html += '<div class="order-card">';
                html += '<div class="order-card-body">';
                html += '<div class="order-name">Don #' + esc(item.id) + '<span class="order-qty">' + esc(item.tong_so_luong || item.so_mon || 0) + ' mon</span></div>';
                html += '<div class="order-note"><span class="order-note-text">' + esc(item.mon_tom_tat || item.ten_mon || '-') + '</span></div>';
                if (item.ghi_chu) {
                    html += '<div class="order-note">Ghi chú: <span class="order-note-text">' + esc(item.ghi_chu) + '</span></div>';
                }
                html += '</div>';
                html += '<button class="btn btn-sm" type="button" onclick="StaffOrders.confirmDish(' + item.id + ')">&#10003; Đã phục vụ</button>';
                html += '</div>';
            }
            el('orders').innerHTML = html;
        },

        confirmDish: function (orderId) {
            var self = this;
            postForm(BASE_URL + '/nhan-vien/xac-nhan-mon', { don_id: orderId }, function (res) {
                if (res.success) {
                    toast(res.thong_bao || 'Đã xác nhận món');
                    self.loadOrders();
                    self.loadTables();
                } else {
                    toast(res.thong_bao || 'Không thể xác nhận món', 'err');
                }
            });
        },

        confirmAll: function () {
            var self = this;
            if (!self.selectedTableId) return;
            if (!confirm('Xác nhận tất cả món ở bàn này đã được phục vụ?')) return;
            postForm(BASE_URL + '/nhan-vien/xac-nhan-tat-ca', { ban_id: self.selectedTableId }, function (res) {
                if (res.success) {
                    toast(res.thong_bao || 'Đã xác nhận tất cả món');
                    self.loadOrders();
                    self.loadTables();
                } else {
                    toast(res.thong_bao || 'Không thể xác nhận tất cả', 'err');
                }
            });
        },

        markTableEmpty: function () {
            var self = this;
            if (!self.selectedTableId) return;
            if (!confirm('Xác nhận bàn này đã trống?')) return;
            postForm(BASE_URL + '/nhan-vien/xac-nhan-ban', { ban_id: self.selectedTableId }, function (res) {
                if (res.success) {
                    toast(res.thong_bao || 'Đã xác nhận bàn trống');
                    self.loadTables();
                    self.loadOrders();
                } else {
                    toast(res.thong_bao || 'Không thể cập nhật bàn', 'err');
                }
            });
        }
    };

    /* ================================================================
       StaffReservations – Quản lý đặt bàn
    ================================================================ */
    window.StaffReservations = {
        tables:          [],
        currentStatus:   '',
        currentKeyword:  '',
        choDuyetBan:     false,

        loadTables: function (cb) {
            var self = this;
            if (self.tables.length) { if (cb) cb(); return; }
            getJson(BASE_URL + '/nhan-vien/danh-sach-ban', function (res) {
                if (res.success) self.tables = res.du_lieu || [];
                if (cb) cb();
            });
        },

        load: function () {
            var keyword = el('reservationSearch') ? el('reservationSearch').value : '';
            var status  = el('reservationStatus') ? el('reservationStatus').value : '';
            var self    = this;
            self.currentKeyword = keyword;
            self.currentStatus  = status;

            var url = BASE_URL + '/nhan-vien/dat-ban/danh-sach'
                + '?trang_thai=' + encodeURIComponent(status)
                + '&tim='        + encodeURIComponent(keyword)
                + (self.choDuyetBan ? '&cho_duyet=1' : '');

            var list = el('reservationList');
            if (list) list.innerHTML = '<div class="empty-state">Đang tải danh sách đặt bàn...</div>';

            self.loadTables(function () {
                getJson(url, function (res) {
                    if (!res.success) {
                        toast(res.thong_bao || 'Không tải được danh sách đặt bàn', 'err');
                        return;
                    }
                    self.render(res.du_lieu || []);
                    self.updatePendingBanner(res.du_lieu || []);
                });
            });
        },

        /* Đếm đơn chưa xác nhận bàn và hiện banner */
        updatePendingBanner: function (items) {
            /* Chỉ đếm khi không đang lọc "chờ duyệt" để tránh vòng lặp */
            if (this.choDuyetBan) return;
            var n = 0;
            for (var i = 0; i < items.length; i++) {
                if (!Number(items[i].ban_da_xac_nhan) && items[i].ban_id && items[i].trang_thai !== 'da_huy' && items[i].trang_thai !== 'hoan_thanh') n++;
            }
            var banner = el('pendingApprovalBanner');
            var count  = el('pendingApprovalCount');
            if (banner) banner.style.display = n > 0 ? 'flex' : 'none';
            if (count)  count.textContent = n;
        },

        render: function (items) {
            var list = el('reservationList');
            if (!list) return;

            if (!items.length) {
                list.innerHTML = '<div class="empty-state">Không có đặt bàn phù hợp.</div>';
                return;
            }

            var html          = '';
            var runningGuests = [];

            for (var i = 0; i < items.length; i++) {
                var r          = items[i];
                var si         = statusInfo(r.trang_thai);
                var totalGuest = Number(r.so_nguoi_lon || 0) + Number(r.so_tre_em || 0);
                var isActive   = r.trang_thai === 'cho_xac_nhan' || r.trang_thai === 'da_xac_nhan';
                var isDone     = r.trang_thai === 'da_huy' || r.trang_thai === 'hoan_thanh';
                var cap        = Number(window.RESTAURANT_CAPACITY || 40);
                var usedBefore = this.runningOverlap(r, runningGuests);
                var remaining  = Math.max(0, cap - usedBefore - (isActive ? totalGuest : 0));
                /* 1 = nhan vien da xac nhan ban, 0 = he thong tu gan chua xac nhan */
                var banDaXacNhan = Number(r.ban_da_xac_nhan || 0) === 1;

                if (isActive) {
                    runningGuests.push({ id: r.id, ngay_dat: r.ngay_dat, gio_dat: r.gio_dat, so_khach: totalGuest });
                }

                html += '<div class="reservation-card">';

                /* ---- Phần trên: thông tin khách ---- */
                html += '<div class="reservation-card-top">';
                html += '<div class="reservation-status-bar ' + si.bar + '"></div>';
                html += '<div class="reservation-main-info">';

                html += '<div class="reservation-title-row">';
                html += '<span class="reservation-name">' + esc(r.ten_khach) + '</span>';
                html += '<span class="badge ' + si.badge + '">' + si.text + '</span>';
                /* Nếu bàn chưa được nhân viên xác nhận, hiện badge cảnh báo */
                if (!isDone && r.ban_id && !banDaXacNhan) {
                    html += '<span class="badge pending-table">&#9888; Chờ duyệt bàn</span>';
                } else if (!isDone && r.ban_id && banDaXacNhan) {
                    html += '<span class="badge confirmed-table">&#10003; Bàn đã xác nhận</span>';
                }
                html += '</div>';

                html += '<div class="reservation-meta">';
                html += '<span>&#128222; <strong>' + esc(r.sdt_khach || '-') + '</strong></span>';
                html += '<span>Mã ĐB: <strong>' + esc(r.ma_dat_ban || '-') + '</strong></span>';
                html += '<span>&#128197; ' + esc(r.ngay_dat || '-') + ' &#128336; ' + esc(r.gio_dat || '') + '</span>';
                html += '<span>&#128101; <strong>' + esc(totalGuest) + '</strong> khách</span>';
                html += '<span>Còn nhận: <strong>' + esc(remaining) + '</strong>/' + esc(cap) + '</span>';
                html += '</div>';

                if (r.ghi_chu) {
                    html += '<div class="reservation-note">&#128221; ' + esc(r.ghi_chu) + '</div>';
                }

                html += '</div>'; /* /reservation-main-info */
                html += '</div>'; /* /reservation-card-top */

                /* ---- Phần dưới: bàn được gán + hành động ---- */
                html += '<div class="reservation-card-bottom">';

                /* === KHỐI BÀN ĐƯỢC GÁN === */
                if (!isDone) {
                    if (r.ban_id && r.so_ban) {
                        /* Có bàn được gán (hệ thống tự gan hoặc nhan vien da chon) */
                        var boxCls = banDaXacNhan ? 'ban-gan-box confirmed' : 'ban-gan-box';
                        html += '<div class="' + boxCls + '">';
                        html += '<div class="ban-gan-header">';
                        if (!banDaXacNhan) {
                            html += '<span class="ban-gan-label">&#9881; Hệ thống tự gán — chờ xác nhận</span>';
                        } else {
                            html += '<span class="ban-gan-label" style="color:#166534">&#10003; Bàn đã được xác nhận</span>';
                        }
                        html += '</div>';

                        /* Viên pills cho từng bàn được gán */
                        html += '<div class="ban-gan-tables">';
                        var banArr = String(r.so_ban).split(',');
                        for (var bi = 0; bi < banArr.length; bi++) {
                            var pillCls = banDaXacNhan ? 'ban-pill confirmed-pill' : 'ban-pill';
                            html += '<span class="' + pillCls + '">&#127860; Bàn ' + esc(banArr[bi].replace(/^\s+|\s+$/g, '')) + '</span>';
                        }
                        html += '</div>';

                        /* Nút hành động bàn */
                        html += '<div class="ban-gan-actions">';
                        if (!banDaXacNhan) {
                            /* Chưa xác nhận: cho xác nhận hoặc đổi bàn */
                            html += '<button type="button" class="btn confirm-table btn-sm" onclick="StaffReservations.xacNhanBan(' + r.id + ')">&#10003; Xác nhận bàn này</button>';
                            html += '<span style="color:#92400e;font-size:12px;line-height:1.5;-ms-flex-item-align:center;align-self:center">hoặc chọn bàn khác bên dưới</span>';
                        } else {
                            /* Đã xác nhận: cho phép đổi bàn */
                            html += '<button type="button" class="btn secondary btn-sm" onclick="StaffReservations.doiBan(' + r.id + ')">&#9998; Đổi bàn</button>';
                        }
                        html += '</div>';

                        /* Dropdown đổi bàn (ẩn mặc định khi đã xác nhận, hiện khi bấm Đổi bàn) */
                        var doiBanStyle = banDaXacNhan ? 'display:none' : 'display:block';
                        html += '<div id="doi-ban-' + r.id + '" style="margin-top:10px;' + doiBanStyle + '">';
                        html += '<div style="display:-webkit-box;display:-ms-flexbox;display:flex;gap:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-align:center;-ms-flex-align:center;align-items:center">';
                        html += '<select class="table-assign-select" id="select-ban-' + r.id + '">';
                        html += '<option value="0">— Chọn bàn thay thế —</option>';
                        for (var ti = 0; ti < this.tables.length; ti++) {
                            var tb       = this.tables[ti];
                            var sel      = String(tb.id) === String(r.ban_id) ? ' selected' : '';
                            var conflict = this.tableConflict(tb, r, items);
                            var tooSmall = Number(tb.suc_chua || 0) < totalGuest;
                            var dis      = (conflict && !sel) ? ' disabled' : '';
                            var lbl      = 'Bàn ' + tb.so_ban + ' (' + (tb.suc_chua || '-') + ' khách)';
                            if (conflict) lbl += ' – ' + conflict;
                            else if (tooSmall) lbl += ' – nhỏ hơn số khách';
                            html += '<option value="' + tb.id + '"' + sel + dis + '>' + esc(lbl) + '</option>';
                        }
                        html += '</select>';
                        html += '<button type="button" class="btn btn-sm" onclick="StaffReservations.doiBanTheoSelect(' + r.id + ')">Lưu bàn mới</button>';
                        html += '<button type="button" class="btn-auto-assign" onclick="StaffReservations.assignTable(' + r.id + ', 0)">&#9881; Tự động</button>';
                        html += '</div></div>';

                        html += '</div>'; /* /ban-gan-box */

                    } else {
                        /* Chưa có bàn nào được gán */
                        html += '<div class="ban-gan-box">';
                        html += '<div class="ban-gan-header"><span class="ban-gan-label">&#9888; Chưa có bàn — cần gán thủ công</span></div>';
                        html += '<div style="display:-webkit-box;display:-ms-flexbox;display:flex;gap:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-top:6px">';
                        html += '<select class="table-assign-select" id="select-ban-' + r.id + '">';
                        html += '<option value="0">— Chọn bàn —</option>';
                        for (var tj = 0; tj < this.tables.length; tj++) {
                            var tbj      = this.tables[tj];
                            var conflictj = this.tableConflict(tbj, r, items);
                            var tooSmallj = Number(tbj.suc_chua || 0) < totalGuest;
                            var disj     = conflictj ? ' disabled' : '';
                            var lblj     = 'Bàn ' + tbj.so_ban + ' (' + (tbj.suc_chua || '-') + ' khách)';
                            if (conflictj) lblj += ' – ' + conflictj;
                            else if (tooSmallj) lblj += ' – nhỏ hơn số khách';
                            html += '<option value="' + tbj.id + '"' + disj + '>' + esc(lblj) + '</option>';
                        }
                        html += '</select>';
                        html += '<button type="button" class="btn btn-sm" onclick="StaffReservations.doiBanTheoSelect(' + r.id + ')">Gán bàn</button>';
                        html += '<button type="button" class="btn-auto-assign" onclick="StaffReservations.assignTable(' + r.id + ', 0)">&#9881; Tự động</button>';
                        html += '</div>';
                        html += '</div>'; /* /ban-gan-box */
                    }
                } else {
                    /* Đơn đã hủy / hoàn thành */
                    html += '<div style="font-size:12px;color:#64748b;padding:6px 0">Không giữ bàn (đơn đã kết thúc)</div>';
                }

                /* === NÚT HÀNH ĐỘNG TRẠNG THÁI === */
                html += '<div class="reservation-actions" style="margin-top:8px">';
                html += this.actionBtn(r.id, 'da_xac_nhan', '&#10003; Xác nhận ĐB', r.trang_thai, '');
                html += this.actionBtn(r.id, 'da_huy',      '&#10007; Hủy',         r.trang_thai, 'danger');
                html += this.actionBtn(r.id, 'hoan_thanh',  'Hoàn thành',           r.trang_thai, 'secondary');
                html += '</div>';

                html += '</div>'; /* /reservation-card-bottom */
                html += '</div>'; /* /reservation-card */
            }

            list.innerHTML = html;
        },

        /* Xác nhận bàn do hệ thống tự gán */
        xacNhanBan: function (id) {
            var self = this;
            postForm(BASE_URL + '/nhan-vien/dat-ban/xac-nhan-gan-ban', { id: id }, function (res) {
                if (res.success) {
                    toast(res.thong_bao || 'Đã xác nhận bàn');
                    self.load();
                } else {
                    toast(res.thong_bao || 'Không thể xác nhận bàn', 'err');
                }
            });
        },

        /* Hiện dropdown đổi bàn khi đã xác nhận */
        doiBan: function (id) {
            var box = el('doi-ban-' + id);
            if (box) box.style.display = box.style.display === 'none' ? 'block' : 'none';
        },

        /* Lấy giá trị select rồi gán bàn */
        doiBanTheoSelect: function (id) {
            var sel = el('select-ban-' + id);
            var tableId = sel ? sel.value : 0;
            this.assignTable(id, tableId);
        },

        actionBtn: function (id, status, label, current, variant) {
            var dis = current === status ? ' disabled' : '';
            var cls = 'btn btn-sm' + (variant ? ' ' + variant : '');
            return '<button type="button" class="' + cls + '" onclick="StaffReservations.updateStatus(' + id + ',\'' + status + '\')"' + dis + '>' + label + '</button>';
        },

        tableConflict: function (table, reservation, all) {
            for (var i = 0; i < all.length; i++) {
                var other = all[i];
                if (String(other.id) === String(reservation.id)) continue;
                if (other.trang_thai !== 'cho_xac_nhan' && other.trang_thai !== 'da_xac_nhan') continue;
                if (other.ngay_dat !== reservation.ngay_dat) continue;
                if (!this.reservationHasTable(other, table.id)) continue;
                if (Math.abs(this.toMin(other.gio_dat) - this.toMin(reservation.gio_dat)) < 90) return 'trùng giờ';
            }
            return '';
        },

        reservationHasTable: function (r, tableId) {
            if (String(r.ban_id || '') === String(tableId)) return true;
            var ids = String(r.ban_ids || '').split(',');
            for (var i = 0; i < ids.length; i++) {
                if (ids[i].replace(/^\s+|\s+$/g, '') === String(tableId)) return true;
            }
            return false;
        },

        toMin: function (v) {
            var p = String(v || '00:00').substring(0, 5).split(':');
            return Number(p[0] || 0) * 60 + Number(p[1] || 0);
        },

        runningOverlap: function (r, running) {
            var total = 0;
            for (var i = 0; i < running.length; i++) {
                var o = running[i];
                if (o.ngay_dat !== r.ngay_dat) continue;
                if (Math.abs(this.toMin(o.gio_dat) - this.toMin(r.gio_dat)) < 90)
                    total += Number(o.so_khach || 0);
            }
            return total;
        },

        updateStatus: function (id, status) {
            var self = this;
            postForm(BASE_URL + '/nhan-vien/cap-nhat-dat-ban', { id: id, trang_thai: status }, function (res) {
                if (res.success) {
                    toast(res.thong_bao || 'Đã cập nhật đặt bàn');
                    self.load();
                } else {
                    toast(res.thong_bao || 'Không thể cập nhật đặt bàn', 'err');
                }
            });
        },

        assignTable: function (id, tableId) {
            var self = this;
            postForm(BASE_URL + '/nhan-vien/dat-ban/gan-ban', { id: id, ban_id: tableId }, function (res) {
                if (res.success) {
                    toast(res.thong_bao || 'Đã gán bàn');
                    self.tables = [];
                    self.load();
                    StaffOrders.loadTables();
                } else {
                    toast(res.thong_bao || 'Không thể gán bàn', 'err');
                }
            });
        },

        setStatus: function (status, keyword, choDuyet) {
            var sel = el('reservationStatus');
            if (sel) sel.value = status || '';
            var srch = el('reservationSearch');
            if (srch && keyword !== undefined) srch.value = keyword;
            this.choDuyetBan = !!choDuyet;

            var chips = document.querySelectorAll('.chip');
            for (var i = 0; i < chips.length; i++) {
                chips[i].className = chips[i].className.replace(' active', '');
                var chipStatus = chips[i].getAttribute('data-status');
                var chipDuyet  = chips[i].getAttribute('data-duyet');
                var matchStatus = (chipStatus === (status || ''));
                var matchDuyet  = choDuyet ? chipDuyet === '1' : chipDuyet !== '1';
                if (matchStatus && matchDuyet) chips[i].className += ' active';
            }
            this.load();
        }
    };

    /* ================================================================
       Init
    ================================================================ */
    function init() {
        /* Tìm kiếm bàn */
        var tableSearch = el('tableSearch');
        if (tableSearch) {
            tableSearch.onkeyup = tableSearch.oninput = function () {
                StaffOrders.renderTables();
            };
        }

        /* Tìm kiếm đặt bàn */
        var resSearch = el('reservationSearch');
        if (resSearch) {
            resSearch.onkeyup = resSearch.oninput = function () {
                clearTimeout(window.__resTimer);
                window.__resTimer = setTimeout(function () { StaffReservations.load(); }, 280);
            };
        }

        /* Select trạng thái đặt bàn */
        var resSel = el('reservationStatus');
        if (resSel) {
            resSel.onchange = function () { StaffReservations.setStatus(this.value); };
        }

        /* Chip tabs đặt bàn */
        var chips = document.querySelectorAll('.chip');
        for (var i = 0; i < chips.length; i++) {
            chips[i].onclick = function () {
                var s      = this.getAttribute('data-status') || '';
                var duyet  = this.getAttribute('data-duyet') === '1';
                StaffReservations.setStatus(s, '', duyet);
            };
        }

        /* Tải bàn lần đầu */
        StaffOrders.loadTables();

        /* Tự động refresh mỗi 8 giây */
        setInterval(function () {
            StaffOrders.loadTables();
            if (StaffOrders.selectedTableId) StaffOrders.loadOrders();
        }, 8000);
    }

    if (window.addEventListener) {
        window.addEventListener('load', init, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', init);
    }

})();
