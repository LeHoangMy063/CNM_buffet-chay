<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';
require_once dirname(__FILE__) . '/MoHinhDanhMucMon.php';

class MoHinhMonAn extends MoHinhCo
{
    private $danhMuc;

    public function __construct()
    {
        parent::__construct();
        $this->danhMuc = new MoHinhDanhMucMon($this->db);
    }

    private function selectMonAn()
    {
        return "
        SELECT
            id_mon_an AS id,
            ten_mon AS ten,
            mon_an.mo_ta,
            danh_muc_mon.ten_danh_muc AS danh_muc,
            mon_an.anh_url,
            mon_an.con_mon,
            mon_an.noi_bat,
            mon_an.thu_tu,
            mon_an.ngay_tao
        FROM mon_an
        LEFT JOIN danh_muc_mon ON danh_muc_mon.id_danh_muc_mon = mon_an.id_danh_muc_mon
        ";
    }

    private function sapXepMacDinh()
    {
        return " ORDER BY danh_muc_mon.thu_tu ASC, mon_an.thu_tu ASC, mon_an.ten_mon ASC";
    }

    private function ganTenHienThiDanhMuc($rows)
    {
        return $this->danhMuc->ganTenHienThiChoDanhSach($rows);
    }

    public function layTatCa()
    {
        $sql = $this->selectMonAn() . $this->sapXepMacDinh();
        return $this->ganTenHienThiDanhMuc($this->db->query($sql));
    }

    public function layDangHienThi()
    {
        $sql = $this->selectMonAn() . "
        WHERE mon_an.con_mon = 1
        " . $this->sapXepMacDinh();
        return $this->ganTenHienThiDanhMuc($this->db->query($sql));
    }

    public function layNhomTheoDanhMuc()
    {
        $rows = $this->layDangHienThi();

        $nhom = array();
        foreach ($rows as $mon) {
            $tenDanhMuc = isset($mon['danh_muc']) ? $mon['danh_muc'] : '';
            if (!isset($nhom[$tenDanhMuc])) {
                $nhom[$tenDanhMuc] = array();
            }
            $nhom[$tenDanhMuc][] = $mon;
        }
        return $nhom;
    }

    public function demTatCa()
    {
        $rows = $this->db->query("SELECT COUNT(*) AS tong FROM mon_an");
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function luu($duLieu)
    {
        $id = isset($duLieu['id']) ? trim($duLieu['id']) : '';
        $ten = isset($duLieu['ten']) ? trim($duLieu['ten']) : '';
        $moTa = isset($duLieu['mo_ta']) ? trim($duLieu['mo_ta']) : '';
        $danhMuc = isset($duLieu['danh_muc']) ? $duLieu['danh_muc'] : '';
        $anhUrl = isset($duLieu['anh_url']) ? trim($duLieu['anh_url']) : '';
        $conMon = isset($duLieu['con_mon']) ? (int)$duLieu['con_mon'] : 1;
        $noiBat = isset($duLieu['noi_bat']) ? (int)$duLieu['noi_bat'] : 0;
        $idDanhMuc = $this->danhMuc->layHoacTaoId($danhMuc);

        if ($id !== '') {
            return $this->capNhat($id, $ten, $moTa, $idDanhMuc, $anhUrl, $conMon, $noiBat);
        }
        return $this->themMoi($ten, $moTa, $idDanhMuc, $anhUrl, $conMon, $noiBat);
    }

    private function capNhat($id, $ten, $moTa, $idDanhMuc, $anhUrl, $conMon, $noiBat)
    {
        $sql = "
        UPDATE mon_an
        SET ten_mon = ?, mo_ta = ?, id_danh_muc_mon = ?, anh_url = ?, con_mon = ?, noi_bat = ?
        WHERE id_mon_an = ?
        ";
        return $this->db->query($sql, array($ten, $moTa, $idDanhMuc, $anhUrl, $conMon, $noiBat, $id));
    }

    private function themMoi($ten, $moTa, $idDanhMuc, $anhUrl, $conMon, $noiBat)
    {
        $id = $this->taoId('MON');
        $sql = "
        INSERT INTO mon_an (id_mon_an, ten_mon, mo_ta, id_danh_muc_mon, anh_url, con_mon, noi_bat)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        return $this->db->query($sql, array($id, $ten, $moTa, $idDanhMuc, $anhUrl, $conMon, $noiBat));
    }

    public function layNoiBat($gioiHan)
    {
        $sql = "
        " . $this->selectMonAn() . "
        WHERE mon_an.con_mon = 1
        ORDER BY mon_an.noi_bat DESC, mon_an.ten_mon ASC
        LIMIT ?
        ";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql, array((int)$gioiHan)));
    }

    public function xoa($id)
    {
        return $this->db->query("DELETE FROM mon_an WHERE id_mon_an = ?", array($id));
    }

    public function timKiem($tuKhoa)
    {
        $sql = "
        " . $this->selectMonAn() . "
        WHERE mon_an.con_mon = 1 AND mon_an.ten_mon LIKE ?
        " . $this->sapXepMacDinh();
        return $this->ganTenHienThiDanhMuc($this->db->query($sql, array('%' . $tuKhoa . '%')));
    }
}
