/* staff-dashboard.js
 * Compatible: IE9+, PHP 5.2.6 project - no transpile needed.
 */
(function () {
  function esc(v) {
    return String(v == null ? "" : v)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function toast(msg, type) {
    var e = document.getElementById("toast");
    if (!e) return;
    e.textContent = msg;
    e.className = "toast show" + (type === "err" ? " err" : "");
    clearTimeout(e._t);
    e._t = setTimeout(function () {
      e.className = "toast";
    }, 3400);
  }

  function postForm(url, data, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      try {
        cb(JSON.parse(xhr.responseText));
      } catch (e) {
        cb({ success: false, thong_bao: "Phản hồi server không hợp lệ" });
      }
    };
    xhr.onerror = function () {
      cb({ success: false, thong_bao: "Không kết nối được máy chủ" });
    };
    var parts = [];
    for (var k in data) {
      if (data.hasOwnProperty(k)) {
        parts.push(encodeURIComponent(k) + "=" + encodeURIComponent(data[k]));
      }
    }
    xhr.send(parts.join("&"));
  }

  function getJson(url, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.onload = function () {
      try {
        cb(JSON.parse(xhr.responseText));
      } catch (e) {
        cb({ success: false, thong_bao: "Phản hồi server không hợp lệ" });
      }
    };
    xhr.onerror = function () {
      cb({ success: false, thong_bao: "Không kết nối được máy chủ" });
    };
    xhr.send();
  }

  function el(id) {
    return document.getElementById(id);
  }

  function setText(id, v) {
    var e = el(id);
    if (e) e.textContent = v;
  }

  function fmtTime() {
    var d = new Date();
    var h = d.getHours();
    var m = d.getMinutes();
    return (h < 10 ? "0" : "") + h + ":" + (m < 10 ? "0" : "") + m;
  }

  function statusInfo(s) {
    var map = {
      cho_xac_nhan: {
        text: "Chờ duyệt bàn",
        badge: "warn",
        bar: "bar-cho_xac_nhan",
      },
      da_xac_nhan: { text: "Đã xác nhận", badge: "ok", bar: "bar-da_xac_nhan" },
      da_huy: { text: "Khách hủy", badge: "danger", bar: "bar-da_huy" },
      cancelled: { text: "Khách hủy", badge: "danger", bar: "bar-da_huy" },
      expired: {
        text: "Khách không tới",
        badge: "muted",
        bar: "bar-hoan_thanh",
      },
      hoan_thanh: { text: "Hoàn thành", badge: "muted", bar: "bar-hoan_thanh" },
    };
    return map[s] || { text: s || "-", badge: "muted", bar: "" };
  }

  function tableStatusInfo(s) {
    var map = {
      trong: { text: "Trống", badge: "ok", cls: "empty" },
      dang_dung: { text: "Đang dùng", badge: "warn", cls: "busy" },
      cho_thanh_toan: { text: "Chờ thanh toán", badge: "blue", cls: "paying" },
      da_thanh_toan: { text: "Đã thanh toán", badge: "muted", cls: "paid" },
    };
    return map[s] || { text: s || "-", badge: "muted", cls: "empty" };
  }

  window.StaffUI = {
    esc: esc,
    toast: toast,
    postForm: postForm,
    getJson: getJson,
    el: el,
    setText: setText,
    fmtTime: fmtTime,
    statusInfo: statusInfo,
    tableStatusInfo: tableStatusInfo,
  };

  window.StaffDashboard = {
    currentSection: "home",
  };

  window.StaffTabs = {
    titles: {
      home: "Trang chủ nhân viên",
      "dat-ban": "Quản lý bàn",
    },
    show: function (name) {
      StaffDashboard.currentSection = name;

      var sections = document.querySelectorAll(".staff-section");
      for (var i = 0; i < sections.length; i++) {
        sections[i].className = sections[i].className
          .replace(" active", "")
          .replace("active", "");
      }

      var links = document.querySelectorAll(".side-link[data-section]");
      for (var j = 0; j < links.length; j++) {
        links[j].className = links[j].className.replace(" active", "");
      }

      var sec = el("section-" + name);
      if (sec) sec.className += " active";

      var lnk = document.querySelector(
        '.side-link[data-section="' + name + '"]',
      );
      if (lnk) lnk.className += " active";

      var titleEl = el("pageTitle");
      if (titleEl)
        titleEl.textContent = this.titles[name] || "Màn hình nhân viên";

      if (name === "dat-ban") {
        StaffTableManager.openMenu();
        StaffTableManager.refreshCurrent();
      }
      if (name === "home") {
        if (window.StaffTableManager) StaffTableManager.closeMenu();
        StaffOrders.loadTables();
      }
    },
  };

  window.StaffTableManager = {
    currentPane: "xac-nhan-dat-ban",
    paneTitles: {
      "xac-nhan-dat-ban": "Xác nhận đặt bàn",
      "cap-nhat-trang-thai-ban": "Cập nhật trạng thái bàn",
      "xac-nhan-mon": "Xác nhận món theo bàn",
    },
    openMenu: function () {
      var menu = el("tableSubnav");
      if (menu) menu.style.display = "grid";
    },
    closeMenu: function () {
      var menu = el("tableSubnav");
      if (menu) menu.style.display = "none";
    },
    showPane: function (name) {
      this.currentPane = name;
      if (StaffDashboard.currentSection !== "dat-ban") {
        StaffTabs.show("dat-ban");
      }
      this.openMenu();

      var panes = document.querySelectorAll(".table-work-pane");
      for (var i = 0; i < panes.length; i++) {
        panes[i].style.display = "none";
        panes[i].className = panes[i].className.replace(" active", "");
      }

      var tabs = document.querySelectorAll(
        ".table-function-tab,.side-sub-link",
      );
      for (var j = 0; j < tabs.length; j++) {
        tabs[j].className = tabs[j].className.replace(" active", "");
      }

      var pane = el("pane-" + name);
      if (pane) {
        pane.style.display = "block";
        pane.className += " active";
      }

      var tab = document.querySelector(
        '.table-function-tab[data-pane="' + name + '"]',
      );
      if (tab) tab.className += " active";
      var subLink = document.querySelector(
        '.side-sub-link[data-pane="' + name + '"]',
      );
      if (subLink) subLink.className += " active";

      var pageTitle = el("pageTitle");
      if (pageTitle)
        pageTitle.textContent = this.paneTitles[name] || "Quản lý bàn";

      this.refreshCurrent();
    },
    refreshCurrent: function () {
      if (this.currentPane === "xac-nhan-dat-ban") StaffReservations.load();
      if (this.currentPane === "cap-nhat-trang-thai-ban")
        StaffTableStatus.load();
      if (this.currentPane === "xac-nhan-mon") StaffOrders.loadTables();
    },
  };

  function init() {
    var tableSearch = el("tableSearch");
    if (tableSearch) {
      tableSearch.onkeyup = tableSearch.oninput = function () {
        StaffOrders.renderTables();
      };
    }

    var tableStatusSearch = el("tableStatusSearch");
    if (tableStatusSearch) {
      tableStatusSearch.onkeyup = tableStatusSearch.oninput = function () {
        StaffTableStatus.render();
      };
    }

    var tableStatusFilter = el("tableStatusFilter");
    if (tableStatusFilter) {
      tableStatusFilter.onchange = function () {
        StaffTableStatus.render();
      };
    }

    var resSearch = el("reservationSearch");
    if (resSearch) {
      resSearch.onkeyup = resSearch.oninput = function () {
        clearTimeout(window.__resTimer);
        window.__resTimer = setTimeout(function () {
          StaffReservations.load();
        }, 280);
      };
    }

    var resSel = el("reservationStatus");
    if (resSel) {
      resSel.onchange = function () {
        StaffReservations.setStatus(this.value);
      };
    }

    var chips = document.querySelectorAll(".chip");
    for (var i = 0; i < chips.length; i++) {
      chips[i].onclick = function () {
        var s = this.getAttribute("data-status") || "";
        var duyet = this.getAttribute("data-duyet") === "1";
        StaffReservations.setStatus(s, "", duyet);
      };
    }

    StaffOrders.loadTables();

    setInterval(function () {
      StaffOrders.loadTables();
      if (StaffTableManager.currentPane === "cap-nhat-trang-thai-ban")
        StaffTableStatus.load();
      if (StaffOrders.selectedTableId) StaffOrders.loadOrders();
    }, 8000);
  }

  if (window.addEventListener) {
    window.addEventListener("load", init, false);
  } else if (window.attachEvent) {
    window.attachEvent("onload", init);
  }
})();

