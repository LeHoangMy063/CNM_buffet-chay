// order-cart-modal.js
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

  ghiHanhViGoiY(id, "view_item", 1);
  taiGoiYHybrid(id);
}

function dq(d) {
  qty = Math.max(1, Math.min(10, qty + d));
  document.getElementById("qn").textContent = qty;
}

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
  ghiHanhViGoiY(selId, "add_to_order", 5);
  taiGoiYHybrid(selId);
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
    renderComboSets();
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
  renderComboSets();
}

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
        payloadItems.forEach(function (item) {
          ghiHanhViGoiY(item.mon_an_id, "submit_order", 7);
        });

        cart = [];
        renderCart();
        toast("Gọi món thành công!");
        refreshOrders();
        taiGoiYHybrid(0);
      } else {
        toast(
          "✕ " + (data.thong_bao || "Gửi thất bại, vui lòng thử lại"),
          true
        );
      }
    })
    .catch(function () {
      toast("✕ Lỗi kết nối", true);
    })
    .then(function () {
      btn.disabled = false;
      btn.textContent = "Xác Nhận Gọi Món";
    });
}
