<?php

require_once dirname(__FILE__) . '/BoieuKhienCo.php';
require_once dirname(__FILE__) . '/../models/MoHinhGoiYMon.php';

class GoiYMonController extends BoieuKhienCo
{
    protected $moHinhGoiY;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhGoiY = new MoHinhGoiYMon();
    }

    public function ghiHanhVi()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $maPhien = trim(strtoupper($this->post('ma', '')));
        $monAnId = trim((string)$this->post('mon_an_id', ''));
        $loaiHanhVi = trim($this->post('loai_hanh_vi', 'view_item'));
        $giaTri = (int)$this->post('gia_tri', 1);
        $phienId = trim((string)$this->post('phien_id', ''));

        if ($maPhien === '' || $monAnId === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Thiếu mã phiên'));
        }

        $ok = $this->moHinhGoiY->ghiHanhVi($phienId, $maPhien, $monAnId, $loaiHanhVi, $giaTri);

        $this->json(array(
            'success' => $ok ? true : false
        ));
    }

    public function layGoiY()
    {
        $maPhien = isset($_GET['ma']) ? trim(strtoupper($_GET['ma'])) : '';
        $monGocId = isset($_GET['mon_id']) ? trim((string)$_GET['mon_id']) : '';

        $monDaGoiRows = $this->moHinhGoiY->layMonDaGoiTheoMaPhien($maPhien);
        $monDaGoi = array();

        foreach ($monDaGoiRows as $row) {
            $monDaGoi[(string)$row['mon_an_id']] = true;
        }

        if ($monGocId !== '') {
            $ds = $this->moHinhGoiY->layGoiYTheoMonGoc($monGocId, 10);
        } else {
            $ds = $this->moHinhGoiY->layGoiYPhoBien(10);
        }

        /*
         * Nếu chưa có batch data thì trả về rỗng.
         * Lát nữa mình chạy /goi-y-mon/cap-nhat-batch là có dữ liệu.
         */
        $ketQua = array();

        foreach ($ds as $mon) {
            $id = (string)$mon['id'];

            if (isset($monDaGoi[$id])) {
                continue;
            }

            $diemBatch = isset($mon['diem_batch']) ? (float)$mon['diem_batch'] : 0;
            $diemContext = $this->tinhDiemContext($mon);
            $diemCuoi = $diemBatch * 0.8 + $diemContext * 0.2;

            $mon['diem_goi_y'] = round($diemCuoi, 2);
            $mon['ly_do'] = $this->taoLyDoGoiY($mon);

            $ketQua[] = $mon;
        }

        $this->json(array(
            'success' => true,
            'data' => array_slice($ketQua, 0, 6)
        ));
    }

    public function capNhatBatch()
    {
        $ok = $this->moHinhGoiY->capNhatBatch();

        $this->json(array(
            'success' => $ok ? true : false,
            'thong_bao' => 'Đã cập nhật dữ liệu gợi ý món'
        ));
    }

    private function tinhDiemContext($mon)
    {
        $diem = 0;
        $gio = (int)date('H');
        $danhMuc = isset($mon['danh_muc']) ? $mon['danh_muc'] : '';

        if ($gio >= 10 && $gio <= 14) {
            if ($danhMuc === 'Mon chinh' || $danhMuc === 'Món chính' || $danhMuc === 'Do uong' || $danhMuc === 'Đồ uống') {
                $diem += 2;
            }
        }

        if ($gio >= 18 && $gio <= 22) {
            if ($danhMuc === 'Nuoc lau' || $danhMuc === 'Nước lẩu' || $danhMuc === 'Topping' || $danhMuc === 'Rau') {
                $diem += 2;
            }
        }

        return $diem;
    }

    private function taoLyDoGoiY($mon)
    {
        $diemDiCung = isset($mon['diem_di_cung']) ? (float)$mon['diem_di_cung'] : 0;
        $diemDanhGia = isset($mon['diem_danh_gia']) ? (float)$mon['diem_danh_gia'] : 0;

        if ($diemDiCung > 0) {
            return 'Thường được gọi cùng với món bạn đang chọn';
        }

        if ($diemDanhGia >= 4) {
            return 'Được khách đánh giá tốt';
        }

        return 'Được nhiều khách lựa chọn';
    }
}
