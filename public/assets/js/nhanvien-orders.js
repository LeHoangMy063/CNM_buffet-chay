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
  var jsArg = ui.jsArg;

  function normalizeText(value) {
    return String(value || "")
      .toLowerCase()
      .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, "a")
      .replace(/[èéẹẻẽêềếệểễ]/g, "e")
      .replace(/[ìíịỉĩ]/g, "i")
      .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, "o")
      .replace(/[ùúụủũưừứựửữ]/g, "u")
      .replace(/[ỳýỵỷỹ]/g, "y")
      .replace(/đ/g, "d");
  }

  function menuItems() {
    var source =
      typeof STAFF_MENU_ITEMS === "undefined" ? [] : STAFF_MENU_ITEMS;
    var items = [];
    for (var i = 0; i < source.length; i++) {
      if (Number(source[i].con_mon || 0) === 0) continue;
      items.push(source[i]);
    }
    return items;
  }

  function pickBy(items, words, count, used) {
    var result = [];
    used = used || {};
    for (var i = 0; i < items.length && result.length < count; i++) {
      var it = items[i];
      if (used[it.id]) continue;
      var text = normalizeText(
        (it.ten || "") + " " + (it.danh_muc || "") + " " + (it.mo_ta || ""),
      );
      var matched = false;
      for (var j = 0; j < words.length; j++) {
        if (text.indexOf(words[j]) !== -1) matched = true;
      }
      if (matched) {
        result.push(it);
        used[it.id] = true;
      }
    }
    return result;
  }

  function fillAny(items, result, count, used) {
    used = used || {};
    for (var i = 0; i < items.length && result.length < count; i++) {
      if (used[items[i].id]) continue;
      result.push(items[i]);
      used[items[i].id] = true;
    }
    return result;
  }

  function dishNames(list) {
    var names = [];
    for (var i = 0; i < list.length; i++) {
      names.push(list[i].ten || "Món");
    }
    return names;
  }

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
          jsArg(t.id) +
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
      var pendingCount = 0;
      for (var pi = 0; pi < orders.length; pi++) {
        if ((orders[pi].trang_thai || "") !== "da_phuc_vu") pendingCount++;
      }
      var isKitchen = typeof STAFF_ROLE !== "undefined" && STAFF_ROLE === "bep";
      var actionBar = el("orderActionBar");
      var confirmAll = el("confirmAllBtn");
      var countLabel = el("orderCountLabel");
      var lastUpd = el("lastUpdated");

      if (lastUpd) lastUpd.textContent = "Cập nhật lúc " + fmtTime();
      this.renderStats(pendingCount);

      if (actionBar) actionBar.style.display = "flex";
      if (confirmAll)
        confirmAll.style.display =
          !isKitchen && pendingCount > 0 ? "inline-flex" : "none";
      if (countLabel) {
        countLabel.textContent =
          pendingCount > 0
            ? pendingCount + " đơn đang chờ"
            : "Không có đơn chờ";
        countLabel.className = "badge " + (pendingCount > 0 ? "warn" : "ok");
      }

      if (!n) {
        el("orders").innerHTML =
          '<div class="empty-state"><div>Bàn này không có đơn nào đang chờ phục vụ.</div></div>';
        return;
      }

      var html = "";
      for (var i = 0; i < orders.length; i++) {
        var item = orders[i];
        var served = (item.trang_thai || "") === "da_phuc_vu";
        var orderStatusLabel = served ? "Đã phục vụ" : "Đang chờ";
        var orderStatusClass = served ? " served" : " pending";
        html += '<div class="order-card' + orderStatusClass + '">';
        html += '<div class="order-card-body">';
        html +=
          '<div class="order-name">Đơn #' +
          esc(item.id) +
          '<span class="order-qty">' +
          esc(item.tong_so_luong || item.so_mon || 0) +
          ' món</span><span class="order-status-mini">' +
          esc(orderStatusLabel) +
          "</span></div>";
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
        if (served) {
          if (!isKitchen) {
            html +=
              '<button class="btn btn-sm" type="button" disabled aria-disabled="true">Đã phục vụ</button>';
          }
          html += "</div>";
          continue;
        }
        html +=
          '<button class="btn btn-sm" type="button" onclick="StaffOrders.confirmDish(' +
          jsArg(item.id) +
          ')">Đã phục vụ</button>';
        html += "</div>";
      }
      if (isKitchen) {
        html = html.replace(
          /<button class="btn btn-sm" type="button" onclick="StaffOrders\.confirmDish\([\s\S]*?\)">[\s\S]*?<\/button>/g,
          "",
        );
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
  };
})();

