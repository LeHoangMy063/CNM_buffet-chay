<?php

require_once dirname(__FILE__) . '/../models/KhachHangModel.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class KhachHangNhanVienController extends BoieuKhienCo
{
    protected $khachHangModel;

    public function __construct()
    {
        parent::__construct();
        $this->khachHangModel = new KhachHangModel();
    }

    private function yeuCauNhanVien()
    {
        if (!$this->daDangNhap()) {
            $this->chuyenHuong(BASE_URL . '/dang-nhap');
        }

        $vaiTro = isset($_SESSION['nguoi_dung']['vai_tro'])
            ? $_SESSION['nguoi_dung']['vai_tro']
            : '';

        if ($vaiTro !== 'nhanvien') {
            $this->chuyenHuong(BASE_URL . '/dang-nhap');
        }
    }

    public function index()
    {
        $this->yeuCauNhanVien();

        $tuKhoa = trim((string)$this->get('tim', ''));
        $trangThai = trim((string)$this->get('trang_thai', ''));
        if (!in_array($trangThai, array('', 'dang_hoat_dong', 'ngung_hoat_dong'))) {
            $trangThai = '';
        }

        $this->view('nhanvien/khachhang/index', array(
            'nhanVien' => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'tuKhoa' => $tuKhoa,
            'trangThai' => $trangThai,
            'danhSachKhachHang' => $this->khachHangModel->layDanhSach($tuKhoa, $trangThai)
        ));
    }

    public function chiTiet($id)
    {
        $this->yeuCauNhanVien();

        $id = trim((string)$id);
        $chiTiet = $id !== '' ? $this->khachHangModel->layChiTiet($id) : null;

        if (!$chiTiet) {
            header('HTTP/1.1 404 Not Found');
        }

        $this->view('nhanvien/khachhang/detail', array(
            'nhanVien' => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'idKhachHang' => $id,
            'chiTiet' => $chiTiet
        ));
    }
}
