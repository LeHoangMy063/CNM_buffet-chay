<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

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
        $this->chuyenHuong(BASE_URL . '/quan-ly/thuc-don');
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

    private function hienThiTrangQuanLy($bangDangMo)
    {
        $this->yeuCauQuanLy();

        $tuNgay = isset($_GET['tu_ngay']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_GET['tu_ngay'])
            ? $_GET['tu_ngay']
            : date('Y-m-01', strtotime('first day of previous month'));
        $denNgay = isset($_GET['den_ngay']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_GET['den_ngay'])
            ? $_GET['den_ngay']
            : date('Y-m-t', strtotime('last day of previous month'));

        $this->view('quanly/thuc-don', array(
            'nguoiDung' => $this->layNguoiDung(),
            'danhSachMon' => $this->moHinhThucDon->layTatCa(),
            'danhSachNhanVien' => $this->moHinhTaiKhoan->layNhanVienQuanLy(),
            'tuNgay' => $tuNgay,
            'denNgay' => $denNgay,
            'tongQuanDoanhThu' => $this->moHinhDon->tongQuanDoanhThu($tuNgay, $denNgay),
            'baoCaoDoanhThu' => $this->moHinhDon->thongKeDoanThu($tuNgay, $denNgay),
            'chiTietDoanhThu' => $this->moHinhDon->chiTietDoanhThu($tuNgay, $denNgay),
            'bangDangMo' => $bangDangMo
        ));
    }

    public function luuMon()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ten = trim($this->post('ten', ''));
        $danhMuc = trim($this->post('danh_muc', ''));
        if ($ten === '' || $danhMuc === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui lòng nhập tên món và danh mục'));
        }

        $ok = $this->moHinhThucDon->luu($_POST);
        $this->json(array(
            'success' => (bool)$ok,
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
            'success' => (bool)$ok,
            'thong_bao' => $ok ? 'Đã xóa món ăn' : 'Không thể xóa món ăn'
        ));
    }

    public function luuNhanVien()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id = intval($this->post('id', 0));
        $duLieu = array(
            'ten_dang_nhap' => trim($this->post('ten_dang_nhap', '')),
            'mat_khau' => $this->post('mat_khau', ''),
            'vai_tro' => $this->post('vai_tro', 'nhanvien'),
            'dang_hoat_dong' => intval($this->post('dang_hoat_dong', 1)),
            'ho_ten' => trim($this->post('ho_ten', '')),
            'email' => trim($this->post('email', '')),
            'so_dien_thoai' => trim($this->post('so_dien_thoai', ''))
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
            $ok = $this->moHinhTaiKhoan->capNhatNhanVien($id, $duLieu);
            $thongBaoThanhCong = 'Đã cập nhật nhân viên';
            $thongBaoLoi = 'Không thể cập nhật nhân viên';
        } else {
            $ok = $this->moHinhTaiKhoan->themNhanVien($duLieu);
            $thongBaoThanhCong = 'Đã thêm nhân viên';
            $thongBaoLoi = 'Không thể thêm nhân viên';
        }

        $this->json(array(
            'success' => (bool)$ok,
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
            'success' => (bool)$ok,
            'thong_bao' => $ok ? 'Đã xóa nhân viên' : 'Không thể xóa nhân viên'
        ));
    }
}
