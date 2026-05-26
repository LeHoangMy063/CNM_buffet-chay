<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhDonMon extends MoHinhCo
{
    private function damBaoBangPhienGoiMon()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS phien_goi_mon (
            id int(11) NOT NULL auto_increment,
            ban_id int(11) NOT NULL,
            ma_phien varchar(30) NOT NULL,
            bat_dau_luc datetime NOT NULL,
            het_han_luc datetime NOT NULL,
            ket_thuc_luc datetime default NULL,
            trang_thai enum('dang_dung','het_han','da_ket_thuc','da_huy') NOT NULL default 'dang_dung',
            ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ma_phien (ma_phien),
            KEY ban_id (ban_id),
            KEY trang_thai (trang_thai),
            KEY bat_dau_luc (bat_dau_luc)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->db->query("
        CREATE TABLE IF NOT EXISTS hoa_don_phien (
            id int(11) NOT NULL auto_increment,
            phien_goi_mon_id int(11) NOT NULL,
            ten_khach varchar(100) default NULL,
            sdt_khach varchar(20) default NULL,
            so_nguoi_lon int(11) NOT NULL default 0,
            so_tre_em int(11) NOT NULL default 0,
            tong_tien decimal(12,0) NOT NULL default 0,
            ghi_chu text,
            ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY phien_goi_mon_id (phien_goi_mon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    private function damBaoBangChiTietDon()
    {
        $this->damBaoBangPhienGoiMon();

        $this->db->query("
        CREATE TABLE IF NOT EXISTS chitiet_donmon (
            id int(11) NOT NULL auto_increment,
            don_mon_id int(11) NOT NULL,
            mon_an_id int(11) NOT NULL,
            so_luong int(11) default '1',
            ghi_chu text,
            trang_thai enum('cho_phuc_vu','dang_che_bien','da_phuc_vu','da_huy') default 'cho_phuc_vu',
            ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY don_mon_id (don_mon_id),
            KEY mon_an_id (mon_an_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $cotCu = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'don_mon'
              AND COLUMN_NAME = 'mon_an_id'
        ");
        if (!empty($cotCu) && (int)$cotCu[0]['co'] > 0) {
            $this->db->query("
                INSERT IGNORE INTO chitiet_donmon
                    (don_mon_id, mon_an_id, so_luong, ghi_chu, trang_thai, ngay_tao)
                SELECT id, mon_an_id, so_luong, ghi_chu, trang_thai, ngay_tao
                FROM don_mon
                WHERE mon_an_id IS NOT NULL
            ");
        }

        $cotPhien = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'don_mon'
              AND COLUMN_NAME = 'phien_goi_mon_id'
        ");
        if (empty($cotPhien) || (int)$cotPhien[0]['co'] === 0) {
            $this->db->query("
                ALTER TABLE don_mon ADD COLUMN phien_goi_mon_id int(11) default NULL
            ");
            $this->db->query("
                ALTER TABLE don_mon ADD KEY phien_goi_mon_id (phien_goi_mon_id)
            ");
        }
    }

    // Lay don theo ban - chi don dang cho phuc vu (nhan vien)
    public function layDonTheoBan($ban_id)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT d.id, d.ban_id, d.trang_thai, d.ngay_tao,
               b.so_ban,
               COUNT(ct.id) AS so_mon,
               SUM(ct.so_luong) AS tong_so_luong,
               GROUP_CONCAT(CONCAT(ct.so_luong, 'x ', m.ten)
                            ORDER BY ct.id SEPARATOR ', ') AS mon_tom_tat
        FROM don_mon d
        JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        JOIN mon_an m ON ct.mon_an_id = m.id
        LEFT JOIN ban b ON d.ban_id = b.id
        WHERE d.ban_id = ?
          AND d.trang_thai = 'cho_phuc_vu'
          AND ct.trang_thai = 'cho_phuc_vu'
        GROUP BY d.id
        ORDER BY d.ngay_tao ASC, d.id ASC
        ";
        return $this->db->query($sql, array((int)$ban_id));
    }

    // Lay chi tiet mon theo ban - khach theo doi ca mon da phuc vu
    public function layTheoMaBan($ban_id, $phien_goi_mon_id = 0)
    {
        $this->damBaoBangChiTietDon();

        $wherePhien = '';
        $params = array((int)$ban_id);
        if ((int)$phien_goi_mon_id > 0) {
            $wherePhien = ' AND d.phien_goi_mon_id = ?';
            $params[] = (int)$phien_goi_mon_id;
        }

        $sql = "
        SELECT ct.id, ct.don_mon_id, ct.so_luong, ct.trang_thai, ct.ghi_chu,
               m.ten AS ten_mon
        FROM don_mon d
        JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        JOIN mon_an m ON ct.mon_an_id = m.id
        WHERE d.ban_id = ?
          " . $wherePhien . "
          AND d.trang_thai IN ('cho_phuc_vu','dang_che_bien','da_phuc_vu')
          AND ct.trang_thai IN ('cho_phuc_vu','dang_che_bien','da_phuc_vu')
        ORDER BY d.ngay_tao ASC, ct.id ASC
        ";
        return $this->db->query($sql, $params);
    }

    public function layTatCa()
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT d.*, b.so_ban,
               b.so_ban AS table_number,
               COUNT(ct.id) AS so_mon,
               SUM(ct.so_luong) AS quantity,
               GROUP_CONCAT(CONCAT(ct.so_luong, 'x ', m.ten)
                            ORDER BY ct.id SEPARATOR ', ') AS ten_mon,
               GROUP_CONCAT(m.danh_muc ORDER BY ct.id SEPARATOR ', ') AS category,
               GROUP_CONCAT(ct.ghi_chu ORDER BY ct.id SEPARATOR '; ') AS note,
               d.ngay_tao AS created_at,
               CASE d.trang_thai
                   WHEN 'cho_phuc_vu' THEN 'pending'
                   WHEN 'dang_che_bien' THEN 'preparing'
                   WHEN 'da_phuc_vu' THEN 'served'
                   WHEN 'da_huy' THEN 'cancelled'
                   ELSE d.trang_thai
               END AS status,
               GROUP_CONCAT(CONCAT(ct.so_luong, 'x ', m.ten)
                            ORDER BY ct.id SEPARATOR ', ') AS item_name
        FROM don_mon d
        LEFT JOIN ban b ON d.ban_id = b.id
        LEFT JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        LEFT JOIN mon_an m ON ct.mon_an_id = m.id
        GROUP BY d.id
        ORDER BY d.ngay_tao DESC, d.id DESC
        ";
        return $this->db->query($sql);
    }

    public function demHomNay()
    {
        $sql  = "SELECT COUNT(*) AS tong FROM don_mon WHERE DATE(ngay_tao) = CURDATE()";
        $rows = $this->db->query($sql);
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function layDauVetDonMon()
    {
        $this->damBaoBangChiTietDon();

        $rows = $this->db->query("
        SELECT
            COUNT(DISTINCT CASE WHEN d.trang_thai = 'cho_phuc_vu' THEN d.id END) AS don_cho,
            COUNT(CASE WHEN ct.trang_thai = 'cho_phuc_vu' THEN ct.id END) AS mon_cho,
            COALESCE(MAX(d.id), 0) AS don_cuoi,
            COALESCE(MAX(ct.id), 0) AS chi_tiet_cuoi,
            COALESCE(MAX(d.ngay_tao), '') AS tao_cuoi
        FROM don_mon d
        LEFT JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        WHERE DATE(d.ngay_tao) = CURDATE()
        ");

        $row = !empty($rows) ? $rows[0] : array(
            'don_cho' => 0,
            'mon_cho' => 0,
            'don_cuoi' => 0,
            'chi_tiet_cuoi' => 0,
            'tao_cuoi' => ''
        );

        $version = implode('|', array(
            isset($row['don_cho']) ? $row['don_cho'] : 0,
            isset($row['mon_cho']) ? $row['mon_cho'] : 0,
            isset($row['don_cuoi']) ? $row['don_cuoi'] : 0,
            isset($row['chi_tiet_cuoi']) ? $row['chi_tiet_cuoi'] : 0,
            isset($row['tao_cuoi']) ? $row['tao_cuoi'] : ''
        ));

        return array(
            'version' => md5($version),
            'don_cho' => (int)$row['don_cho'],
            'mon_cho' => (int)$row['mon_cho'],
            'don_cuoi' => (int)$row['don_cuoi'],
            'luc' => date('Y-m-d H:i:s')
        );
    }

    public function layGanDay($gioi_han)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT d.*, b.so_ban,
               GROUP_CONCAT(CONCAT(ct.so_luong, 'x ', m.ten)
                            ORDER BY ct.id SEPARATOR ', ') AS ten_mon,
               SUM(ct.so_luong) AS so_luong
        FROM don_mon d
        LEFT JOIN ban b ON d.ban_id = b.id
        LEFT JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        LEFT JOIN mon_an m ON ct.mon_an_id = m.id
        GROUP BY d.id
        ORDER BY d.ngay_tao DESC, d.id DESC
        LIMIT ?
        ";
        return $this->db->query($sql, array((int)$gioi_han));
    }

    // Dat mon (khach - Usecase 102)
    public function datMon($ban_id, $mon_an_id, $so_luong, $ghi_chu)
    {
        return $this->datNhieuMon($ban_id, array(array(
            'mon_an_id' => $mon_an_id,
            'so_luong' => $so_luong,
            'ghi_chu'  => $ghi_chu
        )));
    }

    public function datNhieuMon($ban_id, $danh_sach_mon, $phien_goi_mon_id = 0)
    {
        $this->damBaoBangChiTietDon();

        if ((int)$phien_goi_mon_id > 0) {
            $sql = "
            INSERT INTO don_mon (ban_id, phien_goi_mon_id, trang_thai)
            VALUES (?, ?, 'cho_phuc_vu')
            ";
            $ok = $this->db->query($sql, array((int)$ban_id, (int)$phien_goi_mon_id));
        } else {
            $sql = "
            INSERT INTO don_mon (ban_id, trang_thai)
            VALUES (?, 'cho_phuc_vu')
            ";
            $ok = $this->db->query($sql, array((int)$ban_id));
        }
        if (!$ok) {
            return false;
        }

        $donId = $this->db->lastInsertId();
        foreach ($danh_sach_mon as $mon) {
            $monAnId = isset($mon['mon_an_id']) ? (int)$mon['mon_an_id'] : 0;
            $soLuong = isset($mon['so_luong']) ? (int)$mon['so_luong'] : 1;
            $ghiChu  = isset($mon['ghi_chu']) ? $mon['ghi_chu'] : '';

            if ($monAnId <= 0) {
                continue;
            }
            if ($soLuong < 1) {
                $soLuong = 1;
            }
            if ($soLuong > 10) {
                $soLuong = 10;
            }

            $this->db->query("
                INSERT INTO chitiet_donmon
                    (don_mon_id, mon_an_id, so_luong, ghi_chu, trang_thai)
                VALUES (?, ?, ?, ?, 'cho_phuc_vu')
            ", array($donId, $monAnId, $soLuong, $ghiChu));
        }

        return $donId;
    }

    // Huy mon boi khach (chi duoc khi con cho_phuc_vu)
    public function huyBoiKhach($chi_tiet_id, $ban_id, $phien_goi_mon_id = 0)
    {
        $this->damBaoBangChiTietDon();

        $wherePhien = '';
        $params = array((int)$chi_tiet_id, (int)$ban_id);
        if ((int)$phien_goi_mon_id > 0) {
            $wherePhien = ' AND d.phien_goi_mon_id = ?';
            $params[] = (int)$phien_goi_mon_id;
        }

        $sql = "
        UPDATE chitiet_donmon ct
        JOIN don_mon d ON d.id = ct.don_mon_id
        SET ct.trang_thai = 'da_huy'
        WHERE ct.id = ? AND d.ban_id = ? AND ct.trang_thai = 'cho_phuc_vu'
          " . $wherePhien . "
        ";
        $ok = $this->db->query($sql, $params);
        if ($ok) {
            $this->dongBoTrangThaiDonTheoChiTiet($chi_tiet_id);
        }
        return $ok;
    }

    public function capNhatTrangThai($id, $trang_thai)
    {
        $this->damBaoBangChiTietDon();

        $sql = "UPDATE don_mon SET trang_thai = ? WHERE id = ?";
        $ok = $this->db->query($sql, array($trang_thai, (int)$id));
        if ($ok) {
            $this->db->query(
                "UPDATE chitiet_donmon SET trang_thai = ? WHERE don_mon_id = ?",
                array($trang_thai, (int)$id)
            );
        }
        return $ok;
    }

    public function capNhatTatCaTheoBan($ban_id, $trang_thai)
    {
        $this->damBaoBangChiTietDon();

        $rows = $this->db->query(
            "SELECT id FROM don_mon WHERE ban_id = ? AND trang_thai = 'cho_phuc_vu'",
            array((int)$ban_id)
        );
        foreach ($rows as $row) {
            $this->capNhatTrangThai($row['id'], $trang_thai);
        }
        return true;
    }

    private function dongBoTrangThaiDonTheoChiTiet($chi_tiet_id)
    {
        $rows = $this->db->query(
            "SELECT don_mon_id FROM chitiet_donmon WHERE id = ? LIMIT 1",
            array((int)$chi_tiet_id)
        );
        if (empty($rows)) {
            return;
        }

        $donId = (int)$rows[0]['don_mon_id'];
        $conMonKhongHuy = $this->db->query("
            SELECT COUNT(*) AS tong
            FROM chitiet_donmon
            WHERE don_mon_id = ?
              AND trang_thai <> 'da_huy'
        ", array($donId));

        if (!empty($conMonKhongHuy) && (int)$conMonKhongHuy[0]['tong'] === 0) {
            $this->db->query(
                "UPDATE don_mon SET trang_thai = 'da_huy' WHERE id = ?",
                array($donId)
            );
        }
    }

    public function thongKeDoanThu($tu_ngay, $den_ngay)
    {
        $this->damBaoBangDoanhThuNgayVaDuLieuMau();

        $sql = "
        SELECT ngay, so_phien, so_khach, doanh_thu
        FROM doanh_thu_ngay
        WHERE ngay BETWEEN ? AND ?
        ORDER BY ngay ASC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    public function tongQuanDoanhThu($tu_ngay, $den_ngay)
    {
        $this->damBaoBangDoanhThuNgayVaDuLieuMau();

        $sql = "
        SELECT COALESCE(SUM(so_phien), 0) AS so_phien,
               COALESCE(SUM(doanh_thu), 0) AS doanh_thu,
               COALESCE(SUM(so_khach), 0) AS tong_khach
        FROM doanh_thu_ngay
        WHERE ngay BETWEEN ? AND ?
        ";
        $rows = $this->db->query($sql, array($tu_ngay, $den_ngay));
        return !empty($rows) ? $rows[0] : array('so_phien' => 0, 'doanh_thu' => 0, 'tong_khach' => 0);
    }

    public function chiTietDoanhThu($tu_ngay, $den_ngay)
    {
        $this->damBaoBangDoanhThuNgayVaDuLieuMau();

        $sql = "
        SELECT ngay, so_khach, so_phien, doanh_thu, nguon
        FROM doanh_thu_ngay
        WHERE ngay BETWEEN ? AND ?
        ORDER BY ngay DESC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    private function damBaoBangDoanhThuNgayVaDuLieuMau()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS doanh_thu_ngay (
            id int(11) NOT NULL auto_increment,
            ngay date NOT NULL,
            so_khach int(11) NOT NULL default 0,
            so_phien int(11) NOT NULL default 0,
            doanh_thu decimal(12,0) NOT NULL default 0,
            nguon varchar(30) NOT NULL default 'he_thong',
            ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
            ngay_cap_nhat datetime default NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_doanh_thu_ngay (ngay)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $rows = $this->db->query("
            SELECT COUNT(*) AS tong
            FROM doanh_thu_ngay
            WHERE ngay BETWEEN '2026-04-01' AND '2026-04-30'
        ");
        if (!empty($rows) && (int)$rows[0]['tong'] > 0) {
            return;
        }

        $duLieuThang4 = array(
            '2026-04-01' => array(34, 9),  '2026-04-02' => array(41, 11),
            '2026-04-03' => array(48, 12), '2026-04-04' => array(76, 18),
            '2026-04-05' => array(69, 17), '2026-04-06' => array(32, 8),
            '2026-04-07' => array(37, 9),  '2026-04-08' => array(45, 11),
            '2026-04-09' => array(43, 10), '2026-04-10' => array(52, 13),
            '2026-04-11' => array(82, 20), '2026-04-12' => array(74, 18),
            '2026-04-13' => array(35, 9),  '2026-04-14' => array(39, 10),
            '2026-04-15' => array(47, 12), '2026-04-16' => array(44, 11),
            '2026-04-17' => array(58, 14), '2026-04-18' => array(88, 21),
            '2026-04-19' => array(79, 19), '2026-04-20' => array(36, 9),
            '2026-04-21' => array(42, 10), '2026-04-22' => array(49, 12),
            '2026-04-23' => array(46, 11), '2026-04-24' => array(57, 14),
            '2026-04-25' => array(91, 22), '2026-04-26' => array(84, 20),
            '2026-04-27' => array(38, 9),  '2026-04-28' => array(44, 11),
            '2026-04-29' => array(63, 15), '2026-04-30' => array(96, 23)
        );

        $giaNguoi = defined('PRICE_ADULT') ? (int)PRICE_ADULT : 199000;
        foreach ($duLieuThang4 as $ngay => $dong) {
            $soKhach = (int)$dong[0];
            $soPhien = (int)$dong[1];
            $doanhThu = $soKhach * $giaNguoi;
            $this->db->query("
                INSERT IGNORE INTO doanh_thu_ngay
                    (ngay, so_khach, so_phien, doanh_thu, nguon, ngay_cap_nhat)
                VALUES (?, ?, ?, ?, 'du_lieu_mau', NOW())
            ", array($ngay, $soKhach, $soPhien, $doanhThu));
        }
    }

    public function topMonBanChay($gioi_han)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT m.ten, SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id = ct.don_mon_id
        JOIN mon_an m ON ct.mon_an_id = m.id
        WHERE d.trang_thai = 'da_phuc_vu'
          AND ct.trang_thai = 'da_phuc_vu'
        GROUP BY m.id, m.ten
        ORDER BY tong_ban DESC
        LIMIT ?
        ";
        return $this->db->query($sql, array((int)$gioi_han));
    }

    public function topMonBanChayTrongKhoang($gioi_han, $tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT m.id, m.ten, m.danh_muc, SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id = ct.don_mon_id
        JOIN mon_an m ON ct.mon_an_id = m.id
        WHERE d.trang_thai = 'da_phuc_vu'
          AND ct.trang_thai = 'da_phuc_vu'
          AND DATE(d.ngay_tao) BETWEEN ? AND ?
        GROUP BY m.id, m.ten, m.danh_muc
        ORDER BY tong_ban DESC, m.ten ASC
        LIMIT ?
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay, (int)$gioi_han));
    }

    public function thongKeDanhMucTrongKhoang($tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT m.danh_muc, SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id = ct.don_mon_id
        JOIN mon_an m ON ct.mon_an_id = m.id
        WHERE d.trang_thai = 'da_phuc_vu'
          AND ct.trang_thai = 'da_phuc_vu'
          AND DATE(d.ngay_tao) BETWEEN ? AND ?
        GROUP BY m.danh_muc
        ORDER BY tong_ban DESC, m.danh_muc ASC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    public function thongKeDonTheoGio($tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT HOUR(d.ngay_tao) AS gio,
               COUNT(DISTINCT d.id) AS so_don,
               COALESCE(SUM(ct.so_luong), 0) AS so_mon
        FROM don_mon d
        LEFT JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        WHERE d.trang_thai <> 'da_huy'
          AND DATE(d.ngay_tao) BETWEEN ? AND ?
        GROUP BY HOUR(d.ngay_tao)
        ORDER BY gio ASC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    public function monItBanTrongKhoang($gioi_han, $tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();

        $sql = "
        SELECT m.id, m.ten, m.danh_muc,
               COALESCE(SUM(CASE WHEN d.id IS NOT NULL THEN ct.so_luong ELSE 0 END), 0) AS tong_ban
        FROM mon_an m
        LEFT JOIN chitiet_donmon ct
          ON ct.mon_an_id = m.id
         AND ct.trang_thai = 'da_phuc_vu'
        LEFT JOIN don_mon d
          ON d.id = ct.don_mon_id
         AND d.trang_thai = 'da_phuc_vu'
         AND DATE(d.ngay_tao) BETWEEN ? AND ?
        WHERE m.con_mon = 1
        GROUP BY m.id, m.ten, m.danh_muc
        ORDER BY tong_ban ASC, m.noi_bat DESC, m.ten ASC
        LIMIT ?
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay, (int)$gioi_han));
    }
}

// ================= MO HINH MON AN =================
