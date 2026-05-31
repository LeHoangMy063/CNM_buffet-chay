<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhDanhGia extends MoHinhCo
{
    public function layTheoMonAn($mon_an_id)
    {
        $sql = "
        SELECT g.id_danh_gia AS id, g.id_khach_tai_khoan AS tai_khoan_id, g.id_mon_an AS mon_an_id,
               g.so_sao, g.binh_luan, g.ngay_tao, t.ho_ten AS ten_nguoi_danh_gia
        FROM danh_gia g
        JOIN khach_tai_khoan t ON g.id_khach_tai_khoan = t.id_khach_tai_khoan
        WHERE g.id_mon_an = ?
        ORDER BY g.ngay_tao DESC
        ";
        return $this->db->query($sql, array($mon_an_id));
    }

    public function them($tai_khoan_id, $mon_an_id, $so_sao, $binh_luan)
    {
        $sql = "
        INSERT INTO danh_gia (id_danh_gia, id_khach_tai_khoan, id_mon_an, so_sao, binh_luan)
        VALUES (?, ?, ?, ?, ?)
        ";
        $ok = $this->db->query($sql, array(
            $this->taoId('DG', 5, true),
            $tai_khoan_id,
            $mon_an_id,
            (int)$so_sao,
            $binh_luan
        ));
        return $ok ? true : false;
    }

    public function trungBinhSao($mon_an_id)
    {
        $sql  = "SELECT AVG(so_sao) AS trung_binh FROM danh_gia WHERE id_mon_an = ?";
        $rows = $this->db->query($sql, array($mon_an_id));
        return (!empty($rows) && $rows[0]['trung_binh'])
            ? round((float)$rows[0]['trung_binh'], 1)
            : 0;
    }
}
