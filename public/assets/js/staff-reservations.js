/* staff-reservations.js
 * StaffReservations module. Depends on staff-dashboard.js for StaffUI helpers.
 */
(function () {
  var ui = window.StaffUI;
  var esc = ui.esc;
  var toast = ui.toast;
  var postForm = ui.postForm;
  var getJson = ui.getJson;
  var el = ui.el;
  var statusInfo = ui.statusInfo;

  window.StaffReservations = {
    tables: [],
    currentStatus: "",
    currentKeyword: "",
    choDuyetBan: false,
    expandedId: 0,
    cancelChoiceId: 0,
    selectedDate: "",
    calendarMonth: "",
    calendarCounts: {},
    lockItems: [],

    loadTables: function (cb) {
      var self = this;
      if (self.tables.length) {
        if (cb) cb();
        return;
      }
      getJson(BASE_URL + "/nhan-vien/danh-sach-ban", function (res) {
        if (res.success) self.tables = res.du_lieu || [];
        if (cb) cb();
      });
    },

    load: function () {
      var keyword = el("reservationSearch") ? el("reservationSearch").value : "";
      var status = el("reservationStatus") ? el("reservationStatus").value : "";
      var self = this;
      self.currentKeyword = keyword;
      self.currentStatus = status;

      var url =
        BASE_URL +
        "/nhan-vien/dat-ban/danh-sach?trang_thai=" +
        encodeURIComponent(status) +
        "&tim=" +
        encodeURIComponent(keyword) +
        "&ngay=" +
        encodeURIComponent(self.selectedDate || "") +
        (self.choDuyetBan ? "&cho_duyet=1" : "");

      var list = el("reservationList");
      if (list) list.innerHTML = '<div class="empty-state">Đang tải danh sách đặt bàn...</div>';

      self.loadTables(function () {
        self.loadCalendar();
        getJson(url, function (res) {
          if (!res.success) {
            toast(res.thong_bao || "Không tải được danh sách đặt bàn", "err");
            return;
          }
          var visibleItems = res.du_lieu || [];
          getJson(BASE_URL + "/nhan-vien/dat-ban/danh-sach?trang_thai=&tim=&ngay=", function (lockRes) {
            self.lockItems = lockRes.success ? (lockRes.du_lieu || []) : visibleItems;
            self.render(visibleItems);
            self.updatePendingBanner(visibleItems);
          });
        });
      });
    },

    loadCalendar: function () {
      var self = this;
      if (!self.calendarMonth) self.calendarMonth = this.monthKey(new Date());
      getJson(BASE_URL + "/nhan-vien/dat-ban/lich?thang=" + encodeURIComponent(self.calendarMonth), function (res) {
        var map = {};
        var rows = res.success ? (res.du_lieu || []) : [];
        for (var i = 0; i < rows.length; i++) {
          map[rows[i].ngay_dat] = Number(rows[i].tong || 0);
        }
        self.calendarCounts = map;
        self.renderCalendar();
      });
    },

    renderCalendar: function () {
      var box = el("reservationCalendar");
      if (!box) return;
      var parts = String(this.calendarMonth || this.monthKey(new Date())).split("-");
      var year = Number(parts[0]);
      var month = Number(parts[1]);
      var first = new Date(year, month - 1, 1);
      var lastDay = new Date(year, month, 0).getDate();
      var start = first.getDay();
      start = start === 0 ? 6 : start - 1;
      var title = el("reservationCalendarTitle");
      if (title) title.textContent = "Tháng " + month + "/" + year;

      var html = "";
      for (var blank = 0; blank < start; blank++) html += '<span class="calendar-day empty"></span>';
      for (var day = 1; day <= lastDay; day++) {
        var d = year + "-" + (month < 10 ? "0" : "") + month + "-" + (day < 10 ? "0" : "") + day;
        var count = this.calendarCounts[d] || 0;
        var cls = "calendar-day" + (count > 0 ? " has-reservation" : "") + (this.selectedDate === d ? " selected" : "");
        html += '<button type="button" class="' + cls + '" onclick="StaffReservations.selectDate(\'' + d + '\')">';
        html += '<span>' + day + "</span>";
        if (count > 0) html += '<small>' + count + "</small>";
        html += "</button>";
      }
      box.innerHTML = html;
    },

    monthKey: function (dateObj) {
      var y = dateObj.getFullYear();
      var m = dateObj.getMonth() + 1;
      return y + "-" + (m < 10 ? "0" : "") + m;
    },

    changeMonth: function (step) {
      var p = String(this.calendarMonth || this.monthKey(new Date())).split("-");
      var d = new Date(Number(p[0]), Number(p[1]) - 1 + step, 1);
      this.calendarMonth = this.monthKey(d);
      this.selectedDate = "";
      this.load();
    },

    selectDate: function (dateValue) {
      this.selectedDate = this.selectedDate === dateValue ? "" : dateValue;
      this.expandedId = 0;
      this.load();
    },

    updatePendingBanner: function (items) {
      if (this.choDuyetBan) return;

      var n = 0;
      for (var i = 0; i < items.length; i++) {
        if (
          items[i].trang_thai === "cho_xac_nhan"
        ) {
          n++;
        }
      }

      var banner = el("pendingApprovalBanner");
      var count = el("pendingApprovalCount");
      if (banner) banner.style.display = n > 0 ? "flex" : "none";
      if (count) count.textContent = n;
    },

    render: function (items) {
      var list = el("reservationList");
      if (!list) return;

      if (!items.length) {
        list.innerHTML = '<div class="empty-state">Không có đặt bàn phù hợp.</div>';
        return;
      }

      var html = "";
      var runningGuests = [];

      for (var i = 0; i < items.length; i++) {
        var r = items[i];
        var si = statusInfo(r.trang_thai);
        var totalGuest = Number(r.so_nguoi_lon || 0) + Number(r.so_tre_em || 0);
        var isActive = r.trang_thai === "cho_xac_nhan" || r.trang_thai === "da_xac_nhan";
        var isDone = r.trang_thai === "da_huy" || r.trang_thai === "cancelled" || r.trang_thai === "expired" || r.trang_thai === "hoan_thanh";
        var cap = Number(window.RESTAURANT_CAPACITY || 40);
        var usedBefore = this.runningOverlap(r, runningGuests);
        var remaining = Math.max(0, cap - usedBefore - (isActive ? totalGuest : 0));
        var banDaXacNhan = Number(r.ban_da_xac_nhan || 0) === 1;
        var choDuyetBan = !isDone && r.trang_thai === "cho_xac_nhan";

        if (isActive) {
          runningGuests.push({
            id: r.id,
            ngay_dat: r.ngay_dat,
            gio_dat: r.gio_dat,
            so_khach: totalGuest
          });
        }

        var expanded = this.expandedId === -1 ? false : (this.expandedId ? String(this.expandedId) === String(r.id) : i === 0);
        var cardCls = "reservation-accordion-card" + (expanded ? " expanded" : "");
        var dotCls = (r.trang_thai === "da_huy" || r.trang_thai === "cancelled") ? "danger" : (choDuyetBan ? "warn" : (r.trang_thai === "da_xac_nhan" ? "ok" : "warn"));
        var badgeText = choDuyetBan ? "Chờ duyệt bàn" : si.text;
        var badgeClass = choDuyetBan ? "warn" : si.badge;
        var tablePill = isDone ? "" : (r.so_ban ? this.renderTablePills(r.so_ban, banDaXacNhan) : '<span class="ban-pill empty-pill">Chưa gán bàn</span>');
        var tableBox = "";
        if (!isDone) {
          if (r.ban_id && r.so_ban) {
            tableBox = this.renderAssignedTable(r, items, totalGuest, banDaXacNhan);
          } else {
            tableBox = this.renderManualAssign(r, items, totalGuest);
          }
        } else {
          tableBox = "";
        }

        html += '<div class="' + cardCls + '">';
        html += '<button type="button" class="reservation-summary" onclick="StaffReservations.toggle(' + r.id + ')">';
        html += '<span class="reservation-caret">' + (expanded ? "&#9662;" : "&#9656;") + "</span>";
        html += '<span class="reservation-status-dot-mini ' + dotCls + '"></span>';
        html += '<span class="reservation-summary-name">' + esc(r.ten_khach || "-") + "</span>";
        html += '<span class="reservation-summary-table">' + tablePill + "</span>";
        html += '<span class="badge ' + badgeClass + '">' + badgeText + "</span>";
        html += "</button>";

        html += '<div class="reservation-detail" style="display:' + (expanded ? "block" : "none") + '">';
        html += '<div class="reservation-detail-grid">';
        html += '<div class="reservation-detail-block">';
        html += '<span class="detail-label">Thông tin khách</span>';
        html += '<strong>' + esc(r.ten_khach || "-") + "</strong>";
        html += '<p>&#128222; ' + esc(r.sdt_khach || "-") + "</p>";
        html += '<p>&#128101; ' + esc(totalGuest) + " khách</p>";
        if (r.ghi_chu) html += '<p class="reservation-note">&#128221; ' + esc(r.ghi_chu) + "</p>";
        html += "</div>";

        html += '<div class="reservation-detail-block">';
        html += '<span class="detail-label">Thời gian & mã</span>';
        html += '<strong>&#128197; ' + esc(r.ngay_dat || "-") + "</strong>";
        html += '<p>&#128336; ' + esc(String(r.gio_dat || "").substring(0, 5)) + "</p>";
        html += '<p class="reservation-code-chip">' + esc(r.ma_dat_ban || "-") + "</p>";
        html += "</div>";

        html += '<div class="reservation-detail-block reservation-table-block">';
        html += '<span class="detail-label">Bàn được gán</span>';
        html += tableBox;
        html += '<span class="detail-label slot-label">Slot còn nhận</span>';
        html += '<strong>' + esc(remaining) + " / " + esc(cap) + "</strong>";
        html += '<div class="slot-meter"><span style="width:' + esc(Math.min(100, Math.round((remaining / cap) * 100))) + '%"></span></div>';
        html += "</div>";
        html += "</div>";

        html += '<div class="reservation-detail-actions">';
        if (!isDone) {
          html += '<button type="button" class="btn secondary" onclick="StaffReservations.doiBan(' + r.id + ')">&#8644; Đổi bàn</button>';
          if (choDuyetBan) {
            html += '<button type="button" class="btn" onclick="StaffReservations.xacNhanBan(' + r.id + ')">&#10003; Xác nhận đặt bàn</button>';
          } else {
            html += this.actionBtn(r.id, "da_xac_nhan", "&#10003; Xác nhận ĐB", r.trang_thai, "");
          }
          html += '<button type="button" class="btn danger" onclick="StaffReservations.toggleCancelChoices(' + r.id + ')">&#10007; Hủy đặt bàn</button>';
        }
        html += "</div>";
        html += '<div class="reservation-cancel-choices" style="display:' + (String(this.cancelChoiceId) === String(r.id) ? "flex" : "none") + '">';
        html += '<button type="button" class="btn danger btn-sm" onclick="StaffReservations.updateStatus(' + r.id + ",'cancelled'" + ')">Khách hủy</button>';
        html += '<button type="button" class="btn secondary btn-sm" onclick="StaffReservations.updateStatus(' + r.id + ",'expired'" + ')">Khách không tới</button>';
        html += "</div>";
        html += "</div>";
        html += "</div>";
      }

      list.innerHTML = html;
    },

    toggle: function (id) {
      this.expandedId = String(this.expandedId) === String(id) ? -1 : id;
      this.load();
    },

    toggleCancelChoices: function (id) {
      this.cancelChoiceId = String(this.cancelChoiceId) === String(id) ? 0 : id;
      this.expandedId = id;
      this.load();
    },

    renderTablePills: function (tableText, banDaXacNhan) {
      var html = "";
      var banArr = String(tableText || "").split(",");
      for (var bi = 0; bi < banArr.length; bi++) {
        html += '<span class="' + (banDaXacNhan ? "ban-pill confirmed-pill" : "ban-pill") + '">&#8862; Bàn ' + esc(banArr[bi].replace(/^\s+|\s+$/g, "")) + "</span>";
      }
      return html;
    },

    renderAssignedTable: function (r, items, totalGuest, banDaXacNhan) {
      var html = "";
      var boxCls = banDaXacNhan ? "ban-gan-box confirmed" : "ban-gan-box";
      html += '<div class="' + boxCls + '">';
      html += '<div class="ban-gan-header">';
      html += banDaXacNhan
        ? '<span class="ban-gan-label" style="color:#166534">&#10003; Bàn đã được xác nhận</span>'
        : '<span class="ban-gan-label">&#9881; Hệ thống tự gán - chờ xác nhận</span>';
      html += "</div>";

      html += '<div class="ban-gan-tables">';
      html += this.renderTablePills(r.so_ban, banDaXacNhan);
      html += "</div>";

      html += this.renderTableSelect(r, items, totalGuest, "display:none", "Lưu bàn mới");
      html += "</div>";
      return html;
    },

    renderManualAssign: function (r, items, totalGuest) {
      var html = "";
      html += '<div class="ban-gan-box">';
      html += '<div class="ban-gan-header"><span class="ban-gan-label">&#9888; Chưa có bàn - cần gán thủ công</span></div>';
      html += this.renderTableSelect(r, items, totalGuest, "display:block", "Gán bàn");
      html += "</div>";
      return html;
    },

    renderTableSelect: function (r, items, totalGuest, displayStyle, buttonText) {
      var html = "";
      html += '<div id="doi-ban-' + r.id + '" style="margin-top:10px;' + displayStyle + '">';
      html += '<div style="display:-webkit-box;display:-ms-flexbox;display:flex;gap:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-align:center;-ms-flex-align:center;align-items:center">';
      html += '<select class="table-assign-select" id="select-ban-' + r.id + '">';
      html += '<option value="0">-- Chọn bàn --</option>';

      for (var ti = 0; ti < this.tables.length; ti++) {
        var tb = this.tables[ti];
        var sel = String(tb.id) === String(r.ban_id || "") ? " selected" : "";
        var conflict = this.tableConflict(tb, r, this.lockItems && this.lockItems.length ? this.lockItems : items);
        var tooSmall = Number(tb.suc_chua || 0) < totalGuest;
        var dis = conflict && !sel ? " disabled" : "";
        var lbl = "Bàn " + tb.so_ban + " (" + (tb.suc_chua || "-") + " khách)";
        if (conflict) lbl += " - " + conflict;
        else if (tooSmall) lbl += " - nhỏ hơn số khách";
        html += '<option value="' + tb.id + '"' + sel + dis + ">" + esc(lbl) + "</option>";
      }

      html += "</select>";
      html += '<button type="button" class="btn btn-sm" onclick="StaffReservations.doiBanTheoSelect(' + r.id + ')">' + buttonText + "</button>";
      html += '<button type="button" class="btn-auto-assign" onclick="StaffReservations.assignTable(' + r.id + ', 0)">&#9881; Tự động</button>';
      html += "</div></div>";
      return html;
    },

    xacNhanBan: function (id) {
      var self = this;
      postForm(BASE_URL + "/nhan-vien/dat-ban/xac-nhan-gan-ban", { id: id }, function (res) {
        if (res.success) {
          toast(res.thong_bao || "Đã xác nhận đặt bàn");
          self.load();
        } else {
          toast(res.thong_bao || "Không thể xác nhận bàn", "err");
        }
      });
    },

    doiBan: function (id) {
      var box = el("doi-ban-" + id);
      if (box) box.style.display = box.style.display === "none" ? "block" : "none";
    },

    doiBanTheoSelect: function (id) {
      var sel = el("select-ban-" + id);
      var tableId = sel ? sel.value : 0;
      this.assignTable(id, tableId);
    },

    actionBtn: function (id, status, label, current, variant) {
      var dis = current === status ? " disabled" : "";
      var cls = "btn btn-sm" + (variant ? " " + variant : "");
      return '<button type="button" class="' + cls + '" onclick="StaffReservations.updateStatus(' + id + ",'" + status + "')" + '"' + dis + ">" + label + "</button>";
    },

    tableConflict: function (table, reservation, all) {
      for (var i = 0; i < all.length; i++) {
        var other = all[i];
        if (String(other.id) === String(reservation.id)) continue;
        if (other.trang_thai !== "cho_xac_nhan" && other.trang_thai !== "da_xac_nhan") continue;
        if (other.ngay_dat !== reservation.ngay_dat) continue;
        if (!this.reservationHasTable(other, table.id)) continue;
        if (Math.abs(this.toMin(other.gio_dat) - this.toMin(reservation.gio_dat)) < 90) return "trùng giờ";
      }
      return "";
    },

    reservationHasTable: function (r, tableId) {
      if (String(r.ban_id || "") === String(tableId)) return true;
      var ids = String(r.ban_ids || "").split(",");
      for (var i = 0; i < ids.length; i++) {
        if (ids[i].replace(/^\s+|\s+$/g, "") === String(tableId)) return true;
      }
      return false;
    },

    toMin: function (v) {
      var p = String(v || "00:00").substring(0, 5).split(":");
      return Number(p[0] || 0) * 60 + Number(p[1] || 0);
    },

    runningOverlap: function (r, running) {
      var total = 0;
      for (var i = 0; i < running.length; i++) {
        var o = running[i];
        if (o.ngay_dat !== r.ngay_dat) continue;
        if (Math.abs(this.toMin(o.gio_dat) - this.toMin(r.gio_dat)) < 90) total += Number(o.so_khach || 0);
      }
      return total;
    },

    updateStatus: function (id, status) {
      var self = this;
      self.cancelChoiceId = 0;
      postForm(BASE_URL + "/nhan-vien/cap-nhat-dat-ban", { id: id, trang_thai: status }, function (res) {
        if (res.success) {
          toast(res.thong_bao || "Đã cập nhật đặt bàn");
          self.load();
        } else {
          toast(res.thong_bao || "Không thể cập nhật đặt bàn", "err");
        }
      });
    },

    assignTable: function (id, tableId) {
      var self = this;
      postForm(BASE_URL + "/nhan-vien/dat-ban/gan-ban", { id: id, ban_id: tableId }, function (res) {
        if (res.success) {
          toast(res.thong_bao || "Đã gán bàn");
          self.tables = [];
          self.load();
          StaffOrders.loadTables();
        } else {
          toast(res.thong_bao || "Không thể gán bàn", "err");
        }
      });
    },

    setStatus: function (status, keyword, choDuyet) {
      var sel = el("reservationStatus");
      if (sel) sel.value = status || "";

      var srch = el("reservationSearch");
      if (srch && keyword !== undefined) srch.value = keyword;

      this.choDuyetBan = !!choDuyet;

      var chips = document.querySelectorAll(".chip");
      for (var i = 0; i < chips.length; i++) {
        chips[i].className = chips[i].className.replace(" active", "");
        var chipStatus = chips[i].getAttribute("data-status");
        var chipDuyet = chips[i].getAttribute("data-duyet");
        var matchStatus = chipStatus === (status || "");
        var matchDuyet = choDuyet ? chipDuyet === "1" : chipDuyet !== "1";
        if (matchStatus && matchDuyet) chips[i].className += " active";
      }

      this.load();
    }
  };
})();
