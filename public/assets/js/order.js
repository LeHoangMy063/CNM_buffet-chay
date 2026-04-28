// ══════════════════════════════════════════════════════════════════════════════
// ORDER.JS - Quản lý gọi món, giỏ, và danh sách đơn
// ══════════════════════════════════════════════════════════════════════════════

var selId = null,
  selName = "",
  qty = 1;
var cart = [];
var pendingCollapsed = false,
  completedCollapsed = false;

var smap = {
  pending: ["⏳ Chờ", "s-pending"],
  preparing: ["👨‍🍳 Đang làm", "s-preparing"],
  served: ["✓ Hoàn thành", "s-served"],
};

function mapOrderStatus(status) {
  if (status === "cho_phuc_vu") return "pending";
  if (status === "dang_che_bien") return "preparing";
  if (status === "da_phuc_vu") return "served";
  return status || "pending";
}

function esc(s) {
  return String(s || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

/* ── Category filter ─────────────────────────────────── */
function orderSidebarIsDocked() {
  return window.matchMedia && window.matchMedia("(min-width: 681px)").matches;
}

function openOrderPanel() {
  if (orderSidebarIsDocked()) {
    document.body.classList.remove("order-sidebar-collapsed");
    return;
  }
  document.getElementById("ordersPanel")?.classList.add("open");
  document.getElementById("ordersBackdrop")?.classList.add("open");
}

function closeOrderPanel() {
  if (orderSidebarIsDocked()) {
    document.body.classList.add("order-sidebar-collapsed");
    return;
  }
  document.getElementById("ordersPanel")?.classList.remove("open");
  document.getElementById("ordersBackdrop")?.classList.remove("open");
}

function toggleOrderPanel() {
  if (orderSidebarIsDocked()) {
    document.body.classList.toggle("order-sidebar-collapsed");
    return;
  }
  var panel = document.getElementById("ordersPanel");
  if (panel && panel.classList.contains("open")) closeOrderPanel();
  else openOrderPanel();
}

function getOrderQty(order) {
  return parseInt(order.quantity || order.so_luong || 0, 10) || 0;
}

function getOrderName(order) {
  return order.item_name || order.ten_mon || "";
}

function getOrderNote(order) {
  return order.note || order.ghi_chu || "";
}

function groupOrdersByStatus(orders) {
  var grouped = [];
  var indexByKey = {};

  orders.forEach(function (order) {
    if (!order || !order.id) return;

    var status = mapOrderStatus(order.status || order.trang_thai);
    var key = [status, getOrderName(order), getOrderNote(order)].join("|");

    if (indexByKey[key] === undefined) {
      var copy = Object.assign({}, order);
      copy.status = status;
      copy.quantity = getOrderQty(order);
      copy.item_name = getOrderName(order);
      copy.note = getOrderNote(order);
      grouped.push(copy);
      indexByKey[key] = grouped.length - 1;
    } else {
      grouped[indexByKey[key]].quantity += getOrderQty(order);
    }
  });

  return grouped;
}

function filterCat(cat, btn) {
  document.querySelectorAll(".tab").forEach((t) => (t.className = "tab"));
  btn.className = "tab active";
  document.querySelectorAll(".cat-block").forEach((b) => {
    b.style.display = cat === "all" || b.dataset.cat === cat ? "" : "none";
  });
}

/* ── Add modal ───────────────────────────────────────── */
function openAdd(id, name, desc, img) {
  selId = id;
  selName = name;
  qty = 1;
  document.getElementById("am-img").src = img;
  document.getElementById("am-name").textContent = name;
  document.getElementById("am-desc").textContent = desc;
  document.getElementById("qn").textContent = 1;
  document.getElementById("noteInput").value = "";
  document.getElementById("addOverlay").className = "add-overlay open";
}

function dq(d) {
  qty = Math.max(1, Math.min(10, qty + d));
  document.getElementById("qn").textContent = qty;
}

document.getElementById("addOverlay").addEventListener("click", function (e) {
  if (e.target === this) this.className = "add-overlay";
});

/* ── Giỏ tạm ─────────────────────────────────────────── */
function addToCart() {
  var note = document.getElementById("noteInput").value.trim();
  document.getElementById("addOverlay").className = "add-overlay";

  var existing = cart.find((c) => c.id === selId && c.note === note);
  if (existing) {
    existing.qty = Math.min(10, existing.qty + qty);
  } else {
    cart.push({ id: selId, name: selName, qty, note });
  }

  renderCart();
  toast("Đã thêm " + selName + " vào danh sách");
}

function removeFromCart(idx) {
  cart.splice(idx, 1);
  renderCart();
}

function renderCart() {
  var list = document.getElementById("cartList");
  var btn = document.getElementById("confirmAllBtn");
  console.log("renderCart:", list, btn);
  if (!list || !btn) {
    console.error("cartList hoặc confirmAllBtn không tồn tại trong DOM!");
    return;
  }
  if (cart.length === 0) {
    list.innerHTML = '<div class="cart-empty">Chưa có món nào</div>';
    btn.style.display = "none";
    updateVisibility();
    return;
  }

  list.innerHTML = cart
    .map(
      (item, i) =>
        `<div class="o-item cart-item">
      <span class="o-qty">${item.qty}×</span>
      <div class="o-info">
        <div class="o-name">${esc(item.name)}</div>
        ${item.note ? `<div class="o-note">${esc(item.note)}</div>` : ""}
      </div>
      <button class="o-del" onclick="removeFromCart(${i})">✕</button>
    </div>`,
    )
    .join("");

  btn.style.display = "";
  updateVisibility();
}

/* ── Gửi giỏ lên server ──────────────────────────────── */
function submitAllCart() {
  if (!cart.length) {
    toast("Chưa có món nào 😅");
    return;
  }

  var btn = document.getElementById("confirmAllBtn");
  btn.disabled = true;
  btn.textContent = "Đang gửi...";

  var payloadItems = cart.slice().map(function (item) {
    return {
      mon_an_id: item.id,
      so_luong: item.qty,
      ghi_chu: item.note || "",
    };
  });

  var fdAll = new FormData();
  fdAll.append("ma", CODE);
  fdAll.append("items", JSON.stringify(payloadItems));

  fetch(BASE + "/goi-mon/dat", { method: "POST", body: fdAll })
    .then(function (res) {
      return res.json();
    })
    .then(function (data) {
      if (data.success) {
        cart = [];
        renderCart();
        toast("✓ Gọi món thành công!");
        refreshOrders();
      } else {
        toast("✕ " + (data.thong_bao || "Gửi thất bại, vui lòng thử lại"), true);
      }
    })
    .catch(function () {
      toast("✕ Lỗi kết nối", true);
    })
    .then(function () {
      btn.disabled = false;
      btn.textContent = "✓  Xác Nhận Gọi Món";
    });
  return;

  var successCount = 0,
    failCount = 0;
  var items = cart.slice(); // copy

  function sendNext(i) {
    if (i >= items.length) {
      // Xong hết
      cart = [];
      renderCart();
      if (failCount === 0) toast("✅ Gọi món thành công!");
      else if (successCount > 0)
        toast("⚠️ Gửi " + successCount + " món, " + failCount + " lỗi");
      else toast("❌ Gửi thất bại, vui lòng thử lại", true);

      // ✅ Gọi refresh NGAY sau khi xong
      refreshOrders();
      btn.disabled = false;
      btn.textContent = "✓  Xác Nhận Gọi Món";
      return;
    }

    var item = items[i];
    var fd = new FormData();
    fd.append("ma", CODE);
    fd.append("mon_an_id", item.id);
    fd.append("so_luong", item.qty);
    fd.append("ghi_chu", item.note || "");

    fetch(BASE + "/goi-mon/dat", { method: "POST", body: fd })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (data.success) successCount++;
        else failCount++;
      })
      .catch(function () {
        failCount++;
      })
      .then(function () {
        sendNext(i + 1);
      }); // gửi món tiếp
  }

  sendNext(0);
}

