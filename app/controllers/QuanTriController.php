<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class QuanTriController extends BoieuKhienCo
{
    protected $moHinhBan;
    protected $moHinhDatBan;
    protected $moHinhThucDon;
    protected $moHinhDon;
    protected $moHinhTaiKhoan;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhBan      = new MoHinhBan();
        $this->moHinhDatBan   = new MoHinhDatBan();
        $this->moHinhThucDon  = new MoHinhMonAn();
        $this->moHinhDon      = new MoHinhDonMon();
        $this->moHinhTaiKhoan = new MoHinhTaiKhoan();
    }

    // ================= TONG QUAN =================
    public function tongQuan()
    {
        $this->yeuCauVaiTro('admin');

        $thongKeHomNay = $this->moHinhDatBan->thongKeHomNay();
        $tongSoBan     = $this->moHinhBan->demTatCa();
        $soMon         = $this->moHinhThucDon->demTatCa();
        $soDonHomNay   = $this->moHinhDon->demHomNay();
        $datBanGanDay  = $this->moHinhDatBan->layGanDay(5);
        $donGanDay     = $this->moHinhDon->layGanDay(5);

        $data = array(
            'nguoiDung'      => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'tieuDeTrang'    => 'Tong Quan',
            'thongKeHomNay'  => $thongKeHomNay,
            'tongSoBan'      => $tongSoBan,
            'soMon'          => $soMon,
            'soDonHomNay'    => $soDonHomNay,
            'datBanGanDay'   => $datBanGanDay,
            'donGanDay'      => $donGanDay
        );

        $this->view('home/tong-quan', $data);
    }

    // ================= QUAN LY BAN =================
    public function quanLyBan()
    {
        $this->yeuCauVaiTro('admin');

        $data = array(
            'nguoiDung'  => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSachBan' => $this->moHinhBan->layTatCaBan()
        );

        $this->view('home/quan-ly-ban', $data);
    }

    public function capNhatTrangThaiBan()
    {
        $this->yeuCauVaiTro('admin');

        $id         = intval($this->post('id', 0));
        $trang_thai = $this->post('trang_thai', '');

        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID ban khong hop le'));
        }

        $ok = $this->moHinhBan->capNhatTrangThai($id, $trang_thai);
        $this->json(array('success' => (bool)$ok));
    }

    // ================= QUAN LY THUC DON =================
    public function quanLyThucDon()
    {
        $this->yeuCauVaiTro('admin');

        $data = array(
            'nguoiDung'   => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSachMon' => $this->moHinhThucDon->layTatCa()
        );

        $this->view('home/thuc-don', $data);
    }

    public function luuMonAn()
    {
        $this->yeuCauVaiTro('admin');

        $ok = $this->moHinhThucDon->luu($_POST);
        $this->json(array('success' => (bool)$ok));
    }

    public function xoaMonAn()
    {
        $this->yeuCauVaiTro('admin');

        $id = intval($this->post('id', 0));
        $ok = $this->moHinhThucDon->xoa($id);
        $this->json(array('success' => (bool)$ok));
    }

    // ================= QUAN LY DON MON =================
    public function quanLyDonMon()
    {
        $this->yeuCauVaiTro('admin');

        $data = array(
            'nguoiDung'    => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSachDon'  => $this->moHinhDon->layTatCa()
        );

        $this->view('home/don-mon', $data);
    }

    public function capNhatTrangThaiDon()
    {
        $this->yeuCauVaiTro('admin');

        $id         = intval($this->post('id', 0));
        $trang_thai = $this->post('trang_thai', '');

        $ok = $this->moHinhDon->capNhatTrangThai($id, $trang_thai);
        $this->json(array('success' => (bool)$ok));
    }

    // ================= QUAN LY DAT BAN =================
    public function quanLyDatBan()
    {
        $this->yeuCauVaiTro('admin');

        $bo_loc      = isset($_GET['trang_thai']) ? trim($_GET['trang_thai']) : '';
        $tu_khoa     = isset($_GET['tim']) ? trim($_GET['tim']) : '';

        if ($tu_khoa !== '') {
            $danhSach = $this->moHinhDatBan->timKiem($tu_khoa);
        } elseif ($bo_loc !== '') {
            $danhSach = $this->moHinhDatBan->locTheoTrangThai($bo_loc);
        } else {
            $danhSach = $this->moHinhDatBan->layTatCa();
        }

        $data = array(
            'nguoiDung'  => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSach'   => $danhSach,
            'boLoc'      => $bo_loc,
            'tuKhoa'     => $tu_khoa
        );

        $this->view('home/dat-ban', $data);
    }

    public function capNhatTrangThaiDatBan()
    {
        $this->yeuCauVaiTro('admin');

        $id         = intval($this->post('id', 0));
        $trang_thai = $this->post('trang_thai', '');

        $hop_le = array('cho_xac_nhan', 'da_xac_nhan', 'da_huy', 'hoan_thanh');
        if (!in_array($trang_thai, $hop_le)) {
            $this->json(array('success' => false, 'thong_bao' => 'Trang thai khong hop le'));
        }

        $ok = $this->moHinhDatBan->capNhatTrangThai($id, $trang_thai);
        $this->json(array('success' => (bool)$ok));
    }

    // ================= BAO CAO DOANH THU =================
    public function baoCaoDoanThu()
    {
        $this->yeuCauVaiTro('admin');

        $tu_ngay  = isset($_GET['tu_ngay'])  ? $_GET['tu_ngay']  : date('Y-m-01');
        $den_ngay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');

        $baoCao      = $this->moHinhDon->thongKeDoanThu($tu_ngay, $den_ngay);
        $topMon      = $this->moHinhDon->topMonBanChay(10);
        $thongKeHN   = $this->moHinhDatBan->thongKeHomNay();

        $data = array(
            'nguoiDung'  => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'tieuDeTrang' => 'Bao Cao Doanh Thu',
            'tuNgay'     => $tu_ngay,
            'denNgay'    => $den_ngay,
            'baoCao'     => $baoCao,
            'topMon'     => $topMon,
            'thongKeHN'  => $thongKeHN
        );

        $this->view('home/bao-cao', $data);
    }

    // ================= QUAN LY TAI KHOAN =================
    public function quanLyTaiKhoan()
    {
        $this->yeuCauVaiTro('admin');

        $data = array(
            'nguoiDung'  => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSach'   => $this->moHinhTaiKhoan->layTatCa()
        );

        $this->view('home/quan-ly-tai-khoan', $data);
    }

    public function themNhanVien()
    {
        $this->yeuCauVaiTro('admin');

        $duLieu = array(
            'ten_dang_nhap' => trim($this->post('ten_dang_nhap', '')),
            'mat_khau'      => $this->post('mat_khau', ''),
            'vai_tro'       => $this->post('vai_tro', 'nhan_vien'),
            'ho_ten'        => trim($this->post('ho_ten', '')),
            'email'         => trim($this->post('email', '')),
            'so_dien_thoai' => trim($this->post('so_dien_thoai', ''))
        );

        if ($duLieu['ten_dang_nhap'] === '' || $duLieu['mat_khau'] === '' || $duLieu['ho_ten'] === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap day du thong tin'));
            return;
        }

        $id = $this->moHinhTaiKhoan->themNhanVien($duLieu);

        if ($id) {
            $this->json(array('success' => true, 'thong_bao' => 'Da them nhan vien thanh cong'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Ten dang nhap da ton tai hoac loi he thong'));
        }
    }

    public function capNhatTrangThaiTaiKhoan()
    {
        $this->yeuCauVaiTro('admin');

        $id         = intval($this->post('id', 0));
        $trang_thai = intval($this->post('trang_thai', 1));

        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID khong hop le'));
        }

        $nguoi_dung = isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : null;
        if ($nguoi_dung && $nguoi_dung['id'] == $id) {
            $this->json(array('success' => false, 'thong_bao' => 'Khong the khoa chinh tai khoan cua ban'));
        }

        $ok = $this->moHinhTaiKhoan->capNhatTrangThai($id, $trang_thai);
        $this->json(array('success' => (bool)$ok));
    }
}
