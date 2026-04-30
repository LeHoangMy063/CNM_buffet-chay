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
            $this->json(array('success' => false, 'thong_bao' => 'ID bàn không hợp lệ'));
        }

        $danhSachDon = $this->moHinhDon->layDonTheoBan($banId);
        $this->json(array('success' => true, 'du_lieu' => $danhSachDon));
    }

    public function capNhatTrangThaiBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $banId     = intval($this->post('ban_id', 0));
        $trangThai = $this->post('trang_thai', '');

        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Bàn không hợp lệ'));
        }

        $hopLe = array('trong', 'dang_dung', 'cho_thanh_toan', 'da_thanh_toan');
        if (!in_array($trangThai, $hopLe)) {
            $this->json(array('success' => false, 'thong_bao' => 'Trạng thái bàn không hợp lệ'));
        }

        $ok = $this->moHinhBan->capNhatTrangThai($banId, $trangThai);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã cập nhật trạng thái bàn'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi cập nhật trạng thái bàn'));
        }
    }

    public function layDanhSachDatBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $trangThai         = isset($_GET['trang_thai']) ? trim($_GET['trang_thai']) : '';
        $tuKhoa            = isset($_GET['tim'])        ? trim($_GET['tim'])        : '';
        $ngayDat           = isset($_GET['ngay'])       ? trim($_GET['ngay'])       : '';
        $chiChuaXacNhanBan = isset($_GET['cho_duyet']) && $_GET['cho_duyet'] === '1';

        $danhSach = $this->moHinhDatBan->layDanhSachLocNang($trangThai, $tuKhoa, $chiChuaXacNhanBan, $ngayDat);

        $this->json(array('success' => true, 'du_lieu' => $danhSach));
    }

    public function layLichDatBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $thang = isset($_GET['thang']) ? trim($_GET['thang']) : date('Y-m');
        if (!preg_match('/^[0-9]{4}-[0-9]{2}$/', $thang)) {
            $thang = date('Y-m');
        }

        $this->json(array(
            'success' => true,
            'du_lieu' => $this->moHinhDatBan->layThongKeNgayTrongThang($thang)
        ));
    }

    // Nhan vien xac nhan ban duoc gan tu dong la phu hop
    public function xacNhanGanBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id = intval($this->post('id', 0));
        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID đặt bàn không hợp lệ'));
        }

        $datBan = $this->moHinhDatBan->layTheoId($id);
        if (!$datBan) {
            $this->json(array('success' => false, 'thong_bao' => 'Không tìm thấy đặt bàn'));
        }

        if ($datBan['trang_thai'] === 'cancelled' || $datBan['trang_thai'] === 'expired' || $datBan['trang_thai'] === 'da_huy') {
            $this->json(array('success' => false, 'thong_bao' => 'Đặt bàn đã hủy, không thể xác nhận'));
        }

        if (!empty($datBan['sdt_khach']) && $this->moHinhDatBan->khachBiTrungLich($datBan['sdt_khach'], $datBan['ngay_dat'], $datBan['gio_dat'], $id)) {
            $this->json(array('success' => false, 'thong_bao' => 'Khách này đã có đặt bàn trùng khung giờ'));
        }

        $banIds = $this->moHinhDatBan->layBanIdsTheoDatBan($id);
        if (empty($banIds) && !empty($datBan['ban_id'])) {
            $banIds = array((int)$datBan['ban_id']);
        }

        foreach ($banIds as $banId) {
            if ($this->moHinhDatBan->banBiTrungLich($banId, $datBan['ngay_dat'], $datBan['gio_dat'], $id)) {
                $this->json(array('success' => false, 'thong_bao' => 'Bàn này đã có đặt bàn trùng khung giờ'));
            }
        }

        $soKhach = (int)$datBan['so_nguoi_lon'] + (int)$datBan['so_tre_em'];
        $sucChua = defined('RESTAURANT_CAPACITY') ? (int)RESTAURANT_CAPACITY : 40;
        $tongKhachDangCo = $this->moHinhDatBan->tongKhachTrungLich($datBan['ngay_dat'], $datBan['gio_dat'], $id);
        if ($tongKhachDangCo + $soKhach > $sucChua) {
            $this->json(array('success' => false, 'thong_bao' => 'Khung giờ này đã vượt sức chứa nhà hàng'));
        }

        $ok = $this->moHinhDatBan->xacNhanGanBan($id);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã xác nhận đặt bàn'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi xác nhận'));
        }
    }

    public function xacNhanMon()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $donId = intval($this->post('don_id', 0));
        if ($donId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Đơn không hợp lệ'));
        }

        $ok = $this->moHinhDon->capNhatTrangThai($donId, 'da_phuc_vu');
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã xác nhận phục vụ đơn'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi xác nhận'));
        }
    }

    public function xacNhanTatCa()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $banId = intval($this->post('ban_id', 0));
        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Bàn không hợp lệ'));
        }

        $ok = $this->moHinhDon->capNhatTatCaTheoBan($banId, 'da_phuc_vu');
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã xác nhận tất cả đơn của bàn'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi xác nhận tất cả'));
        }
    }

    public function xacNhanBanTrong()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $banId = intval($this->post('ban_id', 0));
        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Bàn không hợp lệ'));
        }

        $ok = $this->moHinhBan->capNhatTrangThai($banId, 'trong');
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã xác nhận bàn trống'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi xác nhận'));
        }
    }

    public function capNhatDatBan()
    {
        $this->yeuCauAdminHoacNhanVien();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id         = intval($this->post('id', 0));
        $trangThai = $this->post('trang_thai', '');

        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'ID không hợp lệ'));
        }

        $hopLe = array('cho_xac_nhan', 'da_xac_nhan', 'da_huy', 'cancelled', 'expired', 'hoan_thanh');
        if (!in_array($trangThai, $hopLe)) {
            $this->json(array('success' => false, 'thong_bao' => 'Trạng thái không hợp lệ'));
        }

        $ok = $this->moHinhDatBan->capNhatTrangThai($id, $trangThai);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã cập nhật đặt bàn'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi cập nhật'));
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
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id    = intval($this->post('id', 0));
        $banId = intval($this->post('ban_id', 0));

        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Đặt bàn không hợp lệ'));
        }

        $datBan = $this->moHinhDatBan->layTheoId($id);
        if (!$datBan) {
            $this->json(array('success' => false, 'thong_bao' => 'Không tìm thấy đặt bàn'));
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
                'thong_bao' => 'Phiên 90 phút này chỉ còn nhận tối đa ' . $conLai . ' khách, không đủ cho ' . $soKhach . ' khách'
            ));
        }

        $danhSachBanGan = array();

        if ($banId > 0) {
            $ban = $this->moHinhBan->layTheoId($banId);
            if (!$ban) {
                $this->json(array('success' => false, 'thong_bao' => 'Bàn không tồn tại'));
            }

            if ($this->moHinhDatBan->banBiTrungLich($banId, $datBan['ngay_dat'], $datBan['gio_dat'], $id)) {
                $this->json(array(
                    'success'   => false,
                    'thong_bao' => 'Bàn ' . $ban['so_ban'] . ' đã có đặt bàn trùng phiên 90 phút'
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
                    'thong_bao' => 'Không còn tổ hợp bàn phù hợp cho ' . $soKhach . ' khách trong phiên 90 phút này'
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
            // Sau khi đổi bàn thủ công -> reset cờ xác nhận để nhân viên xem lại
            $this->moHinhDatBan->boXacNhanGanBan($id);
            $this->json(array('success' => true, 'thong_bao' => 'Đã gán bàn: ' . implode(', ', $tenBan)));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Không thể gán bàn'));
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
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $taiKhoanId = intval($this->post('tai_khoan_id', 0));
        $diem       = intval($this->post('diem', 0));

        if ($taiKhoanId <= 0 || $diem <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Thông tin không hợp lệ'));
        }

        $ok = $this->moHinhTaiKhoan->congDiem($taiKhoanId, $diem);
        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã cộng ' . $diem . ' điểm cho khách hàng'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi cộng điểm'));
        }
    }
}