/* ── Cancel order ────────────────────────────────────── */
function cancelOrd(id) {
  if (!confirm("Huỷ món này?")) return;
  var fd = new FormData();
  fd.append("ma", CODE);
  fd.append("don_id", id);
  fetch(BASE + "/goi-mon/huy", { method: "POST", body: fd })
    .then((r) => r.json())
    .then((d) => {
      if (d.success) {
        document.getElementById("oi-" + id)?.remove();
        updateVisibility();
        toast("✓ " + (d.thong_bao || d.message || "Da huy mon"));
      } else {
        toast("❌ " + (d.thong_bao || d.message || "Khong the huy mon"), true);
      }
    })
    .catch(() => toast("❌ Lỗi kết nối", true));
}

/* ── Refresh đơn đã gửi ──────────────────────────────── */
function refreshOrders() {
  return fetch(BASE + "/goi-mon/danh-sach?ma=" + encodeURIComponent(CODE))
    .then(function (r) {
      return r.json();
    })
    .then(function (d) {
      if (!d.success) return;

      var pendingList = document.getElementById("pendingList");
      var completedList = document.getElementById("completedList");
      if (!pendingList || !completedList) return;

      pendingList.innerHTML = completedList.innerHTML = "";
      var orders = d.orders || d.danh_sach || [];
      var totalQty = orders.reduce(function (sum, order) {
        return sum + getOrderQty(order);
      }, 0);
      document.getElementById("ordCnt").textContent = totalQty;
      if (!orders.length) {
        updateVisibility();
        return;
      }

      groupOrdersByStatus(orders).forEach(function (o) {
        if (!o || !o.id) return;
        var status = mapOrderStatus(o.status || o.trang_thai);
        var sl = smap[status] || [o.trang_thai || o.status || "cho_phuc_vu", "s-pending"];
        var html =
          '<div class="o-item" id="oi-' +
          o.id +
          '">' +
          '<span class="o-qty">' +
          esc(getOrderQty(o)) +
          "×</span>" +
          '<div class="o-info">' +
          '<div class="o-name">' +
          esc(getOrderName(o)) +
          "</div>" +
          (getOrderNote(o) ? '<div class="o-note">' + esc(getOrderNote(o)) + "</div>" : "") +
          "</div>" +
          '<span class="o-status ' +
          sl[1] +
          '">' +
          sl[0] +
          "</span>" +
          "</div>";

        var target =
          status === "pending" || status === "preparing"
            ? pendingList
            : completedList;
        target.insertAdjacentHTML("beforeend", html);
      });

      updateVisibility();
    })
    .catch(function (err) {
      console.error("refreshOrders error:", err);
    });
}

