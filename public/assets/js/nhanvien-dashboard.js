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
    currentSection: typeof STAFF_ROLE !== "undefined" && STAFF_ROLE === "bep" ? "dat-ban" : "home",
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
    currentPane:
      typeof STAFF_DEFAULT_PANE !== "undefined"
        ? STAFF_DEFAULT_PANE
        : "xac-nhan-dat-ban",
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
      if (typeof STAFF_ROLE !== "undefined" && STAFF_ROLE === "bep" && name !== "xac-nhan-mon") {
        return;
      }
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

    var requestedTab = "";
    try {
      requestedTab = new URLSearchParams(window.location.search).get("tab") || "";
    } catch (e) {
      requestedTab = "";
    }
    if (!requestedTab && window.location.hash) {
      requestedTab = window.location.hash.replace(/^#/, "");
    }

    if (typeof STAFF_ROLE !== "undefined" && STAFF_ROLE === "bep") {
      StaffTableManager.showPane("xac-nhan-mon");
    } else if (requestedTab === "dat-ban") {
      StaffTabs.show("dat-ban");
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
        var sessionCode =
          Number(t.ma_phien_con_han || 0) === 1 ? t.ma_phien_goi_mon || "" : "";

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
      var isKitchen = typeof STAFF_ROLE !== "undefined" && STAFF_ROLE === "bep";
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
      if (confirmAll) confirmAll.style.display = !isKitchen && n > 0 ? "inline-flex" : "none";
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
      if (isKitchen) {
        html = html.replace(/<button class="btn btn-sm" type="button" onclick="StaffOrders\.confirmDish\([0-9]+\)">[\s\S]*?<\/button>/g, "");
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

  function money(v) {
    v = Number(v || 0);
    return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
  }

  function priceAdult() {
    return typeof PRICE_ADULT !== "undefined" ? Number(PRICE_ADULT || 0) : 199000;
  }

  function priceChild() {
    return typeof PRICE_CHILD !== "undefined" ? Number(PRICE_CHILD || 0) : 0;
  }

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
        var sessionCode =
          Number(t.ma_phien_con_han || 0) === 1 ? t.ma_phien_goi_mon || "" : "";
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
        if (rawStatus === "dang_dung" && sessionCode) {
          html +=
            "<div><span>Mã tạm thời</span><strong>" +
            esc(sessionCode) +
            "</strong></div>";
        }
        html += "</div>";

        if (rawStatus === "dang_dung" && sessionCode) {
          html +=
            '<div class="table-status-note">Mã tạm thời có hiệu lực gọi món trong 100 phút.</div>';
        }

        if (rawStatus === "dang_dung" && sessionCode && (t.phien_ten_khach || Number(t.phien_nguoi_lon || 0) + Number(t.phien_tre_em || 0) > 0)) {
          html +=
            '<div class="table-status-note">Bill: ' +
            esc(t.phien_ten_khach || "Khach le") +
            " - " +
            esc(Number(t.phien_nguoi_lon || 0) + Number(t.phien_tre_em || 0)) +
            " khach - " +
            esc(money(t.phien_tong_tien || 0)) +
            "</div>";
        }

        if (Number(t.so_don_cho || 0) > 0) {
          html +=
            '<div class="table-status-note">Có ' +
            esc(t.so_don_cho) +
            " đơn đang chờ phục vụ.</div>";
        }

        html += '<div class="table-status-actions">';
        html += this.statusButton(t.id, "trong", rawStatus, "Trống");
        html += this.statusButton(t.id, "dang_dung", rawStatus, "Đang dùng");
        html += "</div>";

        if (rawStatus === "dang_dung" && sessionCode) {
          html +=
            '<button type="button" class="table-status-btn" style="width:100%;margin-top:6px" onclick="StaffTableStatus.inPhieu(' +
            t.id +
            ')">In phiếu gọi món</button>';
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
      if (status === "dang_dung") {
        this.openBillForm(id);
        return;
      }
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
    findTable: function (id) {
      for (var i = 0; i < this.tables.length; i++) {
        if (String(this.tables[i].id) === String(id)) return this.tables[i];
      }
      return null;
    },

    ensureBillForm: function () {
      var box = el("tableBillModal");
      if (box) return box;

      box = document.createElement("div");
      box.id = "tableBillModal";
      box.className = "bill-modal";
      box.innerHTML =
        '<div class="bill-modal-card">' +
        '<div class="bill-modal-head"><div><p class="eyebrow">Bill mẫu</p><h3 id="billModalTitle">Mở bàn</h3></div><button type="button" onclick="StaffTableStatus.closeBillForm()">×</button></div>' +
        '<form id="tableBillForm" class="bill-form">' +
        '<input type="hidden" id="billTableId">' +
        '<label>Tên khách<input id="billCustomerName" type="text" autocomplete="off" required></label>' +
        '<label>SĐT <span>không bắt buộc</span><input id="billCustomerPhone" type="text" autocomplete="off"></label>' +
        '<div class="bill-form-grid">' +
        '<label>Người lớn<input id="billAdultCount" type="number" min="0" value="1"></label>' +
        '<label>Trẻ em<input id="billChildCount" type="number" min="0" value="0"></label>' +
        '</div>' +
        '<div class="bill-preview">' +
        '<div><span>Giá người lớn</span><strong id="billAdultPrice"></strong></div>' +
        '<div><span>Giá trẻ em</span><strong id="billChildPrice"></strong></div>' +
        '<div class="bill-total"><span>Tổng giá</span><strong id="billTotalPrice"></strong></div>' +
        '</div>' +
        '<div class="bill-modal-actions"><button type="button" class="table-status-btn" onclick="StaffTableStatus.closeBillForm()">Hủy</button><button type="submit" class="table-status-btn active">Xác nhận</button></div>' +
        '</form>' +
        '</div>';
      document.body.appendChild(box);

      el("tableBillForm").onsubmit = function (ev) {
        if (ev && ev.preventDefault) ev.preventDefault();
        StaffTableStatus.confirmBillForm();
        return false;
      };
      el("billAdultCount").onkeyup = el("billAdultCount").oninput = function () {
        StaffTableStatus.updateBillTotal();
      };
      el("billChildCount").onkeyup = el("billChildCount").oninput = function () {
        StaffTableStatus.updateBillTotal();
      };
      return box;
    },

    openBillForm: function (id) {
      var t = this.findTable(id);
      var box = this.ensureBillForm();
      el("billTableId").value = id;
      el("billModalTitle").textContent = "Mở " + (t && t.so_ban ? "Bàn " + t.so_ban : "bàn");
      el("billCustomerName").value = t && t.phien_ten_khach ? t.phien_ten_khach : "";
      el("billCustomerPhone").value = t && t.phien_sdt_khach ? t.phien_sdt_khach : "";
      el("billAdultCount").value = t && Number(t.phien_nguoi_lon || 0) > 0 ? Number(t.phien_nguoi_lon || 0) : 1;
      el("billChildCount").value = t && Number(t.phien_tre_em || 0) > 0 ? Number(t.phien_tre_em || 0) : 0;
      this.updateBillTotal();
      box.className = "bill-modal show";
      setTimeout(function () {
        el("billCustomerName").focus();
      }, 20);
    },

    closeBillForm: function () {
      var box = el("tableBillModal");
      if (box) box.className = "bill-modal";
    },

    updateBillTotal: function () {
      var adult = Math.max(0, Number(el("billAdultCount").value || 0));
      var child = Math.max(0, Number(el("billChildCount").value || 0));
      el("billAdultPrice").textContent = money(priceAdult());
      el("billChildPrice").textContent = money(priceChild());
      el("billTotalPrice").textContent = money(adult * priceAdult() + child * priceChild());
    },

    confirmBillForm: function () {
      var id = el("billTableId").value;
      var ten = el("billCustomerName").value.replace(/^\s+|\s+$/g, "");
      var sdt = el("billCustomerPhone").value.replace(/^\s+|\s+$/g, "");
      var adult = Math.max(0, parseInt(el("billAdultCount").value || "0", 10));
      var child = Math.max(0, parseInt(el("billChildCount").value || "0", 10));

      if (!ten) {
        toast("Vui lòng nhập tên khách", "err");
        el("billCustomerName").focus();
        return;
      }
      if (adult + child <= 0) {
        toast("Vui lòng nhập số lượng khách", "err");
        el("billAdultCount").focus();
        return;
      }

      this.closeBillForm();
      var self = this;
      postForm(
        BASE_URL + "/nhan-vien/cap-nhat-trang-thai-ban",
        {
          ban_id: id,
          trang_thai: "dang_dung",
          ten_khach: ten,
          sdt_khach: sdt,
          nguoi_lon: adult,
          tre_em: child,
        },
        function (res) {
          if (res.success) {
            toast(res.thong_bao || "Da cap nhat trang thai ban");
            if (res.du_lieu && res.du_lieu.id) {
              var replaced = false;
              for (var i = 0; i < self.tables.length; i++) {
                if (String(self.tables[i].id) === String(res.du_lieu.id)) {
                  self.tables[i] = res.du_lieu;
                  replaced = true;
                  break;
                }
              }
              if (!replaced) self.tables.push(res.du_lieu);
              self.inPhieu(res.du_lieu.id);
            }
            self.load();
          } else {
            toast(res.thong_bao || "Khong the cap nhat trang thai ban", "err");
          }
        },
      );
    },

    inPhieu: function (id) {
      var t = null;
      for (var i = 0; i < this.tables.length; i++) {
        if (String(this.tables[i].id) === String(id)) {
          t = this.tables[i];
          break;
        }
      }
      if (!t) return;

      var tenBan = "Bàn " + (t.so_ban || id);
      var ma = t.ma_phien_goi_mon || t.ma_truy_cap || "";
      var url = BASE_URL + "/goi-mon?ma=" + encodeURIComponent(ma);
      var qrUrl =
        "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" +
        encodeURIComponent(url);
      var today = new Date();
      var ngayIn =
        (today.getDate() < 10 ? "0" : "") +
        today.getDate() +
        "/" +
        (today.getMonth() + 1 < 10 ? "0" : "") +
        (today.getMonth() + 1) +
        "/" +
        today.getFullYear();
      var nguoiLon = Number(t.phien_nguoi_lon || 0);
      var treEm = Number(t.phien_tre_em || 0);
      var tongKhach = nguoiLon + treEm;
      var tenKhach = t.phien_ten_khach || "";
      var sdtKhach = t.phien_sdt_khach || "";
      var tongTien = Number(t.phien_tong_tien || (nguoiLon * priceAdult() + treEm * priceChild()));
      var batDau = t.phien_bat_dau || "";
      var win = window.open("", "_blank");
      win.document.write(
        "<html><head><title>Hóa đơn gọi món - " +
          tenBan +
          "</title>" +
          "<style>" +
          "body{font-family:Arial,sans-serif;color:#111;padding:24px;background:#fff}" +
          ".bill{width:360px;margin:0 auto}.head{text-align:center;border-bottom:1px solid #ddd;padding-bottom:10px;margin-bottom:12px}" +
          "h2{margin:0 0 4px;font-size:24px}.muted{color:#666;font-size:13px}.row{display:flex;justify-content:space-between;gap:12px;margin:8px 0;font-size:14px}.row strong{text-align:right}" +
          ".section{border-bottom:1px dashed #bbb;padding:8px 0}.qr{text-align:center;margin:18px auto}.qr img{width:180px;height:180px}.note{text-align:center;margin-top:12px;font-size:12px;color:#777}" +
          "</style></head><body>" +
          '<div class="bill">' +
          '<div class="head"><h2>Buffet Chay</h2><div class="muted">Hóa đơn mở phiên gọi món</div></div>' +
          '<div class="section">' +
          '<div class="row"><span>Ngày in</span><strong>' + esc(ngayIn) + '</strong></div>' +
          '<div class="row"><span>Bàn</span><strong>' + esc(tenBan) + '</strong></div>' +
          '<div class="row"><span>Mã tạm thời</span><strong>' + esc(ma) + '</strong></div>' +
          '</div>' +
          '<div class="section">' +
          '<div class="row"><span>Tên khách</span><strong>' + esc(tenKhach) + '</strong></div>' +
          '<div class="row"><span>SĐT</span><strong>' + esc(sdtKhach) + '</strong></div>' +
          '<div class="row"><span>Mở phiên</span><strong>' + esc(batDau) + '</strong></div>' +
          '<div class="row"><span>Người lớn</span><strong>' + esc(nguoiLon) + '</strong></div>' +
          '<div class="row"><span>Trẻ em</span><strong>' + esc(treEm) + '</strong></div>' +
          '<div class="row"><span>Số lượng</span><strong>' + esc(tongKhach ? tongKhach + " khách" : "") + '</strong></div>' +
          '<div class="row"><span>Tổng giá</span><strong>' + esc(money(tongTien)) + '</strong></div>' +
          '</div>' +
          '<div class="qr"><img src="' +
          qrUrl +
          '"><div><strong>Quét QR để gọi món</strong></div></div>' +
          '<div class="note">Mã tạm thời chỉ có hiệu lực gọi món trong 100 phút.</div>' +
          '</div>' +
          "<script>window.onload=function(){window.print()}<\/script>" +
          "</body></html>"
      );
      win.document.close();
    },
  };
})();
