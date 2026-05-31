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

    jsArg: function (value) {
      return "'" + String(value == null ? "" : value).replace(/\\/g, "\\\\").replace(/'/g, "\\'") + "'";
    },

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
    }
  };
})();
