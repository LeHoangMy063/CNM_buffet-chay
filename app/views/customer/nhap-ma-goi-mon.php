<?php
// app/views/customer/nhap-ma-goi-mon.php
// Trang cho khách nhập mã đặt bàn để bắt đầu gọi món
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập Mã Gọi Món - Dặt Bàn</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/base/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px 30px;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 14px;
            color: #999;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        input[type="text"],
        input[type="tel"] {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        input[type="text"]:focus,
        input[type="tel"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            padding: 14px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .options {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .option-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            background: #f9f9f9;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .option-btn:hover,
        .option-btn.active {
            border-color: #667eea;
            color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .info-box p {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        
        .info-box strong {
            color: #333;
        }
        
        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }
        
        .error.show {
            display: block;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin: 0 10px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .loading {
            display: none;
            text-align: center;
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #667eea;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="logo">🍽️</div>
                <h1 class="title">Bắt Đầu Gọi Món</h1>
                <p class="subtitle">Vui lòng nhập mã đặt bàn hoặc mã bàn</p>
            </div>
            
            <div class="info-box">
                <p>
                    <strong>Bạn đã đặt bàn trước?</strong><br>
                    Nhập <strong>mã đặt bàn</strong> được gửi qua SMS hoặc email.
                </p>
            </div>
            
            <form id="reservationForm" action="<?php echo BASE_URL; ?>/khach-hang/trang-goi-mon" method="get">
                <div class="form-group">
                    <label for="reservationCode">Mã Đặt Bàn / Mã Bàn</label>
                    <input 
                        type="text" 
                        id="reservationCode" 
                        name="code"
                        placeholder="Ví dụ: RES-20260427-12345"
                        required
                        autocomplete="off"
                        maxlength="20"
                    >
                    <div class="error" id="codeError"></div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <span id="submitText">→ Bắt Đầu Gọi Món</span>
                    <span class="loading" id="loadingSpinner">
                        <span class="spinner"></span>Đang xử lý...
                    </span>
                </button>
            </form>
            
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">← Quay Lại Trang Chủ</a>
                <a href="<?php echo BASE_URL; ?>/khach-hang/trang-dat-ban">📅 Đặt Bàn Mới</a>
            </div>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('reservationForm');
        const codeInput = document.getElementById('reservationCode');
        const codeError = document.getElementById('codeError');
        const submitText = document.getElementById('submitText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        
        // Chuyển đổi thành chữ hoa tự động
        codeInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase();
            codeError.classList.remove('show');
        });
        
        form.addEventListener('submit', (e) => {
            const code = codeInput.value.trim();
            
            if (!code) {
                e.preventDefault();
                codeError.textContent = 'Vui lòng nhập mã đặt bàn hoặc mã bàn';
                codeError.classList.add('show');
                return false;
            }
            
            if (code.length < 3) {
                e.preventDefault();
                codeError.textContent = 'Mã không hợp lệ (quá ngắn)';
                codeError.classList.add('show');
                return false;
            }
            
            // Hiện loading
            submitText.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
        });
        
        // Focus vào input ngay khi trang load
        codeInput.focus();
    </script>
</body>
</html>
