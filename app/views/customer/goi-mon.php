<?php
$table = isset($table) ? $table : (isset($ban) ? $ban : array());
$currentOrders = isset($currentOrders) ? $currentOrders : (isset($donHienTai) ? $donHienTai : array());
$menuByCategory = isset($menuByCategory) ? $menuByCategory : (isset($thucDonTheoDanhMuc) ? $thucDonTheoDanhMuc : array());

$tableNumber = isset($table['table_number'])
    ? $table['table_number']
    : (isset($table['so_ban']) ? $table['so_ban'] : '');
$accessCode = isset($table['access_code'])
    ? $table['access_code']
    : (isset($table['ma_truy_cap']) ? $table['ma_truy_cap'] : (isset($ma) ? $ma : ''));
$orderCssVersion = filemtime(dirname(__FILE__) . '/../../../public/assets/css/customer/order.css');
$checkoutCssVersion = filemtime(dirname(__FILE__) . '/../../../public/assets/css/customer/checkout.css');
$orderJsVersion = filemtime(dirname(__FILE__) . '/../../../public/assets/js/order.js');
$checkoutJsVersion = filemtime(dirname(__FILE__) . '/../../../public/assets/js/checkout.js');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gọi Món — Bàn <?php echo htmlspecialchars($tableNumber) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Lora:ital,wght@0,600;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/customer/order.css?v=<?php echo $orderCssVersion ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/customer/checkout.css?v=<?php echo $checkoutCssVersion ?>">
</head>

