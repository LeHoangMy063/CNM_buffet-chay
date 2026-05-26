<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhMonAn extends MoHinhCo
{
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

    private function chuanHoaDanhMuc($danh_muc)
    {
        $danh_muc = trim((string)$danh_muc);
        $key = $this->chuThuongKhongDau($danh_muc);

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

        return $danh_muc;
    }

    private function tenHienThiDanhMuc($danh_muc)
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

        $maDanhMuc = $this->chuanHoaDanhMuc($danh_muc);
        return isset($labels[$maDanhMuc]) ? $labels[$maDanhMuc] : $maDanhMuc;
    }

    private function ganTenHienThiDanhMuc($rows)
    {
        for ($i = 0; $i < count($rows); $i++) {
            if (isset($rows[$i]['danh_muc'])) {
                $rows[$i]['danh_muc'] = $this->tenHienThiDanhMuc($rows[$i]['danh_muc']);
            }
        }
        return $rows;
    }

    public function layTatCa()
    {
        $sql = "SELECT * FROM mon_an ORDER BY danh_muc, ten";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql));
    }

    public function layNhomTheoDanhMuc()
    {
        $sql  = "SELECT * FROM mon_an WHERE con_mon = 1 ORDER BY danh_muc, ten";
        $rows = $this->ganTenHienThiDanhMuc($this->db->query($sql));

        $nhom = array();
        foreach ($rows as $mon) {
            $dm = $mon['danh_muc'];
            if (!isset($nhom[$dm])) {
                $nhom[$dm] = array();
            }
            $nhom[$dm][] = $mon;
        }
        return $nhom;
    }

    public function demTatCa()
    {
        $sql  = "SELECT COUNT(*) AS tong FROM mon_an";
        $rows = $this->db->query($sql);
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function luu($du_lieu)
    {
        $id       = isset($du_lieu['id'])       ? (int)$du_lieu['id']        : 0;
        $ten      = isset($du_lieu['ten'])       ? trim($du_lieu['ten'])      : '';
        $mo_ta    = isset($du_lieu['mo_ta'])     ? trim($du_lieu['mo_ta'])    : '';
        $danh_muc = isset($du_lieu['danh_muc'])  ? $this->chuanHoaDanhMuc($du_lieu['danh_muc']) : '';
        $anh_url  = isset($du_lieu['anh_url'])   ? trim($du_lieu['anh_url'])  : '';
        $con_mon  = isset($du_lieu['con_mon'])   ? (int)$du_lieu['con_mon']   : 1;
        $noi_bat  = isset($du_lieu['noi_bat'])   ? (int)$du_lieu['noi_bat']   : 0;

        if ($id > 0) {
            $sql = "
            UPDATE mon_an
            SET ten=?, mo_ta=?, danh_muc=?, anh_url=?, con_mon=?, noi_bat=?
            WHERE id=?
            ";
            return $this->db->query($sql, array(
                $ten,
                $mo_ta,
                $danh_muc,
                $anh_url,
                $con_mon,
                $noi_bat,
                $id
            ));
        } else {
            $sql = "
            INSERT INTO mon_an (ten, mo_ta, danh_muc, anh_url, con_mon, noi_bat)
            VALUES (?, ?, ?, ?, ?, ?)
            ";
            return $this->db->query($sql, array(
                $ten,
                $mo_ta,
                $danh_muc,
                $anh_url,
                $con_mon,
                $noi_bat
            ));
        }
    }

    public function layNoiBat($gioi_han)
    {
        $sql = "
        SELECT * FROM mon_an
        WHERE con_mon = 1
        ORDER BY noi_bat DESC, ten ASC
        LIMIT ?
        ";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql, array((int)$gioi_han)));
    }

    public function xoa($id)
    {
        $sql = "DELETE FROM mon_an WHERE id = ?";
        return $this->db->query($sql, array((int)$id));
    }

    public function timKiem($tu_khoa)
    {
        $sql = "
        SELECT * FROM mon_an
        WHERE con_mon = 1 AND ten LIKE ?
        ORDER BY danh_muc, ten
        ";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql, array('%' . $tu_khoa . '%')));
    }
}

// ================= MO HINH DAT BAN =================
