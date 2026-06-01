/* StaffReservations render helpers. Depends on nhanvien-reservations-core.js. */
(function () {
  var ui = window.StaffUI;
  var esc = ui.esc;
  var el = ui.el;
  var fmtDate = ui.fmtDate;
  var statusInfo = ui.statusInfo;
  var R = window.StaffReservations;
    R.render = function (items) {
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
        var rid = this.jsArg(r.id);
        html += '<button type="button" class="reservation-summary" onclick="StaffReservations.toggle(' + rid + ')">';
        html += '<span class="reservation-caret">' + (expanded ? "Thu gọn" : "Mở") + "</span>";
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
        html += '<p>SĐT: ' + esc(r.sdt_khach || "-") + "</p>";
        html += '<p>Số khách: ' + esc(totalGuest) + " khách</p>";
        if (r.ghi_chu) html += '<p class="reservation-note">Ghi chú: ' + esc(r.ghi_chu) + "</p>";
        html += "</div>";

        html += '<div class="reservation-detail-block">';
        html += '<span class="detail-label">Thời gian & mã</span>';
        html += '<strong>Ngày: ' + esc(fmtDate(r.ngay_dat) || "-") + "</strong>";
        html += '<p>Giờ: ' + esc(String(r.gio_dat || "").substring(0, 5)) + "</p>";
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
          html += '<button type="button" class="btn secondary" onclick="StaffReservations.doiBan(' + rid + ')">Đổi bàn</button>';
          if (choDuyetBan) {
            html += '<button type="button" class="btn" onclick="StaffReservations.xacNhanBan(' + rid + ')">Duyệt đặt bàn</button>';
          } else {
            html += this.actionBtn(r.id, "da_xac_nhan", "Xác nhận ĐB", r.trang_thai, "");
          }
          html += '<button type="button" class="btn danger" onclick="StaffReservations.toggleCancelChoices(' + rid + ')">Hủy đặt bàn</button>';
        }
        html += "</div>";
        html += '<div class="reservation-cancel-choices" style="display:' + (String(this.cancelChoiceId) === String(r.id) ? "flex" : "none") + '">';
        html += '<button type="button" class="btn danger btn-sm" onclick="StaffReservations.updateStatus(' + rid + ",'cancelled'" + ')">Khách hủy</button>';
        html += '<button type="button" class="btn secondary btn-sm" onclick="StaffReservations.updateStatus(' + rid + ",'expired'" + ')">Khách không tới</button>';
        html += "</div>";
        html += "</div>";
        html += "</div>";
      }

      list.innerHTML = html;
    };
    R.toggle = function (id) {
      this.expandedId = String(this.expandedId) === String(id) ? -1 : id;
      this.load();
    };
    R.toggleCancelChoices = function (id) {
      this.cancelChoiceId = String(this.cancelChoiceId) === String(id) ? 0 : id;
      this.expandedId = id;
      this.load();
    };
    R.renderTablePills = function (tableText, banDaXacNhan) {
      var html = "";
      var banArr = String(tableText || "").split(",");
      for (var bi = 0; bi < banArr.length; bi++) {
        html += '<span class="' + (banDaXacNhan ? "ban-pill confirmed-pill" : "ban-pill") + '">Bàn ' + esc(banArr[bi].replace(/^\s+|\s+$/g, "")) + "</span>";
      }
      return html;
    };
    R.renderAssignedTable = function (r, items, totalGuest, banDaXacNhan) {
      var html = "";
      var boxCls = banDaXacNhan ? "ban-gan-box confirmed" : "ban-gan-box";
      html += '<div class="' + boxCls + '">';
      html += '<div class="ban-gan-header">';
      html += banDaXacNhan
        ? '<span class="ban-gan-label" style="color:#166534">Bàn đã được xác nhận</span>'
        : '<span class="ban-gan-label">Hệ thống tự gán - chờ xác nhận</span>';
      html += "</div>";

      html += '<div class="ban-gan-tables">';
      html += this.renderTablePills(r.so_ban, banDaXacNhan);
      html += "</div>";

      html += this.renderTableSelect(r, items, totalGuest, "display:none", "Lưu bàn mới");
      html += "</div>";
      return html;
    };
    R.renderManualAssign = function (r, items, totalGuest) {
      var html = "";
      html += '<div class="ban-gan-box">';
      html += '<div class="ban-gan-header"><span class="ban-gan-label">Chưa có bàn - cần gán thủ công</span></div>';
      html += this.renderTableSelect(r, items, totalGuest, "display:block", "Gán bàn");
      html += "</div>";
      return html;
    };
    R.renderTableSelect = function (r, items, totalGuest, displayStyle, buttonText) {
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
      var rid = this.jsArg(r.id);
      html += '<button type="button" class="btn btn-sm" onclick="StaffReservations.doiBanTheoSelect(' + rid + ')">' + buttonText + "</button>";
      html += '<button type="button" class="btn-auto-assign" onclick="StaffReservations.assignTable(' + rid + ', 0)">Tự động</button>';
      html += "</div></div>";
      return html;
    };})();
