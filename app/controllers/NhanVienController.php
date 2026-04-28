<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class NhanVienController extends BoieuKhienCo
{
    protected $moHinhBan;
    protected $moHinhDon;
    protected $moHinhDatBan;
    protected $moHinhTaiKhoan;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhBan      = new MoHinhBan();
        $this->moHinhDon      = new MoHinhDonMon();
        $this->moHinhDatBan   = new MoHinhDatBan();
        $this->moHinhTaiKhoan = new MoHinhTaiKhoan();
    }

    public function tongQuan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $data = array(
            'nhanVien'    => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSachBan' => $this->moHinhBan->layTatCaBan()
        );

        $this->view('staff/trang-chu', $data);
    }

    public function xemDon()
    {
        $this->yeuCauAdminHoacNhanVien();

        $this->view('staff/trang-chu', array(
            'nhanVien' => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array()
        ));
    }

    public function layDanhSachBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $danhSachBan = $this->moHinhBan->layTatCaBan();
        $this->json(array('success' => true, 'du_lieu' => $danhSachBan));
    }

    public function layDonTheoBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $banId = isset($_GET['ban_id']) ? intval($_GET['ban_id']) : 0;
        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID ban khong hop le'));
        }

        $danhSachDon = $this->moHinhDon->layDonTheoBan($banId);
        $this->json(array('success' => true, 'du_lieu' => $danhSachDon));
    }

    public function layDanhSachDatBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $trangThai         = isset($_GET['trang_thai']) ? trim($_GET['trang_thai']) : '';
        $tuKhoa            = isset($_GET['tim'])        ? trim($_GET['tim'])        : '';
        $chiChuaXacNhanBan = isset($_GET['cho_duyet']) && $_GET['cho_duyet'] === '1';

        $danhSach = $this->moHinhDatBan->layDanhSachLocNang($trangThai, $tuKhoa, $chiChuaXacNhanBan);

        $this->json(array('success' => true, 'du_lieu' => $danhSach));
    }

    // Nhan vien xac nhan ban duoc gan tu dong la phu hop
    public function xacNhanGanBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $id = intval($this->post('id', 0));
        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID dat ban khong hop le'));
        }

        $ok = $this->moHinhDatBan->xacNhanGanBan($id);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Da xac nhan ban cho dat ban nay'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi khi xac nhan'));
        }
    }

    public function xacNhanMon()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $donId = intval($this->post('don_id', 0));
        if ($donId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Don khong hop le'));
        }

        $ok = $this->moHinhDon->capNhatTrangThai($donId, 'da_phuc_vu');
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Da xac nhan phuc vu don'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi khi xac nhan'));
        }
    }

    public function xacNhanTatCa()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $banId = intval($this->post('ban_id', 0));
        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Ban khong hop le'));
        }

        $ok = $this->moHinhDon->capNhatTatCaTheoBan($banId, 'da_phuc_vu');
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Da xac nhan tat ca don cua ban'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi khi xac nhan tat ca'));
        }
    }

    public function xacNhanBanTrong()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $banId = intval($this->post('ban_id', 0));
        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Ban khong hop le'));
        }

        $ok = $this->moHinhBan->capNhatTrangThai($banId, 'trong');
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Da xac nhan ban trong'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi khi xac nhan'));
        }
    }

    public function capNhatDatBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $id         = intval($this->post('id', 0));
        $trangThai = $this->post('trang_thai', '');

        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID khong hop le'));
        }

        $hopLe = array('cho_xac_nhan', 'da_xac_nhan', 'da_huy', 'hoan_thanh');
        if (!in_array($trangThai, $hopLe)) {
            $this->json(array('success' => false, 'thong_bao' => 'Trang thai khong hop le'));
        }

        $ok = $this->moHinhDatBan->capNhatTrangThai($id, $trangThai);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Da cap nhat dat ban'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi khi cap nhat'));
        }
    }

    public function ganBanDatBan()
    {
        $this->ganBanDatBanTheoSucChuaNhaHang();
    }

    public function ganBanDatBanCoDieuKien()
    {
        $this->ganBanDatBanTheoSucChuaNhaHang();
    }

    public function ganBanDatBanTheoSucChuaNhaHang()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $id    = intval($this->post('id', 0));
        $banId = intval($this->post('ban_id', 0));

        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Dat ban khong hop le'));
        }

        $datBan = $this->moHinhDatBan->layTheoId($id);
        if (!$datBan) {
            $this->json(array('success' => false, 'thong_bao' => 'Khong tim thay dat ban'));
        }

        $soKhach = intval($datBan['so_nguoi_lon']) + intval($datBan['so_tre_em']);
        $sucChuaNhaHang = defined('RESTAURANT_CAPACITY') ? RESTAURANT_CAPACITY : 40;
        $tongKhachTrungLich = $this->moHinhDatBan->tongKhachTrungLich($datBan['ngay_dat'], $datBan['gio_dat'], $id);

        if ($tongKhachTrungLich + $soKhach > $sucChuaNhaHang) {
            $conLai = $sucChuaNhaHang - $tongKhachTrungLich;
            if ($conLai < 0) {
                $conLai = 0;
            }
            $this->json(array(
                'success'   => false,
                'thong_bao' => 'Phien 90 phut nay chi con nhan toi da ' . $conLai . ' khach, khong du cho ' . $soKhach . ' khach'
            ));
        }

        $danhSachBanGan = array();

        if ($banId > 0) {
            $ban = $this->moHinhBan->layTheoId($banId);
            if (!$ban) {
                $this->json(array('success' => false, 'thong_bao' => 'Ban khong ton tai'));
            }

            if ($this->moHinhDatBan->banBiTrungLich($banId, $datBan['ngay_dat'], $datBan['gio_dat'], $id)) {
                $this->json(array(
                    'success'   => false,
                    'thong_bao' => 'Ban ' . $ban['so_ban'] . ' da co dat ban trung phien 90 phut'
                ));
            }

            if (intval($ban['suc_chua']) >= $soKhach) {
                $danhSachBanGan[] = $ban;
            } else {
                $banId = 0;
            }
        }

        if ($banId <= 0) {
            $danhSachBanGan = $this->moHinhBan->timToHopBanPhuHop($datBan['ngay_dat'], $datBan['gio_dat'], $soKhach, $id);
            if (empty($danhSachBanGan)) {
                $this->json(array(
                    'success'   => false,
                    'thong_bao' => 'Khong con to hop ban phu hop cho ' . $soKhach . ' khach trong phien 90 phut nay'
                ));
            }
        }

        $banIds = array();
        $tenBan = array();
        foreach ($danhSachBanGan as $banGan) {
            $banIds[] = (int)$banGan['id'];
            $tenBan[] = $banGan['so_ban'];
        }

        $ok = $this->moHinhDatBan->capNhatNhieuBan($id, $banIds);
        if ($ok) {
            // Sau khi doi ban thu cong -> reset co xac nhan de nhan vien xem lai
            $this->moHinhDatBan->boXacNhanGanBan($id);
            $this->json(array('success' => true, 'thong_bao' => 'Da gan ban: ' . implode(', ', $tenBan)));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Khong the gan ban'));
        }
    }

    public function tichDiem()
    {
        $this->yeuCauAdminHoacNhanVien();

        $sdt   = isset($_GET['sdt']) ? trim($_GET['sdt']) : '';
        $khach = null;

        if ($sdt !== '') {
            $khach = $this->moHinhTaiKhoan->layTheoSDT($sdt);
        }

        $this->view('staff/tich-diem', array(
            'nhanVien' => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'sdt'      => $sdt,
            'khach'    => $khach
        ));
    }

    public function xuLyTichDiem()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phuong thuc khong hop le'));
        }

        $taiKhoanId = intval($this->post('tai_khoan_id', 0));
        $diem       = intval($this->post('diem', 0));

        if ($taiKhoanId <= 0 || $diem <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Thong tin khong hop le'));
        }

        $ok = $this->moHinhTaiKhoan->congDiem($taiKhoanId, $diem);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Da cong ' . $diem . ' diem cho khach hang'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi khi cong diem'));
        }
    }
}
