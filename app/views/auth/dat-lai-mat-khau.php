<?php
// View: Dat lai mat khau khach hang
$idYeuCau = isset($idYeuCau) ? $idYeuCau : '';
$hopLe = isset($hopLe) ? $hopLe : false;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dat lai mat khau - Buffet Chay An Lac</title>
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
            max-width: 430px;
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
            margin-bottom: 16px;
            outline: none;
        }
        .o-nhap:focus { border-color: #c8973a; background: #fff; }
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
        }
        .an { display: none; }
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
            <h1>Dat lai mat khau</h1>
            <p>Nhap ma OTP da gui ve email va tao mat khau moi.</p>
        </div>

        <?php if (!$hopLe): ?>
            <div class="thong-bao loi">Yeu cau OTP khong hop le, da het han hoac da duoc su dung.</div>
            <div class="phan-duoi">
                <a href="<?php echo BASE_URL; ?>/khach/quen-mat-khau">Gui lai ma OTP</a>
            </div>
        <?php else: ?>
            <div class="thong-bao an" id="thong-bao"></div>

            <input type="hidden" id="yeu_cau" value="<?php echo htmlspecialchars($idYeuCau, ENT_QUOTES, 'UTF-8'); ?>">

            <label class="nhan" for="otp">Ma OTP</label>
            <input class="o-nhap" type="text" id="otp" placeholder="Nhap 6 so OTP" inputmode="numeric" maxlength="6" autocomplete="one-time-code">

            <label class="nhan" for="mat_khau">Mat khau moi</label>
            <input class="o-nhap" type="password" id="mat_khau" placeholder="Nhap it nhat 6 ky tu" autocomplete="new-password">

            <label class="nhan" for="xac_nhan_mat_khau">Xac nhan mat khau</label>
            <input class="o-nhap" type="password" id="xac_nhan_mat_khau" placeholder="Nhap lai mat khau moi" autocomplete="new-password">

            <button class="nut" id="nut-luu" onclick="datLaiMatKhau()">Luu mat khau moi</button>

            <div class="phan-duoi">
                <a href="<?php echo BASE_URL; ?>/khach/dang-nhap">&larr; Quay lai dang nhap</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($hopLe): ?>
    <script>
        function hienThongBao(noiDung, thanhCong) {
            var hop = document.getElementById('thong-bao');
            hop.className = 'thong-bao ' + (thanhCong ? 'thanh-cong' : 'loi');
            hop.innerHTML = noiDung;
        }

        function datLaiMatKhau() {
            var yeuCau = document.getElementById('yeu_cau').value;
            var otp = document.getElementById('otp').value.trim();
            var matKhau = document.getElementById('mat_khau').value;
            var xacNhan = document.getElementById('xac_nhan_mat_khau').value;
            var nut = document.getElementById('nut-luu');

            if (!/^[0-9]{6}$/.test(otp)) {
                hienThongBao('Vui long nhap ma OTP gom 6 so', false);
                return;
            }

            if (matKhau.length < 6) {
                hienThongBao('Mat khau moi phai co it nhat 6 ky tu', false);
                return;
            }

            if (matKhau !== xacNhan) {
                hienThongBao('Mat khau xac nhan khong khop', false);
                return;
            }

            nut.disabled = true;
            nut.innerHTML = 'Dang luu...';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo BASE_URL; ?>/khach/dat-lai-mat-khau/xu-ly', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                nut.disabled = false;
                nut.innerHTML = 'Luu mat khau moi';
                try {
                    var res = JSON.parse(xhr.responseText);
                    hienThongBao(res.thong_bao || 'Da xu ly yeu cau', !!res.success);
                    if (res.success && res.chuyen_huong) {
                        setTimeout(function() {
                            window.location.href = res.chuyen_huong;
                        }, 900);
                    }
                } catch (e) {
                    hienThongBao('Phan hoi khong hop le, vui long thu lai', false);
                }
            };
            xhr.onerror = function() {
                nut.disabled = false;
                nut.innerHTML = 'Luu mat khau moi';
                hienThongBao('Khong the ket noi, vui long thu lai', false);
            };

            xhr.send(
                'yeu_cau=' + encodeURIComponent(yeuCau) +
                '&otp=' + encodeURIComponent(otp) +
                '&mat_khau=' + encodeURIComponent(matKhau) +
                '&xac_nhan_mat_khau=' + encodeURIComponent(xacNhan)
            );
        }
    </script>
    <?php endif; ?>
</body>

</html>