/* ── Collapse sections ───────────────────────────────── */
function togglePending() {
  var list = document.getElementById("pendingList");
  pendingCollapsed = !pendingCollapsed;
  list.style.display = pendingCollapsed ? "none" : "";
  event.target.textContent = pendingCollapsed ? "∨" : "∧";
}

function toggleCompleted() {
  var list = document.getElementById("completedList");
  completedCollapsed = !completedCollapsed;
  list.style.display = completedCollapsed ? "none" : "";
  event.target.textContent = completedCollapsed ? "∨" : "∧";
}

/* ── Update visibility ───────────────────────────────── */
function updateVisibility() {
  var hasPending = document.getElementById("pendingList").children.length > 0;
  var hasCompleted =
    document.getElementById("completedList").children.length > 0;
  var hasCart = cart.length > 0;

  document.getElementById("pendingSection").style.display = hasPending
    ? ""
    : "none";
  document.getElementById("completedSection").style.display = hasCompleted
    ? ""
    : "none";
  document.getElementById("sbDivider").style.display =
    hasCart && (hasPending || hasCompleted) ? "" : "none";
  document.getElementById("sbDivider2").style.display =
    hasPending && hasCompleted ? "" : "none";
}

/* ── Toast ───────────────────────────────────────────── */
function toast(msg, err) {
  var t = document.getElementById("toast");
  t.textContent = msg;
  t.className = "toast show" + (err ? " err" : "");
  setTimeout(() => (t.className = "toast"), 3500);
}

/* ── Init ────────────────────────────────────────────── */
setInterval(refreshOrders, 25000);
document.addEventListener("DOMContentLoaded", () => {
  updateVisibility();
  refreshOrders();
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeOrderPanel();
  });
  window.addEventListener("resize", function () {
    if (orderSidebarIsDocked()) {
      document.getElementById("ordersPanel")?.classList.remove("open");
      document.getElementById("ordersBackdrop")?.classList.remove("open");
    }
  });
});
