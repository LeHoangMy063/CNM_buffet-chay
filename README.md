# Buffet Chay An Lạc

## 1. Giới thiệu ngắn

**Buffet Chay An Lạc** là website quản lý nhà hàng buffet chay, hỗ trợ các nghiệp vụ chính như đặt bàn, gọi món tại bàn, xử lý món, thanh toán và theo dõi báo cáo doanh thu.

Hệ thống được xây dựng bằng **PHP theo mô hình MVC** và chạy bằng **Docker**. Nhờ đó, người dùng có thể chạy project nhanh chóng mà không cần tự cấu hình PHP, Apache hay database trên máy cá nhân.

Project mô phỏng quy trình vận hành thực tế của một nhà hàng buffet, trong đó các vai trò chính gồm **khách hàng**, **nhân viên phục vụ**, **nhân viên bếp** và **quản lý**.

---

## 2. Chức năng chính

### Khách hàng

* Xem thông tin nhà hàng và thực đơn món chay.
* Đặt bàn theo ngày, giờ và số lượng khách.
* Gọi món bằng mã bàn tại hoặc mã qr.
* Theo dõi danh sách món đã gọi.
* Đánh giá món ăn.
* Đăng ký, đăng nhập tài khoản khách hàng để sử dụng chức năng tích điểm.

### Nhân viên phục vụ

* Theo dõi trạng thái các bàn trong nhà hàng và
* Cập nhật tình trạng bàn khi có khách sử dụng hoặc sau khi thanh toán.
* Xem danh sách đặt bàn, kiểm tra thông tin khách và gán bàn phù hợp cho từng lượt đặt.
* Theo dõi các món khách đã gọi theo từng bàn để nắm được món đang chờ và món đã được phục vụ.
* Xác nhận món đã phục vụ sau khi món được mang ra cho khách.
* Hỗ trợ thanh toán cho bàn bằng tiền mặt hoặc chuyển khoản.
* Ghi nhận điểm tích lũy cho khách hàng sau khi sử dụng dịch vụ.

### Nhân viên bếp

* Xem danh sách món khách đã gọi.

### Quản lý

* Xem báo cáo doanh thu của nhà hàng.
* Quản lý thực đơn, bao gồm danh sách món ăn và thông tin món.
* Quản lý nhân viên, bao gồm tài khoản và vai trò làm việc trong hệ thống.

---

## 3. Công nghệ sử dụng

Hệ thống sử dụng **PHP thuần** để xây dựng backend, xử lý các nghiệp vụ như đặt bàn, gọi món, đăng nhập, thanh toán và báo cáo. Code được tổ chức theo mô hình **MVC**, giúp tách biệt phần xử lý nghiệp vụ, dữ liệu và giao diện.

**MariaDB** được dùng làm cơ sở dữ liệu chính để lưu thông tin bàn, món ăn, đơn gọi món, đặt bàn, tài khoản, hóa đơn và thanh toán. Đây là nhóm dữ liệu quan trọng cần tính ổn định trong quá trình vận hành.

Ngoài MariaDB, hệ thống còn tích hợp **MongoDB** để lưu dữ liệu báo cáo và phân tích. MongoDB không thay thế database chính mà đóng vai trò hỗ trợ lưu dữ liệu thống kê, giúp hệ thống có thể mở rộng theo hướng phân tích doanh thu và hành vi gọi món.

Project chạy bằng **Docker Compose**, bao gồm các service như web server, database, phpMyAdmin và MongoDB. Cách triển khai này giúp việc cài đặt nhanh hơn, hạn chế lỗi môi trường và thuận tiện khi demo trên nhiều máy khác nhau.

Ở phía giao diện, hệ thống sử dụng **HTML, CSS và JavaScript** để xây dựng các màn hình tương tác như gọi món, giỏ món, dashboard nhân viên và báo cáo quản lý. Một số phần sử dụng JavaScript để cập nhật dữ liệu nhanh hơn mà không cần tải lại toàn bộ trang.

Hệ thống còn áp dụng một số công nghệ hỗ trợ như **PWA**, **VietQR** và **gợi ý món ăn**. PWA giúp giao diện nhân viên và bếp có thể sử dụng gần giống ứng dụng. VietQR hỗ trợ thanh toán chuyển khoản nhanh hơn. Chức năng gợi ý món ăn giúp đề xuất món phù hợp dựa trên dữ liệu gọi món và món phổ biến.

---

## 4. Kiến trúc tổng quan

