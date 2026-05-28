// order-state.js
// ORDER STATE - biến dùng chung cho trang gọi món
var selId = null,
  selName = "",
  qty = 1;

var cart = [];
var pendingCollapsed = false,
  completedCollapsed = false;

var suggestionNonce = 0;

var smap = {
  pending: ["⏳ Chờ", "s-pending"],
  preparing: ["👨‍🍳 Đang làm", "s-preparing"],
  served: ["✓ Hoàn thành", "s-served"],
};
