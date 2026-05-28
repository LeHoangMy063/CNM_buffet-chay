<?php

class MongoKetNoi
{
    /** @var \MongoDB\Driver\Manager|null */
    private $manager = null;

    /** @var string */
    private $database = '';
    public function __construct()
    {
        if (!$this->khaDung()) {
            return;
        }

        $this->database = defined('MONGO_DB') ? MONGO_DB : 'buffet_chay_analytics';
        $uri = defined('MONGO_URI') ? MONGO_URI : 'mongodb://localhost:27017';
        $this->manager = new MongoDB\Driver\Manager($uri);
    }

    public function khaDung()
    {
        return defined('MONGO_ENABLED')
            && MONGO_ENABLED === '1'
            && extension_loaded('mongodb')
            && class_exists('MongoDB\Driver\Manager');
    }

    public function dongBoBaoCaoDoanhThu($tuNgay, $denNgay, $tongQuan, $doanhThuNgay, $topMon, $danhMuc, $theoGio)
    {
        if (!$this->khaDung() || !$this->manager) {
            return array(
                'success' => false,
                'message' => 'MongoDB chưa được bật hoặc PHP chưa có extension mongodb.'
            );
        }

        $collection = $this->database . '.bao_cao_doanh_thu';
        $doanhThuNgayTheoNgay = $this->chuyenDoanhThuNgayThanhObject($doanhThuNgay);
        $tongQuanDaTinh = $this->themChiSoTongQuan($tongQuan, $doanhThuNgayTheoNgay);
        $theoGioDaTinh = $this->themPhanTramTheoGio($theoGio);
        $topMonDaTinh = $this->doiTongBanThanhTongLuotGoi($topMon);
        $danhMucDaTinh = $this->doiTongBanThanhTongLuotGoi($danhMuc);

        $document = array(
            'ma_bao_cao' => $tuNgay . '_' . $denNgay,
            'tu_ngay' => $tuNgay,
            'den_ngay' => $denNgay,
            'tong_quan' => $this->lamSach($tongQuanDaTinh),
            'doanh_thu_ngay' => $this->lamSach($doanhThuNgayTheoNgay),
            'top_goi_mon' => $this->lamSach($topMonDaTinh),
            'danh_muc' => $this->lamSach($danhMucDaTinh),
            'theo_gio' => $this->lamSach($theoGioDaTinh),
            'cap_nhat_luc' => new MongoDB\BSON\UTCDateTime((int)(microtime(true) * 1000))
        );

        try {
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->update(
                array('ma_bao_cao' => $document['ma_bao_cao']),
                array(
                    '$set' => $document,
                    '$unset' => array('top_mon' => '')
                ),
                array('upsert' => true)
            );
            $this->manager->executeBulkWrite($collection, $bulk);
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Không đồng bộ được MongoDB: ' . $e->getMessage()
            );
        }