<body>

    <!-- ── TOPBAR ─────────────────────────────────────────────── -->
    <div class="topbar">
        <div class="topbar-left">
            <a href="<?php echo BASE_URL ?>" class="topbar-back">← Trang chủ</a>
            <div class="topbar-divider"></div>
            <div class="topbar-brand">
                <span class="leaf-icon">🌿</span>
                <span class="topbar-brand-name">
                    Buffet Chay
                    <span class="topbar-brand-table">· Bàn <?php echo htmlspecialchars($tableNumber) ?></span>
                </span>
            </div>
        </div>
        <button class="cart-btn" onclick="toggleOrderPanel()">
            🍽 Đã gọi <span class="badge" id="ordCnt"><?php echo count($currentOrders) ?></span>
        </button>
    </div>

    <!-- ── LAYOUT ─────────────────────────────────────────────── -->
    <div class="layout">

        <!-- MENU -->
        <div class="menu-area">
            <div class="tabs" id="tabs">
                <button class="tab active" onclick="filterCat('all',this)">Tất cả</button>
                <?php foreach (array_keys($menuByCategory) as $cat) { ?>
                    <button class="tab" onclick="filterCat('<?php echo htmlspecialchars($cat) ?>',this)">
                        <?php echo htmlspecialchars($cat) ?>
                    </button>
                <?php } ?>
            </div>

            <?php
            $catEmoji = array(
                'Khai vị'     => '🍽️',
                'Món chính'   => '🍜',
                'Topping'     => '🍢',
                'Rau'         => '🥬',
                'Nước lẩu'    => '🍲',
                'Đồ uống'     => '🍹',
                'Tráng miệng' => '🍮',
            );
            foreach ($menuByCategory as $cat => $items) {
                $em = isset($catEmoji[$cat]) ? $catEmoji[$cat] : '🌿';
            ?>
                <div class="cat-block" data-cat="<?php echo htmlspecialchars($cat) ?>">
                    <div class="cat-header">
                        <div class="cat-emoji"><?php echo $em ?></div>
                        <div class="cat-label"><?php echo htmlspecialchars($cat) ?></div>
                        <div class="cat-line"></div>
                    </div>
                    <div class="item-grid">
                        <?php foreach ($items as $item) {
                            $img      = !empty($item['image_url'])
                                ? htmlspecialchars($item['image_url'])
                                : (!empty($item['anh_url'])
                                    ? htmlspecialchars($item['anh_url'])
                                    : 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=70');
                            $itemName = isset($item['name']) ? $item['name'] : (isset($item['ten']) ? $item['ten'] : '');
                            $itemDesc = isset($item['description']) ? $item['description'] : (isset($item['mo_ta']) ? $item['mo_ta'] : '');
                            $dataName = addslashes(htmlspecialchars($itemName));
                            $dataDesc = addslashes(htmlspecialchars($itemDesc));
                        ?>
                            <div class="item-card"
                                onclick="openAdd(<?php echo $item['id'] ?>,'<?php echo $dataName ?>','<?php echo $dataDesc ?>','<?php echo $img ?>')">
                                <div class="item-img-wrap">
                                    <img class="item-img" src="<?php echo $img ?>"
                                        alt="<?php echo htmlspecialchars($itemName) ?>" loading="lazy">
                                </div>
                                <div class="item-body">
                                    <div class="item-name"><?php echo htmlspecialchars($itemName) ?></div>
                                    <div class="item-desc"><?php echo htmlspecialchars($itemDesc) ?></div>
                                    <button class="item-add">+ Gọi món</button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div><!-- /menu-area -->

        <!-- SIDEBAR -->
        <div class="orders-backdrop" id="ordersBackdrop" onclick="closeOrderPanel()"></div>
        <div class="sidebar" id="ordersPanel">
            <div class="sb-head">
                <h3>🍽 Món Đã Gọi</h3>
                <div class="sb-actions">
                    <button class="refresh-btn" onclick="refreshOrders()">↻ Làm mới</button>
                    <button class="panel-close-btn" onclick="toggleOrderPanel()" title="Thu gọn / mở rộng">×</button>
                </div>
            </div>

            <div class="sb-body">
                <!-- Giỏ tạm -->
                <div id="cartSection">
                    <div class="cart-label">🛒 Chờ xác nhận</div>
                    <div id="cartList">
                        <div class="empty-note" id="cartEmpty">
                            <div class="empty-icon">🛒</div>
                            Chưa chọn món nào.<br>Bấm "+ Gọi món" để thêm!
                        </div>
                    </div>
                    <button class="confirm-all-btn" id="confirmAllBtn" onclick="submitAllCart()" style="display:none">
                        ✓ &nbsp;Xác Nhận Gọi Món
                    </button>
                </div>

                <div class="sb-divider" id="sbDivider" style="display:none"></div>

                <!-- Đơn chờ -->
                <div id="pendingSection" style="display:none">
                    <div class="cart-label">
                        <span>⏳ Chờ phục vụ</span>
                        <button class="collapse-btn" onclick="togglePending()">∧</button>
                    </div>
                    <div class="orders-list" id="pendingList"></div>
                </div>

                <div class="sb-divider" id="sbDivider2" style="display:none"></div>

                <!-- Đơn hoàn thành -->
                <div id="completedSection" style="display:none">
                    <div class="cart-label">
                        <span>✓ Hoàn thành</span>
                        <button class="collapse-btn" onclick="toggleCompleted()">∧</button>
                    </div>
                    <div class="orders-list" id="completedList"></div>
                </div>

                <!-- Checkout -->
                <div class="sb-divider" id="sbCheckoutDivider" style="display:none"></div>
                <div id="checkoutSection" style="display:none">
                    <button class="checkout-btn" onclick="handleCheckout()">
                        💳 Thanh Toán - Kết Thúc
                    </button>
                </div>
            </div><!-- /sb-body -->
        </div><!-- /sidebar -->

    </div><!-- /layout -->

    <!-- ADD MODAL -->
    <div class="add-overlay" id="addOverlay">
        <div class="add-modal">
            <div class="add-modal-img-wrap">
                <img class="add-modal-img" id="am-img" src="" alt="">
                <div class="add-modal-img-overlay"></div>
            </div>
            <div class="add-modal-body">
                <div class="add-modal-title" id="am-name"></div>
                <div class="add-modal-desc" id="am-desc"></div>
                <div class="qty-row">
                    <button class="qty-btn" onclick="dq(-1)">−</button>
                    <span class="qty-n" id="qn">1</span>
                    <button class="qty-btn" onclick="dq(1)">+</button>
                    <span class="qty-label">phần</span>
                </div>
                <textarea class="note-input" id="noteInput" rows="2"
                    placeholder="Ghi chú (không hành, ít cay...)"></textarea>
                <button class="confirm-btn" onclick="addToCart()">
                    + &nbsp;Thêm Vào Danh Sách
                </button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        var CODE = '<?php echo htmlspecialchars($accessCode) ?>';
        var BASE = '<?php echo BASE_URL ?>';
    </script>
    <script src="<?php echo BASE_URL ?>/public/assets/js/order.js?v=<?php echo $orderJsVersion ?>"></script>
    <script src="<?php echo BASE_URL ?>/public/assets/js/checkout.js?v=<?php echo $checkoutJsVersion ?>"></script>
</body>

</html>