(function () {
  var ui = window.StaffUI;
  var esc = ui.esc;
  var toast = ui.toast;
  var postForm = ui.postForm;
  var getJson = ui.getJson;
  var el = ui.el;
  var setText = ui.setText;
  var fmtTime = ui.fmtTime;
  var tableStatusInfo = ui.tableStatusInfo;

  window.StaffOrders = {
    tables: [],
    selectedTableId: 0,
    selectedTable: null,

    renderStats: function (pendingCount) {
      var total = this.tables.length;
      var busy = 0;
      for (var i = 0; i < this.tables.length; i++) {
        if (this.tables[i].trang_thai === "dang_dung") busy++;
      }

      setText("statTotal", total);
      setText("statBusy", busy);
      setText("statEmpty", total - busy);
      setText("statOrders", pendingCount != null ? pendingCount : "-");

      setText("statTotalHome", total);
      setText("statBusyHome", busy);
      setText("statEmptyHome", total - busy);
      setText("statOrdersHome", pendingCount != null ? pendingCount : "-");
    },

    renderTables: function () {
      var box = el("tables");
      if (!box) return;

      var input = el("tableSearch");
      var keyword = input ? input.value.toLowerCase() : "";
      var html = "";
      var visible = 0;

      for (var i = 0; i < this.tables.length; i++) {
        var t = this.tables[i];
        var name = "Bàn " + esc(t.so_ban);
        var code = t.ma_truy_cap || "";

        if (
          keyword &&
          (name + " " + code).toLowerCase().indexOf(keyword) === -1
        )
          continue;

        visible++;
        var info = tableStatusInfo(t.trang_thai);
        var isActive = this.selectedTableId == t.id;
        var cardCls = "table-card " + info.cls + (isActive ? " active" : "");

        html +=
          '<button type="button" class="' +
          cardCls +
          '" onclick="StaffOrders.selectTable(' +
          t.id +
          ')">';
        html +=
          '<div class="table-num"><span class="table-status-dot"></span>' +
          name +
          "</div>";
        html +=
          '<div class="table-info">Sức chứa: ' +
          esc(t.suc_chua || "-") +
          " khách";
        if (code) html += "<br>Mã: " + esc(code);
        html += "</div>";
        html +=
          '<span class="badge ' +
          info.badge +
          '" style="margin-top:6px;display:inline-flex">' +
          info.text +
          "</span>";
        html += "</button>";
      }

      if (!visible)
        html = '<div class="empty-state">Không tìm thấy bàn phù hợp.</div>';
      box.innerHTML = html;
      this.renderStats();
    },

    loadTables: function () {
      var self = this;
      getJson(BASE_URL + "/nhan-vien/danh-sach-ban", function (res) {
        if (!res.success) {
          toast(res.thong_bao || "Không tải được danh sách bàn", "err");
          return;
        }

        self.tables = res.du_lieu || [];
        StaffTableStatus.tables = self.tables;
        if (self.selectedTableId) {
          self.selectedTable = null;
          for (var i = 0; i < self.tables.length; i++) {
            if (self.tables[i].id == self.selectedTableId)
              self.selectedTable = self.tables[i];
          }
        }
        self.renderTables();
        StaffTableStatus.render();
      });
    },

    selectTable: function (id) {
      this.selectedTableId = id;
      this.selectedTable = null;
      for (var i = 0; i < this.tables.length; i++) {
        if (this.tables[i].id == id) this.selectedTable = this.tables[i];
      }
      this.renderTables();
      this.loadOrders();

      var refreshBtn = el("refreshOrderBtn");
      if (refreshBtn) refreshBtn.disabled = false;
    },

    loadOrders: function () {
      var self = this;
      if (!self.selectedTableId) return;

      var t = self.selectedTable;
      var title = t ? "Bàn " + t.so_ban : "Bàn #" + self.selectedTableId;
      var meta =
        t && t.ma_truy_cap
          ? "Mã truy cập: " + t.ma_truy_cap
          : "Đang tải đơn món...";

      setText("selectedTableTitle", title);
      setText("selectedTableMeta", meta);

      var ordersBox = el("orders");
      if (ordersBox)
        ordersBox.innerHTML =
          '<div class="empty-state">Đang tải đơn món...</div>';

      getJson(
        BASE_URL +
          "/nhan-vien/don-theo-ban?ban_id=" +
          encodeURIComponent(self.selectedTableId),
        function (res) {
          if (!res.success) {
            toast(res.thong_bao || "Không tải được đơn món", "err");
            return;
          }
          self.renderOrders(res.du_lieu || []);
        },
      );
    },

    renderOrders: function (orders) {
      var n = orders.length;
      var actionBar = el("orderActionBar");
      var clearBtn = el("clearTableBtn");
      var confirmAll = el("confirmAllBtn");
      var countLabel = el("orderCountLabel");
      var lastUpd = el("lastUpdated");

      if (lastUpd) lastUpd.textContent = "Cập nhật lúc " + fmtTime();
      this.renderStats(n);

      if (actionBar) actionBar.style.display = "flex";

      if (clearBtn) {
        clearBtn.disabled = n > 0;
        clearBtn.textContent =
          n > 0 ? "Phục vụ hết đơn trước" : "Xác nhận bàn trống";
      }
      if (confirmAll) confirmAll.style.display = n > 0 ? "inline-flex" : "none";
      if (countLabel) {
        countLabel.textContent =
          n > 0 ? n + " đơn đang chờ" : "Không có đơn chờ";
        countLabel.className = "badge " + (n > 0 ? "warn" : "ok");
      }

      if (!n) {
        el("orders").innerHTML =
          '<div class="empty-state"><div style="font-size:32px;margin-bottom:8px">&#127860;</div><div>Bàn này không có đơn nào đang chờ phục vụ.</div></div>';
        return;
      }

      var html = "";
      for (var i = 0; i < orders.length; i++) {
        var item = orders[i];
        html += '<div class="order-card">';
        html += '<div class="order-card-body">';
        html +=
          '<div class="order-name">Đơn #' +
          esc(item.id) +
          '<span class="order-qty">' +
          esc(item.tong_so_luong || item.so_mon || 0) +
          " món</span></div>";
        html +=
          '<div class="order-note"><span class="order-note-text">' +
          esc(item.mon_tom_tat || item.ten_mon || "-") +
          "</span></div>";
        if (item.ghi_chu) {
          html +=
            '<div class="order-note">Ghi chú: <span class="order-note-text">' +
            esc(item.ghi_chu) +
            "</span></div>";
        }
        html += "</div>";
        html +=
          '<button class="btn btn-sm" type="button" onclick="StaffOrders.confirmDish(' +
          item.id +
          ')">&#10003; Đã phục vụ</button>';
        html += "</div>";
      }
      el("orders").innerHTML = html;
    },

    confirmDish: function (orderId) {
      var self = this;
      postForm(
        BASE_URL + "/nhan-vien/xac-nhan-mon",
        { don_id: orderId },
        function (res) {
          if (res.success) {
            toast(res.thong_bao || "Đã xác nhận đơn");
            self.loadOrders();
            self.loadTables();
          } else {
            toast(res.thong_bao || "Không thể xác nhận đơn", "err");
          }
        },
      );
    },

    confirmAll: function () {
      var self = this;
      if (!self.selectedTableId) return;
      if (!confirm("Xác nhận tất cả đơn ở bàn này đã được phục vụ?")) return;

      postForm(
        BASE_URL + "/nhan-vien/xac-nhan-tat-ca",
        { ban_id: self.selectedTableId },
        function (res) {
          if (res.success) {
            toast(res.thong_bao || "Đã xác nhận tất cả đơn");
            self.loadOrders();
            self.loadTables();
          } else {
            toast(res.thong_bao || "Không thể xác nhận tất cả", "err");
          }
        },
      );
    },

    markTableEmpty: function () {
      var self = this;
      if (!self.selectedTableId) return;
      if (!confirm("Xác nhận bàn này đã trống?")) return;

      postForm(
        BASE_URL + "/nhan-vien/xac-nhan-ban",
        { ban_id: self.selectedTableId },
        function (res) {
          if (res.success) {
            toast(res.thong_bao || "Đã xác nhận bàn trống");
            self.loadTables();
            self.loadOrders();
          } else {
            toast(res.thong_bao || "Không thể cập nhật bàn", "err");
          }
        },
      );
    },
  };
})();

