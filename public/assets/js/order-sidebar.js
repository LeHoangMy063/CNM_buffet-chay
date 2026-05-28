// order-sidebar.js
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

function filterCat(cat, btn) {
  document.querySelectorAll(".tab").forEach((t) => (t.className = "tab"));
  btn.className = "tab active";
  document.querySelectorAll(".cat-block").forEach((b) => {
    b.style.display = cat === "all" || b.dataset.cat === cat ? "" : "none";
  });
}
