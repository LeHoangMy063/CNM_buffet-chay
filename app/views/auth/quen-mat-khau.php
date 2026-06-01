<?php
// View: Quen mat khau khach hang
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quen mat khau - Buffet Chay An Lac</title>
    <link rel="manifest" href="<?php echo BASE_URL; ?>/public/manifest.webmanifest">
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" type="image/svg+xml">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0e8 0%, #e8d5b0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }
        .hop {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 38px 34px;
            width: 100%;
            max-width: 440px;
        }
        .logo-khu { text-align: center; margin-bottom: 26px; }
        .logo-khu img { width: 64px; height: 64px; border-radius: 14px; margin-bottom: 12px; }
        .logo-khu h1 { color: #5a3e1b; font-size: 22px; font-weight: 700; }
        .logo-khu p { color: #a07840; font-size: 13px; margin-top: 5px; line-height: 1.5; }
        .nhan {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #7a5c2e;
            text-transform: uppercase;
            letter-spacing: .7px;
            margin-bottom: 7px;
        }
        .o-nhap {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #d4b896;
            border-radius: 8px;
            font-size: 15px;
            color: #3a2a0e;
            background: #fdf8f2;
            margin-bottom: 14px;
            outline: none;
        }
        .o-nhap:focus { border-color: #c8973a; background: #fff; }
        .goi-y { font-size: 12px; color: #a07840; margin-bottom: 18px; line-height: 1.45; }
        .nut {
            width: 100%;
            padding: 13px;
            background: #c8973a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .nut:hover { background: #a07428; }
        .nut:disabled { opacity: .65; cursor: not-allowed; }
        .thong-bao {
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.45;
            display: none;
        }
        .loi { background: #fdf0f0; border: 1px solid #f5c6c6; color: #c0392b; }
        .thanh-cong { background: #eefaf2; border: 1px solid #bfe7cb; color: #1f7a3c; }
        .phan-duoi { text-align: center; margin-top: 18px; font-size: 13px; color: #a07840; }
        .phan-duoi a { color: #c8973a; text-decoration: none; font-weight: 700; }
        .phan-duoi a:hover { text-decoration: underline; }
    </style>
</head>

<body>
    <div class="hop">
        <div class="logo-khu">
            <img src="<?php echo BASE_URL; ?>/public/assets/icons/pwa-icon.svg" alt="Logo">
            <h1>Quen mat khau</h1>
            <p>Nhap Gmail/email tai khoan khach hang. He thong se gui ma OTP dat lai mat khau ve email nay.</p>
        </div>

        <div class="thong-bao" id="thong-bao"></div>

        <label class="nhan" for="email">Gmail/email</label>
        <input class="o-nhap" type="email" id="email" placeholder="email@gmail.com" autocomplete="email">

        <button class="nut" id="nut-gui" onclick="guiYeuCau()">Gui ma OTP</button>

        <div class="phan-duoi">
            <a href="<?php echo BASE_URL; ?>/khach/dang-nhap">&larr; Quay lai dang nhap</a>
        </div>
    </div>

    <script>
        function hienThongBao(noiDung, thanhCong) {
            var hop = document.getElementById('thong-bao');
            hop.className = 'thong-bao ' + (thanhCong ? 'thanh-cong' : 'loi');
            hop.innerHTML = noiDung;
            hop.style.display = 'block';
        }

        function guiYeuCau() {
            var email = document.getElementById('email').value.trim();
            var nut = document.getElementById('nut-gui');

            if (email === '') {
                hienThongBao('Vui long nhap Gmail/email da dang ky', false);
                return;
            }

            nut.disabled = true;
            nut.innerHTML = 'Dang gui...';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo BASE_URL; ?>/khach/quen-mat-khau/gui', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                nut.disabled = false;
                nut.innerHTML = 'Gui ma OTP';
                try {
                    var res = JSON.parse(xhr.responseText);
                    hienThongBao(res.thong_bao || 'Da xu ly yeu cau', !!res.success);
                    if (res.success && res.chuyen_huong) {
                        setTimeout(function() {
                            window.location.href = res.chuyen_huong;
                        }, 800);
                    }
                } catch (e) {
                    hienThongBao('Phan hoi khong hop le, vui long thu lai', false);
                }
            };
            xhr.onerror = function() {
                nut.disabled = false;
                nut.innerHTML = 'Gui ma OTP';
                hienThongBao('Khong the ket noi, vui long thu lai', false);
            };

            xhr.send('email=' + encodeURIComponent(email));
        }

        document.addEventListener('keydown', function(e) {
            if (e.keyCode === 13) guiYeuCau();
        });
    </script>
</body>

</html>
