<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhDonMon extends MoHinhCo
{
    private function taoIdDon()
    {
        return $this->taoId('DMON', 5, true);
    }

    private function taoIdChiTiet()
    {
        return $this->taoId('CTDM', 5, true);
    }

    private function taoIdLichSuTrangThaiMon()
    {
        return $this->taoId('LSTTM', 5, true);
    }

    private function ghiLichSuTrangThaiMon($chiTietId, $trangThai, $ghiChu = '')
    {
        $map = array(
            'cho_phuc_vu' => 'CHO_CHE_BIEN',
            'dang_che_bien' => 'DANG_CHE_BIEN',
            'da_phuc_vu' => 'DA_PHUC_VU',
            'da_huy' => 'DA_HUY'
        );
        $trangThaiLichSu = isset($map[$trangThai]) ? $map[$trangThai] : $trangThai;
        return $this->db->query("
            INSERT INTO lich_su_trang_thai_mon
                (id_lich_su_trang_thai_mon, id_chitiet_donmon, trang_thai, ghi_chu)
            VALUES (?, ?, ?, ?)
        ", array($this->taoIdLichSuTrangThaiMon(), $chiTietId, $trangThaiLichSu, $ghiChu));
    }

    private function selectDonTomTat()
    {
        return "
        SELECT d.id_don_mon AS id,
               d.id_ban AS ban_id,
               d.id_phien_goi_mon AS phien_goi_mon_id,
               d.trang_thai,
               d.ngay_tao,
               b.so_ban,
               COUNT(ct.id_chitiet_donmon) AS so_mon,
               GROUP_CONCAT(CONCAT(ct.so_luong, 'x ', m.ten_mon) ORDER BY ct.id_chitiet_donmon SEPARATOR ', ') AS ten_mon,
               GROUP_CONCAT(m.ten_mon ORDER BY ct.id_chitiet_donmon SEPARATOR ', ') AS item_name,
               GROUP_CONCAT(dm.ten_danh_muc ORDER BY ct.id_chitiet_donmon SEPARATOR ', ') AS category,
               GROUP_CONCAT(ct.ghi_chu ORDER BY ct.id_chitiet_donmon SEPARATOR '; ') AS note
        FROM don_mon d
        LEFT JOIN ban b ON b.id_ban = d.id_ban
        LEFT JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
        LEFT JOIN mon_an m ON m.id_mon_an = ct.id_mon_an
        LEFT JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = m.id_danh_muc_mon
        ";
    }

    public function layDonTheoBan($ban_id)
    {
        $sql = $this->selectDonTomTat() . "
        WHERE d.id_ban = ?
        GROUP BY d.id_don_mon
        ORDER BY d.ngay_tao ASC, d.id_don_mon ASC
        ";
        return $this->db->query($sql, array($ban_id));
    }

    public function layTheoMaBan($ban_id, $phien_goi_mon_id = '')
    {
        $params = array($ban_id);
        $wherePhien = '';
        if ($phien_goi_mon_id !== '' && $phien_goi_mon_id !== 0 && $phien_goi_mon_id !== '0') {
            $wherePhien = ' AND d.id_phien_goi_mon = ?';
            $params[] = $phien_goi_mon_id;
        }

        $sql = "
        SELECT ct.id_chitiet_donmon AS id,
               ct.id_don_mon AS don_mon_id,
               ct.so_luong,
               ct.trang_thai_hien_tai AS trang_thai,
               ct.ghi_chu,
               m.ten_mon AS ten,
               m.anh_url,
               dm.ten_danh_muc AS danh_muc
        FROM don_mon d
        JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
        JOIN mon_an m ON m.id_mon_an = ct.id_mon_an
        LEFT JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = m.id_danh_muc_mon
        WHERE d.id_ban = ?
        $wherePhien
        ORDER BY d.ngay_tao ASC, ct.id_chitiet_donmon ASC
        ";
        return $this->db->query($sql, $params);
    }

    public function layTatCa()
    {
        return $this->db->query($this->selectDonTomTat() . "
        GROUP BY d.id_don_mon
        ORDER BY d.ngay_tao DESC, d.id_don_mon DESC
        ");
    }

    public function demHomNay()
    {
        $rows = $this->db->query("SELECT COUNT(*) AS tong FROM don_mon WHERE DATE(ngay_tao) = CURDATE()");
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function layDauVetDonMon()
    {
        $rows = $this->db->query("
        SELECT
            COUNT(DISTINCT CASE WHEN d.trang_thai = 'cho_phuc_vu' THEN d.id_don_mon END) AS don_cho,
            COUNT(CASE WHEN ct.trang_thai_hien_tai = 'cho_phuc_vu' THEN ct.id_chitiet_donmon END) AS mon_cho,
            COALESCE(MAX(d.id_don_mon), '') AS don_cuoi,
            COALESCE(MAX(ct.id_chitiet_donmon), '') AS chi_tiet_cuoi,
            COALESCE(MAX(d.ngay_tao), '') AS tao_cuoi
        FROM don_mon d
        LEFT JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
        ");
        $row = !empty($rows) ? $rows[0] : array();
        $row['version'] = implode('|', array(
            isset($row['don_cho']) ? $row['don_cho'] : 0,
            isset($row['mon_cho']) ? $row['mon_cho'] : 0,
            isset($row['don_cuoi']) ? $row['don_cuoi'] : '',
            isset($row['chi_tiet_cuoi']) ? $row['chi_tiet_cuoi'] : '',
            isset($row['tao_cuoi']) ? $row['tao_cuoi'] : ''
        ));
        return $row;
    }

    public function layGanDay($gioi_han)
    {
        return $this->db->query($this->selectDonTomTat() . "
        GROUP BY d.id_don_mon
        ORDER BY d.ngay_tao DESC, d.id_don_mon DESC
        LIMIT " . (int)$gioi_han);
    }

    public function datMon($ban_id, $mon_an_id, $so_luong, $ghi_chu)
    {
        return $this->datNhieuMon($ban_id, array(array(
            'mon_an_id' => $mon_an_id,
            'so_luong'  => $so_luong,
            'ghi_chu'   => $ghi_chu
        )));
    }

    public function datNhieuMon($ban_id, $danh_sach_mon, $phien_goi_mon_id = '')
    {
        $donId = $this->taoIdDon();
        $phienId = ($phien_goi_mon_id !== '' && $phien_goi_mon_id !== 0 && $phien_goi_mon_id !== '0') ? $phien_goi_mon_id : null;

        $ok = $this->db->query("
            INSERT INTO don_mon (id_don_mon, id_ban, id_phien_goi_mon, trang_thai)
            VALUES (?, ?, ?, 'cho_phuc_vu')
        ", array($donId, $ban_id, $phienId));
        if (!$ok) {
            return false;
        }

        foreach ($danh_sach_mon as $mon) {
            $monAnId = isset($mon['mon_an_id']) ? trim((string)$mon['mon_an_id']) : '';
            if ($monAnId === '') {
                continue;
            }
            $soLuong = isset($mon['so_luong']) ? (int)$mon['so_luong'] : 1;
            if ($soLuong <= 0) {
                $soLuong = 1;
            }
            $ghiChu = isset($mon['ghi_chu']) ? trim($mon['ghi_chu']) : '';
            $chiTietId = $this->taoIdChiTiet();
            $this->db->query("
                INSERT INTO chitiet_donmon
                    (id_chitiet_donmon, id_don_mon, id_mon_an, so_luong, ghi_chu, trang_thai_hien_tai)
                VALUES (?, ?, ?, ?, ?, 'cho_phuc_vu')
            ", array($chiTietId, $donId, $monAnId, $soLuong, $ghiChu));
            $this->ghiLichSuTrangThaiMon($chiTietId, 'cho_phuc_vu', 'Khach goi mon');
        }

        return $donId;
    }

    public function huyBoiKhach($chi_tiet_id, $ban_id, $phien_goi_mon_id = '')
    {
        $params = array($chi_tiet_id, $ban_id);
        $wherePhien = '';
        if ($phien_goi_mon_id !== '' && $phien_goi_mon_id !== 0 && $phien_goi_mon_id !== '0') {
            $wherePhien = ' AND d.id_phien_goi_mon = ?';
            $params[] = $phien_goi_mon_id;
        }

        $sql = "
        UPDATE chitiet_donmon ct
        JOIN don_mon d ON d.id_don_mon = ct.id_don_mon
        SET ct.trang_thai_hien_tai = 'da_huy',
            ct.updated_at = NOW()
        WHERE ct.id_chitiet_donmon = ? AND d.id_ban = ? AND ct.trang_thai_hien_tai = 'cho_phuc_vu'
        $wherePhien
        ";
        $ok = $this->db->query($sql, $params);
        if ($ok) {
            $this->ghiLichSuTrangThaiMon($chi_tiet_id, 'da_huy', 'Khach huy mon');
            $this->dongBoTrangThaiDonTheoChiTiet($chi_tiet_id);
        }
        return $ok;
    }

    public function capNhatTrangThai($id, $trang_thai)
    {
        $ok = $this->db->query(
            "UPDATE don_mon SET trang_thai = ? WHERE id_don_mon = ?",
            array($trang_thai, $id)
        );
        if ($ok) {
            $this->db->query(
                "UPDATE chitiet_donmon SET trang_thai_hien_tai = ?, updated_at = NOW() WHERE id_don_mon = ?",
                array($trang_thai, $id)
            );
            $rows = $this->db->query("SELECT id_chitiet_donmon FROM chitiet_donmon WHERE id_don_mon = ?", array($id));
            foreach ($rows as $row) {
                $this->ghiLichSuTrangThaiMon($row['id_chitiet_donmon'], $trang_thai, 'Nhan vien cap nhat don');
            }
        }
        return $ok;
    }

    public function capNhatTatCaTheoBan($ban_id, $trang_thai)
    {
        $rows = $this->db->query(
            "SELECT id_don_mon AS id FROM don_mon WHERE id_ban = ? AND trang_thai = 'cho_phuc_vu'",
            array($ban_id)
        );
        foreach ($rows as $row) {
            $this->capNhatTrangThai($row['id'], $trang_thai);
        }
        return true;
    }

    private function dongBoTrangThaiDonTheoChiTiet($chi_tiet_id)
    {
        $rows = $this->db->query(
            "SELECT id_don_mon AS don_mon_id FROM chitiet_donmon WHERE id_chitiet_donmon = ? LIMIT 1",
            array($chi_tiet_id)
        );
        if (empty($rows)) {
            return;
        }
        $donId = $rows[0]['don_mon_id'];
        $conCho = $this->db->query(
            "SELECT COUNT(*) AS tong FROM chitiet_donmon
             WHERE id_don_mon = ? AND trang_thai_hien_tai <> 'da_huy'",
            array($donId)
        );
        if (!empty($conCho) && (int)$conCho[0]['tong'] === 0) {
            $this->db->query("UPDATE don_mon SET trang_thai = 'da_huy' WHERE id_don_mon = ?", array($donId));
        }
    }

    public function thongKeDoanThu($tu_ngay, $den_ngay)
    {
        return $this->chiTietDoanhThu($tu_ngay, $den_ngay);
    }

    public function tongQuanDoanhThu($tu_ngay, $den_ngay)
    {
        $rows = $this->db->query("
        SELECT COUNT(*) AS so_phien,
               COALESCE(SUM(p.so_nguoi_lon + p.so_tre_em), 0) AS tong_khach,
               COALESCE(SUM(h.thanh_tien), 0) AS doanh_thu
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        WHERE DATE(COALESCE(t.updated_at, p.gio_ket_thuc, h.created_at)) BETWEEN ? AND ?
          AND h.thanh_tien > 0
        ", array($tu_ngay, $den_ngay));
        return !empty($rows) ? $rows[0] : array('so_phien' => 0, 'tong_khach' => 0, 'doanh_thu' => 0);
    }

    public function chiTietDoanhThu($tu_ngay, $den_ngay)
    {
        return $this->db->query("
        SELECT DATE(COALESCE(t.updated_at, p.gio_ket_thuc, h.created_at)) AS ngay,
               COUNT(*) AS so_phien,
               COALESCE(SUM(p.so_nguoi_lon + p.so_tre_em), 0) AS so_khach,
               COALESCE(SUM(h.thanh_tien), 0) AS doanh_thu
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        WHERE DATE(COALESCE(t.updated_at, p.gio_ket_thuc, h.created_at)) BETWEEN ? AND ?
          AND h.thanh_tien > 0
        GROUP BY DATE(COALESCE(t.updated_at, p.gio_ket_thuc, h.created_at))
        ORDER BY ngay ASC
        ", array($tu_ngay, $den_ngay));
    }

    public function topMonBanChay($gioi_han)
    {
        return $this->topMonBanChayTrongKhoang($gioi_han, '1970-01-01', date('Y-m-d'));
    }

    public function topMonBanChayTrongKhoang($gioi_han, $tu_ngay, $den_ngay)
    {
        return $this->db->query("
        SELECT m.id_mon_an AS id, m.ten_mon AS ten, dm.ten_danh_muc AS danh_muc,
               SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id_don_mon = ct.id_don_mon
        JOIN mon_an m ON m.id_mon_an = ct.id_mon_an
        LEFT JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = m.id_danh_muc_mon
        WHERE DATE(d.ngay_tao) BETWEEN ? AND ?
          AND ct.trang_thai_hien_tai <> 'da_huy'
        GROUP BY m.id_mon_an, m.ten_mon, dm.ten_danh_muc
        ORDER BY tong_ban DESC
        LIMIT " . (int)$gioi_han, array($tu_ngay, $den_ngay));
    }

    public function thongKeDanhMucTrongKhoang($tu_ngay, $den_ngay)
    {
        return $this->db->query("
        SELECT dm.ten_danh_muc AS danh_muc, SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id_don_mon = ct.id_don_mon
        JOIN mon_an m ON m.id_mon_an = ct.id_mon_an
        LEFT JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = m.id_danh_muc_mon
        WHERE DATE(d.ngay_tao) BETWEEN ? AND ?
          AND ct.trang_thai_hien_tai <> 'da_huy'
        GROUP BY dm.ten_danh_muc
        ORDER BY tong_ban DESC
        ", array($tu_ngay, $den_ngay));
    }

    public function thongKeDonTheoGio($tu_ngay, $den_ngay)
    {
        return $this->db->query("
        SELECT HOUR(d.ngay_tao) AS gio,
               COUNT(DISTINCT d.id_don_mon) AS so_don,
               COUNT(ct.id_chitiet_donmon) AS so_mon
        FROM don_mon d
        LEFT JOIN chitiet_donmon ct ON ct.id_don_mon = d.id_don_mon
        WHERE DATE(d.ngay_tao) BETWEEN ? AND ?
        GROUP BY HOUR(d.ngay_tao)
        ORDER BY gio ASC
        ", array($tu_ngay, $den_ngay));
    }

    public function monItBanTrongKhoang($gioi_han, $tu_ngay, $den_ngay)
    {
        return $this->db->query("
        SELECT m.id_mon_an AS id, m.ten_mon AS ten, dm.ten_danh_muc AS danh_muc,
               COALESCE(SUM(CASE WHEN ct.trang_thai_hien_tai <> 'da_huy' THEN ct.so_luong ELSE 0 END), 0) AS tong_ban
        FROM mon_an m
        LEFT JOIN danh_muc_mon dm ON dm.id_danh_muc_mon = m.id_danh_muc_mon
        LEFT JOIN chitiet_donmon ct ON ct.id_mon_an = m.id_mon_an
        LEFT JOIN don_mon d ON d.id_don_mon = ct.id_don_mon
          AND DATE(d.ngay_tao) BETWEEN ? AND ?
        GROUP BY m.id_mon_an, m.ten_mon, dm.ten_danh_muc
        ORDER BY tong_ban ASC, m.ten_mon ASC
        LIMIT " . (int)$gioi_han, array($tu_ngay, $den_ngay));
    }
}
