<?php require(dirname(__FILE__) . '/_header.php'); ?>

<h1>Dashboard Admin</h1>

<p>Xin chào: <?php echo $_SESSION['user']['username']; ?></p>

<ul>
    <li><a href="<?php echo BASE_URL ?>/admin/tables">Quản lý bàn</a></li>
    <li><a href="<?php echo BASE_URL ?>/admin/menu">Quản lý menu</a></li>
    <li><a href="<?php echo BASE_URL ?>/admin/orders">Quản lý đơn</a></li>
</ul>

<a href="<?php echo BASE_URL ?>/logout">Đăng xuất</a>

<?php require(dirname(__FILE__) . '/_footer.php'); ?>