        return array(
            'success' => true,
            'message' => 'Đã đồng bộ báo cáo sang MongoDB.',
            'collection' => $collection
        );
    }

    public function layBaoCaoDoanhThu($tuNgay, $denNgay)
    {
        if (!$this->khaDung() || !$this->manager) {
            return null;
        }

        $collection = $this->database . '.bao_cao_doanh_thu';
        $query = new MongoDB\Driver\Query(
            array('ma_bao_cao' => $tuNgay . '_' . $denNgay),
            array('limit' => 1)
        );

        try {
            $cursor = $this->manager->executeQuery($collection, $query);
            foreach ($cursor as $document) {
                return $this->chuyenBsonThanhArray($document);
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    private function themChiSoTongQuan($tongQuan, $doanhThuNgayTheoNgay)
    {
        $tongQuan = is_array($tongQuan) ? $tongQuan : array();
        $soNgay = count($doanhThuNgayTheoNgay);
        $doanhThu = isset($tongQuan['doanh_thu']) ? (float)$tongQuan['doanh_thu'] : 0;
        $soPhien = isset($tongQuan['so_phien']) ? (float)$tongQuan['so_phien'] : 0;
        $tongKhach = isset($tongQuan['tong_khach']) ? (float)$tongQuan['tong_khach'] : 0;

        $ngayCaoDiem = null;
        $ngayThapDiem = null;
        $doanhThuCaoNhat = null;
        $doanhThuThapNhat = null;

        foreach ($doanhThuNgayTheoNgay as $ngay => $row) {
            $doanhThuNgay = isset($row['doanh_thu']) ? (float)$row['doanh_thu'] : 0;

            if ($doanhThuCaoNhat === null || $doanhThuNgay > $doanhThuCaoNhat) {
                $doanhThuCaoNhat = $doanhThuNgay;
                $ngayCaoDiem = $ngay;
            }

            if ($doanhThuThapNhat === null || $doanhThuNgay < $doanhThuThapNhat) {
                $doanhThuThapNhat = $doanhThuNgay;
                $ngayThapDiem = $ngay;
            }
        }

        $tongQuan['doanh_thu_tb_ngay'] = $soNgay > 0 ? round($doanhThu / $soNgay) : 0;
        $tongQuan['khach_tb_moi_phien'] = $soPhien > 0 ? round($tongKhach / $soPhien, 2) : 0;
        $tongQuan['doanh_thu_tb_moi_khach'] = $tongKhach > 0 ? round($doanhThu / $tongKhach) : 0;
        $tongQuan['ngay_cao_diem'] = $ngayCaoDiem;
        $tongQuan['ngay_thap_diem'] = $ngayThapDiem;

        return $tongQuan;
    }

    private function chuyenDoanhThuNgayThanhObject($doanhThuNgay)
    {
        $ketQua = array();

        if (!is_array($doanhThuNgay)) {
            return $ketQua;
        }

        foreach ($doanhThuNgay as $row) {
            if (!is_array($row) || empty($row['ngay'])) {
                continue;
            }

            $ngay = (string)$row['ngay'];
            $duLieuNgay = $row;
            unset($duLieuNgay['ngay']);
            $ketQua[$ngay] = $duLieuNgay;
        }

        ksort($ketQua);
        return $ketQua;
    }

    private function themPhanTramTheoGio($theoGio)
    {
        if (!is_array($theoGio)) {
            return array();
        }

        $tongDon = 0;
        foreach ($theoGio as $row) {
            $tongDon += isset($row['so_don']) ? (int)$row['so_don'] : 0;
        }

        $ketQua = array();
        foreach ($theoGio as $row) {
            if (!is_array($row)) {
                continue;
            }

            $soDon = isset($row['so_don']) ? (int)$row['so_don'] : 0;
            $row['phan_tram_tong'] = $tongDon > 0 ? round(($soDon / $tongDon) * 100, 1) : 0;
            $ketQua[] = $row;
        }

        return $ketQua;
    }

    private function doiTongBanThanhTongLuotGoi($rows)
    {
        if (!is_array($rows)) {
            return array();
        }

        $ketQua = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (isset($row['tong_ban']) && !isset($row['tong_luot_goi'])) {
                $row['tong_luot_goi'] = $row['tong_ban'];
            }
            unset($row['tong_ban']);
            $ketQua[] = $row;
        }

        return $ketQua;
    }

    private function chuyenBsonThanhArray($value)
    {
        if (class_exists('MongoDB\BSON\UTCDateTime') && $value instanceof MongoDB\BSON\UTCDateTime) {
            return $value->toDateTime()->format('c');
        }

        if (class_exists('MongoDB\BSON\ObjectId') && $value instanceof MongoDB\BSON\ObjectId) {
            return (string)$value;
        }

        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            $ketQua = array();
            foreach ($value as $key => $item) {
                $ketQua[$key] = $this->chuyenBsonThanhArray($item);
            }
            return $ketQua;
        }

        return $value;
    }

    private function lamSach($value)
    {
        if (is_array($value)) {
            $clean = array();
            foreach ($value as $key => $item) {
                $clean[$key] = $this->lamSach($item);
            }
            return $clean;
        }

        if (is_numeric($value)) {
            return strpos((string)$value, '.') !== false ? (float)$value : (int)$value;
        }

        return $value;
    }
}
