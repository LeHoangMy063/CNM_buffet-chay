/* StaffReservations actions. Depends on nhanvien-reservations-core.js and render.js. */
(function () {
  var ui = window.StaffUI;
  var esc = ui.esc;
  var toast = ui.toast;
  var postForm = ui.postForm;
  var el = ui.el;
  var R = window.StaffReservations;

    R.xacNhanBan = function (id) {
      var self = this;
      postForm(BASE_URL + "/nhan-vien/dat-ban/xac-nhan-gan-ban", { id: id }, function (res) {
        if (res.success) {
          toast(res.thong_bao || "Đã duyệt đặt bàn");
          self.load();
        } else {
          toast(res.thong_bao || "Không thể xác nhận bàn", "err");
        }
      });
    };
    R.doiBan = function (id) {
      var box = el("doi-ban-" + id);
      if (box) box.style.display = box.style.display === "none" ? "block" : "none";
    };
    R.doiBanTheoSelect = function (id) {
      var sel = el("select-ban-" + id);
      var tableId = sel ? sel.value : 0;
      this.assignTable(id, tableId);
    };
    R.actionBtn = function (id, status, label, current, variant) {
      var dis = current === status ? " disabled" : "";
      var cls = "btn btn-sm" + (variant ? " " + variant : "");
      return '<button type="button" class="' + cls + '" onclick="StaffReservations.updateStatus(' + this.jsArg(id) + ",'" + status + "')" + '"' + dis + ">" + label + "</button>";
    };
    R.tableConflict = function (table, reservation, all) {
      for (var i = 0; i < all.length; i++) {
        var other = all[i];
        if (String(other.id) === String(reservation.id)) continue;
        if (other.trang_thai !== "cho_xac_nhan" && other.trang_thai !== "da_xac_nhan") continue;
        if (other.ngay_dat !== reservation.ngay_dat) continue;
        if (!this.reservationHasTable(other, table.id)) continue;
        if (Math.abs(this.toMin(other.gio_dat) - this.toMin(reservation.gio_dat)) < 90) return "trùng giờ";
      }
      return "";
    };
    R.reservationHasTable = function (r, tableId) {
      if (String(r.ban_id || "") === String(tableId)) return true;
      var ids = String(r.ban_ids || "").split(",");
      for (var i = 0; i < ids.length; i++) {
        if (ids[i].replace(/^\s+|\s+$/g, "") === String(tableId)) return true;
      }
      return false;
    };
    R.toMin = function (v) {
      var p = String(v || "00:00").substring(0, 5).split(":");
      return Number(p[0] || 0) * 60 + Number(p[1] || 0);
    };
    R.runningOverlap = function (r, running) {
      var total = 0;
      for (var i = 0; i < running.length; i++) {
        var o = running[i];
        if (o.ngay_dat !== r.ngay_dat) continue;
        if (Math.abs(this.toMin(o.gio_dat) - this.toMin(r.gio_dat)) < 90) total += Number(o.so_khach || 0);
      }
      return total;
    };
    R.updateStatus = function (id, status) {
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
    };
    R.assignTable = function (id, tableId) {
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
    };
    R.setStatus = function (status, keyword, choDuyet) {
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
})();
