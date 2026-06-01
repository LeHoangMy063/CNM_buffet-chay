<?php

require_once dirname(__FILE__) . '/BoieuKhienCo.php';
require_once dirname(__FILE__) . '/../models/MoHinhGoiYMon.php';
require_once dirname(__FILE__) . '/../models/MoHinhBan.php';

class GoiYMonController extends BoieuKhienCo
{
    protected $moHinhGoiY;
    protected $moHinhBan;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhGoiY = new MoHinhGoiYMon();
        $this->moHinhBan = new MoHinhBan();
    }

    public function ghiHanhVi()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $maPhien = trim(strtoupper($this->post('ma', '')));
        $monAnId = trim((string)$this->post('mon_an_id', ''));
        $loaiHanhVi = trim($this->post('loai_hanh_vi', 'them_mon'));
        $giaTri = (int)$this->post('gia_tri', 1);
        $phienId = trim((string)$this->post('phien_id', ''));

        if ($maPhien === '' || $monAnId === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Thiếu mã phiên'));
        }

        $loaiHanhVi = $this->normalizeHanhVi($loaiHanhVi);
        $phienId = $this->resolvePhienId($maPhien, $phienId);

        if ($phienId === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Mã phiên gọi món không hợp lệ'));
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

        $chonLauTruoc = ($monGocId === '' || $monGocId === '0');

        if (!$chonLauTruoc) {
            $ds = $this->moHinhGoiY->layGoiYTheoMonGoc($monGocId, 10);
        } else {
            $ds = $this->moHinhGoiY->layGoiYLauPhoBien(6);
            if (empty($ds)) {
                $ds = $this->moHinhGoiY->layGoiYPhoBien(6);
            }
        }

        /*
         * Nếu chưa có batch data thì trả về rỗng.
         * Lát nữa mình chạy /goi-y-mon/cap-nhat-batch là có dữ liệu.
         */
        $ketQua = array();
        $diemPhoBienCaoNhat = 0;

        foreach ($ds as $mon) {
            $id = (string)$mon['id'];

            if (!$chonLauTruoc && isset($monDaGoi[$id])) {
                continue;
            }

            $diemBatch = isset($mon['diem_batch']) ? (float)$mon['diem_batch'] : 0;
            $diemPhoBien = isset($mon['diem_pho_bien']) ? (float)$mon['diem_pho_bien'] : 0;
            if ($diemPhoBien > $diemPhoBienCaoNhat) {
                $diemPhoBienCaoNhat = $diemPhoBien;
            }
            $diemContext = $this->tinhDiemContext($mon);
            $diemCuoi = $diemBatch * 0.8 + $diemContext * 0.2;

            $mon['diem_goi_y'] = round($diemCuoi, 2);
            $mon['ly_do'] = $chonLauTruoc
                ? 'Chọn nước lẩu trước để hệ thống gợi ý món ăn kèm phù hợp'
                : $this->taoLyDoGoiY($mon);

            $ketQua[] = $mon;
        }

        for ($i = 0; $i < count($ketQua); $i++) {
            $diemPhoBien = isset($ketQua[$i]['diem_pho_bien']) ? (float)$ketQua[$i]['diem_pho_bien'] : 0;
            $diemDiCung = isset($ketQua[$i]['diem_di_cung']) ? (float)$ketQua[$i]['diem_di_cung'] : 0;
            $ketQua[$i]['xep_hang'] = $i + 1;
            $ketQua[$i]['best_seller'] = ($diemPhoBienCaoNhat > 0 && $diemPhoBien >= $diemPhoBienCaoNhat) ? 1 : 0;
            $ketQua[$i]['nhan_goi_y'] = $ketQua[$i]['best_seller']
                ? 'Best seller'
                : ($diemDiCung > 0 ? 'Gợi ý theo lẩu' : 'Phổ biến');
        }

        $this->json(array(
            'success' => true,
            'che_do' => $chonLauTruoc ? 'chon_lau' : 'goi_y_theo_mon',
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

    private function normalizeHanhVi($loaiHanhVi)
    {
        $loaiHanhVi = trim($loaiHanhVi);
        $allowed = array('them_mon', 'goi_mon', 'huy_mon');
        if (in_array($loaiHanhVi, $allowed, true)) {
            return $loaiHanhVi;
        }

        switch ($loaiHanhVi) {
            case 'view_item':
                return 'them_mon';
            case 'add_to_order':
                return 'them_mon';
            case 'submit_order':
                return 'goi_mon';
            case 'cancel_order':
                return 'huy_mon';
            default:
                return 'them_mon';
        }
    }

    private function resolvePhienId($maPhien, $phienId)
    {
        if ($phienId !== '') {
            return $phienId;
        }

        $maPhien = trim(strtoupper($maPhien));
        if ($maPhien === '') {
            return '';
        }

        // Nếu maPhien thực tế là id_phien_goi_mon, trả về luôn.
        $rows = $this->db->query(
            "SELECT id_phien_goi_mon FROM phien_goi_mon WHERE id_phien_goi_mon = ? LIMIT 1",
            array($maPhien)
        );
        if (!empty($rows) && !empty($rows[0]['id_phien_goi_mon'])) {
            return $rows[0]['id_phien_goi_mon'];
        }

        $ban = $this->moHinhBan->layTheoMaPhienGoiMon($maPhien);
        if ($ban && !empty($ban['phien_goi_mon_id'])) {
            return $ban['phien_goi_mon_id'];
        }

        $ban = $this->moHinhBan->layTheoMaTruyCap($maPhien);
        if ($ban && !empty($ban['phien_goi_mon_id'])) {
            return $ban['phien_goi_mon_id'];
        }

        return '';
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

        if ($diemDiCung > 0) {
            return 'Phù hợp với nước lẩu bạn đang chọn';
        }

        return 'Được nhiều khách lựa chọn';
    }
}
