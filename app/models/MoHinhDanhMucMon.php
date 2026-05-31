<?php

class MoHinhDanhMucMon
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function chuThuongKhongDau($value)
    {
        $value = trim((string)$value);
        $value = function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);

        $from = array(
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        );
        $to = array(
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        );

        return str_replace($from, $to, $value);
    }

    public function chuanHoaTen($danhMuc)
    {
        $danhMuc = trim((string)$danhMuc);
        $key = $this->chuThuongKhongDau($danhMuc);

        if (strpos($key, 'khai') !== false || strpos($key, 'vi') !== false) {
            return 'Khai vi';
        }
        if (strpos($key, 'chinh') !== false || strpos($key, 'mon chinh') !== false) {
            return 'Mon chinh';
        }
        if (strpos($key, 'lau') !== false || strpos($key, 'nuoc') !== false) {
            return 'Nuoc lau';
        }
        if (strpos($key, 'uong') !== false || strpos($key, 'do uong') !== false) {
            return 'Do uong';
        }
        if (strpos($key, 'topping') !== false) {
            return 'Topping';
        }
        if (strpos($key, 'rau') !== false) {
            return 'Rau';
        }
        if (strpos($key, 'trang') !== false) {
            return 'Trang mieng';
        }

        return $danhMuc;
    }

    public function tenHienThi($danhMuc)
    {
        $labels = array(
            'Khai vi' => 'Khai vị',
            'Mon chinh' => 'Món chính',
            'Nuoc lau' => 'Nước lẩu',
            'Topping' => 'Topping',
            'Rau' => 'Rau',
            'Do uong' => 'Đồ uống',
            'Trang mieng' => 'Tráng miệng',
        );

        $maDanhMuc = $this->chuanHoaTen($danhMuc);
        return isset($labels[$maDanhMuc]) ? $labels[$maDanhMuc] : $maDanhMuc;
    }

    public function ganTenHienThiChoDanhSach($rows)
    {
        for ($i = 0; $i < count($rows); $i++) {
            if (isset($rows[$i]['danh_muc'])) {
                $rows[$i]['danh_muc'] = $this->tenHienThi($rows[$i]['danh_muc']);
            }
        }
        return $rows;
    }

    public function layHoacTaoId($danhMuc)
    {
        $ten = $this->chuanHoaTen($danhMuc);
        $map = array(
            'Khai vi' => 'DM-KHAIVI',
            'Mon chinh' => 'DM-MONCHINH',
            'Nuoc lau' => 'DM-NUOCLAU',
            'Topping' => 'DM-TOPPING',
            'Rau' => 'DM-RAU',
            'Do uong' => 'DM-DOUONG',
            'Trang mieng' => 'DM-TRANGMIENG'
        );

        if (isset($map[$ten])) {
            return $map[$ten];
        }

        $id = 'DM-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $ten));
        $this->db->query(
            "INSERT IGNORE INTO danh_muc_mon (id_danh_muc_mon, ten_danh_muc, thu_tu, dang_hien_thi)
             VALUES (?, ?, 99, 1)",
            array($id, $ten)
        );
        return $id;
    }
}
