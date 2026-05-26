<?php
// Models.php - PHP 5.3 compatible
// Tat ca class dung MoHinhCo, truy van dung nhat bang tai_khoan

require_once dirname(__FILE__) . '/MoHinhCo.php';

// ================= MO HINH BAN =================
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

        $this->damBaoBangDoanhThuNgay();
        $this->damBaoCotTichDiemHoaDonPhien();
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
        $this->damBaoCotHoaDonPhien('tich_diem_luc', "ALTER TABLE hoa_don_phien ADD COLUMN tich_diem_luc datetime DEFAULT NULL");
        $this->damBaoCotHoaDonPhien('tich_diem_tai_khoan_id', "ALTER TABLE hoa_don_phien ADD COLUMN tich_diem_tai_khoan_id int(11) DEFAULT NULL");
        $this->damBaoCotHoaDonPhien('diem_da_cong', "ALTER TABLE hoa_don_phien ADD COLUMN diem_da_cong int(11) NOT NULL DEFAULT 0");
    }

    private function damBaoBangDoanhThuNgay()
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
    }

    private function congDoanhThuNgay($ngay, $soKhach, $doanhThu)
    {
        $this->damBaoBangDoanhThuNgay();

        $this->db->query("
        INSERT INTO doanh_thu_ngay (ngay, so_khach, so_phien, doanh_thu, nguon, ngay_cap_nhat)
        VALUES (?, ?, 1, ?, 'he_thong', NOW())
        ON DUPLICATE KEY UPDATE
            so_khach = so_khach + VALUES(so_khach),
            so_phien = so_phien + 1,
            doanh_thu = doanh_thu + VALUES(doanh_thu),
            nguon = 'he_thong',
            ngay_cap_nhat = NOW()
        ", array($ngay, (int)$soKhach, (float)$doanhThu));
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

    private function capNhatKetThucPhienHienTai($id, $trangThaiPhien)
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

        $phienDoanhThu = array();
        if ($trangThaiPhien === 'da_ket_thuc') {
            $phienDoanhThu = $this->db->query("
            SELECT DATE(NOW()) AS ngay,
                   h.tong_tien,
                   (h.so_nguoi_lon + h.so_tre_em) AS so_khach
            FROM phien_goi_mon p
            JOIN hoa_don_phien h ON h.phien_goi_mon_id = p.id
            WHERE p.ban_id = ?
              AND p.trang_thai = 'dang_dung'
            ", array((int)$id));
        }

        $sql = "
        UPDATE phien_goi_mon
        SET trang_thai = ?,
            ket_thuc_luc = NOW()
        WHERE ban_id = ?
          AND trang_thai = 'dang_dung'
        ";
        $ok = $this->db->query($sql, array($trangThaiPhien, (int)$id));

        if ($ok && $trangThaiPhien === 'da_ket_thuc') {
            foreach ($phienDoanhThu as $dong) {
                $this->congDoanhThuNgay(
                    $dong['ngay'],
                    (int)$dong['so_khach'],
                    (float)$dong['tong_tien']
                );
            }
        }

        return $ok;
    }

    public function xoaPhienGoiMon($id)
    {
        $this->damBaoMaPhienBan();
        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc');

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
                h.tich_diem_luc
            FROM hoa_don_phien h
            JOIN phien_goi_mon p ON p.id = h.phien_goi_mon_id
            WHERE h.sdt_khach = ?
              AND (
                  DATE(h.ngay_tao) = ?
                  OR DATE(p.bat_dau_luc) = ?
                  OR DATE(IFNULL(p.ket_thuc_luc, h.ngay_tao)) = ?
              )
              AND h.tong_tien > 0
              AND h.tich_diem_luc IS NULL

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
        LEFT JOIN ban b ON b.id = p.ban_id
        WHERE (h.sdt_khach IS NULL OR h.sdt_khach = '')
          AND (
              DATE(h.ngay_tao) = ?
              OR DATE(p.bat_dau_luc) = ?
              OR DATE(IFNULL(p.ket_thuc_luc, h.ngay_tao)) = ?
          )
          AND h.tong_tien > 0
          AND h.tich_diem_luc IS NULL
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
        WHERE h.id = ?
          AND h.tong_tien > 0
          AND h.tich_diem_luc IS NULL
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
          AND tich_diem_luc IS NULL
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
        SET h.ten_khach = COALESCE(NULLIF(b.phien_ten_khach, ''), h.ten_khach),
            h.sdt_khach = COALESCE(NULLIF(b.phien_sdt_khach, ''), h.sdt_khach),
            h.so_nguoi_lon = b.phien_nguoi_lon,
            h.so_tre_em = b.phien_tre_em,
            h.tong_tien = b.phien_tong_tien
        WHERE b.phien_sdt_khach = ?
          AND DATE(IFNULL(b.phien_bat_dau, NOW())) = ?
          AND b.phien_tong_tien > 0
          AND h.tich_diem_luc IS NULL
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
        UPDATE hoa_don_phien
        SET tich_diem_luc = NOW(),
            tich_diem_tai_khoan_id = ?,
            diem_da_cong = ?
        WHERE sdt_khach = ?
          AND DATE(ngay_tao) = ?
          AND tong_tien > 0
          AND tich_diem_luc IS NULL
        ";

        return $this->db->query($sql, array((int)$taiKhoanId, (int)$diem, $sdt, $ngay));
    }

    public function danhDauMotHoaDonDaTichDiem($id, $taiKhoanId, $diem)
    {
        $this->damBaoBangPhienGoiMon();

        $sql = "
        UPDATE hoa_don_phien
        SET tich_diem_luc = NOW(),
            tich_diem_tai_khoan_id = ?,
            diem_da_cong = ?
        WHERE id = ?
          AND tong_tien > 0
          AND tich_diem_luc IS NULL
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

    public function capNhatTrangThai($id, $trang_thai)
    {
        $this->damBaoMaPhienBan();

        $sql = "UPDATE ban SET trang_thai = ? WHERE id = ?";
        $ok = $this->db->query($sql, array($trang_thai, (int)$id));
        if ($ok && $trang_thai !== 'dang_dung') {
            $this->xoaPhienGoiMon($id);
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
}

// ================= MO HINH MON AN =================
class MoHinhMonAn extends MoHinhCo
{
    private function chuanHoaDanhMuc($danh_muc)
    {
        $danh_muc = trim((string)$danh_muc);
        $key = function_exists('mb_strtolower')
            ? mb_strtolower($danh_muc, 'UTF-8')
            : strtolower($danh_muc);

        $map = array(
            'khai vi' => 'Khai vi',
            'khai vị' => 'Khai vi',
            'mon chinh' => 'Mon chinh',
            'món chính' => 'Mon chinh',
            'nuoc lau' => 'Nuoc lau',
            'nước lẩu' => 'Nuoc lau',
            'topping' => 'Topping',
            'rau' => 'Rau',
            'do uong' => 'Do uong',
            'đồ uống' => 'Do uong',
        );

        return isset($map[$key]) ? $map[$key] : $danh_muc;
    }

    private function tenHienThiDanhMuc($danh_muc)
    {
        $labels = array(
            'Khai vi' => 'Khai vị',
            'Mon chinh' => 'Món chính',
            'Nuoc lau' => 'Nước lẩu',
            'Topping' => 'Topping',
            'Rau' => 'Rau',
            'Do uong' => 'Đồ uống',
        );

        $maDanhMuc = $this->chuanHoaDanhMuc($danh_muc);
        return isset($labels[$maDanhMuc]) ? $labels[$maDanhMuc] : $maDanhMuc;
    }

    private function ganTenHienThiDanhMuc($rows)
    {
        for ($i = 0; $i < count($rows); $i++) {
            if (isset($rows[$i]['danh_muc'])) {
                $rows[$i]['danh_muc'] = $this->tenHienThiDanhMuc($rows[$i]['danh_muc']);
            }
        }
        return $rows;
    }

    public function layTatCa()
    {
        $sql = "SELECT * FROM mon_an ORDER BY danh_muc, ten";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql));
    }

    public function layNhomTheoDanhMuc()
    {
        $sql  = "SELECT * FROM mon_an WHERE con_mon = 1 ORDER BY danh_muc, ten";
        $rows = $this->ganTenHienThiDanhMuc($this->db->query($sql));

        $nhom = array();
        foreach ($rows as $mon) {
            $dm = $mon['danh_muc'];
            if (!isset($nhom[$dm])) {
                $nhom[$dm] = array();
            }
            $nhom[$dm][] = $mon;
        }
        return $nhom;
    }

    public function demTatCa()
    {
        $sql  = "SELECT COUNT(*) AS tong FROM mon_an";
        $rows = $this->db->query($sql);
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function luu($du_lieu)
    {
        $id       = isset($du_lieu['id'])       ? (int)$du_lieu['id']        : 0;
        $ten      = isset($du_lieu['ten'])       ? trim($du_lieu['ten'])      : '';
        $mo_ta    = isset($du_lieu['mo_ta'])     ? trim($du_lieu['mo_ta'])    : '';
        $danh_muc = isset($du_lieu['danh_muc'])  ? $this->chuanHoaDanhMuc($du_lieu['danh_muc']) : '';
        $anh_url  = isset($du_lieu['anh_url'])   ? trim($du_lieu['anh_url'])  : '';
        $con_mon  = isset($du_lieu['con_mon'])   ? (int)$du_lieu['con_mon']   : 1;
        $noi_bat  = isset($du_lieu['noi_bat'])   ? (int)$du_lieu['noi_bat']   : 0;

        if ($id > 0) {
            $sql = "
            UPDATE mon_an
            SET ten=?, mo_ta=?, danh_muc=?, anh_url=?, con_mon=?, noi_bat=?
            WHERE id=?
            ";
            return $this->db->query($sql, array(
                $ten,
                $mo_ta,
                $danh_muc,
                $anh_url,
                $con_mon,
                $noi_bat,
                $id
            ));
        } else {
            $sql = "
            INSERT INTO mon_an (ten, mo_ta, danh_muc, anh_url, con_mon, noi_bat)
            VALUES (?, ?, ?, ?, ?, ?)
            ";
            return $this->db->query($sql, array(
                $ten,
                $mo_ta,
                $danh_muc,
                $anh_url,
                $con_mon,
                $noi_bat
            ));
        }
    }

    public function layNoiBat($gioi_han)
    {
        $sql = "
        SELECT * FROM mon_an
        WHERE con_mon = 1
        ORDER BY noi_bat DESC, ten ASC
        LIMIT ?
        ";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql, array((int)$gioi_han)));
    }

    public function xoa($id)
    {
        $sql = "DELETE FROM mon_an WHERE id = ?";
        return $this->db->query($sql, array((int)$id));
    }

    public function timKiem($tu_khoa)
    {
        $sql = "
        SELECT * FROM mon_an
        WHERE con_mon = 1 AND ten LIKE ?
        ORDER BY danh_muc, ten
        ";
        return $this->ganTenHienThiDanhMuc($this->db->query($sql, array('%' . $tu_khoa . '%')));
    }
}

// ================= MO HINH DAT BAN =================
class MoHinhDatBan extends MoHinhCo
{
    private function damBaoTrangThaiDatBan()
    {
        $this->db->query("
            ALTER TABLE dat_ban
            MODIFY trang_thai enum('cho_xac_nhan','da_xac_nhan','da_huy','cancelled','expired','hoan_thanh')
            DEFAULT 'cho_xac_nhan'
        ");
    }

    private function damBaoBangGanBan()
    {
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

        $this->dongBoBanDaiDien();
    }

    private function dongBoBanDaiDien()
    {
        $this->db->query("
        UPDATE dat_ban db
        LEFT JOIN (
            SELECT dat_ban_id, MIN(ban_id) AS ban_id
            FROM chitiet_datban
            GROUP BY dat_ban_id
        ) ct ON ct.dat_ban_id = db.id
        SET db.ban_id = ct.ban_id
        WHERE ct.ban_id IS NOT NULL
          AND (db.ban_id IS NULL OR db.ban_id <> ct.ban_id)
        ");
    }

    private function selectDatBanVoiBan()
    {
        return "
        SELECT r.*,
               CASE
                   WHEN r.trang_thai IN ('da_huy', 'cancelled', 'expired', 'hoan_thanh') THEN NULL
                   ELSE COALESCE(
                       GROUP_CONCAT(DISTINCT bm.so_ban ORDER BY bm.so_ban SEPARATOR ', '),
                       bo.so_ban
                   )
               END AS so_ban,
               CASE
                   WHEN r.trang_thai IN ('da_huy', 'cancelled', 'expired', 'hoan_thanh') THEN NULL
                   ELSE COALESCE(
                       GROUP_CONCAT(DISTINCT bm.id ORDER BY bm.so_ban SEPARATOR ','),
                       r.ban_id
                   )
               END AS ban_ids,
               CASE
                   WHEN r.trang_thai IN ('da_huy', 'cancelled', 'expired', 'hoan_thanh') THEN 0
                   WHEN r.ban_xac_nhan = 1 THEN 1
                   ELSE 0
               END AS ban_da_xac_nhan
        FROM dat_ban r
        LEFT JOIN ban bo ON r.ban_id = bo.id
        LEFT JOIN chitiet_datban dbb ON dbb.dat_ban_id = r.id
        LEFT JOIN ban bm ON bm.id = dbb.ban_id
        ";
    }

    // Tao ma dat ban duy nhat: RES-YYYYMMDD-XXXXX
    private function taoMaDatBan()
    {
        $ngay = date('Ymd');
        do {
            $ngau_nhien = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $ma = 'RES-' . $ngay . '-' . $ngau_nhien;
        } while ($this->maDaTonTai($ma));
        return $ma;
    }

    private function maDaTonTai($ma)
    {
        $sql  = "SELECT COUNT(*) AS cnt FROM dat_ban WHERE ma_dat_ban = ? LIMIT 1";
        $rows = $this->db->query($sql, array($ma));
        return !empty($rows) && (int)$rows[0]['cnt'] > 0;
    }

    public function layTatCa()
    {
        $this->damBaoBangGanBan();

        $sql = $this->selectDatBanVoiBan() . "
        GROUP BY r.id
        ORDER BY r.ngay_dat DESC, r.gio_dat DESC
        ";
        return $this->db->query($sql);
    }

    public function timKiem($tu_khoa)
    {
        $this->damBaoBangGanBan();

        $k   = '%' . $tu_khoa . '%';
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.ten_khach LIKE ? OR r.sdt_khach LIKE ?
        GROUP BY r.id
        ORDER BY r.ngay_dat DESC
        ";
        return $this->db->query($sql, array($k, $k));
    }

    public function locTheoTrangThai($trang_thai)
    {
        $this->damBaoBangGanBan();

        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.trang_thai = ?
        GROUP BY r.id
        ORDER BY r.ngay_dat DESC
        ";
        return $this->db->query($sql, array($trang_thai));
    }

    public function layTheoId($id)
    {
        $sql = "SELECT * FROM dat_ban WHERE id = ? LIMIT 1";
        $rows = $this->db->query($sql, array((int)$id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function banBiTrungLich($ban_id, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $this->damBaoBangGanBan();

        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT COUNT(*) AS tong FROM (
            SELECT db.id
            FROM dat_ban db
            WHERE db.ban_id = ?
              AND db.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db.id <> ?
              AND db.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
            UNION
            SELECT db.id
            FROM chitiet_datban dbb
            JOIN dat_ban db ON db.id = dbb.dat_ban_id
            WHERE dbb.ban_id = ?
              AND db.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db.id <> ?
              AND db.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        ) x
        ";
        $rows = $this->db->query($sql, array(
            (int)$ban_id,
            $ngay_dat,
            $gio_dat,
            $sessionSeconds,
            (int)$bo_qua_id,
            (int)$ban_id,
            $ngay_dat,
            $gio_dat,
            $sessionSeconds,
            (int)$bo_qua_id
        ));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function tongKhachTrungLich($ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT COALESCE(SUM(so_nguoi_lon + so_tre_em), 0) AS tong_khach
        FROM dat_ban
        WHERE ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND id <> ?
          AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        ";
        $rows = $this->db->query($sql, array($ngay_dat, $gio_dat, $sessionSeconds, (int)$bo_qua_id));
        return !empty($rows) ? (int)$rows[0]['tong_khach'] : 0;
    }

    public function tongKhachDaXacNhanTrungLich($ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT COALESCE(SUM(so_nguoi_lon + so_tre_em), 0) AS tong_khach
        FROM dat_ban
        WHERE ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND id <> ?
          AND trang_thai = 'da_xac_nhan'
        ";
        $rows = $this->db->query($sql, array($ngay_dat, $gio_dat, $sessionSeconds, (int)$bo_qua_id));
        return !empty($rows) ? (int)$rows[0]['tong_khach'] : 0;
    }

    public function khachDaXacNhanTrungLich($sdt_khach, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT COUNT(*) AS tong
        FROM dat_ban
        WHERE sdt_khach = ?
          AND ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND id <> ?
          AND trang_thai = 'da_xac_nhan'
        ";
        $rows = $this->db->query($sql, array(
            $sdt_khach,
            $ngay_dat,
            $gio_dat,
            $sessionSeconds,
            (int)$bo_qua_id
        ));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function khachBiTrungLich($sdt_khach, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT COUNT(*) AS tong
        FROM dat_ban
        WHERE sdt_khach = ?
          AND ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND id <> ?
          AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        ";
        $rows = $this->db->query($sql, array(
            $sdt_khach,
            $ngay_dat,
            $gio_dat,
            $sessionSeconds,
            (int)$bo_qua_id
        ));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function banDaXacNhanTrungLich($ban_id, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $this->damBaoBangGanBan();

        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = "
        SELECT COUNT(*) AS tong FROM (
            SELECT db.id
            FROM dat_ban db
            WHERE db.ban_id = ?
              AND db.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db.id <> ?
              AND db.trang_thai = 'da_xac_nhan'
            UNION
            SELECT db.id
            FROM chitiet_datban dbb
            JOIN dat_ban db ON db.id = dbb.dat_ban_id
            WHERE dbb.ban_id = ?
              AND db.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db.id <> ?
              AND db.trang_thai = 'da_xac_nhan'
        ) x
        ";
        $rows = $this->db->query($sql, array(
            (int)$ban_id,
            $ngay_dat,
            $gio_dat,
            $sessionSeconds,
            (int)$bo_qua_id,
            (int)$ban_id,
            $ngay_dat,
            $gio_dat,
            $sessionSeconds,
            (int)$bo_qua_id
        ));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function layGanDay($gioi_han)
    {
        $this->damBaoBangGanBan();

        $sql = $this->selectDatBanVoiBan() . "
        GROUP BY r.id
        ORDER BY r.ngay_tao DESC
        LIMIT ?
        ";
        return $this->db->query($sql, array((int)$gioi_han));
    }

    public function thongKeHomNay()
    {
        $sql  = "
        SELECT
            COUNT(*) AS tong,
            SUM(CASE WHEN trang_thai = 'da_xac_nhan'  THEN 1 ELSE 0 END) AS da_xac_nhan,
            SUM(CASE WHEN trang_thai = 'cho_xac_nhan' THEN 1 ELSE 0 END) AS cho_xac_nhan,
            SUM(CASE WHEN trang_thai IN ('da_huy', 'cancelled') THEN 1 ELSE 0 END) AS da_huy,
            SUM(CASE WHEN trang_thai = 'expired'      THEN 1 ELSE 0 END) AS expired,
            SUM(CASE WHEN trang_thai = 'hoan_thanh'   THEN 1 ELSE 0 END) AS hoan_thanh,
            COALESCE(SUM(tong_tien), 0) AS doanh_thu
        FROM dat_ban
        WHERE ngay_dat = CURDATE()
        ";
        $rows = $this->db->query($sql);
        return !empty($rows) ? $rows[0] : array();
    }

    // Them dat ban moi - tra ve array('id'=>..., 'ma_dat_ban'=>...) hoac false
    public function them($du_lieu)
    {
        $ma_dat_ban = $this->taoMaDatBan();

        $sql = "
        INSERT INTO dat_ban
            (ten_khach, sdt_khach, ngay_dat, gio_dat,
             so_nguoi_lon, so_tre_em, ban_id, ghi_chu, tong_tien, trang_thai, ma_dat_ban)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $ok = $this->db->query($sql, array(
            $du_lieu['ten_khach'],
            $du_lieu['sdt_khach'],
            $du_lieu['ngay_dat'],
            $du_lieu['gio_dat'],
            (int)$du_lieu['so_nguoi_lon'],
            (int)$du_lieu['so_tre_em'],
            $du_lieu['ban_id'],
            $du_lieu['ghi_chu'],
            (float)$du_lieu['tong_tien'],
            $du_lieu['trang_thai'],
            $ma_dat_ban
        ));

        if ($ok) {
            return array(
                'id'         => $this->db->lastInsertId(),
                'ma_dat_ban' => $ma_dat_ban
            );
        }
        return false;
    }

    public function capNhatTrangThai($id, $trang_thai)
    {
        $this->damBaoTrangThaiDatBan();

        $sql = "UPDATE dat_ban SET trang_thai = ? WHERE id = ?";
        $ok = $this->db->query($sql, array($trang_thai, (int)$id));

        if ($ok && ($trang_thai === 'da_huy' || $trang_thai === 'cancelled' || $trang_thai === 'expired' || $trang_thai === 'hoan_thanh')) {
            $this->capNhatNhieuBan($id, array());
        }

        return $ok;
    }

    public function capNhatBan($id, $ban_id)
    {
        $this->damBaoBangGanBan();

        $this->db->query("DELETE FROM chitiet_datban WHERE dat_ban_id = ?", array((int)$id));
        if ((int)$ban_id > 0) {
            $this->db->query(
                "INSERT IGNORE INTO chitiet_datban (dat_ban_id, ban_id) VALUES (?, ?)",
                array((int)$id, (int)$ban_id)
            );
        }

        $sql = "UPDATE dat_ban SET ban_id = ? WHERE id = ?";
        return $this->db->query($sql, array($ban_id > 0 ? (int)$ban_id : null, (int)$id));
    }

    public function capNhatNhieuBan($id, $ban_ids)
    {
        $this->damBaoBangGanBan();

        $id = (int)$id;
        $danhSachHopLe = array();

        foreach ($ban_ids as $ban_id) {
            $ban_id = (int)$ban_id;
            if ($ban_id > 0 && !in_array($ban_id, $danhSachHopLe)) {
                $danhSachHopLe[] = $ban_id;
            }
        }

        $this->db->query("DELETE FROM chitiet_datban WHERE dat_ban_id = ?", array($id));

        $banDauTien = null;
        foreach ($danhSachHopLe as $ban_id) {
            if ($banDauTien === null) {
                $banDauTien = $ban_id;
            }
            $this->db->query(
                "INSERT IGNORE INTO chitiet_datban (dat_ban_id, ban_id) VALUES (?, ?)",
                array($id, $ban_id)
            );
        }

        $sql = "UPDATE dat_ban SET ban_id = ? WHERE id = ?";
        return $this->db->query($sql, array($banDauTien ? $banDauTien : null, $id));
    }

    public function layBanIdsTheoDatBan($id)
    {
        $this->damBaoBangGanBan();

        $rows = $this->db->query(
            "SELECT ban_id FROM chitiet_datban WHERE dat_ban_id = ? ORDER BY id ASC",
            array((int)$id)
        );

        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int)$row['ban_id'];
        }
        return $ids;
    }

    // Nhan vien xac nhan ban duoc gan tu dong la phu hop
    public function xacNhanGanBan($id)
    {
        $this->damBaoTrangThaiDatBan();

        $sql = "UPDATE dat_ban SET ban_xac_nhan = 1, trang_thai = 'da_xac_nhan' WHERE id = ?";
        return $this->db->query($sql, array((int)$id));
    }

    // Nhan vien bo xac nhan (truoc khi doi ban)
    public function boXacNhanGanBan($id)
    {
        $sql = "UPDATE dat_ban SET ban_xac_nhan = 0 WHERE id = ?";
        return $this->db->query($sql, array((int)$id));
    }

    // Lay dat ban chua xac nhan ban (cho nhan vien duyet)
    public function layChuaXacNhanBan()
    {
        $this->damBaoBangGanBan();
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
          AND r.ban_xac_nhan = 0
          AND r.ban_id IS NOT NULL
        GROUP BY r.id
        ORDER BY r.ngay_dat ASC, r.gio_dat ASC
        ";
        return $this->db->query($sql);
    }

    // Lay dat ban theo trang_thai va tu_khoa voi bien da loc doi tuong
    public function layDanhSachLocNang($trang_thai, $tu_khoa, $chi_chua_xac_nhan_ban, $ngay_dat)
    {
        $this->damBaoBangGanBan();

        $where = array();
        $params = array();

        if ($trang_thai !== '') {
            if ($trang_thai === 'cancelled' || $trang_thai === 'da_huy') {
                $where[] = "r.trang_thai IN ('cancelled', 'expired', 'da_huy')";
            } else {
                $where[] = 'r.trang_thai = ?';
                $params[] = $trang_thai;
            }
        } else {
            $where[] = "r.trang_thai NOT IN ('cancelled', 'expired', 'da_huy')";
        }

        if ($tu_khoa !== '') {
            $k = '%' . $tu_khoa . '%';
            $where[] = '(r.ten_khach LIKE ? OR r.sdt_khach LIKE ? OR r.ma_dat_ban LIKE ?)';
            $params[] = $k;
            $params[] = $k;
            $params[] = $k;
        }

        if ($ngay_dat !== '' && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay_dat)) {
            $where[] = 'r.ngay_dat = ?';
            $params[] = $ngay_dat;
        }

        if ($chi_chua_xac_nhan_ban) {
            $where[] = "r.trang_thai = 'cho_xac_nhan'";
        }

        $sql = $this->selectDatBanVoiBan();
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY r.id ORDER BY r.ngay_tao ASC, r.id ASC';

        return $this->db->query($sql, $params);
    }

    public function layThongKeNgayTrongThang($thang)
    {
        $sql = "
        SELECT ngay_dat, COUNT(*) AS tong
        FROM dat_ban
        WHERE DATE_FORMAT(ngay_dat, '%Y-%m') = ?
          AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        GROUP BY ngay_dat
        ORDER BY ngay_dat ASC
        ";
        return $this->db->query($sql, array($thang));
    }

    // Lay dat ban theo ma (khach goi mon sau khi dat ban)
    public function layTheoMaDatBan($ma)
    {
        $sql  = "SELECT * FROM dat_ban WHERE ma_dat_ban = ? LIMIT 1";
        $rows = $this->db->query($sql, array($ma));
        return !empty($rows) ? $rows[0] : null;
    }

    // Lay lich su dat ban cua khach theo SDT (Usecase 111)
    public function layLichSuTheoSDT($sdt)
    {
        $this->damBaoBangGanBan();

        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.sdt_khach = ?
        GROUP BY r.id
        ORDER BY r.ngay_tao DESC
        ";
        return $this->db->query($sql, array($sdt));
    }
}

// ================= MO HINH TAI KHOAN =================
// Chi dung cho quan ly / nhan vien / bep. Khach hang dung bang khach_tai_khoan.
class MoHinhTaiKhoan extends MoHinhCo
{
    private function damBaoVaiTroQuanLy()
    {
        $khachCu = $this->db->query("SELECT COUNT(*) AS tong FROM tai_khoan WHERE vai_tro = 'khach'");
        if (!empty($khachCu) && (int)$khachCu[0]['tong'] > 0) {
            $this->db->query("
            CREATE TABLE IF NOT EXISTS khach_tai_khoan (
                id int(11) NOT NULL auto_increment,
                ten_dang_nhap varchar(50) DEFAULT NULL,
                mat_khau varchar(255) DEFAULT NULL,
                vai_tro enum('khach') NOT NULL default 'khach',
                dang_hoat_dong tinyint(1) NOT NULL default 1,
                ho_ten varchar(100) DEFAULT NULL,
                email varchar(100) DEFAULT NULL,
                so_dien_thoai varchar(20) DEFAULT NULL,
                diem_tich_luy int(11) NOT NULL default 0,
                ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY ten_dang_nhap (ten_dang_nhap),
                UNIQUE KEY so_dien_thoai (so_dien_thoai)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ");

            $this->db->query("
            INSERT IGNORE INTO khach_tai_khoan
                (ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                 ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao)
            SELECT ten_dang_nhap, mat_khau, 'khach', dang_hoat_dong,
                   ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao
            FROM tai_khoan
            WHERE vai_tro = 'khach'
              AND so_dien_thoai IS NOT NULL
              AND so_dien_thoai <> ''
            ");
            return;
        }

        $this->db->query("
            ALTER TABLE tai_khoan
            MODIFY vai_tro enum('quanly','nhanvien','bep') NOT NULL default 'nhanvien'
        ");
    }

    // Dang nhap staff/admin bang ten_dang_nhap
    public function layTheoTenDangNhap($ten_dang_nhap)
    {
        $this->damBaoVaiTroQuanLy();

        $sql  = "SELECT * FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1";
        $rows = $this->db->query($sql, array($ten_dang_nhap));
        return !empty($rows) ? $rows[0] : null;
    }

    public function damBaoTaiKhoanBepMacDinh()
    {
        $this->damBaoVaiTroQuanLy();

        $rows = $this->db->query(
            "SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1",
            array('nhanvien02')
        );

        if (!empty($rows)) {
            return $this->db->query(
                "UPDATE tai_khoan SET mat_khau = ?, vai_tro = 'bep', dang_hoat_dong = 1 WHERE ten_dang_nhap = ?",
                array(md5('123'), 'nhanvien02')
            );
        }

        return $this->db->query(
            "INSERT INTO tai_khoan
                (ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong, ho_ten, email, so_dien_thoai, diem_tich_luy)
             VALUES (?, ?, 'bep', 1, ?, NULL, NULL, 0)",
            array('nhanvien02', md5('123'), 'Nhan vien bep')
        );
    }

    // Tim tai khoan nhan su theo so dien thoai
    public function layTheoSDT($sdt)
    {
        $sql  = "SELECT * FROM tai_khoan WHERE so_dien_thoai = ? LIMIT 1";
        $rows = $this->db->query($sql, array($sdt));
        return !empty($rows) ? $rows[0] : null;
    }

    // Tim tai khoan nhan su theo email
    public function layTheoEmail($email)
    {
        $sql  = "SELECT * FROM tai_khoan WHERE email = ? LIMIT 1";
        $rows = $this->db->query($sql, array($email));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTatCa()
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "SELECT * FROM tai_khoan ORDER BY vai_tro, ngay_tao DESC";
        return $this->db->query($sql);
    }

    public function layNhanVienQuanLy()
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "
        SELECT id, ten_dang_nhap, vai_tro, dang_hoat_dong,
               ho_ten, email, so_dien_thoai, ngay_tao
        FROM tai_khoan
        WHERE vai_tro IN ('quanly', 'nhanvien', 'bep')
        ORDER BY vai_tro, ho_ten, ten_dang_nhap
        ";
        return $this->db->query($sql);
    }

    public function layTheoId($id)
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "SELECT * FROM tai_khoan WHERE id = ? LIMIT 1";
        $rows = $this->db->query($sql, array((int)$id));
        return !empty($rows) ? $rows[0] : null;
    }

    // Khach hang dung MoHinhKhach::dangKy() va bang khach_tai_khoan.
    public function dangKy($ho_ten, $so_dien_thoai, $email, $mat_khau)
    {
        return false;
    }

    // Them nhan vien/admin (Usecase 113)
    public function themNhanVien($du_lieu)
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "
        INSERT INTO tai_khoan
            (ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
             ho_ten, email, so_dien_thoai)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $ok = $this->db->query($sql, array(
            $du_lieu['ten_dang_nhap'],
            md5($du_lieu['mat_khau']),
            $du_lieu['vai_tro'],
            isset($du_lieu['dang_hoat_dong']) ? (int)$du_lieu['dang_hoat_dong'] : 1,
            $du_lieu['ho_ten'],
            $du_lieu['email'],
            $du_lieu['so_dien_thoai']
        ));
        return $ok ? $this->db->lastInsertId() : false;
    }

    public function capNhatNhanVien($id, $du_lieu)
    {
        $this->damBaoVaiTroQuanLy();

        $params = array(
            $du_lieu['ten_dang_nhap'],
            $du_lieu['vai_tro'],
            (int)$du_lieu['dang_hoat_dong'],
            $du_lieu['ho_ten'],
            $du_lieu['email'],
            $du_lieu['so_dien_thoai']
        );

        $sql = "
        UPDATE tai_khoan
        SET ten_dang_nhap = ?,
            vai_tro = ?,
            dang_hoat_dong = ?,
            ho_ten = ?,
            email = ?,
            so_dien_thoai = ?
        ";

        if (isset($du_lieu['mat_khau']) && trim($du_lieu['mat_khau']) !== '') {
            $sql .= ", mat_khau = ?";
            $params[] = md5($du_lieu['mat_khau']);
        }

        $sql .= " WHERE id = ?";
        $params[] = (int)$id;

        return $this->db->query($sql, $params);
    }

    public function xoaNhanVien($id)
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "DELETE FROM tai_khoan WHERE id = ? AND vai_tro IN ('quanly', 'nhanvien', 'bep')";
        return $this->db->query($sql, array((int)$id));
    }

    // Khoa / mo khoa tai khoan (Usecase 115)
    public function capNhatTrangThai($id, $trang_thai)
    {
        $sql = "UPDATE tai_khoan SET dang_hoat_dong = ? WHERE id = ?";
        return $this->db->query($sql, array((int)$trang_thai, (int)$id));
    }

    // Diem tich luy cua khach nam trong khach_tai_khoan.
    public function congDiem($id, $diem)
    {
        return false;
    }

    // Doi mat khau (Usecase 110)
    public function doiMatKhau($id, $mat_khau_moi)
    {
        $sql = "UPDATE tai_khoan SET mat_khau = ? WHERE id = ?";
        return $this->db->query($sql, array(md5($mat_khau_moi), (int)$id));
    }

    // Cap nhat thong tin ca nhan (Usecase 109)
    public function capNhatThongTin($id, $ho_ten, $email)
    {
        $sql = "UPDATE tai_khoan SET ho_ten = ?, email = ? WHERE id = ?";
        return $this->db->query($sql, array($ho_ten, $email, (int)$id));
    }
}

// ================= MO HINH DANH GIA =================
// Usecase 116: Danh gia va binh luan mon an
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