Hệ thống hoạt động theo kiến trúc web MVC. Người dùng thao tác trên trình duyệt hoặc giao diện PWA. Request được gửi đến server PHP, sau đó router điều hướng đến controller phù hợp. Controller xử lý nghiệp vụ, gọi model để làm việc với database và trả kết quả về giao diện.

```text
Người dùng
   |
   v
Trình duyệt / PWA
   |
   v
PHP + Apache
   |
   v
Router
   |
   v
Controller
   |
   v
Model
   |
   +------------------> MariaDB
   |
   +------------------> MongoDB
```

Trong đó:

* **Trình duyệt/PWA** là nơi người dùng thao tác với hệ thống.
* **PHP + Apache** là môi trường chạy backend.
* **Router** điều hướng request đến đúng controller.
* **Controller** xử lý các nghiệp vụ chính.
* **Model** truy vấn và cập nhật dữ liệu.
* **MariaDB** lưu dữ liệu vận hành chính.
* **MongoDB** lưu dữ liệu báo cáo và phân tích.

Hệ thống được triển khai bằng Docker với các thành phần chính:

```text
Docker Compose
|
├── Web Server: PHP + Apache
├── Database: MariaDB
├── Database Tool: phpMyAdmin
└── Analytics Database: MongoDB
```

---

## 5. Luồng xử lý chính

### Luồng đặt bàn

Khách hàng truy cập trang đặt bàn, nhập thông tin gồm họ tên, số điện thoại, ngày giờ đặt và số lượng khách. Sau khi gửi yêu cầu, hệ thống kiểm tra thông tin và lưu đơn đặt bàn vào database.

```text
Khách hàng
   |
   v
Nhập thông tin đặt bàn
   |
   v
Hệ thống kiểm tra dữ liệu
   |
   v
Lưu thông tin đặt bàn
   |
   v
Nhân viên xác nhận và gán bàn
```

Luồng này giúp nhà hàng biết trước số lượng khách, thời gian khách đến và chuẩn bị bàn phù hợp.

### Luồng gọi món

Khách hàng gọi món bằng mã bàn hoặc mã đặt bàn. Sau khi truy cập đúng phiên gọi món, khách hàng chọn món, thêm vào giỏ và xác nhận gọi món.

```text
Khách hàng
   |
   v
Truy cập bằng mã bàn / mã đặt bàn
   |
   v
Chọn món từ thực đơn
   |
   v
Xác nhận gọi món
   |
   v
Đơn món được gửi đến nhân viên và bếp
```

Luồng này giúp khách hàng gọi món trực tiếp trên website mà không cần chờ nhân viên ghi món thủ công.

### Luồng xử lý món

Sau khi khách gọi món, nhân viên và bếp có thể xem danh sách món cần xử lý. Bếp chuẩn bị món, nhân viên phục vụ món và cập nhật trạng thái món trong hệ thống.

```text
Khách gọi món
   |
   v
Bếp nhận món cần chuẩn bị
   |
   v
Nhân viên theo dõi món theo bàn
   |
   v
Xác nhận món đã phục vụ
   |
   v
Cập nhật trạng thái đơn món
```

Luồng này giúp nhà hàng kiểm soát món nào đang chờ, món nào đã phục vụ và hạn chế nhầm lẫn giữa các bàn.

### Luồng thanh toán

Khi khách dùng bữa xong, nhân viên kiểm tra phiên gọi món và thực hiện thanh toán. Hệ thống hỗ trợ thanh toán bằng tiền mặt hoặc chuyển khoản thông qua VietQR nếu được cấu hình.

```text
Khách yêu cầu thanh toán
   |
   v
Nhân viên kiểm tra phiên bàn
   |
   v
Chọn phương thức thanh toán
   |
   v
Lưu thông tin thanh toán
   |
   v
Cập nhật trạng thái bàn
```

Sau khi thanh toán xong, bàn có thể được đưa về trạng thái trống để phục vụ lượt khách tiếp theo.

### Luồng báo cáo

Quản lý truy cập dashboard để xem dữ liệu doanh thu, món bán chạy và tình hình hoạt động của nhà hàng. Dữ liệu chính được lấy từ MariaDB, sau đó có thể được lưu thêm sang MongoDB để phục vụ phân tích.

```text
Quản lý
   |
   v
Mở dashboard / báo cáo
   |
   v
Hệ thống tổng hợp dữ liệu
   |
   v
Hiển thị thống kê doanh thu
   |
   v
Lưu dữ liệu phân tích sang MongoDB
```

