<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Đăng Nhập Admin – <?php echo APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --green-dark: #1a2e1a;
            --green-mid: #2d4a2d;
            --green-accent: #4a7c59;
            --gold: #c9a84c;
            --gold-light: #e8c97a;
            --cream: #f5f0e8;
            --white: #ffffff;
            --err: #c0392b;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--green-dark);
            font-family: 'Be Vietnam Pro', sans-serif;
            overflow: hidden;
        }

        /* Decorative background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 80%, rgba(74, 124, 89, .25) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 20%, rgba(201, 168, 76, .12) 0%, transparent 55%);
            pointer-events: none;
        }

        /* Leaf pattern */
        body::after {
            content: '🌿';
            position: fixed;
            font-size: 320px;
            bottom: -80px;
            right: -60px;
            opacity: .04;
            pointer-events: none;
            user-select: none;
        }

        .card {
            position: relative;
            width: 420px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(201, 168, 76, .2);
            border-radius: 16px;
            padding: 48px 44px 44px;
            backdrop-filter: blur(12px);
            box-shadow:
                0 32px 80px rgba(0, 0, 0, .45),
                inset 0 1px 0 rgba(255, 255, 255, .06);
            animation: fadeUp .5s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Gold top bar */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 44px;
            right: 44px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            border-radius: 0 0 2px 2px;
        }

        .logo {
            text-align: center;
            margin-bottom: 36px;
        }

        .logo-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--green-mid), var(--green-accent));
            border-radius: 14px;
            font-size: 26px;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .3);
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: var(--gold-light);
            letter-spacing: .3px;
            line-height: 1.3;
        }

        .logo p {
            font-size: 12px;
            color: rgba(255, 255, 255, .4);
            margin-top: 6px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(201, 168, 76, .2);
        }

        .divider span {
            font-size: 11px;
            color: rgba(255, 255, 255, .3);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, .55);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px 16px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 8px;
            color: var(--white);
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 15px;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }

        input::placeholder {
            color: rgba(255, 255, 255, .25);
        }

        input:focus {
            border-color: var(--gold);
            background: rgba(255, 255, 255, .09);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, .12);
        }

        .err-box {
            display: none;
            align-items: center;
            gap: 8px;
            background: rgba(192, 57, 43, .15);
            border: 1px solid rgba(192, 57, 43, .35);
            border-radius: 8px;
            padding: 11px 14px;
            margin-bottom: 18px;
            color: #e74c3c;
            font-size: 13.5px;
        }

        .err-box.show {
            display: flex;
            animation: shake .35s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0)
            }

            20% {
                transform: translateX(-6px)
            }

            40% {
                transform: translateX(6px)
            }

            60% {
                transform: translateX(-4px)
            }

            80% {
                transform: translateX(4px)
            }
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--gold) 0%, #a8893a 100%);
            border: none;
            border-radius: 8px;
            color: var(--green-dark);
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .5px;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            margin-top: 6px;
            box-shadow: 0 4px 16px rgba(201, 168, 76, .3);
        }

        .btn:hover:not(:disabled) {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(201, 168, 76, .35);
        }

        .btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .btn-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(26, 46, 26, .3);
            border-top-color: var(--green-dark);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn.loading .spinner {
            display: block;
        }

        .btn.loading .btn-text::after {
            content: '...';
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: rgba(255, 255, 255, .35);
            text-decoration: none;
            transition: color .2s;
        }

        .back-link:hover {
            color: var(--gold-light);
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="logo">
            <div class="logo-icon">🌿</div>
            <h1><?php echo APP_NAME ?></h1>
            <p>Trang quản trị nội bộ</p>
        </div>

        <div class="divider"><span>Đăng nhập</span></div>

        <div class="err-box" id="errBox">
            <span>⚠</span>
            <span id="errMsg"></span>
        </div>

        <form id="loginForm" method="POST" action="<?php echo BASE_URL ?>/dang-nhap/xu-ly" autocomplete="off">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="ten_dang_nhap" placeholder="admin" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="mat_khau" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn" id="submitBtn">
                <span class="btn-inner">
                    <span class="spinner" id="spinner"></span>
                    <span class="btn-text" id="btnText">Đăng Nhập →</span>
                </span>
            </button>
        </form>

        <a href="<?php echo BASE_URL ?>" class="back-link">← Về trang chủ</a>
    </div>

    <script>
        (function() {
            var form = document.getElementById('loginForm');
            var btn = document.getElementById('submitBtn');
            var btnTxt = document.getElementById('btnText');
            var spin = document.getElementById('spinner');
            var errBox = document.getElementById('errBox');
            var errMsg = document.getElementById('errMsg');

            function showErr(msg) {
                errMsg.textContent = msg;
                errBox.className = 'err-box';
                // force reflow for animation restart
                void errBox.offsetWidth;
                errBox.className = 'err-box show';
            }

            function setLoading(on) {
                btn.disabled = on;
                btn.className = on ? 'btn loading' : 'btn';
                spin.style.display = on ? 'block' : 'none';
                btnTxt.textContent = on ? 'Đang đăng nhập' : 'Đăng Nhập →';
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                errBox.className = 'err-box';
                setLoading(true);

                var fd = new FormData(form);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo BASE_URL ?>/dang-nhap/xu-ly', true);

                xhr.onload = function() {
                    setLoading(false);
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            btnTxt.textContent = '✓ Thành công';
                            btn.style.background = 'linear-gradient(135deg,#4a7c59,#2d4a2d)';
                            btn.style.color = '#fff';
                            setTimeout(function() {
                                window.location.href = res.chuyen_huong;
                            }, 400);
                        } else {
                            showErr(res.thong_bao || 'Đăng nhập thất bại');
                        }
                    } catch (err) {
                        showErr('Lỗi hệ thống, vui lòng thử lại');
                    }
                };

                xhr.onerror = function() {
                    setLoading(false);
                    showErr('Không thể kết nối đến máy chủ');
                };

                xhr.send(fd);
            });
        })();
    </script>
</body>

</html>
