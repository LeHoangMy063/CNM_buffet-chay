<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class KhachHangController extends BoieuKhienCo
{
    protected $moHinhBan;
    protected $moHinhDatBan;
    protected $moHinhThucDon;
    protected $moHinhDon;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhBan     = new MoHinhBan();
        $this->moHinhDatBan  = new MoHinhDatBan();
        $this->moHinhThucDon = new MoHinhMonAn();
        $this->moHinhDon     = new MoHinhDonMon();
    }

    // ================= TRANG GOI MON =================
    public function trangGoiMon()
    {
        $ma = isset($_GET['ma']) ? trim(strtoupper($_GET['ma'])) : '';

        if ($ma === '') {
            $this->chuyenHuong(BASE_URL . '/');
        }

        // Tim ma dat ban truoc
        $datBan = $this->moHinhDatBan->layTheoMaDatBan($ma);

        if ($datBan) {
            $banId = isset($datBan['ban_id']) ? $datBan['ban_id'] : null;
            if ($banId) {
                $ban = $this->moHinhBan->layTheoId($banId);
            } else {
                $ban = array(
                    'id'             => null,
                    'so_ban'         => 'Đặt Bàn #' . $datBan['id'],
                    'ma_truy_cap'    => $ma,
                    'la_dat_ban'     => true
                );
            }
        } else {
            // Tim ma ban thuong
            $ban = $this->moHinhBan->layTheoMaTruyCap($ma);
        }

        if (!$ban) {
            $this->view('customer/ma-khong-hop-le', array('ma' => $ma));
            return;
        }

        $thucDonTheoDanhMuc = $this->moHinhThucDon->layNhomTheoDanhMuc();

        if (isset($ban['id']) && $ban['id']) {
            $donHienTai = $this->moHinhDon->layTheoMaBan($ban['id']);
        } else if (isset($datBan) && $datBan) {
            $donHienTai = array();
        } else {
            $donHienTai = array();
        }

        $data = array(
            'ban'              => $ban,
            'thucDonTheoDanhMuc' => $thucDonTheoDanhMuc,
            'donHienTai'       => $donHienTai,
            'datBan'           => isset($datBan) ? $datBan : null,
            'ma'               => $ma
        );

        $this->view('customer/goi-mon', $data);
    }

    // ================= DAT MON =================
    public function datMon()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ma      = trim(strtoupper($this->post('ma', $this->post('code', ''))));
        $monAnId = intval($this->post('mon_an_id', $this->post('menu_item_id', 0)));
        $soLuong = intval($this->post('so_luong', $this->post('quantity', 1)));
        $ghiChu  = $this->post('ghi_chu', $this->post('note', ''));
        $itemsJson = $this->post('items', '');

        if ($ma === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Thieu ma ban'));
        }

        $datBan = $this->moHinhDatBan->layTheoMaDatBan($ma);
        $banId  = null;

        if ($datBan) {
            $banId = isset($datBan['ban_id']) ? $datBan['ban_id'] : null;
        } else {
            $ban = $this->moHinhBan->layTheoMaTruyCap($ma);
            if ($ban) {
                $banId = $ban['id'];
            }
        }

        if (!$banId) {
            $this->json(array('success' => false, 'thong_bao' => 'Mã bàn không hợp lệ'));
        }

        $danhSachMon = array();
        if ($itemsJson !== '') {
            $items = json_decode($itemsJson, true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $itemMonId = isset($item['mon_an_id']) ? intval($item['mon_an_id']) : (isset($item['id']) ? intval($item['id']) : 0);
                    $itemQty   = isset($item['so_luong']) ? intval($item['so_luong']) : (isset($item['qty']) ? intval($item['qty']) : 1);
                    $itemNote  = isset($item['ghi_chu']) ? $item['ghi_chu'] : (isset($item['note']) ? $item['note'] : '');

                    if ($itemMonId > 0) {
                        $danhSachMon[] = array(
                            'mon_an_id' => $itemMonId,
                            'so_luong'  => $itemQty,
                            'ghi_chu'   => $itemNote
                        );
                    }
                }
            }
        }

        if (empty($danhSachMon)) {
            if ($monAnId <= 0) {
                $this->json(array('success' => false, 'thong_bao' => 'Món ăn không hợp lệ'));
            }

            if ($soLuong < 1)  $soLuong = 1;
            if ($soLuong > 10) $soLuong = 10;
            $danhSachMon[] = array(
                'mon_an_id' => $monAnId,
                'so_luong'  => $soLuong,
                'ghi_chu'   => $ghiChu
            );
        }

        $id = $this->moHinhDon->datNhieuMon($banId, $danhSachMon);

        if ($id) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã gọi món thành công!', 'don_id' => $id));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi gọi món, thử lại.'));
        }
    }

    // ================= HUY MON =================
    public function huyMon()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ma    = trim(strtoupper($this->post('ma', $this->post('code', ''))));
        $donId = intval($this->post('don_id', $this->post('order_id', 0)));

        $datBan = $this->moHinhDatBan->layTheoMaDatBan($ma);
        $banId  = null;

        if ($datBan) {
            $banId = isset($datBan['ban_id']) ? $datBan['ban_id'] : null;
        } else {
            $ban = $this->moHinhBan->layTheoMaTruyCap($ma);
            if ($ban) {
                $banId = $ban['id'];
            }
        }

        if (!$banId) {
            $this->json(array('success' => false, 'thong_bao' => 'Mã bàn không hợp lệ'));
        }

        $ok = $this->moHinhDon->huyBoiKhach($donId, $banId);

        if ($ok) {
            $this->json(array('success' => true, 'thong_bao' => 'Đã hủy món'));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Không thể hủy (có thể đã đang chế biến)'));
        }
    }

    // ================= LAY DON HIEN TAI (AJAX) =================
    public function layDonHienTai()
    {
        $ma = isset($_GET['ma']) ? trim(strtoupper($_GET['ma'])) : (isset($_GET['code']) ? trim(strtoupper($_GET['code'])) : '');

        if ($ma === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Thieu ma ban'));
        }

        $datBan = $this->moHinhDatBan->layTheoMaDatBan($ma);
        $banId  = null;

        if ($datBan) {
            $banId = isset($datBan['ban_id']) ? $datBan['ban_id'] : null;
        } else {
            $ban = $this->moHinhBan->layTheoMaTruyCap($ma);
            if ($ban) {
                $banId = $ban['id'];
            }
        }

        if (!$banId) {
            $this->json(array('success' => false, 'thong_bao' => 'Mã bàn không hợp lệ'));
        }

        $danhSachDon = $this->moHinhDon->layTheoMaBan($banId);

        $ketQua = array();
        foreach ($danhSachDon as $don) {
            $ketQua[] = array(
                'id'        => $don['id'],
                'ten_mon'   => $don['ten_mon'],
                'item_name' => $don['ten_mon'],
                'so_luong'  => $don['so_luong'],
                'quantity'  => $don['so_luong'],
                'trang_thai' => $don['trang_thai'],
                'status'    => $this->mapTrangThaiDonChoJs($don['trang_thai']),
                'ghi_chu'   => $don['ghi_chu'],
                'note'      => $don['ghi_chu']
            );
        }

        $this->json(array('success' => true, 'danh_sach' => $ketQua, 'orders' => $ketQua));
    }

    private function mapTrangThaiDonChoJs($trangThai)
    {
        if ($trangThai === 'cho_phuc_vu') {
            return 'pending';
        }
        if ($trangThai === 'dang_che_bien') {
            return 'preparing';
        }
        if ($trangThai === 'da_phuc_vu') {
            return 'served';
        }
        return $trangThai;
    }

    // ================= KET THUC GOI MON =================
    public function ketThucGoiMon()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ma = trim(strtoupper($this->post('ma', '')));

        if ($ma === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Thieu ma ban'));
        }

        $datBan      = $this->moHinhDatBan->layTheoMaDatBan($ma);
        $banId       = null;
        $laDatBan    = false;

        if ($datBan) {
            $banId    = isset($datBan['ban_id']) ? $datBan['ban_id'] : null;
            $laDatBan = true;
        } else {
            $ban = $this->moHinhBan->layTheoMaTruyCap($ma);
            if ($ban) {
                $banId = $ban['id'];
            }
        }

        if (!$banId) {
            $this->json(array('success' => false, 'thong_bao' => 'Mã bàn không hợp lệ'));
        }

        if ($laDatBan) {
            $ok = $this->moHinhDatBan->capNhatTrangThai($datBan['id'], 'hoan_thanh');
        } else {
            $ok = $this->moHinhBan->capNhatTrangThai($banId, 'trong');
        }

        if ($ok) {
            $this->json(array(
                'success'      => true,
                'thong_bao'    => 'Cam on ban da su dung dich vu!',
                'chuyen_huong' => BASE_URL . '/'
            ));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi khi kết thúc gọi món'));
        }
    }

    // ================= TRANG NHAP MA GOI MON =================
    public function trangNhapMaGoiMon()
    {
        $this->view('customer/nhap-ma-goi-mon');
    }

    // ================= TRANG DAT BAN =================
    public function trangDatBan()
    {
        $danhSachBan = $this->moHinhBan->layTatCaBan();
        $this->view('home/dat-ban', array('danhSachBan' => $danhSachBan));
    }

    private function gioDatBanHopLe($gio)
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $gio)) {
            return false;
        }

        $parts = explode(':', $gio);
        $phut = intval($parts[0]) * 60 + intval($parts[1]);
        return $phut >= 10 * 60
            && $phut <= 21 * 60 + 30
            && (($phut - 10 * 60) % 30 === 0);
    }

    // ================= XU LY DAT BAN =================
    public function xuLyDatBan()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ten      = trim($this->post('ten_khach', $this->post('customer_name', '')));
        $sdt      = trim($this->post('sdt_khach', $this->post('customer_phone', '')));
        $ngay     = $this->post('ngay_dat', $this->post('reservation_date', ''));
        $gio      = $this->post('gio_dat', $this->post('reservation_time', ''));
        $nguoiLon = intval($this->post('so_nguoi_lon', $this->post('adult_count', 1)));
        $treEm    = intval($this->post('so_tre_em', $this->post('child_count', 0)));
        $banId    = intval($this->post('ban_id', 0));
        $ghiChu   = $this->post('ghi_chu', $this->post('notes', ''));

        if ($ten === '' || $sdt === '' || $ngay === '' || $gio === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui long dien day du thong tin'));
        }

        if (!$this->gioDatBanHopLe($gio)) {
            $this->json(array(
                'success'   => false,
                'thong_bao' => 'Giờ đặt bàn phải thuộc các khung 10:00, 10:30, ..., 21:30'
            ));
        }

        $soKhach = $nguoiLon + $treEm;
        if ($this->moHinhDatBan->khachBiTrungLich($sdt, $ngay, $gio, 0)) {
            $this->json(array(
                'success'   => false,
                'thong_bao' => 'Số điện thoại này đã có đặt bàn trong cùng phiên 90 phút'
            ));
        }

        $sucChuaNhaHang = defined('RESTAURANT_CAPACITY') ? RESTAURANT_CAPACITY : 40;
        $tongKhachTrungLich = $this->moHinhDatBan->tongKhachTrungLich($ngay, $gio, 0);
        if ($tongKhachTrungLich + $soKhach > $sucChuaNhaHang) {
            $conLai = $sucChuaNhaHang - $tongKhachTrungLich;
            if ($conLai < 0) {
                $conLai = 0;
            }
            $this->json(array(
                'success'   => false,
                'thong_bao' => 'Khung giờ này chỉ còn nhận tối đa ' . $conLai . ' khách trong phiên buffet 90 phút'
            ));
        }

        $danhSachBanGan = array();

        if ($banId > 0) {
            $ban = $this->moHinhBan->layTheoId($banId);
            if (!$ban || intval($ban['suc_chua']) < $soKhach || $this->moHinhDatBan->banBiTrungLich($banId, $ngay, $gio, 0)) {
                $banId = 0;
            } else {
                $danhSachBanGan[] = $ban;
            }
        }

        if ($banId <= 0) {
            $danhSachBanGan = $this->moHinhBan->timToHopBanPhuHop($ngay, $gio, $soKhach, 0);
            if (empty($danhSachBanGan)) {
                $this->json(array(
                    'success'   => false,
                    'thong_bao' => 'Không còn bàn phù hợp cho ' . $soKhach . ' khách trong khung giờ này. Vui lòng chọn khung giờ khác hoặc liên hệ nhà hàng.'
                ));
            }
            $banId = intval($danhSachBanGan[0]['id']);
        }

        $tongTien = $nguoiLon * PRICE_ADULT;

        $duLieu = array(
            'ten_khach'    => $ten,
            'sdt_khach'    => $sdt,
            'ngay_dat'     => $ngay,
            'gio_dat'      => $gio,
            'so_nguoi_lon' => $nguoiLon,
            'so_tre_em'    => $treEm,
            'ban_id'       => $banId > 0 ? $banId : null,
            'ghi_chu'      => $ghiChu,
            'tong_tien'    => $tongTien,
            'trang_thai'   => 'cho_xac_nhan'
        );

        $ketQua = $this->moHinhDatBan->them($duLieu);

        if ($ketQua && isset($ketQua['id']) && isset($ketQua['ma_dat_ban'])) {
            $banIds = array();
            $tenBan = array();
            foreach ($danhSachBanGan as $banGan) {
                $banIds[] = (int)$banGan['id'];
                $tenBan[] = $banGan['so_ban'];
            }
            $this->moHinhDatBan->capNhatNhieuBan($ketQua['id'], $banIds);

            $this->json(array(
                'success'     => true,
                'thong_bao'   => 'Đặt bàn thành công! Mã gọi món của bạn: ' . $ketQua['ma_dat_ban'],
                'dat_ban_id'  => $ketQua['id'],
                'ma_dat_ban'  => $ketQua['ma_dat_ban'],
                'ban_duoc_gan' => implode(', ', $tenBan),
                'tong_tien'   => number_format($tongTien, 0, ',', '.'),
                'huong_dan'   => 'Vui lòng lưu mã này để gọi món: ' . $ketQua['ma_dat_ban']
            ));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Lỗi hệ thống, vui lòng thử lại'));
        }
    }

    // ================= CAP NHAT THONG TIN =================
    public function capNhatThongTin()
    {
        $this->json(array('success' => false, 'thong_bao' => 'Chuc nang dang phat trien'));
    }

    // ================= DOI MAT KHAU =================
    public function doiMatKhau()
    {
        $this->json(array('success' => false, 'thong_bao' => 'Chuc nang dang phat trien'));
    }

    // ================= DANH GIA =================
    public function danhGia()
    {
        $this->json(array('success' => false, 'thong_bao' => 'Chuc nang dang phat trien'));
    }
}
