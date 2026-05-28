<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhBan extends MoHinhCo
{
    private function damBaoMaPhienBan()
    {
        $cotMa = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'ban'
              AND COLUMN_NAME = 'ma_phien_goi_mon'
        ");
        if (empty($cotMa) || (int)$cotMa[0]['co'] === 0) {
            $this->db->query("
                ALTER TABLE ban ADD COLUMN ma_phien_goi_mon varchar(30) DEFAULT NULL
            ");
        }

        $cotHan = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'ban'
              AND COLUMN_NAME = 'ma_phien_het_han'
        ");
        if (empty($cotHan) || (int)$cotHan[0]['co'] === 0) {
            $this->db->query("
                ALTER TABLE ban ADD COLUMN ma_phien_het_han datetime DEFAULT NULL
            ");
        }

        $cotPhien = array(
            'phien_ten_khach'  => "ALTER TABLE ban ADD COLUMN phien_ten_khach varchar(100) DEFAULT NULL",
            'phien_sdt_khach'  => "ALTER TABLE ban ADD COLUMN phien_sdt_khach varchar(20) DEFAULT NULL",
            'phien_nguoi_lon'  => "ALTER TABLE ban ADD COLUMN phien_nguoi_lon int(11) NOT NULL DEFAULT 0",
            'phien_tre_em'     => "ALTER TABLE ban ADD COLUMN phien_tre_em int(11) NOT NULL DEFAULT 0",
            'phien_tong_tien'  => "ALTER TABLE ban ADD COLUMN phien_tong_tien decimal(12,0) NOT NULL DEFAULT 0",
            'phien_bat_dau'    => "ALTER TABLE ban ADD COLUMN phien_bat_dau datetime DEFAULT NULL"
        );

        foreach ($cotPhien as $tenCot => $lenhThemCot) {
            $kiemTra = $this->db->query("
                SELECT COUNT(*) AS co
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'ban'
                  AND COLUMN_NAME = ?
            ", array($tenCot));
            if (empty($kiemTra) || (int)$kiemTra[0]['co'] === 0) {
                $this->db->query($lenhThemCot);
            }
        }

        $this->damBaoBangPhienGoiMon();
        $this->capNhatPhienHetHan();
    }

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
        $this->damBaoCotTichDiemHoaDonPhien();
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

    private function damBaoCotHoaDonPhien($cot, $lenhThem)
    {
        $rows = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'hoa_don_phien'
              AND COLUMN_NAME = ?
        ", array($cot));

        if (empty($rows) || (int)$rows[0]['co'] === 0) {
            $this->db->query($lenhThem);
        }
    }

    private function damBaoCotTichDiemHoaDonPhien()
    {
        $this->db->query("
        INSERT IGNORE INTO thanh_toan_phien (hoa_don_phien_id, ngay_tao)
        SELECT id, ngay_tao
        FROM hoa_don_phien
        WHERE tong_tien > 0
        ");
    }

    private function capNhatPhienHetHan()
    {
        $this->db->query("
        UPDATE ban b
        JOIN phien_goi_mon p ON p.ban_id = b.id AND p.ma_phien = b.ma_phien_goi_mon
        SET b.trang_thai = 'trong',
            b.ma_phien_goi_mon = NULL,
            b.ma_phien_het_han = NULL,
            b.phien_ten_khach = NULL,
            b.phien_sdt_khach = NULL,
            b.phien_nguoi_lon = 0,
            b.phien_tre_em = 0,
            b.phien_tong_tien = 0,
            b.phien_bat_dau = NULL
        WHERE p.trang_thai = 'dang_dung'
          AND p.het_han_luc <= NOW()
        ");

        $this->db->query("
        UPDATE phien_goi_mon
        SET trang_thai = 'het_han',
            ket_thuc_luc = IFNULL(ket_thuc_luc, NOW())
        WHERE trang_thai = 'dang_dung'
          AND het_han_luc <= NOW()
        ");
    }

    private function damBaoBangGanBan()
    {
        // Bang lien ket dat_ban voi ban (nhieu ban)
        $this->db->query("
        CREATE TABLE IF NOT EXISTS chitiet_datban (
            id int(11) NOT NULL auto_increment,
            dat_ban_id int(11) NOT NULL,
            ban_id int(11) NOT NULL,
            ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_chitiet_datban (dat_ban_id, ban_id),
            KEY ban_id (ban_id),
            KEY dat_ban_id (dat_ban_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->db->query("
        INSERT IGNORE INTO chitiet_datban (dat_ban_id, ban_id)
        SELECT id, ban_id FROM dat_ban WHERE ban_id IS NOT NULL
        ");

        $bangCu = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'dat_ban_ban'
        ");
        if (!empty($bangCu) && (int)$bangCu[0]['co'] > 0) {
            $this->db->query("
                INSERT IGNORE INTO chitiet_datban (dat_ban_id, ban_id)
                SELECT dat_ban_id, ban_id FROM dat_ban_ban
            ");
        }

        // Them cot ban_xac_nhan neu chua co (tuong thich MySQL 5.0+)
        // Kiem tra qua INFORMATION_SCHEMA truoc, tranh loi "Duplicate column"
        $kiemTraCot = $this->db->query("
            SELECT COUNT(*) AS co
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME  = 'dat_ban'
              AND COLUMN_NAME = 'ban_xac_nhan'
        ");
        if (empty($kiemTraCot) || (int)$kiemTraCot[0]['co'] === 0) {
            $this->db->query("
                ALTER TABLE dat_ban ADD COLUMN ban_xac_nhan tinyint(1) NOT NULL DEFAULT 0
            ");
        }
    }

    public function layTheoId($id)
    {
        $this->damBaoMaPhienBan();

        $sql  = "SELECT * FROM ban WHERE id = ? LIMIT 1";
        $rows = $this->db->query($sql, array((int)$id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTatCaBan()
    {
        $this->damBaoMaPhienBan();

        $sql = "
        SELECT
            b.id, b.so_ban, b.suc_chua, b.ma_truy_cap, b.ma_phien_goi_mon, b.ma_phien_het_han,
            b.phien_ten_khach, b.phien_sdt_khach, b.phien_nguoi_lon, b.phien_tre_em,
            b.phien_tong_tien, b.phien_bat_dau,
            CASE
                WHEN b.ma_phien_goi_mon IS NOT NULL
                 AND b.ma_phien_het_han IS NOT NULL
                 AND b.ma_phien_het_han > NOW()
                THEN 1
                ELSE 0
            END AS ma_phien_con_han,
            CASE
                WHEN b.trang_thai = 'dang_dung' THEN 'dang_dung'
                ELSE 'trong'
            END AS trang_thai_goc,
            IFNULL(dm.so_don_cho, 0) AS so_don_cho,
            CASE
                WHEN IFNULL(dm.so_don_cho, 0) > 0
                THEN 'dang_dung'
                WHEN b.trang_thai = 'dang_dung' THEN 'dang_dung'
                ELSE 'trong'
            END AS trang_thai
        FROM ban b
        LEFT JOIN (
            SELECT ban_id, COUNT(*) AS so_don_cho
            FROM don_mon
            WHERE trang_thai = 'cho_phuc_vu'
            GROUP BY ban_id
        ) dm ON dm.ban_id = b.id
        ORDER BY b.so_ban
        ";
        return $this->db->query($sql);
    }

    public function layTheoMaTruyCap($ma)
    {
        $this->damBaoMaPhienBan();

        $sql  = "
        SELECT * FROM ban
        WHERE (ma_phien_goi_mon = ? AND ma_phien_het_han > NOW())
           OR (
               ma_truy_cap = ?
               AND (
                   trang_thai <> 'dang_dung'
                   OR ma_phien_goi_mon IS NULL
                   OR ma_phien_het_han IS NULL
               )
           )
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array($ma, $ma));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoMaPhienGoiMon($ma)
    {
        $this->damBaoMaPhienBan();

        $sql = "
        SELECT b.*, p.id AS phien_goi_mon_id
        FROM ban b
        JOIN phien_goi_mon p ON p.ban_id = b.id AND p.ma_phien = b.ma_phien_goi_mon
        WHERE ma_phien_goi_mon = ?
          AND ma_phien_het_han > NOW()
          AND b.trang_thai = 'dang_dung'
          AND p.trang_thai = 'dang_dung'
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array($ma));
        return !empty($rows) ? $rows[0] : null;
    }

    private function taoMaPhienGoiMon($maBan)
    {
        $goc = preg_replace('/[^A-Za-z0-9]/', '', $maBan);
        if ($goc === '') {
            $goc = 'BAN';
        }

        do {
            $ma = strtoupper($goc) . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $rows = $this->db->query(
                "SELECT COUNT(*) AS tong FROM ban WHERE ma_phien_goi_mon = ? AND ma_phien_het_han > NOW()",
                array($ma)
            );
        } while (!empty($rows) && (int)$rows[0]['tong'] > 0);

        return $ma;
    }

    public function taoPhienGoiMon($id, $thongTinBill = array())
    {
        $this->damBaoMaPhienBan();

        $ban = $this->layTheoId($id);
        if (!$ban) {
            return null;
        }

        $maBan = !empty($ban['ma_truy_cap']) ? $ban['ma_truy_cap'] : ('BAN' . (int)$id);
        $maPhien = $this->taoMaPhienGoiMon($maBan);
        $tenKhach = isset($thongTinBill['ten_khach']) ? trim($thongTinBill['ten_khach']) : '';
        $sdtKhach = isset($thongTinBill['sdt_khach']) ? trim($thongTinBill['sdt_khach']) : '';
        $nguoiLon = isset($thongTinBill['nguoi_lon']) ? (int)$thongTinBill['nguoi_lon'] : 0;
        $treEm = isset($thongTinBill['tre_em']) ? (int)$thongTinBill['tre_em'] : 0;
        $tongTien = isset($thongTinBill['tong_tien']) ? (int)$thongTinBill['tong_tien'] : 0;

        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc');

        $sql = "
        UPDATE ban
        SET ma_phien_goi_mon = ?,
            ma_phien_het_han = DATE_ADD(NOW(), INTERVAL 100 MINUTE),
            phien_ten_khach = ?,
            phien_sdt_khach = ?,
            phien_nguoi_lon = ?,
            phien_tre_em = ?,
            phien_tong_tien = ?,
            phien_bat_dau = NOW()
        WHERE id = ?
        ";
        $ok = $this->db->query($sql, array($maPhien, $tenKhach, $sdtKhach, $nguoiLon, $treEm, $tongTien, (int)$id));
        if (!$ok) {
            return null;
        }

        $this->db->query("
            INSERT INTO phien_goi_mon
                (ban_id, ma_phien, bat_dau_luc, het_han_luc, trang_thai)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 100 MINUTE), 'dang_dung')
        ", array((int)$id, $maPhien));
        $phienId = $this->db->lastInsertId();

        if ($phienId) {
            $this->db->query("
                INSERT INTO hoa_don_phien
                    (phien_goi_mon_id, ten_khach, sdt_khach, so_nguoi_lon, so_tre_em, tong_tien)
                VALUES (?, ?, ?, ?, ?, ?)
            ", array($phienId, $tenKhach, $sdtKhach, $nguoiLon, $treEm, $tongTien));
        }

        return $this->layTheoId($id);
    }

    private function capNhatKetThucPhienHienTai($id, $trangThaiPhien, $phuongThucThanhToan = '')
    {
        $this->damBaoBangPhienGoiMon();

        $this->db->query("
        UPDATE hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        JOIN ban b ON b.id = p.ban_id
        SET h.ten_khach = COALESCE(NULLIF(b.phien_ten_khach, ''), h.ten_khach),
            h.sdt_khach = COALESCE(NULLIF(b.phien_sdt_khach, ''), h.sdt_khach),
            h.so_nguoi_lon = b.phien_nguoi_lon,
            h.so_tre_em = b.phien_tre_em,
            h.tong_tien = b.phien_tong_tien
        WHERE p.ban_id = ?
          AND p.trang_thai = 'dang_dung'
        ", array((int)$id));

        if ($phuongThucThanhToan !== '') {
            $this->db->query("
            INSERT INTO thanh_toan_phien (hoa_don_phien_id, phuong_thuc, thanh_toan_luc)
            SELECT h.id, ?, NOW()
            FROM hoa_don_phien h
            JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
            WHERE p.ban_id = ?
              AND p.trang_thai = 'dang_dung'
            ON DUPLICATE KEY UPDATE
                phuong_thuc = VALUES(phuong_thuc),
                thanh_toan_luc = VALUES(thanh_toan_luc)
            ", array($phuongThucThanhToan, (int)$id));
        }

        $sql = "
        UPDATE phien_goi_mon
        SET trang_thai = ?,
            ket_thuc_luc = NOW()
        WHERE ban_id = ?
          AND trang_thai = 'dang_dung'
        ";
        return $this->db->query($sql, array($trangThaiPhien, (int)$id));
    }

    public function xoaPhienGoiMon($id, $phuongThucThanhToan = '')
    {
        $this->damBaoMaPhienBan();
        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc', $phuongThucThanhToan);

        $sql = "
        UPDATE ban
        SET ma_phien_goi_mon = NULL,
            ma_phien_het_han = NULL,
            phien_ten_khach = NULL,
            phien_sdt_khach = NULL,
            phien_nguoi_lon = 0,
            phien_tre_em = 0,
            phien_tong_tien = 0,
            phien_bat_dau = NULL
        WHERE id = ?";
        return $this->db->query($sql, array((int)$id));
    }

    public function layDoanhThuPhienGanNhatTheoBan($id)
    {
        $this->damBaoBangPhienGoiMon();

        $sql = "
        SELECT h.tong_tien, h.so_nguoi_lon, h.so_tre_em, h.ten_khach, h.sdt_khach,
               p.id AS phien_goi_mon_id, p.ket_thuc_luc
        FROM phien_goi_mon p
        JOIN hoa_don_phien h ON h.phien_goi_mon_id = p.id
        WHERE p.ban_id = ?
          AND p.trang_thai = 'da_ket_thuc'
        ORDER BY p.ket_thuc_luc DESC, p.id DESC
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array((int)$id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTongHoaDonTichDiemTheoSDTNgay($sdt, $ngay)
    {
        $this->damBaoBangPhienGoiMon();

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        $sql = "
        SELECT
            COALESCE(SUM(x.tong_tien), 0) AS tong_tien,
            COUNT(*) AS so_hoa_don,
            MIN(x.ngay_tao) AS tu_luc,
            MAX(x.ngay_tao) AS den_luc,
            MAX(x.ten_khach) AS ten_khach,
            MAX(x.sdt_khach) AS sdt_khach
        FROM (
            SELECT
                h.id,
                h.ten_khach,
                h.sdt_khach,
                h.tong_tien,
                h.ngay_tao,
                t.tich_diem_luc
            FROM hoa_don_phien h
            JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
            LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
            WHERE h.sdt_khach = ?
              AND (
                  DATE(h.ngay_tao) = ?
                  OR DATE(p.bat_dau_luc) = ?
                  OR DATE(IFNULL(p.ket_thuc_luc, h.ngay_tao)) = ?
              )
              AND h.tong_tien > 0
              AND (t.id IS NULL OR t.tich_diem_luc IS NULL)

            UNION

            SELECT
                0 AS id,
                b.phien_ten_khach AS ten_khach,
                b.phien_sdt_khach AS sdt_khach,
                b.phien_tong_tien AS tong_tien,
                IFNULL(b.phien_bat_dau, NOW()) AS ngay_tao,
                NULL AS tich_diem_luc
            FROM ban b
            WHERE b.phien_sdt_khach = ?
              AND DATE(IFNULL(b.phien_bat_dau, NOW())) = ?
              AND b.phien_tong_tien > 0
              AND b.ma_phien_goi_mon IS NOT NULL
        ) x
        ";

        $rows = $this->db->query($sql, array($sdt, $ngay, $ngay, $ngay, $sdt, $ngay));
        if (empty($rows)) {
            return array(
                'tong_tien' => 0,
                'so_hoa_don' => 0,
                'diem_quy_doi' => 0,
                'ngay' => $ngay
            );
        }

        $row = $rows[0];
        $tongTien = isset($row['tong_tien']) ? (int)$row['tong_tien'] : 0;
        $row['tong_tien'] = $tongTien;
        $row['so_hoa_don'] = isset($row['so_hoa_don']) ? (int)$row['so_hoa_don'] : 0;
        $row['diem_quy_doi'] = (int)floor($tongTien / 10000);
        $row['ngay'] = $ngay;

        return $row;
    }

    public function layHoaDonChuaSdtTheoNgay($ngay)
    {
        $this->damBaoBangPhienGoiMon();

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        $sql = "
        SELECT
            h.id,
            h.ten_khach,
            h.sdt_khach,
            h.tong_tien,
            h.ngay_tao,
            p.ma_phien,
            p.ban_id,
            b.so_ban
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        LEFT JOIN ban b ON b.id = p.ban_id
        WHERE (h.sdt_khach IS NULL OR h.sdt_khach = '')
          AND (
              DATE(h.ngay_tao) = ?
              OR DATE(p.bat_dau_luc) = ?
              OR DATE(IFNULL(p.ket_thuc_luc, h.ngay_tao)) = ?
          )
          AND h.tong_tien > 0
          AND (t.id IS NULL OR t.tich_diem_luc IS NULL)
        ORDER BY h.ngay_tao DESC, h.id DESC
        ";

        $rows = $this->db->query($sql, array($ngay, $ngay, $ngay));
        foreach ($rows as $i => $row) {
            $tongTien = isset($row['tong_tien']) ? (int)$row['tong_tien'] : 0;
            $rows[$i]['tong_tien'] = $tongTien;
            $rows[$i]['diem_quy_doi'] = (int)floor($tongTien / 10000);
        }

        return $rows;
    }

    public function layHoaDonChuaTichDiemTheoId($id)
    {
        $this->damBaoBangPhienGoiMon();

        $sql = "
        SELECT h.*, p.ma_phien, p.ban_id
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        WHERE h.id = ?
          AND h.tong_tien > 0
          AND (t.id IS NULL OR t.tich_diem_luc IS NULL)
        LIMIT 1
        ";

        $rows = $this->db->query($sql, array((int)$id));
        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        $tongTien = isset($row['tong_tien']) ? (int)$row['tong_tien'] : 0;
        $row['tong_tien'] = $tongTien;
        $row['diem_quy_doi'] = (int)floor($tongTien / 10000);

        return $row;
    }

    public function capNhatKhachHoaDon($id, $tenKhach, $sdt)
    {
        $this->damBaoBangPhienGoiMon();

        $sql = "
        UPDATE hoa_don_phien
        SET ten_khach = ?,
            sdt_khach = ?
        WHERE id = ?
          AND id NOT IN (
              SELECT hoa_don_phien_id
              FROM thanh_toan_phien
              WHERE tich_diem_luc IS NOT NULL
          )
        ";

        return $this->db->query($sql, array($tenKhach, $sdt, (int)$id));
    }

    public function taoHoaDonTuPhienDangDungTheoSDTNgay($sdt, $ngay)
    {
        $this->damBaoBangPhienGoiMon();

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        $this->db->query("
        UPDATE hoa_don_phien h
        JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
        JOIN ban b ON b.ma_phien_goi_mon = p.ma_phien
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        SET h.ten_khach = COALESCE(NULLIF(b.phien_ten_khach, ''), h.ten_khach),
            h.sdt_khach = COALESCE(NULLIF(b.phien_sdt_khach, ''), h.sdt_khach),
            h.so_nguoi_lon = b.phien_nguoi_lon,
            h.so_tre_em = b.phien_tre_em,
            h.tong_tien = b.phien_tong_tien
        WHERE b.phien_sdt_khach = ?
          AND DATE(IFNULL(b.phien_bat_dau, NOW())) = ?
          AND b.phien_tong_tien > 0
          AND (t.id IS NULL OR t.tich_diem_luc IS NULL)
        ", array($sdt, $ngay));

        $rows = $this->db->query("
        SELECT
            b.id AS ban_id,
            b.phien_ten_khach,
            b.phien_sdt_khach,
            b.phien_nguoi_lon,
            b.phien_tre_em,
            b.phien_tong_tien,
            p.id AS phien_goi_mon_id
        FROM ban b
        JOIN phien_goi_mon p ON p.ma_phien = b.ma_phien_goi_mon
        WHERE b.phien_sdt_khach = ?
          AND DATE(IFNULL(b.phien_bat_dau, NOW())) = ?
          AND b.phien_tong_tien > 0
          AND p.trang_thai = 'dang_dung'
          AND NOT EXISTS (
              SELECT 1
              FROM hoa_don_phien h
              WHERE h.phien_goi_mon_id = p.id
          )
        ", array($sdt, $ngay));

        foreach ($rows as $row) {
            $this->db->query("
                INSERT INTO hoa_don_phien
                    (phien_goi_mon_id, ten_khach, sdt_khach, so_nguoi_lon, so_tre_em, tong_tien)
                VALUES (?, ?, ?, ?, ?, ?)
            ", array(
                (int)$row['phien_goi_mon_id'],
                $row['phien_ten_khach'],
                $row['phien_sdt_khach'],
                (int)$row['phien_nguoi_lon'],
                (int)$row['phien_tre_em'],
                (int)$row['phien_tong_tien']
            ));
        }

        return !empty($rows);
    }

    public function danhDauHoaDonDaTichDiem($sdt, $ngay, $taiKhoanId, $diem)
    {
        $this->damBaoBangPhienGoiMon();

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        $sql = "
        INSERT INTO thanh_toan_phien
            (hoa_don_phien_id, tich_diem_luc, tich_diem_tai_khoan_id, diem_da_cong)
        SELECT h.id, NOW(), ?, ?
        FROM hoa_don_phien h
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        WHERE h.sdt_khach = ?
          AND DATE(h.ngay_tao) = ?
          AND h.tong_tien > 0
          AND (t.id IS NULL OR t.tich_diem_luc IS NULL)
        ON DUPLICATE KEY UPDATE
            tich_diem_luc = VALUES(tich_diem_luc),
            tich_diem_tai_khoan_id = VALUES(tich_diem_tai_khoan_id),
            diem_da_cong = VALUES(diem_da_cong)
        ";

        return $this->db->query($sql, array((int)$taiKhoanId, (int)$diem, $sdt, $ngay));
    }

    public function danhDauMotHoaDonDaTichDiem($id, $taiKhoanId, $diem)
    {
        $this->damBaoBangPhienGoiMon();

        $sql = "
        INSERT INTO thanh_toan_phien
            (hoa_don_phien_id, tich_diem_luc, tich_diem_tai_khoan_id, diem_da_cong)
        SELECT h.id, NOW(), ?, ?
        FROM hoa_don_phien h
        LEFT JOIN thanh_toan_phien t ON t.hoa_don_phien_id = h.id
        WHERE h.id = ?
          AND h.tong_tien > 0
          AND (t.id IS NULL OR t.tich_diem_luc IS NULL)
        ON DUPLICATE KEY UPDATE
            tich_diem_luc = VALUES(tich_diem_luc),
            tich_diem_tai_khoan_id = VALUES(tich_diem_tai_khoan_id),
            diem_da_cong = VALUES(diem_da_cong)
        ";

        return $this->db->query($sql, array((int)$taiKhoanId, (int)$diem, (int)$id));
    }

    public function layTheoMaCoDinh($ma)
    {
        $sql  = "SELECT * FROM ban WHERE ma_truy_cap = ? LIMIT 1";
        $rows = $this->db->query($sql, array($ma));
        return !empty($rows) ? $rows[0] : null;
    }

    public function demTatCa()
    {
        $sql  = "SELECT COUNT(*) AS tong FROM ban";
        $rows = $this->db->query($sql);
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function capNhatTrangThai($id, $trang_thai, $phuongThucThanhToan = '')
    {
        $this->damBaoMaPhienBan();

        $sql = "UPDATE ban SET trang_thai = ? WHERE id = ?";
        $ok = $this->db->query($sql, array($trang_thai, (int)$id));
        if ($ok && $trang_thai !== 'dang_dung') {
            $this->xoaPhienGoiMon($id, $phuongThucThanhToan);
        }
        return $ok;
    }

    public function layBanTrong($ngay, $gio)
    {
        $this->damBaoBangGanBan();

        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT b.* FROM ban b
        WHERE b.id NOT IN (
            SELECT db2.ban_id FROM dat_ban db2
            WHERE db2.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db2.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db2.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
              AND db2.ban_id IS NOT NULL
        )
        AND b.id NOT IN (
            SELECT dbb.ban_id FROM chitiet_datban dbb
            JOIN dat_ban db2 ON db2.id = dbb.dat_ban_id
            WHERE db2.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db2.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db2.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
        )
        ORDER BY b.so_ban
        ";
        return $this->db->query($sql, array($ngay, $gio, $sessionSeconds, $ngay, $gio, $sessionSeconds));
    }

    // Tim ban nho nhat phu hop voi so khach, chua bi dat trong phien 90 phut
    // Tra ve row ban hoac null neu khong co ban trong
    public function timBanPhuHop($ngay, $gio, $so_khach, $bo_qua_dat_ban_id)
    {
        $this->damBaoBangGanBan();

        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT b.*
        FROM ban b
        WHERE b.suc_chua >= ?
          AND b.id NOT IN (
              SELECT db2.ban_id
              FROM dat_ban db2
              WHERE db2.ngay_dat = ?
                AND ABS(TIME_TO_SEC(db2.gio_dat) - TIME_TO_SEC(?)) < ?
                AND db2.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
                AND db2.ban_id IS NOT NULL
                AND db2.id <> ?
          )
          AND b.id NOT IN (
              SELECT dbb.ban_id
              FROM chitiet_datban dbb
              JOIN dat_ban db2 ON db2.id = dbb.dat_ban_id
              WHERE db2.ngay_dat = ?
                AND ABS(TIME_TO_SEC(db2.gio_dat) - TIME_TO_SEC(?)) < ?
                AND db2.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
                AND db2.id <> ?
          )
        ORDER BY b.suc_chua ASC, b.so_ban ASC
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array(
            (int)$so_khach,
            $ngay,
            $gio,
            $sessionSeconds,
            (int)$bo_qua_dat_ban_id,
            $ngay,
            $gio,
            $sessionSeconds,
            (int)$bo_qua_dat_ban_id
        ));
        return !empty($rows) ? $rows[0] : null;
    }

    public function timToHopBanPhuHop($ngay, $gio, $so_khach, $bo_qua_dat_ban_id)
    {
        $this->damBaoBangGanBan();

        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT b.*
        FROM ban b
        WHERE b.id NOT IN (
              SELECT db2.ban_id
              FROM dat_ban db2
              WHERE db2.ngay_dat = ?
                AND ABS(TIME_TO_SEC(db2.gio_dat) - TIME_TO_SEC(?)) < ?
                AND db2.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
                AND db2.ban_id IS NOT NULL
                AND db2.id <> ?
          )
          AND b.id NOT IN (
              SELECT dbb.ban_id
              FROM chitiet_datban dbb
              JOIN dat_ban db2 ON db2.id = dbb.dat_ban_id
              WHERE db2.ngay_dat = ?
                AND ABS(TIME_TO_SEC(db2.gio_dat) - TIME_TO_SEC(?)) < ?
                AND db2.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
                AND db2.id <> ?
          )
        ORDER BY b.suc_chua ASC, b.so_ban ASC
        ";
        $banTrong = $this->db->query($sql, array(
            $ngay,
            $gio,
            $sessionSeconds,
            (int)$bo_qua_dat_ban_id,
            $ngay,
            $gio,
            $sessionSeconds,
            (int)$bo_qua_dat_ban_id
        ));

        $best = array();
        $bestCapacity = null;
        $bestCount = null;
        $n = count($banTrong);

        for ($mask = 1; $mask < (1 << $n); $mask++) {
            $combo = array();
            $capacity = 0;

            for ($i = 0; $i < $n; $i++) {
                if ($mask & (1 << $i)) {
                    $combo[] = $banTrong[$i];
                    $capacity += (int)$banTrong[$i]['suc_chua'];
                }
            }

            if ($capacity >= (int)$so_khach) {
                $count = count($combo);
                if (
                    $bestCapacity === null ||
                    $capacity < $bestCapacity ||
                    ($capacity == $bestCapacity && $count < $bestCount)
                ) {
                    $best = $combo;
                    $bestCapacity = $capacity;
                    $bestCount = $count;
                }
            }
        }

        return $best;
    }
}

// ================= MO HINH DON MON =================
