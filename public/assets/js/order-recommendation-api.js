// order-recommendation-api.js
function ghiHanhViGoiY(monId, loaiHanhVi, giaTri) {
  var fd = new FormData();
  fd.append("ma", CODE);
  fd.append("mon_an_id", monId || 0);
  fd.append("loai_hanh_vi", loaiHanhVi || "view_item");
  fd.append("gia_tri", giaTri || 1);

  fetch(BASE + "/goi-y-mon/hanh-vi", {
    method: "POST",
    body: fd,
  }).catch(function () {});
}

function taiGoiYHybrid(monId) {
  var box = document.getElementById("comboList");
  if (!box) return;

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
      if (!result.success || !result.data || !result.data.length) {
        renderComboSets();
        return;
      }

      hienThiGoiYHybrid(result.data);
    })
    .catch(function () {
      renderComboSets();
    });
}

function hienThiGoiYHybrid(items) {
  var box = document.getElementById("comboList");
  if (!box) return;

  var html =
    '<div class="ai-recommend-grid">' +
    items
      .map(function (item) {
        var ten = item.ten || item.name || "";
        var danhMuc = item.danh_muc || "";
        var lyDo = item.ly_do || "Phù hợp với món bạn đang chọn";
        var img = item.anh_url || item.hinh_anh || "";

        return (
          '<div class="ai-recommend-card">' +
            '<div class="ai-recommend-img-wrap">' +
              (img
                ? '<img src="' + esc(img) + '" alt="' + esc(ten) + '" class="ai-recommend-img">'
                : '<div class="ai-recommend-img-empty">🥗</div>'
              ) +
            '</div>' +

            '<div class="ai-recommend-info">' +
              '<div class="ai-recommend-badge">AI gợi ý</div>' +
              '<h3>' + esc(ten) + '</h3>' +
              '<p class="ai-recommend-category">' + esc(danhMuc) + '</p>' +
              '<p class="ai-recommend-reason">' + esc(lyDo) + '</p>' +

              '<button type="button" class="ai-recommend-btn" onclick="openAdd(' +
                item.id + ',\'' +
                esc(ten).replace(/'/g, "\\'") + '\',\'' +
                esc(item.mo_ta || '').replace(/'/g, "\\'") + '\',\'' +
                esc(img).replace(/'/g, "\\'") +
              '\')">+ Thêm món</button>' +
            '</div>' +
          '</div>'
        );
      })
      .join("") +
    '</div>';

  box.innerHTML = html;
}
