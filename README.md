
# Buffet Chay An Lac

Website quản lý nhà hàng buffet chay viết bằng PHP thuần theo mô hình MVC đơn giản. Dự án hỗ trợ khách đặt bàn, gọi món bằng mã bàn/mã đặt bàn, nhân viên phục vụ theo đơn và quản trị dữ liệu nhà hàng.

## Công nghệ sử dụng

- PHP thuần, tương thích kiểu code PHP 5.x
- MySQL/MariaDB
- HTML, CSS, JavaScript thuần
- Chạy tốt trên WAMP/XAMPP

## Chức năng chính

### Khách hàng

- Xem trang chủ và thực đơn.
- Đặt bàn theo ngày, giờ, số người.
- Nhận mã đặt bàn để vào trang gọi món.
- Gọi nhiều món trong một lần xác nhận giỏ.
- Theo dõi món đang chờ phục vụ và món đã hoàn thành.
- Kết thúc gọi món/thanh toán.

### Nhân viên

- Xem danh sách bàn và trạng thái bàn.
- Xem các đơn gọi món theo bàn.
- Phục vụ theo đơn, mỗi đơn gồm nhiều món trong `chitiet_donmon`.
- Xác nhận bàn trống.
- Quản lý đặt bàn, gán bàn và xác nhận bàn hệ thống tự gán.
- Tích điểm cho khách hàng.

### Quản trị

- Xem tổng quan hệ thống.
- Quản lý bàn.
- Quản lý đặt bàn.
- Quản lý thực đơn.
- Quản lý đơn món.
- Quản lý tài khoản.
- Xem báo cáo doanh thu và món bán chạy.

## Cấu trúc thư mục

```text
buffet-chay/
├── app/
│   ├── controllers/    # Controller xử lý request
│   ├── core/           # Database, xác thực
│   ├── middleware/     # Kiểm tra quyền
│   ├── models/         # Model thao tác dữ liệu
│   └── views/          # Giao diện
├── public/
│   └── assets/
│       ├── css/
│       └── js/
├── database.sql        # Toàn bộ schema và dữ liệu mẫu
├── index.php           # Router chính
└── README.md
```

## Cài đặt

1. Copy thư mục project vào:

```text
d:\wamp\www\buffet-chay
```

2. Tạo database MySQL:

```sql
CREATE DATABASE buffet_chay CHARACTER SET utf8 COLLATE utf8_general_ci;
```

3. Import file:

```text
database.sql
```

4. Kiểm tra cấu hình trong:

```text
app/config.php
```

Mặc định:

```php
DB_NAME  = buffet_chay
DB_USER  = root
DB_PASS  = ''
BASE_URL = http://localhost/buffet-chay
```

5. Mở trình duyệt:

```text
http://localhost/buffet-chay
```

## Mã bàn mẫu để gọi món

Các bàn có sẵn trong dữ liệu mẫu:

```text
BAN-A1
BAN-A2
BAN-A3
BAN-A4
BAN-B1
BAN-B2
BAN-B3
BAN-B4
```

Ví dụ mở trang gọi món bàn A1:

```text
http://localhost/buffet-chay/goi-mon?ma=BAN-A1
```

## Ghi chú database

File `database.sql` là file SQL duy nhất của dự án. Các bảng chính:

- `ban`: danh sách bàn.
- `dat_ban`: thông tin đặt bàn.
- `chitiet_datban`: các bàn được gán cho một đặt bàn.
- `don_mon`: một đơn gọi món theo bàn.
- `chitiet_donmon`: các món nằm trong một đơn.
- `mon_an`: thực đơn.
- `tai_khoan`: tài khoản admin, nhân viên, khách hàng.
- `danh_gia`: đánh giá món ăn.

Hiện tại không dùng file migration riêng; mọi thay đổi schema đã được gộp vào `database.sql`.

## Một số cấu hình quan trọng

Trong `app/config.php`:

```php
define('PRICE_ADULT', 199000);
define('PRICE_CHILD', 0);
define('RESTAURANT_CAPACITY', 40);
define('BUFFET_SESSION_MINUTES', 90);
```

Nếu đổi tên thư mục hoặc domain local, cần cập nhật lại `BASE_URL`.
