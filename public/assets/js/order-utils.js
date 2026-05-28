// order-utils.js

window.esc = function (s) {
  return String(s || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
};

window.mapOrderStatus = function (status) {
  if (status === "cho_phuc_vu") return "pending";
  if (status === "dang_che_bien") return "preparing";
  if (status === "da_phuc_vu") return "served";
  return status || "pending";
};

window.normalizeText = function (value) {
  return String(value || "")
    .toLowerCase()
    .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, "a")
    .replace(/[èéẹẻẽêềếệểễ]/g, "e")
    .replace(/[ìíịỉĩ]/g, "i")
    .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, "o")
    .replace(/[ùúụủũưừứựửữ]/g, "u")
    .replace(/[ỳýỵỷỹ]/g, "y")
    .replace(/đ/g, "d");
};

window.getOrderQty = function (order) {
  return parseInt(order.quantity || order.so_luong || 0, 10) || 0;
};

window.getOrderName = function (order) {
  return order.item_name || order.ten_mon || "";
};

window.getOrderNote = function (order) {
  return order.note || order.ghi_chu || "";
};

window.groupOrdersByStatus = function (orders) {
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
};

window.menuItemName = function (item) {
  return item.name || item.ten || "";
};

window.menuItemDesc = function (item) {
  return item.description || item.mo_ta || "";
};

window.menuItemText = function (item) {
  return normalizeText(
    menuItemName(item) +
      " " +
      (item.category || item.danh_muc || "") +
      " " +
      menuItemDesc(item),
  );
};

window.hasWord = function (text, words) {
  return words.some(function (word) {
    return text.indexOf(word) !== -1;
  });
};
