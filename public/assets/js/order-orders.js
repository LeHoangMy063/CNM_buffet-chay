// order-orders.js
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
        var sl = smap[status] || [
          o.trang_thai || o.status || "cho_phuc_vu",
          "s-pending",
        ];
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
          (getOrderNote(o)
            ? '<div class="o-note">' + esc(getOrderNote(o)) + "</div>"
            : "") +
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
      renderComboSets();
    })
    .catch(function (err) {
      console.error("refreshOrders error:", err);
    });
}

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

function toast(msg, err) {
  var t = document.getElementById("toast");
  t.textContent = msg;
  t.className = "toast show" + (err ? " err" : "");
  setTimeout(() => (t.className = "toast"), 3500);
}
