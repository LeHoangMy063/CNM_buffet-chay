<?php
class MoHinhCo
{
    protected $db;

    public function __construct()
    {
        require_once dirname(__FILE__) . '/../core/CosoDuLieu.php';
        $this->db = new CosoDuLieu();
    }

    protected function taoId($tienTo, $doDai = 3, $themNgay = false)
    {
        $rows = $this->db->query(
            "SELECT gia_tri_hien_tai FROM bo_dem_ma WHERE tien_to = ? LIMIT 1",
            array($tienTo)
        );

        if (empty($rows)) {
            $this->db->query(
                "INSERT INTO bo_dem_ma (tien_to, gia_tri_hien_tai) VALUES (?, 0)",
                array($tienTo)
            );
            $hienTai = 0;
        } else {
            $hienTai = (int)$rows[0]['gia_tri_hien_tai'];
        }

        $moi = $hienTai + 1;
        $this->db->query(
            "UPDATE bo_dem_ma SET gia_tri_hien_tai = ? WHERE tien_to = ?",
            array($moi, $tienTo)
        );

        $phanSo = str_pad($moi, $doDai, '0', STR_PAD_LEFT);
        if ($themNgay) {
            return $tienTo . '-' . date('Ymd') . '-' . $phanSo;
        }
        return $tienTo . '-' . $phanSo;
    }
}
