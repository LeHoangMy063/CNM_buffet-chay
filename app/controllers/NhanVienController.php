<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/../models/MoHinhKhach.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class NhanVienController extends BoieuKhienCo
{
    protected $moHinhBan;
    protected $moHinhDon;
    protected $moHinhDatBan;
    protected $moHinhTaiKhoan;
    protected $moHinhKhach;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhBan      = new MoHinhBan();
        $this->moHinhDon      = new MoHinhDonMon();
        $this->moHinhDatBan   = new MoHinhDatBan();
        $this->moHinhTaiKhoan = new MoHinhTaiKhoan();
        $this->moHinhKhach    = new MoHinhKhach();
    }

    private function laBep()
    {
        return isset($_SESSION['nguoi_dung']['vai_tro']) && $_SESSION['nguoi_dung']['vai_tro'] === 'bep';
    }

    private function yeuCauNhanVienPhucVuJson()
    {
        $this->yeuCauAdminHoacNhanVien();
        if ($this->laBep()) {
            $this->json(array('success' => false, 'thong_bao' => 'Tài khoản bếp chỉ được xem đơn món'));
        }
    }

    private function yeuCauNhanVienPhucVuPage()
    {
        $this->yeuCauAdminHoacNhanVien();
        if ($this->laBep()) {
            $this->chuyenHuong(BASE_URL . '/nhan-vien/tong-quan');
        }
    }

    public function tongQuan()
    {
        $this->yeuCauAdminHoacNhanVien();

        $data = array(
            'nhanVien'    => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'danhSachBan' => $this->moHinhBan->layTatCaBan()
        );

        $this->view('nhanvien/trang-chu', $data);
    }

    public function xemDon()
    {
        $this->yeuCauAdminHoacNhanVien();

        $this->view('nhanvien/trang-chu', array(
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
        $this->yeuCauNhanVienPhucVuJson();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $banId     = intval($this->post('ban_id', 0));
        $trangThai = $this->post('trang_thai', '');

        if ($banId <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Bàn không hợp lệ'));
        }

        $hopLe = array('trong', 'dang_dung');
        if (!in_array($trangThai, $hopLe)) {
            $this->json(array('success' => false, 'thong_bao' => 'Trạng thái bàn không hợp lệ'));
        }

        $thongTinBill = array();
        if ($trangThai === 'dang_dung') {
            $tenKhach = trim($this->post('ten_khach', ''));
            $sdtKhach = trim($this->post('sdt_khach', ''));
            $nguoiLon = intval($this->post('nguoi_lon', 0));
            $treEm    = intval($this->post('tre_em', 0));

            if ($tenKhach === '') {
                $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap ten khach'));
            }
            if ($nguoiLon < 0 || $treEm < 0 || ($nguoiLon + $treEm) <= 0) {
                $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap so luong khach hop le'));
            }

            $giaNguoiLon = defined('PRICE_ADULT') ? (int)PRICE_ADULT : 199000;
            $giaTreEm    = defined('PRICE_CHILD') ? (int)PRICE_CHILD : 0;
            $tongTien    = ($nguoiLon * $giaNguoiLon) + ($treEm * $giaTreEm);

            $thongTinBill = array(
                'ten_khach' => $tenKhach,
                'sdt_khach' => $sdtKhach,
                'nguoi_lon' => $nguoiLon,
                'tre_em'    => $treEm,
                'tong_tien' => $tongTien
            );
        }

        $ok = $this->moHinhBan->capNhatTrangThai($banId, $trangThai);
        $banSauCapNhat = null;
        if ($ok && $trangThai === 'dang_dung') {
            $banSauCapNhat = $this->moHinhBan->taoPhienGoiMon($banId, $thongTinBill);
        } elseif ($ok) {
            $banSauCapNhat = $this->moHinhBan->layTheoId($banId);
        }

        if ($ok) {
            $thongBao = 'Đã cập nhật trạng thái bàn';
            if ($trangThai === 'dang_dung' && $banSauCapNhat && !empty($banSauCapNhat['ma_phien_goi_mon'])) {
                $thongBao = 'Đã mở phiên gọi món. Mã tạm thời: ' . $banSauCapNhat['ma_phien_goi_mon'];
            }
            $this->json(array(
                'success' => true,
                'thong_bao' => $thongBao,
                'du_lieu' => $banSauCapNhat
            ));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi cập nhật trạng thái bàn'));
        }
    }

    public function layDanhSachDatBan()
    {
        $this->yeuCauNhanVienPhucVuJson();

        $trangThai         = isset($_GET['trang_thai']) ? trim($_GET['trang_thai']) : '';
        $tuKhoa            = isset($_GET['tim'])        ? trim($_GET['tim'])        : '';
        $ngayDat           = isset($_GET['ngay'])       ? trim($_GET['ngay'])       : '';
        $chiChuaXacNhanBan = isset($_GET['cho_duyet']) && $_GET['cho_duyet'] === '1';

        $danhSach = $this->moHinhDatBan->layDanhSachLocNang($trangThai, $tuKhoa, $chiChuaXacNhanBan, $ngayDat);

        $this->json(array('success' => true, 'du_lieu' => $danhSach));
    }

    public function layLichDatBan()
    {
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuJson();

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
        $this->yeuCauNhanVienPhucVuPage();

        $sdt   = isset($_GET['sdt']) ? trim($_GET['sdt']) : '';
        $ngay  = isset($_GET['ngay']) ? trim($_GET['ngay']) : date('Y-m-d');
        $khach = null;
        $hoaDonTichDiem = null;
        $hoaDonChuaSdt = array();

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        if ($sdt !== '') {
            $khach = $this->moHinhKhach->layTheoSDT($sdt);
            if ($khach) {
                $khach['_bang_tich_diem'] = 'khach_tai_khoan';
            }

            $hoaDonTichDiem = $this->moHinhBan->layTongHoaDonTichDiemTheoSDTNgay($sdt, $ngay);
            if (empty($hoaDonTichDiem) || (int)$hoaDonTichDiem['tong_tien'] <= 0) {
                $this->moHinhBan->taoHoaDonTuPhienDangDungTheoSDTNgay($sdt, $ngay);
                $hoaDonTichDiem = $this->moHinhBan->layTongHoaDonTichDiemTheoSDTNgay($sdt, $ngay);
            }
        }

        $hoaDonChuaSdt = $this->moHinhBan->layHoaDonChuaSdtTheoNgay($ngay);

        $this->view('nhanvien/tich-diem', array(
            'nhanVien' => isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : array(),
            'sdt'      => $sdt,
            'ngay'     => $ngay,
            'khach'    => $khach,
            'hoaDonTichDiem' => $hoaDonTichDiem,
            'hoaDonChuaSdt' => $hoaDonChuaSdt
        ));
    }

    private function taoMatKhauKhachTuSdt($sdt)
    {
        $so = preg_replace('/[^0-9]/', '', $sdt);
        return strlen($so) >= 6 ? substr($so, -6) : $sdt;
    }

    private function layHoacTaoKhachTichDiem($sdt, $tenKhach, &$daTaoTaiKhoan, &$matKhauMacDinh)
    {
        $sdt = trim($sdt);
        $tenKhach = trim($tenKhach);
        $daTaoTaiKhoan = false;
        $matKhauMacDinh = '';

        if ($sdt === '') {
            return null;
        }

        $khach = $this->moHinhKhach->layTheoSDT($sdt);
        if ($khach) {
            return $khach;
        }

        if ($tenKhach === '') {
            $tenKhach = $sdt;
        }

        $matKhauMacDinh = $this->taoMatKhauKhachTuSdt($sdt);
        $idMoi = $this->moHinhKhach->dangKy($tenKhach, $sdt, '', $matKhauMacDinh);
        if (!$idMoi) {
            return null;
        }

        $daTaoTaiKhoan = true;
        return $this->moHinhKhach->layTheoId($idMoi);
    }

    public function xuLyTichDiem()
    {
        $this->yeuCauNhanVienPhucVuJson();

        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $taiKhoanId = intval($this->post('tai_khoan_id', 0));
        $diem       = intval($this->post('diem', 0));
        $sdt        = trim($this->post('sdt', ''));
        $ngay       = trim($this->post('ngay', ''));
        $tuHoaDon   = $this->post('tu_hoa_don', '0') === '1';
        $hoaDonId   = intval($this->post('hoa_don_id', 0));
        $tenKhachPost = trim($this->post('ten_khach', ''));
        $hoaDonTichDiem = null;

        if ($tuHoaDon && $hoaDonId > 0) {
            $hoaDonTichDiem = $this->moHinhBan->layHoaDonChuaTichDiemTheoId($hoaDonId);
            if (!$hoaDonTichDiem) {
                $this->json(array('success' => false, 'thong_bao' => 'Hóa đơn không hợp lệ hoặc đã tích điểm'));
            }
            $diem = isset($hoaDonTichDiem['diem_quy_doi']) ? (int)$hoaDonTichDiem['diem_quy_doi'] : 0;
            if ($sdt === '') {
                $sdt = isset($hoaDonTichDiem['sdt_khach']) ? trim($hoaDonTichDiem['sdt_khach']) : '';
            }
        } elseif ($tuHoaDon && $sdt !== '' && $ngay !== '') {
            $hoaDonTichDiem = $this->moHinhBan->layTongHoaDonTichDiemTheoSDTNgay($sdt, $ngay);
            if (empty($hoaDonTichDiem) || (int)$hoaDonTichDiem['tong_tien'] <= 0) {
                $this->moHinhBan->taoHoaDonTuPhienDangDungTheoSDTNgay($sdt, $ngay);
                $hoaDonTichDiem = $this->moHinhBan->layTongHoaDonTichDiemTheoSDTNgay($sdt, $ngay);
            }
            $diem = isset($hoaDonTichDiem['diem_quy_doi']) ? (int)$hoaDonTichDiem['diem_quy_doi'] : 0;
        }

        $daTaoTaiKhoan = false;
        $matKhauMacDinh = '';
        if ($taiKhoanId <= 0 && $sdt !== '' && $diem > 0) {
            $tenKhachTao = $tenKhachPost;
            if ($tenKhachTao === '' && isset($hoaDonTichDiem['ten_khach'])) {
                $tenKhachTao = trim($hoaDonTichDiem['ten_khach']);
            }
            $khachTichDiem = $this->layHoacTaoKhachTichDiem($sdt, $tenKhachTao, $daTaoTaiKhoan, $matKhauMacDinh);
            if ($khachTichDiem) {
                $taiKhoanId = (int)$khachTichDiem['id'];
            }
        }

        if ($taiKhoanId <= 0 || $diem <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Thông tin không hợp lệ'));
        }

        $ok = $this->moHinhKhach->congDiem($taiKhoanId, $diem);
        if ($ok) {
            if ($tuHoaDon && $hoaDonId > 0) {
                $tenHoaDon = $tenKhachPost !== ''
                    ? $tenKhachPost
                    : (isset($hoaDonTichDiem['ten_khach']) ? trim($hoaDonTichDiem['ten_khach']) : '');
                if ($tenHoaDon === '') {
                    $tenHoaDon = $sdt;
                }
                $this->moHinhBan->capNhatKhachHoaDon($hoaDonId, $tenHoaDon, $sdt);
                $this->moHinhBan->danhDauMotHoaDonDaTichDiem($hoaDonId, $taiKhoanId, $diem);
            }
            if ($tuHoaDon && $hoaDonId <= 0 && $sdt !== '' && $ngay !== '') {
                $this->moHinhBan->danhDauHoaDonDaTichDiem($sdt, $ngay, $taiKhoanId, $diem);
            }
            $thongBao = 'Đã cộng ' . $diem . ' điểm cho khách hàng';
            if ($daTaoTaiKhoan) {
                $thongBao = 'Đã tạo tài khoản khách và cộng ' . $diem . ' điểm. Mật khẩu mặc định: ' . $matKhauMacDinh;
            }
            $this->json(array('success' => true, 'thong_bao' => $thongBao));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi cộng điểm'));
        }
    }
}
