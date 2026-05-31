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

  function jsArg(v) {
    return (
      "'" +
      String(v == null ? "" : v)
        .replace(/\\/g, "\\\\")
        .replace(/'/g, "\\'") +
      "'"
    );
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
        var res = JSON.parse(xhr.responseText);
        if (res && res.chuyen_huong) window.location.href = res.chuyen_huong;
        cb(res);
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
        var res = JSON.parse(xhr.responseText);
        if (res && res.chuyen_huong) window.location.href = res.chuyen_huong;
        cb(res);
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

  function fmtDate(v) {
    if (!v) return "";
    var text = String(v);
    var m = text.match(/^(\d{4})-(\d{2})-(\d{2})(.*)$/);
    if (m) return m[3] + "-" + m[2] + "-" + m[1].slice(2) + m[4];
    m = text.match(/^(\d{2})\/(\d{2})\/(\d{4})(.*)$/);
    if (m) return m[1] + "-" + m[2] + "-" + m[3].slice(2) + m[4];
    return text;
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
    fmtDate: fmtDate,
    jsArg: jsArg,
    statusInfo: statusInfo,
    tableStatusInfo: tableStatusInfo,
  };

  window.StaffDashboard = {
    currentSection:
      typeof STAFF_ROLE !== "undefined" && STAFF_ROLE === "bep"
        ? "dat-ban"
        : "home",
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
      "xac-nhan-dat-ban": "Duyệt đặt bàn",
      "cap-nhat-trang-thai-ban": "Điều phối bàn",
      "xac-nhan-mon": "Quản lý gọi món",
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
      if (
        typeof STAFF_ROLE !== "undefined" &&
        STAFF_ROLE === "bep" &&
        name !== "xac-nhan-mon"
      ) {
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

  window.StaffRealtime = {
    source: null,
    version: "",
    enabled: !!window.EventSource,
    audioCtx: null,
    audioReady: false,
    unlockAudio: function () {
      try {
        var AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        if (!this.audioCtx) this.audioCtx = new AudioCtx();
        if (this.audioCtx.state === "suspended") this.audioCtx.resume();
        this.audioReady = true;
      } catch (e) {}
    },
    connect: function () {
      if (!this.enabled) return;
      if (this.source) {
        this.source.close();
        this.source = null;
      }

      var url = BASE_URL + "/nhan-vien/don-mon/su-kien";
      if (this.version) url += "?v=" + encodeURIComponent(this.version);

      var self = this;
      try {
        this.source = new EventSource(url);
      } catch (e) {
        this.enabled = false;
        return;
      }

      this.source.addEventListener("orders", function (ev) {
        var data = {};
        try {
          data = JSON.parse(ev.data || "{}");
        } catch (e) {
          data = {};
        }
        var oldVersion = self.version;
        self.version = data.version || self.version;

        if (oldVersion && data.don_cho > 0) {
          self.notifyNewOrder();
        }

        if (window.StaffOrders) {
          StaffOrders.loadTables();
          if (StaffOrders.selectedTableId) StaffOrders.loadOrders();
        }

        self.source.close();
        self.source = null;
        setTimeout(function () {
          self.connect();
        }, 250);
      });

      this.source.addEventListener("heartbeat", function () {
        self.source.close();
        self.source = null;
        setTimeout(function () {
          self.connect();
        }, 250);
      });

      this.source.onerror = function () {
        if (self.source) self.source.close();
        self.source = null;
        setTimeout(function () {
          self.connect();
        }, 4000);
      };
    },
    notifyNewOrder: function () {
      toast("Có đơn mới từ khách");
      try {
        var AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        if (!this.audioCtx) this.audioCtx = new AudioCtx();
        var ctx = this.audioCtx;
        if (ctx.state === "suspended") ctx.resume();

        var gain = ctx.createGain();
        gain.gain.setValueAtTime(0.0001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.42);
        gain.connect(ctx.destination);

        [880, 1175].forEach(function (freq, idx) {
          var osc = ctx.createOscillator();
          osc.type = "sine";
          osc.frequency.value = freq;
          osc.connect(gain);
          osc.start(ctx.currentTime + idx * 0.16);
          osc.stop(ctx.currentTime + idx * 0.16 + 0.14);
        });
        setTimeout(function () {
          gain.disconnect();
        }, 520);
      } catch (e) {}
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
      requestedTab =
        new URLSearchParams(window.location.search).get("tab") || "";
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
    StaffRealtime.connect();
    document.addEventListener(
      "click",
      function () {
        StaffRealtime.unlockAudio();
      },
      { once: true },
    );
    document.addEventListener(
      "touchstart",
      function () {
        StaffRealtime.unlockAudio();
      },
      { once: true },
    );

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

