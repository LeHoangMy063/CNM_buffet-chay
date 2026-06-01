// order-recommendation-api.js
function normalizeRecommendationAction(action) {
  switch (action) {
    case "view_item":
      return "them_mon";
    case "add_to_order":
      return "them_mon";
    case "submit_order":
      return "goi_mon";
    case "cancel_order":
      return "huy_mon";
    case "them_mon":
    case "goi_mon":
    case "huy_mon":
      return action;
    default:
      return "them_mon";
  }
}

function ghiHanhViGoiY(monId, loaiHanhVi, giaTri) {
  var fd = new FormData();
  fd.append("ma", CODE);
  fd.append("mon_an_id", monId || 0);
  fd.append("loai_hanh_vi", normalizeRecommendationAction(loaiHanhVi || "view_item"));
  fd.append("gia_tri", giaTri || 1);

  fetch(BASE + "/goi-y-mon/hanh-vi", {
    method: "POST",
    body: fd,
  }).catch(function () {});
}

window.__lastSuggestMonId = window.__lastSuggestMonId || 0;
window.__suggestRequestId = window.__suggestRequestId || 0;

function refreshPreferredSuggestions(monId) {
  if (monId && typeof monId === "object") {
    monId = undefined;
  }

  if (typeof monId !== "undefined") {
    window.__lastSuggestMonId = monId || 0;
  }

  taiGoiYHybrid(window.__lastSuggestMonId || 0);
}

function taiGoiYHybrid(monId) {
  var box = document.getElementById("comboList");
  if (!box) return;

  window.__lastSuggestMonId = monId || 0;
  var requestId = ++window.__suggestRequestId;

  fetch(
    BASE +
      "/goi-y-mon?ma=" +
      encodeURIComponent(CODE) +
      "&mon_id=" +
      encodeURIComponent(monId || 0),
  )
    .then(function (res) {
      return res.json();
    })
    .then(function (result) {
      if (requestId !== window.__suggestRequestId) return;

      if (!result.success || !result.data || !result.data.length) {
        renderComboSets();
        return;
      }

      hienThiGoiYHybrid(result.data, result.che_do || "");
    })
    .catch(function () {
      if (requestId !== window.__suggestRequestId) return;
      renderComboSets();
    });
}

function recommendationCategoryLabel(value) {
  var key = normalizeText(value || "");
  if (key.indexOf("khai") !== -1) return "Khai vị";
  if (key.indexOf("nuoc") !== -1 || key.indexOf("lau") !== -1)
    return "Nước lẩu";
  if (key.indexOf("chinh") !== -1) return "Món chính";
  if (key.indexOf("rau") !== -1) return "Rau";
  if (key.indexOf("topping") !== -1) return "Topping";
  if (key.indexOf("uong") !== -1) return "Đồ uống";
  return value || "";
}

function recommendationCategoryOrder(value) {
  var label = recommendationCategoryLabel(value);
  if (label === "Khai vị") return 1;
  if (label === "Nước lẩu") return 2;
  if (label === "Món chính") return 3;
  if (label === "Rau") return 4;
  if (label === "Topping") return 5;
  if (label === "Đồ uống") return 9;
  return 6;
}

function addSuggestedItem(index) {
  var set = window.__comboSets && window.__comboSets[0];
  var item = set && set.items && set.items[index];
  if (!item) return;

  openAdd(item.id, menuItemName(item), item.mo_ta || "", item.anh_url || "");
}

function hienThiGoiYHybrid(items, mode) {
  var box = document.getElementById("comboList");
  if (!box) return;
  var isChoosingHotpot = mode === "chon_lau";

  var comboItems = items
    .map(function (item) {
      var danhMuc = recommendationCategoryLabel(item.danh_muc || "");
      return {
        id: item.id,
        ten: item.ten || item.name || "",
        name: item.ten || item.name || "",
        mo_ta: item.mo_ta || "",
        danh_muc: danhMuc,
        category: danhMuc,
        anh_url: item.anh_url || item.hinh_anh || "",
        ly_do: item.ly_do || "Phù hợp với dữ liệu gọi món",
        diem_goi_y: Number(item.diem_goi_y || item.diem_batch || 0),
        diem_di_cung: Number(item.diem_di_cung || 0),
        diem_pho_bien: Number(item.diem_pho_bien || 0),
        best_seller: Number(item.best_seller || 0) === 1,
        nhan_goi_y: item.nhan_goi_y || "",
        xep_hang: Number(item.xep_hang || 0),
      };
    })
    .sort(function (a, b) {
      var aRank = a.xep_hang > 0 ? a.xep_hang : Number.MAX_SAFE_INTEGER;
      var bRank = b.xep_hang > 0 ? b.xep_hang : Number.MAX_SAFE_INTEGER;
      if (aRank !== bRank) return aRank - bRank;
      if (!isChoosingHotpot) {
        var orderDiff =
          recommendationCategoryOrder(a.danh_muc) -
          recommendationCategoryOrder(b.danh_muc);
        if (orderDiff !== 0) return orderDiff;
      }
      return (b.diem_goi_y || 0) - (a.diem_goi_y || 0);
    });

  var title = isChoosingHotpot ? "Chọn nước lẩu trước" : "Món nên gọi tiếp";
  var note = isChoosingHotpot
    ? "Bắt đầu bằng một nồi lẩu, sau đó hệ thống sẽ gợi ý rau, topping và món ăn kèm dựa trên dữ liệu gọi món."
    : "Gợi ý món hợp với nước lẩu bạn đã chọn, ưu tiên món phổ biến và đẩy đồ uống xuống cuối.";

  window.__comboSets = [
    {
      title: title,
      mood: note,
      items: comboItems,
    },
  ];

  var itemsHtml = comboItems
    .map(function (item, index) {
      var badge = item.best_seller
        ? "Best seller"
        : item.nhan_goi_y || "Ph\u1ed5 bi\u1ebfn";
      var stats = item.diem_pho_bien > 0
          ? "\u0110\u00e3 g\u1ecdi " + item.diem_pho_bien + " l\u1ea7n"
          : "";
      return (
        '<div class="combo-dish combo-dish-recommend' +
        (item.best_seller ? " is-best-seller" : "") +
        '">' +
        '<div class="combo-dish-badges">' +
        '<span class="combo-rank">#' +
        esc(item.xep_hang || index + 1) +
        "</span>" +
        '<span class="combo-signal">' +
        esc(badge) +
        "</span>" +
        "</div>" +
        '<div class="combo-dish-main"><strong>' +
        esc(menuItemName(item)) +
        "</strong>" +
        (item.danh_muc ? "<small>" + esc(item.danh_muc) + "</small>" : "") +
        "</div>" +
        (stats ? '<div class="combo-dish-stats">' + esc(stats) + "</div>" : "") +
        (item.ly_do ? "<small>" + esc(item.ly_do) + "</small>" : "") +
        '<button type="button" class="combo-dish-add" onclick="addSuggestedItem(' +
        index +
        ')">+ Thêm</button>' +
        "</div>"
      );
    })
    .join("");

  box.innerHTML =
    '<article class="combo-card">' +
    '<div class="combo-card-top"><span>Gợi ý thông minh</span></div>' +
    "<h3>" +
    esc(title) +
    "</h3>" +
    '<p class="combo-card-note">' +
    esc(note) +
    "</p>" +
    '<div class="combo-items">' +
    itemsHtml +
    "</div>" +
    (isChoosingHotpot
      ? ""
      : '<button type="button" onclick="addComboSet(0)">+ Thêm gợi ý này</button>') +
    "</article>";
}
