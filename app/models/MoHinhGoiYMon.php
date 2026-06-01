<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhGoiYMon extends MoHinhCo
{
    public function ghiHanhVi($phienId, $maPhien, $monAnId, $loaiHanhVi, $giaTri)
    {
        $idPhien = $phienId !== '' ? $phienId : $maPhien;
        if ($idPhien === '' || $monAnId === '') {
            return false;
        }
        return $this->db->query("
            INSERT INTO hanh_vi_goi_mon
            (id_hanh_vi_goi_mon, id_phien_goi_mon, id_mon_an, loai_hanh_vi, gia_tri)
            VALUES (?, ?, ?, ?, ?)
        ", array($this->taoId('HV', 5, true), $idPhien, $monAnId, $loaiHanhVi, (int)$giaTri));
    }

    private function selectMon()
    {
        return "
            SELECT
                ma.id_mon_an AS id,
                ma.ten_mon AS ten,
                ma.mo_ta,
                dm.id_danh_muc_mon AS id_danh_muc,
                dm.ten_danh_muc AS danh_muc,
                ma.anh_url
            FROM mon_an ma
            LEFT JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = ma.id_danh_muc_mon
        ";
    }

    public function layGoiYTheoMonGoc($monGocId, $gioiHan)
    {
        return $this->db->query("
            SELECT x.*, gy.diem_batch, gy.diem_di_cung, gy.diem_pho_bien
            FROM goi_y_mon_batch gy
            JOIN (" . $this->selectMon() . ") x ON x.id = gy.id_mon_goi_y
            WHERE gy.id_mon_goc = ?
            ORDER BY gy.diem_batch DESC
            LIMIT " . (int)$gioiHan, array($monGocId));
    }

    public function layGoiYPhoBien($gioiHan)
    {
        return $this->db->query("
            SELECT x.*, gy.diem_batch, gy.diem_di_cung, gy.diem_pho_bien
            FROM goi_y_mon_batch gy
            JOIN (" . $this->selectMon() . ") x ON x.id = gy.id_mon_goi_y
            WHERE gy.id_mon_goc IS NULL
            ORDER BY gy.diem_batch DESC
            LIMIT " . (int)$gioiHan);
    }

    public function layGoiYLauPhoBien($gioiHan)
    {
        return $this->db->query("
            SELECT
                ma.id_mon_an AS id,
                ma.ten_mon AS ten,
                ma.mo_ta,
                dm.id_danh_muc_mon AS id_danh_muc,
                dm.ten_danh_muc AS danh_muc,
                ma.anh_url,
                COALESCE(gy.diem_batch, 0) AS diem_batch,
                COALESCE(gy.diem_di_cung, 0) AS diem_di_cung,
                COALESCE(gy.diem_pho_bien, 0) AS diem_pho_bien
            FROM mon_an ma
            JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = ma.id_danh_muc_mon
            LEFT JOIN goi_y_mon_batch gy
              ON gy.id_mon_goc IS NULL
             AND gy.id_mon_goi_y = ma.id_mon_an
            WHERE ma.con_mon = 1
              AND dm.id_danh_muc_mon = 'DM-NUOCLAU'
            ORDER BY COALESCE(gy.diem_batch, 0) DESC, ma.thu_tu ASC, ma.ten_mon ASC
            LIMIT " . (int)$gioiHan);
    }

    public function layMonDaGoiTheoMaPhien($maPhien)
    {
        return $this->db->query("
            SELECT DISTINCT ct.id_mon_an AS mon_an_id
            FROM don_mon d
            JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
            WHERE d.id_phien_goi_mon = ?
              AND d.trang_thai <> 'da_huy'
              AND ct.trang_thai_hien_tai <> 'da_huy'
        ", array($maPhien));
    }

    public function layHanhViGanNhat($maPhien)
    {
        return $this->db->query("
            SELECT id_mon_an AS mon_an_id, SUM(gia_tri) AS diem_realtime
            FROM hanh_vi_goi_mon
            WHERE id_phien_goi_mon = ?
              AND thoi_gian >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
            GROUP BY id_mon_an
            ORDER BY diem_realtime DESC
        ", array($maPhien));
    }

    public function capNhatBatch()
    {
        $this->db->query("DELETE FROM goi_y_mon_batch");

        $this->db->query("
            INSERT INTO goi_y_mon_batch
            (id_goi_y_mon_batch, id_mon_goc, id_mon_goi_y, diem_pho_bien, diem_di_cung, diem_batch)
            SELECT
                CONCAT('GY-PB-', ma.id_mon_an) AS id_goi_y_mon_batch,
                NULL AS id_mon_goc,
                ma.id_mon_an AS id_mon_goi_y,
                COUNT(ct.id_chitiet_donmon) AS diem_pho_bien,
                0 AS diem_di_cung,
                COUNT(ct.id_chitiet_donmon) * 0.3 AS diem_batch
            FROM mon_an ma
            LEFT JOIN chitiet_donmon ct ON ct.id_mon_an = ma.id_mon_an
            WHERE ma.con_mon = 1
            GROUP BY ma.id_mon_an
        ");

        $this->db->query("
            INSERT INTO goi_y_mon_batch
            (id_goi_y_mon_batch, id_mon_goc, id_mon_goi_y, diem_pho_bien, diem_di_cung, diem_batch)
            SELECT
                CONCAT('GY-CG-', pair.id_mon_goc, '-', pair.id_mon_goi_y) AS id_goi_y_mon_batch,
                pair.id_mon_goc,
                pair.id_mon_goi_y,
                COALESCE(pop.diem_pho_bien, 0) AS diem_pho_bien,
                pair.diem_di_cung,
                pair.diem_di_cung * 0.7 + COALESCE(pop.diem_pho_bien, 0) * 0.3 AS diem_batch
            FROM (
                SELECT
                    a.id_mon_an AS id_mon_goc,
                    b.id_mon_an AS id_mon_goi_y,
                    COUNT(DISTINCT a.id_phien_goi_mon) AS diem_di_cung
                FROM (
                    SELECT DISTINCT d.id_phien_goi_mon, ct.id_mon_an
                    FROM don_mon d
                    JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
                    JOIN mon_an ma ON ma.id_mon_an = ct.id_mon_an
                    WHERE d.trang_thai <> 'da_huy'
                      AND ct.trang_thai_hien_tai <> 'da_huy'
                      AND ma.con_mon = 1
                ) a
                JOIN (
                    SELECT DISTINCT d.id_phien_goi_mon, ct.id_mon_an
                    FROM don_mon d
                    JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
                    JOIN mon_an ma ON ma.id_mon_an = ct.id_mon_an
                    WHERE d.trang_thai <> 'da_huy'
                      AND ct.trang_thai_hien_tai <> 'da_huy'
                      AND ma.con_mon = 1
                ) b ON b.id_phien_goi_mon = a.id_phien_goi_mon
                   AND b.id_mon_an <> a.id_mon_an
                GROUP BY a.id_mon_an, b.id_mon_an
            ) pair
            LEFT JOIN (
                SELECT ct.id_mon_an, COUNT(ct.id_chitiet_donmon) AS diem_pho_bien
                FROM chitiet_donmon ct
                JOIN don_mon d ON d.id_don_mon = ct.id_don_mon
                WHERE d.trang_thai <> 'da_huy'
                  AND ct.trang_thai_hien_tai <> 'da_huy'
                GROUP BY ct.id_mon_an
            ) pop ON pop.id_mon_an = pair.id_mon_goi_y
        ");

        return true;
    }
}
