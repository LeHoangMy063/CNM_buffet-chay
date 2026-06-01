function esc(v) {
  return String(v == null ? "" : v)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatDateShort(v) {
  if (!v) return "";
  var text = String(v);
  var m = text.match(/^(\d{4})-(\d{2})-(\d{2})(.*)$/);
  if (m) return m[3] + "-" + m[2] + "-" + m[1].slice(2) + m[4];
  m = text.match(/^(\d{2})\/(\d{2})\/(\d{4})(.*)$/);
  if (m) return m[1] + "-" + m[2] + "-" + m[3].slice(2) + m[4];
  return text;
}

function categoryLabel(value) {
  var labels = {
    "Khai vi": "Khai vị",
    "Mon chinh": "Món chính",
    "Nuoc lau": "Nước lẩu",
    "Do uong": "Đồ uống",
  };
  return labels[canonicalCategory(value)] || value;
}

function canonicalCategory(value) {
  var text = String(value || "").trim().toLowerCase();
  var map = {
    "khai vi": "Khai vi",
    "khai vị": "Khai vi",
    "mon chinh": "Mon chinh",
    "món chính": "Mon chinh",
    "nuoc lau": "Nuoc lau",
    "nước lẩu": "Nuoc lau",
    "topping": "Topping",
    "rau": "Rau",
    "do uong": "Do uong",
    "đồ uống": "Do uong",
  };
  return map[text] || value;
}

function toast(msg, err) {
  var e = document.getElementById("toast");
  e.textContent = msg;
  e.className = "toast show" + (err ? " err" : "");
  clearTimeout(e._t);
  e._t = setTimeout(function () {
    e.className = "toast";
  }, 2600);
}

function findItem(id) {
  for (var i = 0; i < ITEMS.length; i++) {
    if (String(ITEMS[i].id) === String(id)) {
      return ITEMS[i];
    }
  }
  return null;
}

function toggleMenu(menuId, trigger) {
  var menu = document.getElementById(menuId);
  if (!menu) {
    return;
  }

  var isOpen = menu.className.indexOf("open") !== -1;
  menu.className = isOpen ? "nav-sub" : "nav-sub open";
  if (trigger) {
    trigger.className = isOpen
      ? "nav-parent side-link active"
      : "nav-parent side-link active open";
  }
}

function showPanel(id) {
  if (id === "panel-add") {
    showAddForm();
    return;
  }

  var panels = document.querySelectorAll(".panel");
  for (var i = 0; i < panels.length; i++) {
    panels[i].className = "panel";
  }
  var panel = document.getElementById(id);
  if (!panel) {
    return;
  }
  panel.className = "panel active";

  var btns = document.querySelectorAll(".nav-action");
  for (var j = 0; j < btns.length; j++) {
    btns[j].className =
      btns[j].getAttribute("data-panel") === id
        ? "nav-action active"
        : "nav-action";
  }
}

function openModal(id, focusId) {
  var modal = document.getElementById(id);
  if (!modal) {
    return;
  }
  modal.classList.add("open");
  modal.setAttribute("aria-hidden", "false");
  document.body.classList.add("modal-open");
  if (focusId && document.getElementById(focusId)) {
    document.getElementById(focusId).focus();
  }
}

function closeModal(id) {
  var modal = document.getElementById(id);
  if (!modal) {
    return;
  }
  modal.classList.remove("open");
  modal.setAttribute("aria-hidden", "true");
  if (!document.querySelector(".modal.open")) {
    document.body.classList.remove("modal-open");
  }
}

function showAddForm() {
  var form = document.getElementById("addForm");
  if (!form) {
    return;
  }
  form.reset();
  openModal("addModal", "add_ten");
}

function hideAddForm() {
  closeModal("addModal");
}

function initCategories() {
  var listCategory = document.getElementById("listCategory");
  var addCategory = document.getElementById("add_danh_muc");
  var editCategory = document.getElementById("edit_danh_muc");
  var categories = ["Khai vi", "Mon chinh", "Nuoc lau", "Topping", "Rau", "Do uong"];

  setCategoryOptions(listCategory, categories, "Tất cả danh mục");
  setCategoryOptions(addCategory, categories, "Chọn danh mục");
  setCategoryOptions(editCategory, categories, "Chọn danh mục");
}

function setCategoryOptions(select, categories, placeholder) {
  if (!select) {
    return;
  }

  var currentValue = select.value;
  select.innerHTML = "";
  select.appendChild(new Option(placeholder, ""));

  for (var i = 0; i < categories.length; i++) {
    select.appendChild(new Option(categoryLabel(categories[i]), categories[i]));
  }

  if (currentValue) {
    select.value = currentValue;
  }
}

function rowHtml(it) {
  var html = "<tr>";
  html +=
    "<td>" +
    (it.anh_url
      ? '<img class="thumb" src="' + esc(it.anh_url) + '" alt="">'
      : '<div class="thumb"></div>') +
    "</td>";
  html +=
    "<td><strong>" +
    esc(it.ten || "-") +
    '</strong><div class="muted">' +
    esc(it.mo_ta || "") +
    "</div></td>";
  html += "<td>" + esc(categoryLabel(it.danh_muc || "-")) + "</td>";
  html +=
    '<td><span class="badge ' +
    (Number(it.con_mon || 0) ? "ok" : "off") +
    '">' +
    (Number(it.con_mon || 0) ? "Đang hiện" : "Đang ẩn") +
    "</span> ";
  if (Number(it.noi_bat || 0)) {
    html += '<span class="badge star">Nổi bật</span>';
  }
  html += "</td>";
  html +=
    "<td>" +
    '<button class="btn secondary js-menu-edit" type="button" data-id="' +
    esc(it.id || "") +
    '">Sửa</button> ' +
    '<button class="btn danger js-menu-delete" type="button" data-id="' +
    esc(it.id || "") +
    '">Xóa</button>' +
    "</td>";
  html += "</tr>";
  return html;
}

function filteredRows(searchId, categoryId) {
  var q = document.getElementById(searchId).value.toLowerCase();
  var cat = document.getElementById(categoryId).value;
  var html = "";
  var count = 0;

  for (var i = 0; i < ITEMS.length; i++) {
    var it = ITEMS[i];
    var haystack = (
      (it.ten || "") +
      " " +
      (it.danh_muc || "") +
      " " +
      categoryLabel(it.danh_muc || "") +
      " " +
      (it.mo_ta || "")
    ).toLowerCase();
    if (q && haystack.indexOf(q) === -1) {
      continue;
    }
    if (cat && canonicalCategory(it.danh_muc) !== cat) {
      continue;
    }
    count++;
    html += rowHtml(it);
  }

  return count
    ? html
    : '<tr><td colspan="5" class="empty">Không có món phù hợp.</td></tr>';
}

function renderList() {
  if (!ITEMS.length && typeof ITEM_COUNT !== "undefined" && ITEM_COUNT > 0) {
    toast("Không tải được dữ liệu danh sách cho JavaScript", true);
    return;
  }

  document.getElementById("listRows").innerHTML = filteredRows(
    "listSearch",
    "listCategory"
  );
}

function chooseEdit(id) {
  var it = findItem(id);
  if (!it) {
    return;
  }

  document.getElementById("edit_id").value = it.id;
  document.getElementById("edit_ten").value = it.ten || "";
  document.getElementById("edit_danh_muc").value = canonicalCategory(it.danh_muc || "");
  document.getElementById("edit_mo_ta").value = it.mo_ta || "";
  document.getElementById("edit_anh_url").value = it.anh_url || "";
  document.getElementById("edit_con_mon").checked =
    Number(it.con_mon || 0) === 1;
  document.getElementById("edit_noi_bat").checked =
    Number(it.noi_bat || 0) === 1;

  openModal("editModal", "edit_ten");
}

function hideEditForm() {
  closeModal("editModal");
}

function submitForm(form) {
  var fd = new FormData(form);
  if (
    form.querySelector("[name=con_mon]") &&
    !form.querySelector("[name=con_mon]").checked
  ) {
    fd.set("con_mon", "0");
  }
  if (
    form.querySelector("[name=noi_bat]") &&
    !form.querySelector("[name=noi_bat]").checked
  ) {
    fd.set("noi_bat", "0");
  }

  fetch(BASE_URL + "/quan-ly/thuc-don/luu", { method: "POST", body: fd })
    .then(function (r) {
      return r.json();
    })
    .then(function (res) {
      toast(res.thong_bao || "Đã xử lý", !res.success);
      if (res.success) {
        setTimeout(function () {
          location.reload();
        }, 500);
      }
    })
    .catch(function () {
      toast("Không kết nối được máy chủ", true);
    });
}

function deleteItem(id) {
  var it = findItem(id);
  if (!confirm('Xóa món "' + (it ? it.ten : id) + '"?')) {
    return;
  }

  var fd = new FormData();
  fd.append("id", id);
  fetch(BASE_URL + "/quan-ly/thuc-don/xoa", { method: "POST", body: fd })
    .then(function (r) {
      return r.json();
    })
    .then(function (res) {
      toast(res.thong_bao || "Đã xử lý", !res.success);
      if (res.success) {
        setTimeout(function () {
          location.reload();
        }, 500);
      }
    })
    .catch(function () {
      toast("Không kết nối được máy chủ", true);
    });
}

function staffRoleLabel(value) {
  var labels = {
    quanly: "Quản lý",
    quan_ly: "Quản lý",
    nhanvien: "Nhân viên",
    nhan_vien: "Nhân viên",
    bep: "Bếp",
  };
  return labels[value] || value || "-";
}

function staffStatusLabel(active) {
  return active ? "Đang hoạt động" : "Đã khóa";
}

function compactNumber(value) {
  var n = Number(value || 0);
  if (n >= 1000000) {
    return Math.round(n / 100000) / 10 + "tr";
  }
  if (n >= 1000) {
    return Math.round(n / 100) / 10 + "k";
  }
  return String(n);
}

function fitCanvas(canvas) {
  var rect = canvas.getBoundingClientRect();
  var ratio = window.devicePixelRatio || 1;
  var width = Math.max(320, Math.floor(rect.width || canvas.width));
  var height = Math.max(240, Math.floor(rect.height || 300));
  canvas.width = width * ratio;
  canvas.height = height * ratio;
  var ctx = canvas.getContext("2d");
  ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
  return { ctx: ctx, width: width, height: height };
}

function drawEmptyChart(ctx, width, height, message) {
  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = "#f8faf5";
  ctx.fillRect(0, 0, width, height);
  ctx.fillStyle = "#7b887f";
  ctx.font = "700 13px Be Vietnam Pro, Arial";
  ctx.textAlign = "center";
  ctx.fillText(message || "Chua co du lieu", width / 2, height / 2);
}

function drawLineChart(canvasId, rows, valueKey, labelKey) {
  var canvas = document.getElementById(canvasId);
  if (!canvas) {
    return;
  }
  var fitted = fitCanvas(canvas);
  var ctx = fitted.ctx;
  var width = fitted.width;
  var height = fitted.height;
  var data = rows || [];
  if (!data.length) {
    drawEmptyChart(ctx, width, height, "Chua co du lieu khach buffet");
    return;
  }

  var pad = { left: 54, right: 20, top: 22, bottom: 42 };
  var chartW = width - pad.left - pad.right;
  var chartH = height - pad.top - pad.bottom;
  var max = 0;
  for (var i = 0; i < data.length; i++) {
    max = Math.max(max, Number(data[i][valueKey] || 0));
  }
  max = max || 1;

  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = "#fff";
  ctx.fillRect(0, 0, width, height);
  ctx.strokeStyle = "#e0e5dc";
  ctx.lineWidth = 1;
  ctx.fillStyle = "#7b887f";
  ctx.font = "600 11px Be Vietnam Pro, Arial";
  ctx.textAlign = "right";

  for (var g = 0; g <= 4; g++) {
    var y = pad.top + chartH - (chartH * g) / 4;
    ctx.beginPath();
    ctx.moveTo(pad.left, y);
    ctx.lineTo(width - pad.right, y);
    ctx.stroke();
    ctx.fillText(compactNumber((max * g) / 4), pad.left - 8, y + 4);
  }

  ctx.beginPath();
  for (var j = 0; j < data.length; j++) {
    var x = pad.left + (data.length === 1 ? chartW / 2 : (chartW * j) / (data.length - 1));
    var yPoint = pad.top + chartH - (Number(data[j][valueKey] || 0) / max) * chartH;
    if (j === 0) {
      ctx.moveTo(x, yPoint);
    } else {
      ctx.lineTo(x, yPoint);
    }
  }
  ctx.strokeStyle = "#2f6b4f";
  ctx.lineWidth = 3;
  ctx.stroke();

  ctx.fillStyle = "#2f6b4f";
  for (var k = 0; k < data.length; k++) {
    var px = pad.left + (data.length === 1 ? chartW / 2 : (chartW * k) / (data.length - 1));
    var py = pad.top + chartH - (Number(data[k][valueKey] || 0) / max) * chartH;
    ctx.beginPath();
    ctx.arc(px, py, 4, 0, Math.PI * 2);
    ctx.fill();
  }

  ctx.fillStyle = "#526157";
  ctx.textAlign = "center";
  var step = Math.max(1, Math.ceil(data.length / 8));
  for (var l = 0; l < data.length; l += step) {
    var lx = pad.left + (data.length === 1 ? chartW / 2 : (chartW * l) / (data.length - 1));
    ctx.fillText(formatDateShort(data[l][labelKey] || "").slice(0, 8), lx, height - 16);
  }
}

function drawBarChart(canvasId, rows, valueKey, labelKey, emptyMessage) {
  var canvas = document.getElementById(canvasId);
  if (!canvas) {
    return;
  }
  var fitted = fitCanvas(canvas);
  var ctx = fitted.ctx;
  var width = fitted.width;
  var height = fitted.height;
  var data = rows || [];
  if (!data.length) {
    drawEmptyChart(ctx, width, height, emptyMessage);
    return;
  }

  var pad = { left: 18, right: 18, top: 18, bottom: 86 };
  var chartW = width - pad.left - pad.right;
  var chartH = height - pad.top - pad.bottom;
  var max = 0;
  for (var i = 0; i < data.length; i++) {
    max = Math.max(max, Number(data[i][valueKey] || 0));
  }
  max = max || 1;

  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = "#fff";
  ctx.fillRect(0, 0, width, height);

  var gap = 10;
  var barW = Math.max(22, (chartW - gap * (data.length - 1)) / data.length);
  for (var j = 0; j < data.length; j++) {
    var value = Number(data[j][valueKey] || 0);
    var barH = (value / max) * chartH;
    var x = pad.left + j * (barW + gap);
    var y = pad.top + chartH - barH;
    ctx.fillStyle = j % 2 ? "#b7791f" : "#2f6b4f";
    ctx.fillRect(x, y, barW, barH);
    ctx.fillStyle = "#17211c";
    ctx.font = "800 12px Be Vietnam Pro, Arial";
    ctx.textAlign = "center";
    ctx.fillText(compactNumber(value), x + barW / 2, Math.max(14, y - 6));

    ctx.save();
    ctx.translate(x + barW / 2, height - 72);
    ctx.rotate(-Math.PI / 5);
    ctx.fillStyle = "#526157";
    ctx.font = "600 11px Be Vietnam Pro, Arial";
    ctx.textAlign = "right";
    ctx.fillText(String(data[j][labelKey] || "-").slice(0, 18), 0, 0);
    ctx.restore();
  }
}

function drawHourChart() {
  var rows = [];
  var source = typeof HOUR_CHART === "undefined" ? [] : HOUR_CHART;
  for (var i = 0; i < source.length; i++) {
    rows.push({
      label: String(source[i].gio || "0") + "h",
      value: Number(source[i].so_mon || 0),
    });
  }
  drawBarChart("hourCanvas", rows, "value", "label", "Chua co du lieu tai bep theo gio");
}

function initDashboardCharts() {
  var revenueKey =
    typeof MANAGER_SECTION !== "undefined" && MANAGER_SECTION === "bao-cao"
      ? "doanh_thu"
      : "so_khach";
  drawLineChart("revenueCanvas", typeof REVENUE_CHART === "undefined" ? [] : REVENUE_CHART, revenueKey, "ngay");
  drawBarChart("topDishCanvas", typeof TOP_DISH_CHART === "undefined" ? [] : TOP_DISH_CHART, "tong_ban", "ten", "Chua co du lieu mon duoc goi");
  drawBarChart("categoryCanvas", typeof CATEGORY_CHART === "undefined" ? [] : CATEGORY_CHART, "tong_ban", "danh_muc", "Chua co du lieu co cau nhom mon");
  drawHourChart();
}

function findStaff(id) {
  for (var i = 0; i < STAFF_ITEMS.length; i++) {
    if (String(STAFF_ITEMS[i].id) === String(id)) {
      return STAFF_ITEMS[i];
    }
  }
  return null;
}

function staffRowHtml(staff) {
  var active = Number(staff.dang_hoat_dong || 0) === 1;
  var html = "<tr>";
  html +=
    "<td><strong>" +
    esc(staff.ho_ten || "-") +
    "</strong></td>";
  html +=
    "<td><div>" +
    esc(staff.email || "") +
    '</div><div class="muted">' +
    esc(staff.so_dien_thoai || "") +
    "</div></td>";
  html += "<td>" + esc(staffRoleLabel(staff.vai_tro)) + "</td>";
  html +=
    '<td><span class="badge ' +
    (active ? "ok" : "off") +
    '">' +
    staffStatusLabel(active) +
    "</span></td>";
  html +=
    '<td><button class="btn secondary js-staff-edit" type="button" data-id="' +
    esc(staff.id || "") +
    '">Sửa</button> ' +
    '<button class="btn danger js-staff-delete" type="button" data-id="' +
    esc(staff.id || "") +
    '">Xóa</button></td>';
  html += "</tr>";
  return html;
}

function renderStaffList() {
  var rows = document.getElementById("staffRows");
  if (!rows) {
    return;
  }

  var search = document.getElementById("staffSearch");
  var roleFilter = document.getElementById("staffRoleFilter");
  var q = search ? search.value.toLowerCase() : "";
  var role = roleFilter ? roleFilter.value : "";
  var html = "";
  var count = 0;

  for (var i = 0; i < STAFF_ITEMS.length; i++) {
    var staff = STAFF_ITEMS[i];
    var haystack = (
      (staff.ho_ten || "") +
      " " +
      (staff.ten_dang_nhap || "") +
      " " +
      (staff.email || "") +
      " " +
      (staff.so_dien_thoai || "") +
      " " +
      staffRoleLabel(staff.vai_tro)
    ).toLowerCase();
    if (q && haystack.indexOf(q) === -1) {
      continue;
    }
    if (role && staff.vai_tro !== role) {
      continue;
    }
    count++;
    html += staffRowHtml(staff);
  }

  rows.innerHTML = count
    ? html
    : '<tr><td colspan="5" class="empty">Chưa có nhân viên phù hợp.</td></tr>';
}

function showStaffForm() {
  var form = document.getElementById("staffForm");
  if (!form) {
    return;
  }
  form.reset();
  document.getElementById("staff_id").value = "";
  document.getElementById("staff_vai_tro").value = "nhanvien";
  document.getElementById("staff_dang_hoat_dong").value = "1";
  document.getElementById("staffModalTitle").textContent = "Thêm nhân viên";
  document.getElementById("staff_mat_khau").required = true;
  openModal("staffModal", "staff_ho_ten");
}

function hideStaffForm() {
  closeModal("staffModal");
}

function editStaff(id) {
  var staff = findStaff(id);
  if (!staff) {
    return;
  }

  document.getElementById("staff_id").value = staff.id;
  document.getElementById("staff_ho_ten").value = staff.ho_ten || "";
  document.getElementById("staff_ten_dang_nhap").value = staff.ten_dang_nhap || "";
  document.getElementById("staff_email").value = staff.email || "";
  document.getElementById("staff_so_dien_thoai").value = staff.so_dien_thoai || "";
  document.getElementById("staff_vai_tro").value = staff.vai_tro || "nhanvien";
  document.getElementById("staff_dang_hoat_dong").value =
    Number(staff.dang_hoat_dong || 0) === 1 ? "1" : "0";
  document.getElementById("staff_mat_khau").value = "";
  document.getElementById("staff_mat_khau").required = false;
  document.getElementById("staffModalTitle").textContent = "Sửa nhân viên";
  openModal("staffModal", "staff_ho_ten");
}

function viewStaff(id) {
  var staff = findStaff(id);
  if (!staff) {
    return;
  }

  document.getElementById("staffDetailBody").innerHTML =
    '<div class="detail-grid">' +
    '<div><span>Họ tên</span><strong>' +
    esc(staff.ho_ten || "-") +
    "</strong></div>" +
    '<div><span>Tên đăng nhập</span><strong>' +
    esc(staff.ten_dang_nhap || "-") +
    "</strong></div>" +
    '<div><span>Email</span><strong>' +
    esc(staff.email || "-") +
    "</strong></div>" +
    '<div><span>Số điện thoại</span><strong>' +
    esc(staff.so_dien_thoai || "-") +
    "</strong></div>" +
    '<div><span>Vai trò</span><strong>' +
    esc(staffRoleLabel(staff.vai_tro)) +
    "</strong></div>" +
    '<div><span>Trạng thái</span><strong>' +
    staffStatusLabel(Number(staff.dang_hoat_dong || 0) === 1) +
    "</strong></div>" +
    '<div><span>Ngày tạo</span><strong>' +
    esc(formatDateShort(staff.ngay_tao || "") || "-") +
    "</strong></div>" +
    "</div>";
  openModal("staffDetailModal");
}

function hideStaffDetail() {
  closeModal("staffDetailModal");
}

function submitStaffForm(form) {
  var fd = new FormData(form);
  fetch(BASE_URL + "/quan-ly/nhan-vien/luu", { method: "POST", body: fd })
    .then(function (r) {
      return r.json();
    })
    .then(function (res) {
      toast(res.thong_bao || "Đã xử lý", !res.success);
      if (res.success) {
        setTimeout(function () {
          location.reload();
        }, 500);
      }
    })
    .catch(function () {
      toast("Không kết nối được máy chủ", true);
    });
}

function deleteStaff(id) {
  var staff = findStaff(id);
  if (!confirm('Xóa nhân viên "' + (staff ? staff.ho_ten : id) + '"?')) {
    return;
  }

  var fd = new FormData();
  fd.append("id", id);
  fetch(BASE_URL + "/quan-ly/nhan-vien/xoa", { method: "POST", body: fd })
    .then(function (r) {
      return r.json();
    })
    .then(function (res) {
      toast(res.thong_bao || "Đã xử lý", !res.success);
      if (res.success) {
        setTimeout(function () {
          location.reload();
        }, 500);
      }
    })
    .catch(function () {
      toast("Không kết nối được máy chủ", true);
    });
}

var addForm = document.getElementById("addForm");
if (addForm) {
  addForm.onsubmit = function (e) {
    e.preventDefault();
    submitForm(this);
  };
}

var editForm = document.getElementById("editForm");
if (editForm) {
  editForm.onsubmit = function (e) {
    e.preventDefault();
    submitForm(this);
  };
}

var staffForm = document.getElementById("staffForm");
if (staffForm) {
  staffForm.onsubmit = function (e) {
    e.preventDefault();
    submitStaffForm(this);
  };
}

document.addEventListener("click", function (e) {
  var target = e.target;
  if (!target || !target.getAttribute) return;

  if (target.className.indexOf("js-menu-edit") !== -1) {
    chooseEdit(target.getAttribute("data-id"));
  } else if (target.className.indexOf("js-menu-delete") !== -1) {
    deleteItem(target.getAttribute("data-id"));
  } else if (target.className.indexOf("js-staff-edit") !== -1) {
    editStaff(target.getAttribute("data-id"));
  } else if (target.className.indexOf("js-staff-delete") !== -1) {
    deleteStaff(target.getAttribute("data-id"));
  }
});

document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    hideAddForm();
    hideEditForm();
    hideStaffForm();
    hideStaffDetail();
  }
});

if (
  typeof MANAGER_SECTION !== "undefined" &&
  (MANAGER_SECTION === "dashboard" || MANAGER_SECTION === "bao-cao")
) {
  initDashboardCharts();
  window.addEventListener("resize", initDashboardCharts);
} else if (typeof MANAGER_SECTION === "undefined" || MANAGER_SECTION === "thuc-don") {
  initCategories();
  renderList();
} else if (MANAGER_SECTION === "nhan-vien") {
  renderStaffList();
}
