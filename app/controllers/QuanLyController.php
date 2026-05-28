<?php

require_once __DIR__ . '/../models/MoHinhCo.php';
require_once __DIR__ . '/../models/MoHinh.php';
require_once __DIR__ . '/../core/MongoKetNoi.php';
require_once __DIR__ . '/BoieuKhienCo.php';
require_once __DIR__ . '/XuatBaoCaoController.php';

class QuanLyController extends BoieuKhienCo
{
    protected $moHinhThucDon;
    protected $moHinhTaiKhoan;
    protected $moHinhDon;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhThucDon = new MoHinhMonAn();
        $this->moHinhTaiKhoan = new MoHinhTaiKhoan();
        $this->moHinhDon = new MoHinhDonMon();
    }

    public function trangChu()
    {
        $this->chuyenHuong(BASE_URL . '/quan-ly/bao-cao');
    }

    public function dashboard()
    {
        $this->chuyenHuong(BASE_URL . '/quan-ly/bao-cao');
    }

    public function thucDon()
    {
        $this->hienThiTrangQuanLy('thuc-don');
    }

    public function nhanVien()
    {
        $this->hienThiTrangQuanLy('nhan-vien');
    }

    public function baoCaoDoanhThu()
    {
        $this->hienThiTrangQuanLy('bao-cao');
    }

    // Delegate sang XuatBaoCaoController
    public function xuatBaoCaoDoanhThu()
    {
        $xuatBaoCao = new XuatBaoCaoController();
        $xuatBaoCao->xuatBaoCaoDoanhThu();
    }

    private function hienThiTrangQuanLy($bangDangMo)
    {
        $this->yeuCauQuanLy();

        $tuNgay = isset($_GET['tu_ngay']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_GET['tu_ngay'])
            ? $_GET['tu_ngay']
            : date('Y-m-d');
        $denNgay = isset($_GET['den_ngay']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_GET['den_ngay'])
            ? $_GET['den_ngay']
            : date('Y-m-d');

        $topMonBanChay    = $this->moHinhDon->topMonBanChayTrongKhoang(6, $tuNgay, $denNgay);
        $thongKeDanhMuc   = $this->moHinhDon->thongKeDanhMucTrongKhoang($tuNgay, $denNgay);
        $thongKeTheoGio   = $this->moHinhDon->thongKeDonTheoGio($tuNgay, $denNgay);
        $monCanDay        = $this->moHinhDon->monItBanTrongKhoang(5, $tuNgay, $denNgay);
        $tongQuanDoanhThu = $this->moHinhDon->tongQuanDoanhThu($tuNgay, $denNgay);
        $baoCaoDoanhThu   = $this->moHinhDon->thongKeDoanThu($tuNgay, $denNgay);
        $chiTietDoanhThu  = $this->moHinhDon->chiTietDoanhThu($tuNgay, $denNgay);

        if ($bangDangMo === 'bao-cao') {
            $this->dongBoMongoBaoCaoTuDong($tuNgay, $denNgay, $tongQuanDoanhThu, $chiTietDoanhThu, $topMonBanChay, $thongKeDanhMuc, $thongKeTheoGio);
        }

        $this->view('quanly/thuc-don', array(
            'nguoiDung'        => $this->layNguoiDung(),
            'danhSachMon'      => $this->moHinhThucDon->layTatCa(),
            'danhSachNhanVien' => $this->moHinhTaiKhoan->layNhanVienQuanLy(),
            'tuNgay'           => $tuNgay,
            'denNgay'          => $denNgay,
            'tongQuanDoanhThu' => $tongQuanDoanhThu,
            'baoCaoDoanhThu'   => $baoCaoDoanhThu,
            'chiTietDoanhThu'  => $chiTietDoanhThu,
            'topMonBanChay'    => $topMonBanChay,
            'thongKeDanhMuc'   => $thongKeDanhMuc,
            'thongKeTheoGio'   => $thongKeTheoGio,
            'monCanDay'        => $monCanDay,
            'goiYMonAi'        => $this->taoGoiYMonAi($topMonBanChay, $thongKeDanhMuc, $monCanDay, $tongQuanDoanhThu),
            'bangDangMo'       => $bangDangMo
        ));
    }

    private function dongBoMongoBaoCaoTuDong($tuNgay, $denNgay, $tongQuan, $doanhThuNgay, $topMon, $danhMuc, $theoGio)
    {
        $mongo = new MongoKetNoi();
        $mongo->dongBoBaoCaoDoanhThu($tuNgay, $denNgay, $tongQuan, $doanhThuNgay, $topMon, $danhMuc, $theoGio);
    }

    private function taoGoiYMonAi($topMon, $danhMuc, $monCanDay, $tongQuan)
    {
        $goiY      = array();
        $soPhien   = isset($tongQuan['so_phien'])   ? (int)$tongQuan['so_phien']   : 0;
        $tongKhach = isset($tongQuan['tong_khach']) ? (int)$tongQuan['tong_khach'] : 0;

        if (!empty($topMon)) {
            $mon = $topMon[0];
            $goiY[] = array(
                'nhan'      => 'Món được gọi nhiều',
                'tieu_de'   => 'Chuẩn bị sẵn "' . $mon['ten'] . '" trước giờ đông khách',
                'mo_ta'     => 'Món này có lượng gọi cao nhất trong buffet. Nên sơ chế trước theo mẻ nhỏ để ra món nhanh mà vẫn giữ chất lượng.',
                'hanh_dong' => 'Tăng định mức sơ chế cho ca cao điểm.'
            );
        }

        if (!empty($danhMuc)) {
            $dm = $danhMuc[0];
            $goiY[] = array(
                'nhan'      => 'Nhóm món hút khách',
                'tieu_de'   => 'Ưu tiên quay vòng nhóm ' . $dm['danh_muc'],
                'mo_ta'     => 'Nhóm này đang được gọi nhiều trong buffet. Nên bố trí nguyên liệu và nhân sự bếp theo nhóm này để tránh trễ món.',
                'hanh_dong' => 'Kiểm tra tồn kho và lịch sơ chế theo nhóm.'
            );
        }

        if (!empty($monCanDay)) {
            $monCham = $monCanDay[0];
            $goiY[] = array(
                'nhan'      => 'Món ít được gọi',
                'tieu_de'   => 'Xem lại vị trí hiển thị của "' . $monCham['ten'] . '"',
                'mo_ta'     => 'Món còn phục vụ nhưng ít khách chọn. Nên đổi ảnh, tên gọi, mô tả hoặc đưa vào vị trí dễ thấy hơn trên màn hình gọi món.',
                'hanh_dong' => 'Thử hiển thị nổi bật trong 1 ca phục vụ.'
            );
        }

        if ($soPhien > 0 && $tongKhach > 0) {
            $khachMoiPhien = round($tongKhach / $soPhien, 1);
            $goiY[] = array(
                'nhan'      => 'Tải bếp',
                'tieu_de'   => 'Trung bình ' . $khachMoiPhien . ' khách mỗi phiên buffet',
                'mo_ta'     => 'Khi số khách mỗi phiên cao, nên đưa các món ra nhanh như khai vị, rau và topping lên trước để giảm áp lực bếp nóng.',
                'hanh_dong' => 'Sắp xếp màn hình gọi món theo tốc độ ra món.'
            );
        }

        if (empty($goiY)) {
            $goiY[] = array(
                'nhan'      => 'Khởi động',
                'tieu_de'   => 'Cần thêm dữ liệu gọi món để AI gợi ý tốt hơn',
                'mo_ta'     => 'Hãy phục vụ vài phiên buffet đầu tiên, sau đó dashboard sẽ nhìn được món được gọi nhiều, giờ cao điểm và món ít được chọn.',
                'hanh_dong' => 'Tiếp tục ghi nhận đơn món.'
            );
        }

        return array_slice($goiY, 0, 4);
    }

    public function luuMon()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ten     = trim($this->post('ten', ''));
        $danhMuc = trim($this->post('danh_muc', ''));
        if ($ten === '' || $danhMuc === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui lòng nhập tên món và danh mục'));
        }

        $ok = $this->moHinhThucDon->luu($_POST);
        $this->json(array(
            'success'   => (bool)$ok,
            'thong_bao' => $ok ? 'Đã lưu món ăn' : 'Không thể lưu món ăn'
        ));
    }

    public function xoaMon()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id = intval($this->post('id', 0));
        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Món ăn không hợp lệ'));
        }

        $ok = $this->moHinhThucDon->xoa($id);
        $this->json(array(
            'success'   => (bool)$ok,
            'thong_bao' => $ok ? 'Đã xóa món ăn' : 'Không thể xóa món ăn'
        ));
    }

    public function luuNhanVien()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id     = intval($this->post('id', 0));
        $duLieu = array(
            'ten_dang_nhap'  => trim($this->post('ten_dang_nhap', '')),
            'mat_khau'       => $this->post('mat_khau', ''),
            'vai_tro'        => $this->post('vai_tro', 'nhanvien'),
            'dang_hoat_dong' => intval($this->post('dang_hoat_dong', 1)),
            'ho_ten'         => trim($this->post('ho_ten', '')),
            'email'          => trim($this->post('email', '')),
            'so_dien_thoai'  => trim($this->post('so_dien_thoai', ''))
        );

        $vaiTroHopLe = array('quanly', 'nhanvien', 'bep');
        if (!in_array($duLieu['vai_tro'], $vaiTroHopLe)) {
            $this->json(array('success' => false, 'thong_bao' => 'Vai trò không hợp lệ'));
        }

        if ($duLieu['ten_dang_nhap'] === '' || $duLieu['ho_ten'] === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui lòng nhập tên đăng nhập và họ tên'));
        }

        if ($duLieu['email'] !== '' && !filter_var($duLieu['email'], FILTER_VALIDATE_EMAIL)) {
            $this->json(array('success' => false, 'thong_bao' => 'Email không hợp lệ'));
        }

        if ($id <= 0 && trim($duLieu['mat_khau']) === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui lòng nhập mật khẩu cho nhân viên mới'));
        }

        $nguoiDung = $this->layNguoiDung();
        if ($id > 0 && $nguoiDung && isset($nguoiDung['id']) && (int)$nguoiDung['id'] === $id && $duLieu['dang_hoat_dong'] !== 1) {
            $this->json(array('success' => false, 'thong_bao' => 'Không thể khóa chính tài khoản đang đăng nhập'));
        }

        if ($id > 0) {
            $taiKhoan = $this->moHinhTaiKhoan->layTheoId($id);
            if (!$taiKhoan || !in_array($taiKhoan['vai_tro'], $vaiTroHopLe)) {
                $this->json(array('success' => false, 'thong_bao' => 'Không tìm thấy nhân viên'));
            }
            $ok                = $this->moHinhTaiKhoan->capNhatNhanVien($id, $duLieu);
            $thongBaoThanhCong = 'Đã cập nhật nhân viên';
            $thongBaoLoi       = 'Không thể cập nhật nhân viên';
        } else {
            $ok                = $this->moHinhTaiKhoan->themNhanVien($duLieu);
            $thongBaoThanhCong = 'Đã thêm nhân viên';
            $thongBaoLoi       = 'Không thể thêm nhân viên';
        }

        $this->json(array(
            'success'   => (bool)$ok,
            'thong_bao' => $ok ? $thongBaoThanhCong : $thongBaoLoi
        ));
    }

    public function xoaNhanVien()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id = intval($this->post('id', 0));
        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Nhân viên không hợp lệ'));
        }

        $nguoiDung = $this->layNguoiDung();
        if ($nguoiDung && isset($nguoiDung['id']) && (int)$nguoiDung['id'] === $id) {
            $this->json(array('success' => false, 'thong_bao' => 'Không thể xóa chính tài khoản đang đăng nhập'));
        }

        $taiKhoan = $this->moHinhTaiKhoan->layTheoId($id);
        if (!$taiKhoan || !in_array($taiKhoan['vai_tro'], array('quanly', 'nhanvien', 'bep'))) {
            $this->json(array('success' => false, 'thong_bao' => 'Không tìm thấy nhân viên'));
        }

        $ok = $this->moHinhTaiKhoan->xoaNhanVien($id);
        $this->json(array(
            'success'   => (bool)$ok,
            'thong_bao' => $ok ? 'Đã xóa nhân viên' : 'Không thể xóa nhân viên'
        ));
    }
}
