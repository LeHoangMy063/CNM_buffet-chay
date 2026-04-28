<?php
// app/views/staff/login.php
// Trang đăng nhập chung — backend tự phân biệt admin / staff
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập — Buffet Chay</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --green-dark: #1e3a2f;
            --green-mid: #2d5a42;
            --green-light: #4a8c64;
            --text: #1a2e25;
            --muted: #6b8c7a;
            --white: #ffffff;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--green-dark);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(74, 140, 100, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(240, 165, 0, 0.08) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234a8c64' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            padding: 20px;
        }

        .card {
            background: var(--white);
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.35);
        }

        .card-header {
            background: linear-gradient(135deg, var(--green-mid) 0%, var(--green-dark) 100%);
            padding: 36px 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='40' cy='40' r='35' fill='none' stroke='%234a8c64' stroke-width='0.5' stroke-opacity='0.3'/%3E%3C/svg%3E") center/160px repeat;
            opacity: 0.4;
        }

        .brand-icon {
            font-size: 46px;
            display: block;
            margin-bottom: 12px;
            position: relative;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--white);
            position: relative;
        }

        .brand-sub {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 5px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            position: relative;
        }

        .card-body {
            padding: 32px 36px 36px;
        }

        .alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 22px;
            border-left: 4px solid;
            line-height: 1.5;
        }

        .alert-error {
            background: #fff0f0;
            border-color: #e05c5c;
            color: #c0392b;
        }

        .alert-success {
            background: #f0fff5;
            border-color: #4a8c64;
            color: #1e6641;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #dde8e3;
            border-radius: 9px;
            font-family: inherit;
            font-size: 14px;
            color: var(--text);
            background: #fafcfb;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--green-light);
            box-shadow: 0 0 0 3px rgba(74, 140, 100, 0.12);
            background: var(--white);
        }

        input::placeholder {
            color: #aabfb5;
        }

        .submit-btn {
            width: 100%;
            padding: 13px;
            margin-top: 6px;
            background: linear-gradient(135deg, var(--green-light) 0%, var(--green-mid) 100%);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(45, 90, 66, 0.3);
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
        }

        .submit-btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(45, 90, 66, 0.35);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
        }

        .back-link a {
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: var(--green-mid);
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="card-header">
            <span class="brand-icon">🌿</span>
            <div class="brand-name">Buffet Chay An Lạc</div>
            <div class="brand-sub">Hệ thống quản lý nội bộ</div>
        </div>

        <div class="card-body">

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($_SESSION['message']);
                        unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL ?>/staff/login">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username"
                        placeholder="Nhập tên đăng nhập hoặc email"
                        required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password"
                        placeholder="Nhập mật khẩu"
                        required autocomplete="current-password">
                </div>
                <button type="submit" class="submit-btn">Đăng Nhập</button>
            </form>

            <div class="back-link">
                <a href="<?php echo BASE_URL ?>">← Quay lại trang chủ</a>
            </div>
        </div>
    </div>

</body>

</html>