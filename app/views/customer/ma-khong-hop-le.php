<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mã Bàn Không Hợp Lệ</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
body{font-family:'DM Sans',sans-serif;background:#faf6ef;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;}
.box{background:#fff;border-radius:20px;padding:3.5rem;text-align:center;max-width:400px;box-shadow:0 8px 40px rgba(0,0,0,.08);}
.icon{font-size:3.5rem;margin-bottom:1.25rem;}
h2{font-family:'Cormorant Garamond',serif;color:#1a1208;font-size:1.8rem;margin-bottom:.75rem;}
p{color:#8a7d6b;margin-bottom:1.5rem;line-height:1.7;font-size:.92rem;}
.code{background:#f2ebe0;padding:.5rem 1.25rem;border-radius:8px;font-weight:700;font-size:1.1rem;display:inline-block;margin-bottom:1.5rem;color:#4a5a40;letter-spacing:.08em;}
a{display:inline-block;padding:.8rem 2rem;background:#4a5a40;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;transition:background .2s;}
a:hover{background:#6b7c5e;}
</style>
</head>
<body>
<div class="box">
    <div class="icon">🔍</div>
    <h2>Mã Bàn Không Tìm Thấy</h2>
    <?php if (!empty($code)) { ?><div class="code"><?php echo htmlspecialchars($code) ?></div><br><?php } ?>
    <p>Mã bàn này không tồn tại hoặc đã hết hạn.<br>Vui lòng kiểm tra lại biển hiệu trên bàn.</p>
    <a href="<?php echo BASE_URL ?>">← Về Trang Chủ</a>
</div>
</body>
</html>
