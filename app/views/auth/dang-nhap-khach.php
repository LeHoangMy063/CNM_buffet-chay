<?php
// View: Trang dang nhap danh cho KHACH HANG
// Dang nhap bang SDT hoac Gmail - giao dien rieng biet
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dang nhap - Buffet Chay An Lac</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0e8 0%, #e8d5b0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hop-dang-nhap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
        }

        .logo-khu {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-khu img {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            margin-bottom: 12px;
        }

        .logo-khu h1 {
            color: #5a3e1b;
            font-size: 22px;
            font-weight: 700;
        }

        .logo-khu p {
            color: #a07840;
            font-size: 13px;
            margin-top: 4px;
        }

        .nhan {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #7a5c2e;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .o-nhap {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #d4b896;
            border-radius: 8px;
            font-size: 15px;
            color: #3a2a0e;
            background: #fdf8f2;
            margin-bottom: 18px;
            outline: none;
            transition: border-color 0.2s;
        }

        .o-nhap:focus {
            border-color: #c8973a;
            background: #fff;
        }

        .goi-y {
            font-size: 12px;
            color: #a07840;
            margin-top: -14px;
            margin-bottom: 18px;
        }

        .nut-dang-nhap {
            width: 100%;
            padding: 13px;
            background: #c8973a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }

        .nut-dang-nhap:hover {
            background: #a07428;
        }

        .thong-bao-loi {
            background: #fdf0f0;
            border: 1px solid #f5c6c6;
            color: #c0392b;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
            display: none;
        }

        .phan-duoi {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #a07840;
        }

        .phan-duoi a {
            color: #c8973a;
            text-decoration: none;
            font-weight: 600;
        }

        .phan-duoi a:hover {
            text-decoration: underline;
        }

        .duong-phan {
            border: none;
            border-top: 1px solid #ecdcc4;
            margin: 20px 0;
        }
    </style>
</head>

<body>

    <div class="hop-dang-nhap">
        <div class="logo-khu">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
            <h1>Buffet Chay An Lac</h1>
            <p>Dang nhap tai khoan khach hang</p>
        </div>

        <div class="thong-bao-loi" id="thong-bao-loi"></div>

        <label class="nhan">So dien thoai hoac Gmail</label>
        <input
            class="o-nhap"
            type="text"
            id="dang_nhap"
            placeholder="0901234567 hoac email@gmail.com"
            autocomplete="username">
        <div class="goi-y">Nhap so dien thoai hoac dia chi Gmail da dang ky</div>

        <label class="nhan">Mat khau</label>
        <input
            class="o-nhap"
            type="password"
            id="mat_khau"
            placeholder="Nhap mat khau"
            autocomplete="current-password">

        <button class="nut-dang-nhap" id="nut-dang-nhap" onclick="xuLyDangNhap()">
            Dang Nhap &rarr;
        </button>

        <hr class="duong-phan">

        <div class="phan-duoi">
            Chua co tai khoan?
            <a href="<?php echo BASE_URL; ?>/dang-ky">Dang ky ngay</a>
        </div>
        <div class="phan-duoi" style="margin-top:10px;">
            <a href="<?php echo BASE_URL; ?>/">&larr; Ve trang chu</a>
        </div>
    </div>

    <script>
        function xuLyDangNhap() {
            var dang_nhap = document.getElementById('dang_nhap').value.trim();
            var mat_khau = document.getElementById('mat_khau').value;
            var thong_bao = document.getElementById('thong-bao-loi');
            var nut = document.getElementById('nut-dang-nhap');

            thong_bao.style.display = 'none';

            if (dang_nhap === '' || mat_khau === '') {
                thong_bao.innerHTML = 'Vui long nhap day du thong tin';
                thong_bao.style.display = 'block';
                return;
            }

            nut.disabled = true;
            nut.innerHTML = 'Dang xu ly...';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo BASE_URL; ?>/khach/dang-nhap/xu-ly', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                nut.disabled = false;
                nut.innerHTML = 'Dang Nhap &rarr;';
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        window.location.href = res.chuyen_huong;
                    } else {
                        thong_bao.innerHTML = res.thong_bao;
                        thong_bao.style.display = 'block';
                    }
                } catch (e) {
                    thong_bao.innerHTML = 'Loi he thong, vui long thu lai';
                    thong_bao.style.display = 'block';
                }
            };
            xhr.onerror = function() {
                nut.disabled = false;
                nut.innerHTML = 'Dang Nhap &rarr;';
                thong_bao.innerHTML = 'Khong the ket noi, kiem tra lai mang';
                thong_bao.style.display = 'block';
            };

            var data = 'dang_nhap=' + encodeURIComponent(dang_nhap) +
                '&mat_khau=' + encodeURIComponent(mat_khau);
            xhr.send(data);
        }

        // Enter de dang nhap
        document.addEventListener('keydown', function(e) {
            if (e.keyCode === 13) xuLyDangNhap();
        });
    </script>

</body>

</html>