Luồng này giúp quản lý theo dõi tình hình kinh doanh và có dữ liệu hỗ trợ ra quyết định.

### Luồng gợi ý món ăn

Khi khách hàng xem hoặc gọi món, hệ thống có thể hiển thị các món được đề xuất dựa trên món phổ biến, món thường được gọi kèm hoặc dữ liệu hành vi gọi món trước đó.

```text
Khách hàng xem / gọi món
   |
   v
Hệ thống lấy dữ liệu món ăn
   |
   v
Phân tích món phổ biến / món liên quan
   |
   v
Hiển thị gợi ý món phù hợp
```

Luồng này giúp khách hàng chọn món nhanh hơn và hỗ trợ nhà hàng giới thiệu các món nổi bật.

---

## 6. Cách chạy bằng Docker

Project được thiết kế để chạy bằng Docker.

### Bước 1: Clone project

```bash
git clone <repository-url>
cd <project-folder>
```

### Bước 2: Tạo file môi trường

Tạo file `.env` từ file mẫu:

```bash
cp .env.example .env
```

Sau đó cập nhật các biến cấu hình phù hợp với môi trường chạy project.

### Bước 3: Chạy project

```bash
docker compose up -d --build
```

### Bước 4: Truy cập website

```text
http://localhost:8080/buffet-chay
```

### Bước 5: Truy cập phpMyAdmin

```text
http://localhost:8081
```

Thông tin đăng nhập database được cấu hình trong file `.env`.

### Bước 6: Truy cập MongoDB

```text
mongodb://localhost:27018
```

### Một số lệnh Docker thường dùng

Dừng hệ thống:

```bash
docker compose down
```

Chạy lại hệ thống:

```bash
docker compose up -d
```

Xem log:

```bash
docker compose logs -f
```

Reset toàn bộ dữ liệu Docker:

```bash
docker compose down -v
docker compose up -d --build
```

> Lưu ý: Lệnh reset sẽ xóa dữ liệu trong volume Docker và khởi tạo lại database từ đầu.

---

## 7. Tài khoản demo

Hệ thống có sẵn một số tài khoản mẫu để kiểm thử các vai trò chính như quản lý, nhân viên phục vụ, nhân viên bếp và khách hàng.

Thông tin tài khoản demo được lưu trong dữ liệu mẫu của project. Khi cần kiểm thử, người dùng có thể xem hoặc cập nhật trực tiếp trong database sau khi chạy hệ thống bằng Docker.

> Không nên đưa tài khoản thật, mật khẩu thật hoặc thông tin đăng nhập nhạy cảm vào README khi public project lên GitHub.

---

## 8. Cấu hình môi trường

Các cấu hình chính của hệ thống được đặt trong file `.env`. Project có thể cung cấp file `.env.example` để mô tả các biến môi trường cần có, nhưng không nên đưa thông tin thật vào README hoặc commit file `.env` lên GitHub.

Ví dụ file `.env.example`:

```env
BASE_URL=http://localhost:8080/buffet-chay
PUBLIC_BASE_URL=http://localhost:8080/buffet-chay

DB_HOST=db
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

VIETQR_ENABLED=0
VIETQR_BANK_ID=your_bank_id
VIETQR_ACCOUNT_NO=your_account_number
VIETQR_ACCOUNT_NAME=your_account_name
VIETQR_TEMPLATE=compact2

MONGO_ENABLED=1
MONGO_URI=mongodb://mongo:27017
MONGO_DB=your_mongodb_database
```

Ý nghĩa các nhóm cấu hình:

| Nhóm cấu hình                              | Ý nghĩa                                             |
| ------------------------------------------ | --------------------------------------------------- |
| `BASE_URL`, `PUBLIC_BASE_URL`              | Cấu hình đường dẫn chạy hệ thống                    |
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Cấu hình kết nối MariaDB                            |
| `VIETQR_*`                                 | Cấu hình thanh toán chuyển khoản bằng VietQR        |
| `MONGO_*`                                  | Cấu hình MongoDB dùng cho dữ liệu báo cáo/phân tích |

Khi chạy project ở máy cá nhân, người dùng tự tạo file `.env` dựa trên `.env.example` và điền thông tin phù hợp với môi trường của mình.

File `.env` nên được thêm vào `.gitignore` để tránh đẩy thông tin nhạy cảm lên GitHub:

```gitignore
.env
```

---
