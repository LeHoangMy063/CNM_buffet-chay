<?php
// app/views/auth/dang-ky.php
// Trang đăng ký khách hàng - Lưu thông tin vào database
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký tài khoản - Buffet Chay</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --cream: #f7f2e9;
            --green: #2d6a4f;
            --green-soft: #4a7c59;
            --gold: #c9a84c;
            --text: #2b2b23;
            --muted: #6d6a60;
            --border: rgba(45, 106, 79, .18);
            --shadow: 0 24px 70px rgba(33, 34, 28, .14);
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, rgba(201, 168, 76, .14), transparent 24%),
                linear-gradient(180deg, #faf5ed 0%, #f1eadf 100%);
            font-family: 'Be Vietnam Pro', sans-serif;
            color: var(--text);
            padding: 2rem;
        }

        body::before {
            content: '🍃';
            position: fixed;
            top: 12%;
            left: 8%;
            font-size: 160px;
            opacity: .08;
            pointer-events: none;
            user-select: none;
        }

        .auth-card {
            width: min(460px, 100%);
            background: rgba(255, 255, 255, .92);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 42px 36px 34px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand strong {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 2.15rem;
            letter-spacing: -.03em;
            color: var(--green);
            margin-bottom: 10px;
        }

        .brand span {
            display: inline-block;
            color: var(--muted);
            font-size: .95rem;
            letter-spacing: .05em;
        }

        .intro {
            text-align: center;
            margin-bottom: 28px;
            color: #4f4b45;
            font-size: .98rem;
            line-height: 1.7;
        }

        .form-alert {
            display: none;
            padding: 12px 14px;
            margin-bottom: 22px;
            border-radius: 14px;
            border: 1px solid transparent;
            font-size: .95rem;
            line-height: 1.5;
            animation: slideDown .3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-alert.success {
            background: rgba(76, 175, 80, .12);
            color: #275b32;
            border-color: rgba(76, 175, 80, .25);
        }

        .form-alert.error {
            background: rgba(192, 57, 43, .12);
            color: #7b1f1f;
            border-color: rgba(192, 57, 43, .25);
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: .85rem;
            color: var(--green-soft);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .required {
            color: #c9404a;
            margin-left: 2px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(45, 106, 79, .14);
            background: #fff;
            font-size: 1rem;
            color: var(--text);
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            border-color: var(--green);
            outline: none;
            box-shadow: 0 0 0 5px rgba(45, 106, 79, .08);
        }

        input::placeholder {
            color: var(--muted);
        }

        input.error {
            border-color: #c9404a;
            background: rgba(201, 64, 74, .04);
        }

        .input-help {
            display: none;
            font-size: .8rem;
            color: #c9404a;
            margin-top: 4px;
        }

        .input-help.show {
            display: block;
        }

        .btn-submit {
            width: 100%;
            padding: 14px 0;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--gold), #b0903d);
            color: var(--green);
            font-family: 'Be Vietnam Pro', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: .02em;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s, opacity .2s;
            box-shadow: 0 18px 32px rgba(201, 168, 76, .18);
        }

        .btn-submit:hover:not(:disabled) {
            opacity: .96;
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .btn-submit.loading {
            position: relative;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(45, 106, 79, .3);
            border-top: 2px solid var(--green);
            border-radius: 50%;
            animation: spin .6s linear infinite;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .btn-submit.loading .spinner {
            display: block;
        }

        .btn-submit.loading .btn-text {
            opacity: 0;
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .footer-note {
            margin-top: 22px;
            text-align: center;
            font-size: .93rem;
            color: var(--muted);
        }

        .footer-note a {
            color: var(--green);
            text-decoration: none;
            font-weight: 600;
            transition: color .2s;
        }

        .footer-note a:hover {
            text-decoration: underline;
            color: var(--green-soft);
        }
    </style>
</head>

<body>
    <div class="auth-card">
        <div class="brand">
            <strong><?php echo defined('APP_NAME') ? APP_NAME : 'Buffet Chay'; ?></strong>
            <span>Đăng ký tài khoản để đặt bàn nhanh và nhận ưu đãi</span>
        </div>

        <p class="intro">Tạo tài khoản khách hàng của bạn bằng thông tin cơ bản. Chúng tôi sẽ giữ liên hệ để xác nhận đặt bàn và phục vụ tốt nhất.</p>

        <div id="formAlert" class="form-alert"></div>

        <form id="form-dang-ky" novalidate>
            <div class="form-group">
                <label for="ho_ten">Họ và tên <span class="required">*</span></label>
                <input
                    id="ho_ten"
                    type="text"
                    name="ho_ten"
                    placeholder="Ví dụ: Nguyễn Văn A"
                    required
                    minlength="2"
                    maxlength="100">
                <div class="input-help" id="help-ho_ten">Vui lòng nhập họ và tên (2-100 ký tự)</div>
            </div>

            <div class="form-group">
                <label for="so_dien_thoai">Số điện thoại <span class="required">*</span></label>
                <input
                    id="so_dien_thoai"
                    type="tel"
                    name="so_dien_thoai"
                    placeholder="Ví dụ: 0901234567"
                    required
                    pattern="0[0-9]{9,10}"
                    maxlength="11">
                <div class="input-help" id="help-so_dien_thoai">Vui lòng nhập số điện thoại hợp lệ (10-11 chữ số)</div>
            </div>

            <div class="form-group">
                <label for="email">Email (tùy chọn)</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="Ví dụ: email@example.com"
                    maxlength="100">
                <div class="input-help" id="help-email">Vui lòng nhập email hợp lệ</div>
            </div>

            <div class="form-group">
                <label for="mat_khau">Mật khẩu <span class="required">*</span></label>
                <input
                    id="mat_khau"
                    type="password"
                    name="mat_khau"
                    placeholder="Mật khẩu ít nhất 6 ký tự"
                    required
                    minlength="6"
                    maxlength="255">
                <div class="input-help" id="help-mat_khau">Mật khẩu phải có ít nhất 6 ký tự</div>
            </div>

            <div class="form-group">
                <label for="xac_nhan_mat_khau">Xác nhận mật khẩu <span class="required">*</span></label>
                <input
                    id="xac_nhan_mat_khau"
                    type="password"
                    name="xac_nhan_mat_khau"
                    placeholder="Nhập lại mật khẩu"
                    required
                    minlength="6"
                    maxlength="255">
                <div class="input-help" id="help-xac_nhan_mat_khau">Mật khẩu không trùng khớp</div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                <span class="btn-text">Đăng ký</span>
                <span class="spinner"></span>
            </button>
        </form>

        <p class="footer-note">Đã có tài khoản? <a href="<?php echo BASE_URL; ?>/dang-nhap">Đăng nhập ngay</a></p>
    </div>

    <script>
        var form = document.getElementById('form-dang-ky');
        var alertBox = document.getElementById('formAlert');
        var btnSubmit = document.getElementById('btnSubmit');

        // Hiển thị thông báo
        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = 'form-alert ' + (type === 'success' ? 'success' : 'error');
            alertBox.style.display = 'block';
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Kiểm tra input client-side
        function validateInput(name, value) {
            var helpElement = document.getElementById('help-' + name);
            var inputElement = document.getElementById(name);
            var isValid = true;
            var message = '';

            switch (name) {
                case 'ho_ten':
                    if (value.trim().length < 2) {
                        isValid = false;
                        message = 'Vui lòng nhập họ và tên (tối thiểu 2 ký tự)';
                    }
                    break;

                case 'so_dien_thoai':
                    if (!value.match(/^0[0-9]{9,10}$/)) {
                        isValid = false;
                        message = 'Số điện thoại không hợp lệ (phải bắt đầu 0, 10-11 chữ số)';
                    }
                    break;

                case 'email':
                    if (value && !value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        isValid = false;
                        message = 'Email không hợp lệ';
                    }
                    break;

                case 'mat_khau':
                    if (value.length < 6) {
                        isValid = false;
                        message = 'Mật khẩu phải có ít nhất 6 ký tự';
                    }
                    break;

                case 'xac_nhan_mat_khau':
                    var matKhau = document.getElementById('mat_khau').value;
                    if (value !== matKhau) {
                        isValid = false;
                        message = 'Mật khẩu không trùng khớp';
                    }
                    break;
            }

            if (isValid) {
                inputElement.classList.remove('error');
                if (helpElement) helpElement.classList.remove('show');
            } else {
                inputElement.classList.add('error');
                if (helpElement) {
                    helpElement.textContent = message;
                    helpElement.classList.add('show');
                }
            }

            return isValid;
        }

        // Event listener cho validation real-time
        document.getElementById('ho_ten').addEventListener('blur', function() {
            validateInput('ho_ten', this.value);
        });

        document.getElementById('so_dien_thoai').addEventListener('blur', function() {
            validateInput('so_dien_thoai', this.value);
        });

        document.getElementById('email').addEventListener('blur', function() {
            if (this.value) validateInput('email', this.value);
        });

        document.getElementById('mat_khau').addEventListener('blur', function() {
            validateInput('mat_khau', this.value);
            var xacNhan = document.getElementById('xac_nhan_mat_khau').value;
            if (xacNhan) validateInput('xac_nhan_mat_khau', xacNhan);
        });

        document.getElementById('xac_nhan_mat_khau').addEventListener('blur', function() {
            validateInput('xac_nhan_mat_khau', this.value);
        });

        // Submit form
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alertBox.style.display = 'none';

            var hoTen = document.getElementById('ho_ten').value;
            var soDienThoai = document.getElementById('so_dien_thoai').value;
            var email = document.getElementById('email').value;
            var matKhau = document.getElementById('mat_khau').value;
            var xacNhan = document.getElementById('xac_nhan_mat_khau').value;

            var valid = true;
            valid = validateInput('ho_ten', hoTen) && valid;
            valid = validateInput('so_dien_thoai', soDienThoai) && valid;
            if (email) valid = validateInput('email', email) && valid;
            valid = validateInput('mat_khau', matKhau) && valid;
            valid = validateInput('xac_nhan_mat_khau', xacNhan) && valid;

            if (!valid) {
                showAlert('Vui lòng kiểm tra lại thông tin nhập vào', 'error');
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.classList.add('loading');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo BASE_URL; ?>/dang-ky/xu-ly');
            xhr.onload = function() {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('loading');

                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        showAlert(res.thong_bao || 'Đăng ký thành công! Chuyển hướng đến trang đăng nhập...', 'success');
                        form.reset();
                        if (res.chuyen_huong) {
                            setTimeout(function() {
                                window.location.href = res.chuyen_huong;
                            }, 1500);
                        }
                    } else {
                        showAlert(res.thong_bao || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
                    }
                } catch (err) {
                    showAlert('Lỗi máy chủ. Vui lòng thử lại sau.', 'error');
                }
            };

            xhr.onerror = function() {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('loading');
                showAlert('Không thể kết nối đến máy chủ.', 'error');
            };

            xhr.send(new FormData(form));
        });
    </script>
</body>

</html>