(function () {
  var ui = window.StaffUI;
  var esc = ui.esc;
  var toast = ui.toast;
  var postForm = ui.postForm;
  var getJson = ui.getJson;
  var el = ui.el;
  var tableStatusInfo = ui.tableStatusInfo;

  window.StaffTableStatus = {
    tables: [],

    load: function () {
      var self = this;
      var box = el("tableStatusList");
      if (box)
        box.innerHTML =
          '<div class="empty-state">Đang tải danh sách bàn...</div>';
      getJson(BASE_URL + "/nhan-vien/danh-sach-ban", function (res) {
        if (!res.success) {
          toast(res.thong_bao || "Không tải được danh sách bàn", "err");
          return;
        }
        self.tables = res.du_lieu || [];
        StaffOrders.tables = self.tables;
        self.render();
        StaffOrders.renderTables();
      });
    },

    render: function () {
      var box = el("tableStatusList");
      if (!box) return;

      var search = el("tableStatusSearch");
      var filter = el("tableStatusFilter");
      var keyword = search ? search.value.toLowerCase() : "";
      var status = filter ? filter.value : "";
      var html = "";
      var visible = 0;

      for (var i = 0; i < this.tables.length; i++) {
        var t = this.tables[i];
        var effectiveStatus = t.trang_thai || "trong";
        var rawStatus = t.trang_thai_goc || effectiveStatus;
        var code = t.ma_truy_cap || "";
        var name = "Bàn " + t.so_ban;

        if (
          keyword &&
          (name + " " + code).toLowerCase().indexOf(keyword) === -1
        )
          continue;
        if (status && effectiveStatus !== status && rawStatus !== status)
          continue;

        visible++;
        var info = tableStatusInfo(effectiveStatus);
        var emptyText = effectiveStatus === "trong" ? "Bàn trống" : info.text;

        html += '<div class="table-status-card ' + info.cls + '">';
        html += '<div class="table-status-head">';
        html +=
          '<div class="table-status-title"><strong>' +
          esc(name) +
          "</strong><span>" +
          esc(emptyText) +
          "</span></div>";
        html +=
          '<span class="badge ' + info.badge + '">' + info.text + "</span>";
        html += "</div>";

        html += '<div class="table-status-facts">';
        html +=
          "<div><span>Sức chứa</span><strong>" +
          esc(t.suc_chua || "-") +
          " khách</strong></div>";
        html +=
          "<div><span>Mã bàn</span><strong>" +
          esc(code || "-") +
          "</strong></div>";
        html += "</div>";

        if (Number(t.so_don_cho || 0) > 0) {
          html +=
            '<div class="table-status-note">Có ' +
            esc(t.so_don_cho) +
            " đơn đang chờ phục vụ.</div>";
        }

        html += '<div class="table-status-actions">';
        html += this.statusButton(t.id, "trong", rawStatus, "Trống");
        html += this.statusButton(t.id, "dang_dung", rawStatus, "Đang dùng");
        html += this.statusButton(
          t.id,
          "cho_thanh_toan",
          rawStatus,
          "Chờ thanh toán",
        );
        html += this.statusButton(
          t.id,
          "da_thanh_toan",
          rawStatus,
          "Đã thanh toán",
        );
        html += "</div>";

        if (rawStatus === "dang_dung" && code) {
          html +=
            '<button type="button" class="table-status-btn" style="width:100%;margin-top:6px"' +
            ' onclick="StaffTableStatus.inPhieu(' +
            t.id +
            ",'" +
            esc(name) +
            "','" +
            esc(code) +
            "')\"" +
            ">🖨️ In phiếu gọi món</button>";
        }

        html += "</div>"; // đóng table-status-card
      }

      if (!visible)
        html = '<div class="empty-state">Không tìm thấy bàn phù hợp.</div>';
      box.innerHTML = html;
    },

    statusButton: function (id, status, current, label) {
      var active = current === status ? " active" : "";
      return (
        '<button type="button" class="table-status-btn' +
        active +
        '" onclick="StaffTableStatus.update(' +
        id +
        ",'" +
        status +
        "')\">" +
        label +
        "</button>"
      );
    },

    update: function (id, status) {
      var self = this;
      postForm(
        BASE_URL + "/nhan-vien/cap-nhat-trang-thai-ban",
        { ban_id: id, trang_thai: status },
        function (res) {
          if (res.success) {
            toast(res.thong_bao || "Đã cập nhật trạng thái bàn");
            self.load();
          } else {
            toast(res.thong_bao || "Không thể cập nhật trạng thái bàn", "err");
          }
        },
      );
    },
    inPhieu: function (id, tenBan, ma) {
      var url = BASE_URL + "/goi-mon?ma=" + encodeURIComponent(ma);
      var qrUrl =
        "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" +
        encodeURIComponent(url);
      var win = window.open("", "_blank");
      win.document.write(
        "<html><head><title>Phiếu gọi món - " +
          tenBan +
          "</title>" +
          "<style>" +
          "body{font-family:Arial,sans-serif;text-align:center;padding:30px}" +
          "h2{margin-bottom:4px} p{color:#555;margin:4px 0}" +
          ".qr{margin:20px auto} .note{margin-top:16px;font-size:13px;color:#888}" +
          "</style></head><body>" +
          "<h2>🌿 Buffet Chay</h2>" +
          "<p>" +
          tenBan +
          "</p>" +
          '<div class="qr"><img src="' +
          qrUrl +
          '" width="200" height="200"></div>' +
          "<p><strong>Quét QR để gọi món</strong></p>" +
          '<p style="font-size:12px;color:#aaa">Mã: ' +
          ma +
          "</p>" +
          '<div class="note">Phiếu chỉ có hiệu lực trong phiên này</div>' +
          "<script>window.onload=function(){window.print()}<\/script>" +
          "</body></html>",
      );
      win.document.close();
    },
  };
})();
