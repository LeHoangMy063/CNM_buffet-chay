(function () {
  var ui = window.StaffUI;
  var esc = ui.esc;
  var toast = ui.toast;
  var postForm = ui.postForm;
  var getJson = ui.getJson;
  var el = ui.el;
  var tableStatusInfo = ui.tableStatusInfo;
  var jsArg = ui.jsArg;
  var fmtDate = ui.fmtDate;

  function money(v) {
    v = Number(v || 0);
    return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
  }

  function vietQrNote(t) {
    var code =
      t && (t.ma_phien_goi_mon || t.ma_truy_cap || t.so_ban)
        ? t.ma_phien_goi_mon || t.ma_truy_cap || t.so_ban
        : "";
    return ("BUFFET " + code)
      .replace(/[^A-Za-z0-9 ]/g, " ")
      .replace(/\s+/g, " ")
      .replace(/^\s+|\s+$/g, "");
  }

  function vietQrImageUrl(t) {
    var cfg = typeof VIETQR !== "undefined" ? VIETQR : {};
    var amount = Number(t && t.phien_tong_tien ? t.phien_tong_tien : 0);
    var template = cfg.template || "compact2";
    return (
      "https://img.vietqr.io/image/" +
      encodeURIComponent(cfg.bankId || "") +
      "-" +
      encodeURIComponent(cfg.accountNo || "") +
      "-" +
      encodeURIComponent(template) +
      ".png?amount=" +
      encodeURIComponent(amount) +
      "&addInfo=" +
      encodeURIComponent(vietQrNote(t)) +
      "&accountName=" +
      encodeURIComponent(cfg.accountName || "")
    );
  }

  function paymentMethodText(method) {
    if (method === "tien_mat") return "Tiền mặt";
    if (method === "chuyen_khoan") return "Chuyển khoản";
    return "Thanh toán";
  }

  function priceAdult() {
    return typeof PRICE_ADULT !== "undefined"
      ? Number(PRICE_ADULT || 0)
      : 199000;
  }

  function priceChild() {
    return typeof PRICE_CHILD !== "undefined" ? Number(PRICE_CHILD || 0) : 0;
  }
  function reservationDateText(v) {
    return fmtDate(v);
  }

  function isReservationSoon(t) {
    if (!t || !t.dat_ban_sap_toi_ngay || !t.dat_ban_sap_toi_gio) return false;
    var date = String(t.dat_ban_sap_toi_ngay);
    var time = String(t.dat_ban_sap_toi_gio);
    if (time.length === 5) time += ":00";

    var at = new Date(date + "T" + time);
    if (isNaN(at.getTime())) return false;

    var diffMinutes = (at.getTime() - new Date().getTime()) / 60000;
    return diffMinutes >= 0 && diffMinutes <= 90;
  }

  function reservationLabel(t) {
    if (!t || !t.dat_ban_sap_toi_id) return "";
    if (!isReservationSoon(t)) return "";

    var time = t.dat_ban_sap_toi_gio ? t.dat_ban_sap_toi_gio + " " : "";
    var date = reservationDateText(t.dat_ban_sap_toi_ngay);
    var name = t.dat_ban_sap_toi_ten_khach || "Khách đặt bàn";
    var guests = Number(t.dat_ban_sap_toi_so_khach || 0);

    return (
      "Đã có đặt bàn " +
      time +
      date +
      " - " +
      name +
      (guests > 0 ? " - " + guests + " khách" : "")
    );
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
        var reservationText = reservationLabel(t);
        var reservationNote = t.dat_ban_sap_toi_ghi_chu || "";

        if (
          keyword &&
          (name + " " + code + " " + reservationText + " " + reservationNote)
            .toLowerCase()
            .indexOf(keyword) === -1
        )
          continue;

        visible++;
        var info = tableStatusInfo(effectiveStatus);
        var emptyText = effectiveStatus === "trong" ? "Bàn trống" : info.text;
        var hasReservation = !!t.dat_ban_sap_toi_id;

        html +=
          '<div class="table-status-card ' +
          info.cls +
          (hasReservation ? " has-reservation" : "") +
          '">';
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
        if (reservationText) {
          html +=
            '<div class="table-status-note reservation-warning">' +
            esc(reservationText) +
            "</div>";
        }
        if (reservationNote) {
          html +=
            '<div class="table-status-note reservation-note">Ghi chú: ' +
            esc(reservationNote) +
            "</div>";
        }
        if (rawStatus === "dang_dung" && sessionCode) {
          html +=
            '<div class="table-status-note">Mã tạm thời có hiệu lực gọi món trong 100 phút.</div>';
        }

        if (
          rawStatus === "dang_dung" &&
          sessionCode &&
          (t.phien_ten_khach ||
            Number(t.phien_nguoi_lon || 0) + Number(t.phien_tre_em || 0) > 0)
        ) {
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

        if (rawStatus === "dang_dung" && sessionCode) {
          html += '<div class="table-status-actions">';
          html +=
            '<button type="button" class="table-status-btn" onclick="StaffTableStatus.openPaymentModal(' +
            jsArg(t.id) +
            ",'tien_mat')\">Tiền mặt</button>";
          html +=
            '<button type="button" class="table-status-btn active" onclick="StaffTableStatus.openPaymentModal(' +
            jsArg(t.id) +
            ",'chuyen_khoan')\">Chuyển khoản</button>";
          html += "</div>";
          html +=
            '<button type="button" class="table-status-btn" style="width:100%;margin-top:6px" onclick="StaffTableStatus.inPhieu(' +
            jsArg(t.id) +
            ')">In phiếu gọi món</button>';
        } else {
          html += '<div class="table-status-actions">';
          html += this.statusButton(t.id, "dang_dung", rawStatus, "Mở bàn");
          html += "</div>";
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
        jsArg(id) +
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
      this.completePayment(id, "");
    },

    completePayment: function (id, method) {
      var self = this;
      var data = { ban_id: id, trang_thai: "trong" };
      if (method) data.phuong_thuc_thanh_toan = method;
      postForm(
        BASE_URL + "/nhan-vien/cap-nhat-trang-thai-ban",
        data,
        function (res) {
          if (res.success) {
            toast(res.thong_bao || "Đã điều phối bàn");
            self.load();
          } else {
            toast(res.thong_bao || "Không thể điều phối bàn", "err");
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
        '<div id="billReservationWarning" class="table-status-note reservation-warning" style="display:none"></div>' +
        '<div id="billReservationNote" class="table-status-note reservation-note" style="display:none"></div>' +
        '<label>Tên khách<input id="billCustomerName" type="text" autocomplete="off" required></label>' +
        '<label>SĐT <span>không bắt buộc</span><input id="billCustomerPhone" type="text" autocomplete="off"></label>' +
        '<div class="bill-form-grid">' +
        '<label>Người lớn<input id="billAdultCount" type="number" min="0" value="1"></label>' +
        '<label>Trẻ em<input id="billChildCount" type="number" min="0" value="0"></label>' +
        "</div>" +
        '<div class="bill-preview">' +
        '<div><span>Giá người lớn</span><strong id="billAdultPrice"></strong></div>' +
        '<div><span>Giá trẻ em</span><strong id="billChildPrice"></strong></div>' +
        '<div class="bill-total"><span>Tổng giá</span><strong id="billTotalPrice"></strong></div>' +
        "</div>" +
        '<div class="bill-modal-actions"><button type="button" class="table-status-btn" onclick="StaffTableStatus.closeBillForm()">Hủy</button><button type="submit" class="table-status-btn active">Xác nhận</button></div>' +
        "</form>" +
        "</div>";
      document.body.appendChild(box);

      el("tableBillForm").onsubmit = function (ev) {
        if (ev && ev.preventDefault) ev.preventDefault();
        StaffTableStatus.confirmBillForm();
        return false;
      };
      el("billAdultCount").onkeyup = el("billAdultCount").oninput =
        function () {
          StaffTableStatus.updateBillTotal();
        };
      el("billChildCount").onkeyup = el("billChildCount").oninput =
        function () {
          StaffTableStatus.updateBillTotal();
        };
      return box;
    },

    openBillForm: function (id) {
      var t = this.findTable(id);
      var box = this.ensureBillForm();
      el("billTableId").value = id;
      el("billModalTitle").textContent =
        "Mở " + (t && t.so_ban ? "Bàn " + t.so_ban : "bàn");
      var reservationText = reservationLabel(t);
      var reservationNote =
        t && t.dat_ban_sap_toi_ghi_chu ? t.dat_ban_sap_toi_ghi_chu : "";
      var reservationWarning = el("billReservationWarning");
      if (reservationWarning) {
        reservationWarning.style.display = reservationText ? "block" : "none";
        reservationWarning.textContent = reservationText || "";
      }
      var reservationNoteBox = el("billReservationNote");
      if (reservationNoteBox) {
        reservationNoteBox.style.display = reservationNote ? "block" : "none";
        reservationNoteBox.textContent = reservationNote
          ? "Ghi chú: " + reservationNote
          : "";
      }
      el("billCustomerName").value =
        t && t.phien_ten_khach ? t.phien_ten_khach : "";
      el("billCustomerPhone").value =
        t && t.phien_sdt_khach ? t.phien_sdt_khach : "";
      el("billAdultCount").value =
        t && Number(t.phien_nguoi_lon || 0) > 0
          ? Number(t.phien_nguoi_lon || 0)
          : 1;
      el("billChildCount").value =
        t && Number(t.phien_tre_em || 0) > 0 ? Number(t.phien_tre_em || 0) : 0;
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

    ensurePaymentModal: function () {
      var box = el("staffPaymentModal");
      if (box) return box;

      box = document.createElement("div");
      box.id = "staffPaymentModal";
      box.className = "bill-modal";
      box.innerHTML =
        '<div class="bill-modal-card vietqr-staff-card">' +
        '<div class="bill-modal-head"><div><p class="eyebrow">Thanh toán</p><h3 id="paymentModalTitle">Xác nhận thanh toán</h3></div><button type="button" onclick="StaffTableStatus.closePaymentModal()">×</button></div>' +
        '<div class="vietqr-staff-body">' +
        '<div id="paymentQrWrap" style="display:none"><img id="paymentQrImage" class="vietqr-staff-img" src="" alt="VietQR thanh toán"></div>' +
        '<div class="vietqr-staff-info">' +
        '<div><span>Phương thức</span><strong id="paymentMethodText"></strong></div>' +
        '<div><span>Số tiền</span><strong id="paymentAmount"></strong></div>' +
        '<div id="paymentNoteRow" style="display:none"><span>Nội dung</span><strong id="paymentNote"></strong></div>' +
        '<div id="paymentAccountRow" style="display:none"><span>Tài khoản</span><strong id="paymentAccount"></strong></div>' +
        '<div id="paymentNameRow" style="display:none"><span>Chủ TK</span><strong id="paymentName"></strong></div>' +
        "</div>" +
        '<p id="paymentHelpText" class="vietqr-staff-note"></p>' +
        '<div class="bill-modal-actions"><button type="button" class="table-status-btn" onclick="StaffTableStatus.closePaymentModal()">Hủy</button><button id="paymentConfirmBtn" type="button" class="table-status-btn active">Xác nhận đã nhận tiền</button></div>' +
        "</div>" +
        "</div>";
      document.body.appendChild(box);
      return box;
    },

    closePaymentModal: function () {
      var box = el("staffPaymentModal");
      if (box) box.className = "bill-modal";
    },

    openPaymentModal: function (id, method) {
      var cfg = typeof VIETQR !== "undefined" ? VIETQR : {};
      var t = this.findTable(id);
      var amount = Number(t && t.phien_tong_tien ? t.phien_tong_tien : 0);
      var isTransfer = method === "chuyen_khoan";

      if (!t) {
        toast("Không tìm thấy thông tin bàn", "err");
        return;
      }
      if (amount <= 0) {
        toast("Bàn này chưa có số tiền bill", "err");
        return;
      }
      if (isTransfer && (!cfg.enabled || !cfg.bankId || !cfg.accountNo)) {
        toast("Chưa cấu hình VietQR trong .env", "err");
        return;
      }

      var box = this.ensurePaymentModal();
      el("paymentModalTitle").textContent =
        "Thanh toán Bàn " + (t.so_ban || id);
      el("paymentMethodText").textContent = paymentMethodText(method);
      el("paymentAmount").textContent = money(amount);
      el("paymentQrWrap").style.display = isTransfer ? "" : "none";
      el("paymentNoteRow").style.display = isTransfer ? "flex" : "none";
      el("paymentAccountRow").style.display = isTransfer ? "flex" : "none";
      el("paymentNameRow").style.display = isTransfer ? "flex" : "none";
      if (isTransfer) {
        el("paymentQrImage").src = vietQrImageUrl(t);
        el("paymentNote").textContent = vietQrNote(t);
        el("paymentAccount").textContent = cfg.accountNo || "-";
        el("paymentName").textContent = cfg.accountName || "-";
        el("paymentHelpText").textContent =
          "Cho khách quét QR/chuyển khoản, kiểm tra app ngân hàng. Khi tiền đã vào, bấm xác nhận để trả bàn.";
      } else {
        el("paymentHelpText").textContent =
          "Thu đủ tiền mặt từ khách. Khi đã nhận tiền, bấm xác nhận để trả bàn.";
      }
      el("paymentConfirmBtn").onclick = function () {
        StaffTableStatus.closePaymentModal();
        StaffTableStatus.completePayment(id, method);
      };
      box.className = "bill-modal show";
    },

    updateBillTotal: function () {
      var adult = Math.max(0, Number(el("billAdultCount").value || 0));
      var child = Math.max(0, Number(el("billChildCount").value || 0));
      el("billAdultPrice").textContent = money(priceAdult());
      el("billChildPrice").textContent = money(priceChild());
      el("billTotalPrice").textContent = money(
        adult * priceAdult() + child * priceChild(),
      );
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
      var qrBase =
        typeof PUBLIC_BASE_URL !== "undefined" && PUBLIC_BASE_URL
          ? PUBLIC_BASE_URL
          : BASE_URL;
      var url =
        qrBase.replace(/\/+$/, "") + "/goi-mon?ma=" + encodeURIComponent(ma);
      var qrUrl =
        "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" +
        encodeURIComponent(url);
      var today = new Date();
      var ngayIn =
        (today.getDate() < 10 ? "0" : "") +
        today.getDate() +
        "-" +
        (today.getMonth() + 1 < 10 ? "0" : "") +
        (today.getMonth() + 1) +
        "-" +
        String(today.getFullYear()).slice(2);
      var nguoiLon = Number(t.phien_nguoi_lon || 0);
      var treEm = Number(t.phien_tre_em || 0);
      var tongKhach = nguoiLon + treEm;
      var tenKhach = t.phien_ten_khach || "";
      var sdtKhach = t.phien_sdt_khach || "";
      var tongTien = Number(
        t.phien_tong_tien || nguoiLon * priceAdult() + treEm * priceChild(),
      );
      var batDau = fmtDate(t.phien_bat_dau || "");
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
          '<div class="row"><span>Ngày in</span><strong>' +
          esc(ngayIn) +
          "</strong></div>" +
          '<div class="row"><span>Bàn</span><strong>' +
          esc(tenBan) +
          "</strong></div>" +
          '<div class="row"><span>Mã tạm thời</span><strong>' +
          esc(ma) +
          "</strong></div>" +
          "</div>" +
          '<div class="section">' +
          '<div class="row"><span>Tên khách</span><strong>' +
          esc(tenKhach) +
          "</strong></div>" +
          '<div class="row"><span>SĐT</span><strong>' +
          esc(sdtKhach) +
          "</strong></div>" +
          '<div class="row"><span>Mở phiên</span><strong>' +
          esc(batDau) +
          "</strong></div>" +
          '<div class="row"><span>Người lớn</span><strong>' +
          esc(nguoiLon) +
          "</strong></div>" +
          '<div class="row"><span>Trẻ em</span><strong>' +
          esc(treEm) +
          "</strong></div>" +
          '<div class="row"><span>Số lượng</span><strong>' +
          esc(tongKhach ? tongKhach + " khách" : "") +
          "</strong></div>" +
          '<div class="row"><span>Tổng giá</span><strong>' +
          esc(money(tongTien)) +
          "</strong></div>" +
          "</div>" +
          '<div class="qr"><img src="' +
          qrUrl +
          '"><div><strong>Quét QR để gọi món</strong></div></div>' +
          '<div class="note">Mã tạm thời chỉ có hiệu lực gọi món trong 100 phút.</div>' +
          "</div>" +
          "<script>window.onload=function(){window.print()}<\/script>" +
          "</body></html>",
      );
      win.document.close();
    },
  };
})();
