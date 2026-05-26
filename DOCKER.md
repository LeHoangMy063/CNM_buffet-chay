# 🐳 Chạy dự án buffet-chay bằng Docker

## Yêu cầu

Máy cần cài:

- Docker Desktop
- Docker Compose

Kiểm tra Docker:

```powershell
docker --version
docker compose version
```

---

## Chạy project lần đầu

Mở PowerShell tại thư mục project:

```powershell
cd D:\wamp\www\buffet-chay
```

Build và chạy Docker:

```powershell
docker compose up -d --build
```

Docker sẽ tự động:

- Build PHP + Apache
- Chạy website
- Chạy MariaDB
- Chạy phpMyAdmin
- Import `database.sql`

---

## Truy cập hệ thống

Website:

```text
http://localhost:8080/buffet-chay
```

phpMyAdmin:

```text
http://localhost:8081
```

---

## Chạy lại project

Những lần sau chỉ cần:

```powershell
docker compose up -d
```

Nếu sửa file Docker (`Dockerfile`, `docker-compose.yml`, `apache.conf`) thì build lại:

```powershell
docker compose up -d --build
```

---

## Dừng project

```powershell
docker compose down
```

---

## Xem log

Xem toàn bộ log:

```powershell
docker compose logs -f
```

Log website:

```powershell
docker compose logs -f web
```

Log database:

```powershell
docker compose logs -f db
```

---

## Reset database

⚠️ Xóa toàn bộ dữ liệu database hiện tại:

```powershell
docker compose down -v
docker compose up -d --build
```

---

## Thông tin database

```text
Host: localhost
Port: 3307
Database: buffet_chay
User: buffet_user
Password: buffet_pass
```

Cấu hình trong Docker:

```env
DB_HOST=db
DB_NAME=buffet_chay
DB_USER=buffet_user
DB_PASS=buffet_pass
BASE_URL=http://localhost:8080/buffet-chay
BASE_URL=http://localhost:8080/buffet-chay
PUBLIC_BASE_URL=http://localhost:8080/buffet-chay
```

`PUBLIC_BASE_URL` dùng cho mã QR gọi món trên điện thoại. Khi demo bằng ngrok, giá trị này được lấy từ file `.env`.

## Demo QR bằng ngrok

Cài và đăng nhập ngrok một lần trên máy:

```powershell
winget install ngrok.ngrok
ngrok config add-authtoken YOUR_NGROK_TOKEN
```

Nếu máy không có `winget`, tải ngrok bản Windows từ trang chủ, giải nén `ngrok.exe`, rồi đặt file đó vào một thư mục có trong PATH. Cách nhanh nhất là đặt `ngrok.exe` ngay trong thư mục project và chạy:

```powershell
.\ngrok.exe config add-authtoken YOUR_NGROK_TOKEN
```

Mỗi lần demo, chạy:

```powershell
.\docker\ngrok-demo.ps1
```

Script sẽ:

- chạy `docker compose up -d`
- mở tunnel `ngrok http 8080`
- lấy URL HTTPS từ ngrok
- ghi vào `.env` dạng `BASE_URL=https://...ngrok-free.app/buffet-chay` và `PUBLIC_BASE_URL=https://...ngrok-free.app/buffet-chay`
- restart web container để QR mới dùng URL ngrok

Sau đó vào màn hình nhân viên, mở bàn và in lại phiếu QR. Điện thoại quét QR sẽ vào URL ngrok, không cần cùng Wi-Fi với laptop.

## PWA cho nhân viên / bếp

Trang nhân viên và bếp đã có PWA manifest + service worker. Khi mở bằng Chrome/Edge tại:

```text
http://localhost:8080/buffet-chay/nhan-vien/tong-quan
```

hoặc URL ngrok khi demo, có thể chọn `Install app` / `Add to Home screen` để mở như app riêng. PWA cache giao diện và asset tĩnh; dữ liệu đơn món, realtime và thao tác xác nhận vẫn cần kết nối tới server.

---

## Một số lệnh Docker thường dùng

Chạy project:

```powershell
docker compose up -d
```

Build lại:

```powershell
docker compose up -d --build
```

Dừng project:

```powershell
docker compose down
```

Kiểm tra container:

```powershell
docker ps
```

---

## Lỗi thường gặp

### Forbidden

Thử build lại:

```powershell
docker compose down
docker compose up -d --build
```

### Port đã được sử dụng

Ví dụ:

```text
Bind for 0.0.0.0:8080 failed
```

Nguyên nhân: port đang bị ứng dụng khác sử dụng.
