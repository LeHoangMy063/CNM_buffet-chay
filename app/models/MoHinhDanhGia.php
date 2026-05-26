<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhDanhGia extends MoHinhCo
{
    public function layTheoMonAn($mon_an_id)
    {
        $sql = "
        SELECT g.*, t.ho_ten AS ten_nguoi_danh_gia
        FROM danh_gia g
        JOIN tai_khoan t ON g.tai_khoan_id = t.id
        WHERE g.mon_an_id = ?
        ORDER BY g.ngay_tao DESC
        ";
        return $this->db->query($sql, array((int)$mon_an_id));
    }

    public function them($tai_khoan_id, $mon_an_id, $so_sao, $binh_luan)
    {
        $sql = "
        INSERT INTO danh_gia (tai_khoan_id, mon_an_id, so_sao, binh_luan)
        VALUES (?, ?, ?, ?)
        ";
        $ok = $this->db->query($sql, array(
            (int)$tai_khoan_id,
            (int)$mon_an_id,
            (int)$so_sao,
            $binh_luan
        ));
        return $ok ? $this->db->lastInsertId() : false;
    }

    public function trungBinhSao($mon_an_id)
    {
        $sql  = "SELECT AVG(so_sao) AS trung_binh FROM danh_gia WHERE mon_an_id = ?";
        $rows = $this->db->query($sql, array((int)$mon_an_id));
        return (!empty($rows) && $rows[0]['trung_binh'])
            ? round((float)$rows[0]['trung_binh'], 1)
            : 0;
    }
}
