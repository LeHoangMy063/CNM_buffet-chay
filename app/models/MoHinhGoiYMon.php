<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhGoiYMon extends MoHinhCo
{
    public function ghiHanhVi($phienId, $maPhien, $monAnId, $loaiHanhVi, $giaTri)
    {
        $sql = "
            INSERT INTO hanh_vi_goi_mon
            (phien_goi_mon_id, ma_phien, mon_an_id, loai_hanh_vi, gia_tri)
            VALUES (?, ?, ?, ?, ?)
        ";

        return $this->db->query($sql, array(
            (int)$phienId,
            $maPhien,
            (int)$monAnId,
            $loaiHanhVi,
            (int)$giaTri
        ));
    }

    public function layGoiYTheoMonGoc($monGocId, $gioiHan)
    {
        $sql = "
            SELECT 
                gy.mon_goi_y_id AS id,
                ma.ten,
                ma.mo_ta,
                ma.danh_muc,
                ma.anh_url,
                ma.gia,
                gy.diem_batch,
                gy.diem_di_cung,
                gy.diem_pho_bien,
                gy.diem_danh_gia
            FROM goi_y_mon_batch gy
            JOIN mon_an ma ON ma.id = gy.mon_goi_y_id
            WHERE ma.con_mon = 1
              AND gy.mon_goc_id = ?
            ORDER BY gy.diem_batch DESC
            LIMIT ?
        ";

        return $this->db->query($sql, array((int)$monGocId, (int)$gioiHan));
    }

    public function layGoiYPhoBien($gioiHan)
    {
        $sql = "
            SELECT 
                ma.id,
                ma.ten,
                ma.mo_ta,
                ma.danh_muc,
                ma.anh_url,
                ma.gia,
                gy.diem_batch,
                gy.diem_di_cung,
                gy.diem_pho_bien,
                gy.diem_danh_gia
            FROM goi_y_mon_batch gy
            JOIN mon_an ma ON ma.id = gy.mon_goi_y_id
            WHERE ma.con_mon = 1
              AND gy.mon_goc_id IS NULL
            ORDER BY gy.diem_batch DESC
            LIMIT ?
        ";

        return $this->db->query($sql, array((int)$gioiHan));
    }

    public function layMonDaGoiTheoMaPhien($maPhien)
    {
        $sql = "
            SELECT DISTINCT ct.mon_an_id
            FROM phien_goi_mon p
            JOIN don_mon d ON d.phien_goi_mon_id = p.id
            JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
            WHERE p.ma_phien = ?
              AND d.trang_thai <> 'da_huy'
              AND ct.trang_thai <> 'da_huy'
        ";

        return $this->db->query($sql, array($maPhien));
    }

    public function layHanhViGanNhat($maPhien)
    {
        $sql = "
            SELECT 
                hv.mon_an_id,
                SUM(hv.gia_tri) AS diem_realtime
            FROM hanh_vi_goi_mon hv
            WHERE hv.ma_phien = ?
              AND hv.thoi_gian >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
            GROUP BY hv.mon_an_id
            ORDER BY diem_realtime DESC
        ";

        return $this->db->query($sql, array($maPhien));
    }

    public function capNhatBatch()
    {
        $this->db->query("DELETE FROM goi_y_mon_batch");

        /*
         * 1. Gợi ý phổ biến chung
         * mon_goc_id = NULL nghĩa là gợi ý cho khách mới.
         */
        $this->db->query("
            INSERT INTO goi_y_mon_batch
            (mon_goc_id, mon_goi_y_id, diem_pho_bien, diem_di_cung, diem_danh_gia, diem_batch)
            SELECT 
                NULL AS mon_goc_id,
                ma.id AS mon_goi_y_id,
                COUNT(ct.id) AS diem_pho_bien,
                0 AS diem_di_cung,
                COALESCE(AVG(dg.so_sao), 0) AS diem_danh_gia,
                COUNT(ct.id) * 0.7 + COALESCE(AVG(dg.so_sao), 0) * 0.3 AS diem_batch
            FROM mon_an ma
            LEFT JOIN chitiet_donmon ct ON ct.mon_an_id = ma.id
            LEFT JOIN danh_gia dg ON dg.mon_an_id = ma.id
            WHERE ma.con_mon = 1
            GROUP BY ma.id
            ORDER BY diem_batch DESC
        ");

        /*
         * 2. Gợi ý món thường đi chung.
         */
        $this->db->query("
            INSERT INTO goi_y_mon_batch
            (mon_goc_id, mon_goi_y_id, diem_pho_bien, diem_di_cung, diem_danh_gia, diem_batch)
            SELECT 
                ct1.mon_an_id AS mon_goc_id,
                ct2.mon_an_id AS mon_goi_y_id,
                COUNT(ct2.id) AS diem_pho_bien,
                COUNT(*) AS diem_di_cung,
                COALESCE(AVG(dg.so_sao), 0) AS diem_danh_gia,
                COUNT(*) * 0.6 + COUNT(ct2.id) * 0.2 + COALESCE(AVG(dg.so_sao), 0) * 0.2 AS diem_batch
            FROM chitiet_donmon ct1
            JOIN chitiet_donmon ct2 
                ON ct1.don_mon_id = ct2.don_mon_id
               AND ct1.mon_an_id <> ct2.mon_an_id
            JOIN mon_an ma ON ma.id = ct2.mon_an_id
            LEFT JOIN danh_gia dg ON dg.mon_an_id = ct2.mon_an_id
            WHERE ma.con_mon = 1
            GROUP BY ct1.mon_an_id, ct2.mon_an_id
            ORDER BY diem_batch DESC
        ");

        return true;
    }
}
