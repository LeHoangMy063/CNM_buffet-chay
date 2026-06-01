// order-init.js
/* INIT - chạy khi trang gọi món đã tải xong */
setInterval(refreshOrders, 25000);

document.addEventListener("DOMContentLoaded", function () {
  var suggestTitle = document.querySelector(".combo-suggest-head h2");
  if (suggestTitle) suggestTitle.textContent = "Món nên gọi tiếp";

  if (typeof INITIAL_ORDERS !== "undefined") {
    renderOrderStatusSidebar(INITIAL_ORDERS || []);
  }
  refreshPreferredSuggestions(0);
  updateVisibility();
  refreshOrders();

  var addOverlay = document.getElementById("addOverlay");
  if (addOverlay) {
    addOverlay.addEventListener("click", function (e) {
      if (e.target === this) this.className = "add-overlay";
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeOrderPanel();
  });

  document.querySelectorAll(".combo-pill").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var group = btn.closest(".combo-control-group");
      (group
        ? group.querySelectorAll(".combo-pill")
        : document.querySelectorAll(".combo-pill")
      ).forEach(function (item) {
        item.classList.remove("active");
      });
      btn.classList.add("active");
      refreshPreferredSuggestions();
    });
  });

  document
    .getElementById("suggestPeople")
    ?.addEventListener("change", function () {
      refreshPreferredSuggestions();
    });

  document
    .getElementById("suggestIntent")
    ?.addEventListener("input", function () {
      window.clearTimeout(window.__suggestIntentTimer);
      window.__suggestIntentTimer = window.setTimeout(
        refreshPreferredSuggestions,
        180,
      );
    });

  window.addEventListener("resize", function () {
    if (orderSidebarIsDocked()) {
      document.getElementById("ordersPanel")?.classList.remove("open");
      document.getElementById("ordersBackdrop")?.classList.remove("open");
    }
  });
});
