<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhMonAn extends MoHinhCo
{
    private function chuanHoaDanhMuc($danh_muc)
    {
        $danh_muc = trim((string)$danh_muc);
        $key = function_exists('mb_strtolower')
            ? mb_strtolower($danh_muc, 'UTF-8')
            : strtolower($danh_muc);

        $map = array(
            'khai vi' => 'Khai vi',
            'khai vá»‹' => 'Khai vi',
            'mon chinh' => 'Mon chinh',
            'mÃ³n chÃ­nh' => 'Mon chinh',
            'nuoc lau' => 'Nuoc lau',
            'nÆ°á»›c láº©u' => 'Nuoc lau',
            'topping' => 'Topping',
            'rau' => 'Rau',
            'do uong' => 'Do uong',
            'Ä‘á»“ uá»‘ng' => 'Do uong',
        );

        return isset($map[$key]) ? $map[$key] : $danh_muc;
    }

    private function tenHienThiDanhMuc($danh_muc)
    {
        $labels = array(
            'Khai vi' => 'Khai vá»‹',
            'Mon chinh' => 'MÃ³n chÃ­nh',
            'Nuoc lau' => 'NÆ°á»›c láº©u',
            'Topping' => 'Topping',
            'Rau' => 'Rau',
            'Do uong' => 'Äá»“ uá»‘ng',
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
