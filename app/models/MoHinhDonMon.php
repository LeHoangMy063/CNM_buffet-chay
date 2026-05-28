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

        $this->db->query("
        CREATE TABLE IF NOT EXISTS thanh_toan_phien (
            id int(11) NOT NULL auto_increment,
            hoa_don_phien_id int(11) NOT NULL,
            phuong_thuc varchar(30) DEFAULT NULL,
            thanh_toan_luc datetime DEFAULT NULL,
            tich_diem_luc datetime DEFAULT NULL,
            tich_diem_tai_khoan_id int(11) DEFAULT NULL,
            diem_da_cong int(11) NOT NULL DEFAULT 0,
            ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY hoa_don_phien_id (hoa_don_phien_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->chuyenCotThanhToanCuSangBangRieng();
    }

    private function cotHoaDonPhienTonTai($cot)
    {
        $rows = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'hoa_don_phien'
              AND COLUMN_NAME = ?
        ", array($cot));
        return !empty($rows) && (int)$rows[0]['co'] > 0;
    }

    private function xoaCotHoaDonPhienNeuCo($cot)
    {
        if ($this->cotHoaDonPhienTonTai($cot)) {
            $this->db->query("ALTER TABLE hoa_don_phien DROP COLUMN " . $cot);
        }
    }

    private function chuyenCotThanhToanCuSangBangRieng()
    {
        $coPhuongThuc = $this->cotHoaDonPhienTonTai('phuong_thuc_thanh_toan');
        $coThanhToanLuc = $this->cotHoaDonPhienTonTai('thanh_toan_luc');
        $coTichDiemLuc = $this->cotHoaDonPhienTonTai('tich_diem_luc');
        $coTaiKhoan = $this->cotHoaDonPhienTonTai('tich_diem_tai_khoan_id');
        $coDiem = $this->cotHoaDonPhienTonTai('diem_da_cong');

        if ($coPhuongThuc || $coThanhToanLuc || $coTichDiemLuc || $coTaiKhoan || $coDiem) {
            $phuongThuc = $coPhuongThuc ? 'h.phuong_thuc_thanh_toan' : 'NULL';
            $thanhToanLuc = $coThanhToanLuc ? 'h.thanh_toan_luc' : 'NULL';
            $tichDiemLuc = $coTichDiemLuc ? 'h.tich_diem_luc' : 'NULL';
            $taiKhoan = $coTaiKhoan ? 'h.tich_diem_tai_khoan_id' : 'NULL';
            $diem = $coDiem ? 'h.diem_da_cong' : '0';

            $this->db->query("
                INSERT INTO thanh_toan_phien
                    (hoa_don_phien_id, phuong_thuc, thanh_toan_luc, tich_diem_luc, tich_diem_tai_khoan_id, diem_da_cong, ngay_tao)
                SELECT h.id, $phuongThuc, $thanhToanLuc, $tichDiemLuc, $taiKhoan, $diem, h.ngay_tao
                FROM hoa_don_phien h
                WHERE h.tong_tien > 0
                ON DUPLICATE KEY UPDATE
                    phuong_thuc = COALESCE(VALUES(phuong_thuc), phuong_thuc),
                    thanh_toan_luc = COALESCE(VALUES(thanh_toan_luc), thanh_toan_luc),
                    tich_diem_luc = COALESCE(VALUES(tich_diem_luc), tich_diem_luc),
                    tich_diem_tai_khoan_id = COALESCE(VALUES(tich_diem_tai_khoan_id), tich_diem_tai_khoan_id),
                    diem_da_cong = GREATEST(VALUES(diem_da_cong), diem_da_cong)
            ");
        }

        $this->xoaCotHoaDonPhienNeuCo('phuong_thuc_thanh_toan');
        $this->xoaCotHoaDonPhienNeuCo('thanh_toan_luc');
        $this->xoaCotHoaDonPhienNeuCo('tich_diem_luc');
        $this->xoaCotHoaDonPhienNeuCo('tich_diem_tai_khoan_id');
        $this->xoaCotHoaDonPhienNeuCo('diem_da_cong');
    }

    private function damBaoCotHoaDonPhien($cot, $sql)
    {
        $rows = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'hoa_don_phien'
              AND COLUMN_NAME = ?
        ", array($cot));

        if (empty($rows) || (int)$rows[0]['co'] === 0) {
            $this->db->query($sql);
        }
    }

    private function damBaoDuLieuThang4TheoPhien()
    {
        $this->damBaoBangPhienGoiMon();
        $this->damBaoBangChiTietDon();

        $rows = $this->db->query("
            SELECT
                COUNT(DISTINCT p.id) AS so_phien,
                COUNT(DISTINCT t.id) AS so_thanh_toan,
                COUNT(DISTINCT d.id) AS so_don,
                COUNT(ct.id) AS so_chi_tiet,
                SUM(CASE WHEN h.ten_khach = 'Khach thang 4' THEN 1 ELSE 0 END) AS ten_cu
            FROM phien_goi_mon p
            LEFT JOIN hoa_don_phien h ON h.phien_goi_mon_id = p.id
            LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
            LEFT JOIN don_mon d ON d.phien_goi_mon_id = p.id AND d.id BETWEEN 910000 AND 913099
            LEFT JOIN chitiet_donmon ct ON ct.don_mon_id = d.id AND ct.id BETWEEN 920000 AND 950999
            WHERE p.ma_phien LIKE 'APR-202604%'
        ");
        if (
            !empty($rows) &&
            (int)$rows[0]['so_phien'] === 408 &&
            (int)$rows[0]['so_thanh_toan'] === 408 &&
            (int)$rows[0]['so_don'] === 408 &&
            (int)$rows[0]['so_chi_tiet'] === 1224 &&
            (int)$rows[0]['ten_cu'] === 0
        ) {
            return;
        }

        $this->db->query("
            DELETE t
            FROM thanh_toan_phien t
            JOIN hoa_don_phien h ON h.id = t.hoa_don_phien_id
            JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
            WHERE p.ma_phien LIKE 'APR-202604%'
        ");
        $this->db->query("
            UPDATE don_mon d
            JOIN phien_goi_mon p ON p.id = d.phien_goi_mon_id
            SET d.phien_goi_mon_id = NULL
            WHERE p.ma_phien LIKE 'APR-202604%'
        ");
        $this->db->query("DELETE FROM chitiet_donmon WHERE id BETWEEN 920000 AND 950999");
        $this->db->query("DELETE FROM don_mon WHERE id BETWEEN 910000 AND 913099");
        $this->db->query("
            DELETE h
            FROM hoa_don_phien h
            JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
            WHERE p.ma_phien LIKE 'APR-202604%'
        ");
        $this->db->query("DELETE FROM phien_goi_mon WHERE ma_phien LIKE 'APR-202604%'");

        $this->db->query("
            CREATE TEMPORARY TABLE IF NOT EXISTS seed_apr_revenue (
                ngay date NOT NULL,
                so_khach int(11) NOT NULL,
                so_phien int(11) NOT NULL
            )
        ");
        $this->db->query("DELETE FROM seed_apr_revenue");
        $this->db->query("
            INSERT INTO seed_apr_revenue (ngay, so_khach, so_phien) VALUES
            ('2026-04-01', 34, 9),  ('2026-04-02', 41, 11), ('2026-04-03', 48, 12), ('2026-04-04', 76, 18),
            ('2026-04-05', 69, 17), ('2026-04-06', 32, 8),  ('2026-04-07', 37, 9),  ('2026-04-08', 45, 11),
            ('2026-04-09', 43, 10), ('2026-04-10', 52, 13), ('2026-04-11', 82, 20), ('2026-04-12', 74, 18),
            ('2026-04-13', 35, 9),  ('2026-04-14', 39, 10), ('2026-04-15', 47, 12), ('2026-04-16', 44, 11),
            ('2026-04-17', 58, 14), ('2026-04-18', 88, 21), ('2026-04-19', 79, 19), ('2026-04-20', 36, 9),
            ('2026-04-21', 42, 10), ('2026-04-22', 49, 12), ('2026-04-23', 46, 11), ('2026-04-24', 57, 14),
            ('2026-04-25', 91, 22), ('2026-04-26', 84, 20), ('2026-04-27', 38, 9),  ('2026-04-28', 44, 11),
            ('2026-04-29', 63, 15), ('2026-04-30', 96, 23)
        ");

        $this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS seed_nums (n int(11) NOT NULL)");
        $this->db->query("DELETE FROM seed_nums");
        $this->db->query("
            INSERT INTO seed_nums (n) VALUES
            (1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12),
            (13),(14),(15),(16),(17),(18),(19),(20),(21),(22),(23)
        ");

        $this->db->query("
            INSERT IGNORE INTO phien_goi_mon
                (id, ban_id, ma_phien, bat_dau_luc, het_han_luc, ket_thuc_luc, trang_thai, ngay_tao)
            SELECT
                900000 + DAY(r.ngay) * 100 + n.n,
                MOD(n.n - 1, 8) + 1,
                CONCAT('APR-', DATE_FORMAT(r.ngay, '%Y%m%d'), '-', LPAD(n.n, 2, '0')),
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60)) MINUTE),
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60) + 100) MINUTE),
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60) + 85) MINUTE),
                'da_ket_thuc',
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60)) MINUTE)
            FROM seed_apr_revenue r
            JOIN seed_nums n ON n.n <= r.so_phien
        ");

        $this->db->query("
            INSERT IGNORE INTO hoa_don_phien
                (id, phien_goi_mon_id, ten_khach, sdt_khach, so_nguoi_lon, so_tre_em, tong_tien, ghi_chu,
                 ngay_tao)
            SELECT
                900000 + DAY(r.ngay) * 100 + n.n,
                900000 + DAY(r.ngay) * 100 + n.n,
                CASE MOD(DAY(r.ngay) + n.n, 12)
                    WHEN 0 THEN 'Nguyen Minh Anh'
                    WHEN 1 THEN 'Tran Hoai Nam'
                    WHEN 2 THEN 'Le Thanh Truc'
                    WHEN 3 THEN 'Pham Gia Han'
                    WHEN 4 THEN 'Vo Quoc Bao'
                    WHEN 5 THEN 'Dang My Linh'
                    WHEN 6 THEN 'Bui Hoang Phuc'
                    WHEN 7 THEN 'Huynh Ngoc Mai'
                    WHEN 8 THEN 'Do Anh Khoa'
                    WHEN 9 THEN 'Phan Tuong Vy'
                    WHEN 10 THEN 'Ngo Bao Chau'
                    ELSE 'Khach vang lai'
                END,
                CASE WHEN MOD(n.n, 5) = 0 THEN ''
                    ELSE CONCAT('09', LPAD(30000000 + DAY(r.ngay) * 1000 + n.n * 17, 8, '0'))
                END,
                FLOOR(r.so_khach / r.so_phien) + IF(n.n <= MOD(r.so_khach, r.so_phien), 1, 0),
                0,
                (FLOOR(r.so_khach / r.so_phien) + IF(n.n <= MOD(r.so_khach, r.so_phien), 1, 0)) * 199000,
                NULL,
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60)) MINUTE)
            FROM seed_apr_revenue r
            JOIN seed_nums n ON n.n <= r.so_phien
        ");

        $this->db->query("
            INSERT IGNORE INTO thanh_toan_phien
                (id, hoa_don_phien_id, phuong_thuc, thanh_toan_luc, ngay_tao)
            SELECT
                900000 + DAY(r.ngay) * 100 + n.n,
                900000 + DAY(r.ngay) * 100 + n.n,
                IF(MOD(n.n, 3) = 0, 'chuyen_khoan', 'tien_mat'),
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60) + 90) MINUTE),
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60)) MINUTE)
            FROM seed_apr_revenue r
            JOIN seed_nums n ON n.n <= r.so_phien
        ");

        $this->db->query("
            INSERT IGNORE INTO don_mon
                (id, ban_id, phien_goi_mon_id, trang_thai, tong_tien, ngay_tao)
            SELECT
                910000 + DAY(r.ngay) * 100 + n.n,
                MOD(n.n - 1, 8) + 1,
                900000 + DAY(r.ngay) * 100 + n.n,
                'da_phuc_vu',
                0,
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60) + 15) MINUTE)
            FROM seed_apr_revenue r
            JOIN seed_nums n ON n.n <= r.so_phien
        ");

        $this->db->query("
            INSERT IGNORE INTO chitiet_donmon
                (id, don_mon_id, mon_an_id, so_luong, ghi_chu, trang_thai, ngay_tao)
            SELECT
                920000 + DAY(r.ngay) * 1000 + n.n * 10 + k.k,
                910000 + DAY(r.ngay) * 100 + n.n,
                MOD(DAY(r.ngay) + n.n + k.k * 7, 42) + 1,
                1 + MOD(DAY(r.ngay) + n.n + k.k, 2),
                '',
                'da_phuc_vu',
                DATE_ADD(CAST(CONCAT(r.ngay, ' 10:00:00') AS DATETIME), INTERVAL (MOD(n.n - 1, 11) * 60 + MOD((n.n - 1) * 7, 60) + 20 + k.k * 5) MINUTE)
            FROM seed_apr_revenue r
            JOIN seed_nums n ON n.n <= r.so_phien
            JOIN (SELECT 1 AS k UNION ALL SELECT 2 UNION ALL SELECT 3) k
        ");

        $this->db->query("UPDATE don_mon SET phien_goi_mon_id = 902801 WHERE id IN (3, 4, 5, 6)");
        $this->db->query("UPDATE don_mon SET phien_goi_mon_id = 903001 WHERE id = 7");
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
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) AS ngay,
               COUNT(*) AS so_phien,
               COALESCE(SUM(h.so_nguoi_lon + h.so_tre_em), 0) AS so_khach,
               COALESCE(SUM(h.tong_tien), 0) AS doanh_thu
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        WHERE p.trang_thai = 'da_ket_thuc'
          AND h.tong_tien > 0
          AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        GROUP BY DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao))
        ORDER BY ngay ASC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    public function tongQuanDoanhThu($tu_ngay, $den_ngay)
    {
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT COUNT(*) AS so_phien,
               COALESCE(SUM(h.tong_tien), 0) AS doanh_thu,
               COALESCE(SUM(h.so_nguoi_lon + h.so_tre_em), 0) AS tong_khach
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        WHERE p.trang_thai = 'da_ket_thuc'
          AND h.tong_tien > 0
          AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        ";
        $rows = $this->db->query($sql, array($tu_ngay, $den_ngay));
        return !empty($rows) ? $rows[0] : array('so_phien' => 0, 'doanh_thu' => 0, 'tong_khach' => 0);
    }

    public function chiTietDoanhThu($tu_ngay, $den_ngay)
    {
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) AS ngay,
               COALESCE(SUM(h.so_nguoi_lon + h.so_tre_em), 0) AS so_khach,
               COUNT(*) AS so_phien,
               COALESCE(SUM(h.tong_tien), 0) AS doanh_thu
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        WHERE p.trang_thai = 'da_ket_thuc'
          AND h.tong_tien > 0
          AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        GROUP BY DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao))
        ORDER BY ngay DESC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
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
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT m.id, m.ten, m.danh_muc, SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id = ct.don_mon_id
        JOIN phien_goi_mon p ON p.id = d.phien_goi_mon_id
        JOIN hoa_don_phien h ON h.phien_goi_mon_id = p.id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        JOIN mon_an m ON ct.mon_an_id = m.id
        WHERE d.trang_thai = 'da_phuc_vu'
          AND ct.trang_thai = 'da_phuc_vu'
          AND p.trang_thai = 'da_ket_thuc'
          AND h.tong_tien > 0
          AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        GROUP BY m.id, m.ten, m.danh_muc
        ORDER BY tong_ban DESC, m.ten ASC
        LIMIT ?
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay, (int)$gioi_han));
    }

    public function thongKeDanhMucTrongKhoang($tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT m.danh_muc, SUM(ct.so_luong) AS tong_ban
        FROM chitiet_donmon ct
        JOIN don_mon d ON d.id = ct.don_mon_id
        JOIN phien_goi_mon p ON p.id = d.phien_goi_mon_id
        JOIN hoa_don_phien h ON h.phien_goi_mon_id = p.id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        JOIN mon_an m ON ct.mon_an_id = m.id
        WHERE d.trang_thai = 'da_phuc_vu'
          AND ct.trang_thai = 'da_phuc_vu'
          AND p.trang_thai = 'da_ket_thuc'
          AND h.tong_tien > 0
          AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        GROUP BY m.danh_muc
        ORDER BY tong_ban DESC, m.danh_muc ASC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    public function thongKeDonTheoGio($tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT HOUR(d.ngay_tao) AS gio,
               COUNT(DISTINCT d.id) AS so_don,
               COALESCE(SUM(ct.so_luong), 0) AS so_mon
        FROM don_mon d
        JOIN phien_goi_mon p ON p.id = d.phien_goi_mon_id
        JOIN hoa_don_phien h ON h.phien_goi_mon_id = p.id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        LEFT JOIN chitiet_donmon ct ON ct.don_mon_id = d.id
        WHERE d.trang_thai <> 'da_huy'
          AND p.trang_thai = 'da_ket_thuc'
          AND h.tong_tien > 0
          AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        GROUP BY HOUR(d.ngay_tao)
        ORDER BY gio ASC
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay));
    }

    public function monItBanTrongKhoang($gioi_han, $tu_ngay, $den_ngay)
    {
        $this->damBaoBangChiTietDon();
        $this->damBaoDuLieuThang4TheoPhien();

        $sql = "
        SELECT m.id, m.ten, m.danh_muc,
               COALESCE(SUM(CASE WHEN t.id IS NOT NULL THEN ct.so_luong ELSE 0 END), 0) AS tong_ban
        FROM mon_an m
        LEFT JOIN chitiet_donmon ct
          ON ct.mon_an_id = m.id
         AND ct.trang_thai = 'da_phuc_vu'
        LEFT JOIN don_mon d
          ON d.id = ct.don_mon_id
         AND d.trang_thai = 'da_phuc_vu'
        LEFT JOIN phien_goi_mon p
          ON p.id = d.phien_goi_mon_id
         AND p.trang_thai = 'da_ket_thuc'
        LEFT JOIN hoa_don_phien h
          ON h.phien_goi_mon_id = p.id
         AND h.tong_tien > 0
        LEFT JOIN thanh_toan_phien t
          ON t.hoa_don_phien_id = h.id
         AND DATE(COALESCE(t.thanh_toan_luc, p.ket_thuc_luc, h.ngay_tao)) BETWEEN ? AND ?
        WHERE m.con_mon = 1
        GROUP BY m.id, m.ten, m.danh_muc
        ORDER BY tong_ban ASC, m.noi_bat DESC, m.ten ASC
        LIMIT ?
        ";
        return $this->db->query($sql, array($tu_ngay, $den_ngay, (int)$gioi_han));
    }
}

// ================= MO HINH MON AN =================
