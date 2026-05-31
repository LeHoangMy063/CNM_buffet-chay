<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhBan extends MoHinhCo
{
    private function damBaoMaPhienBan()
    {
        $cotPhien = array(
            'ma_phien_goi_mon' => "ALTER TABLE ban ADD COLUMN ma_phien_goi_mon nvarchar(30) DEFAULT NULL",
            'ma_phien_het_han' => "ALTER TABLE ban ADD COLUMN ma_phien_het_han datetime DEFAULT NULL",
            'phien_ten_khach' => "ALTER TABLE ban ADD COLUMN phien_ten_khach varchar(100) DEFAULT NULL",
            'phien_sdt_khach' => "ALTER TABLE ban ADD COLUMN phien_sdt_khach varchar(20) DEFAULT NULL",
            'phien_nguoi_lon' => "ALTER TABLE ban ADD COLUMN phien_nguoi_lon int(11) NOT NULL DEFAULT 0",
            'phien_tre_em' => "ALTER TABLE ban ADD COLUMN phien_tre_em int(11) NOT NULL DEFAULT 0",
            'phien_tong_tien' => "ALTER TABLE ban ADD COLUMN phien_tong_tien decimal(12,0) NOT NULL DEFAULT 0",
            'phien_bat_dau' => "ALTER TABLE ban ADD COLUMN phien_bat_dau datetime DEFAULT NULL"
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

        $this->capNhatPhienHetHan();
    }

    private function taoIdHoaDon()
    {
        return $this->taoId('HD', 5, true);
    }

    private function taoIdThanhToan()
    {
        return $this->taoId('TT', 5, true);
    }

    private function capNhatPhienHetHan()
    {
        $this->db->query("
        UPDATE ban b
        JOIN phien_ban pb ON pb.id_ban = b.id_ban
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = pb.id_phien_goi_mon
                            AND p.id_phien_goi_mon = b.ma_phien_goi_mon
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

    private function selectBan()
    {
        return "
    SELECT
        b.id_ban AS id,
        b.so_ban,
        b.khu_vuc,
        b.suc_chua,
        b.ma_truy_cap,
        b.ma_phien_goi_mon,
        b.ma_phien_het_han,
        b.phien_ten_khach,
        b.phien_sdt_khach,
        b.phien_nguoi_lon,
        b.phien_tre_em,
        b.phien_tong_tien,
        b.phien_bat_dau,
        CASE
            WHEN b.ma_phien_goi_mon IS NOT NULL
             AND b.ma_phien_het_han IS NOT NULL
             AND b.ma_phien_het_han > NOW()
            THEN 1 ELSE 0
        END AS ma_phien_con_han,
        CASE WHEN b.trang_thai = 'dang_dung' THEN 'dang_dung' ELSE 'trong' END AS trang_thai_goc,
        IFNULL(dm.so_don_cho, 0) AS so_don_cho,

        rs.dat_ban_id AS dat_ban_sap_toi_id,
        rs.dat_ban_ten_khach AS dat_ban_sap_toi_ten_khach,
        rs.dat_ban_sdt_khach AS dat_ban_sap_toi_sdt_khach,
        rs.dat_ban_ngay AS dat_ban_sap_toi_ngay,
        rs.dat_ban_gio AS dat_ban_sap_toi_gio,
        rs.dat_ban_so_khach AS dat_ban_sap_toi_so_khach,
        rs.dat_ban_trang_thai AS dat_ban_sap_toi_trang_thai,

        CASE
            WHEN IFNULL(dm.so_don_cho, 0) > 0 THEN 'dang_dung'
            WHEN b.trang_thai = 'dang_dung' THEN 'dang_dung'
            ELSE 'trong'
        END AS trang_thai
    FROM ban b
    LEFT JOIN (
        SELECT id_ban, COUNT(*) AS so_don_cho
        FROM don_mon
        WHERE trang_thai = 'cho_phuc_vu'
        GROUP BY id_ban
    ) dm ON dm.id_ban = b.id_ban
    LEFT JOIN (
        SELECT
            ct.id_ban,
            SUBSTRING_INDEX(
                GROUP_CONCAT(db.id_dat_ban ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_id,
            SUBSTRING_INDEX(
                GROUP_CONCAT(db.ten_khach ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_ten_khach,
            SUBSTRING_INDEX(
                GROUP_CONCAT(db.sdt_khach ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_sdt_khach,
            SUBSTRING_INDEX(
                GROUP_CONCAT(DATE_FORMAT(db.ngay_dat, '%Y-%m-%d') ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_ngay,
            SUBSTRING_INDEX(
                GROUP_CONCAT(DATE_FORMAT(db.gio_dat, '%H:%i') ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_gio,
            SUBSTRING_INDEX(
                GROUP_CONCAT((db.so_nguoi_lon + db.so_tre_em) ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_so_khach,
            SUBSTRING_INDEX(
                GROUP_CONCAT(db.trang_thai ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'),
                '||',
                1
            ) AS dat_ban_trang_thai
        FROM chitiet_datban ct
        JOIN dat_ban db ON db.id_dat_ban = ct.id_dat_ban
        WHERE db.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
          AND TIMESTAMP(db.ngay_dat, db.gio_dat) >= DATE_SUB(NOW(), INTERVAL 90 MINUTE)
          AND TIMESTAMP(db.ngay_dat, db.gio_dat) < DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        GROUP BY ct.id_ban
    ) rs ON rs.id_ban = b.id_ban
    ";
    }

    public function layTheoId($id)
    {
        $this->damBaoMaPhienBan();
        $sql = $this->selectBan() . " WHERE b.id_ban = ? LIMIT 1";
        $rows = $this->db->query($sql, array($id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTatCaBan()
    {
        $this->damBaoMaPhienBan();
        return $this->db->query($this->selectBan() . " ORDER BY b.so_ban");
    }

    public function layTheoMaTruyCap($ma)
    {
        $this->damBaoMaPhienBan();
        $sql = $this->selectBan() . "
        WHERE (b.ma_phien_goi_mon = ? AND b.ma_phien_het_han > NOW())
           OR (
               b.ma_truy_cap = ?
               AND (
                   b.trang_thai <> 'dang_dung'
                   OR b.ma_phien_goi_mon IS NULL
                   OR b.ma_phien_het_han IS NULL
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
        $sql = $this->selectBan() . "
        JOIN phien_ban pb ON pb.id_ban = b.id_ban
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = pb.id_phien_goi_mon
                            AND p.id_phien_goi_mon = b.ma_phien_goi_mon
        WHERE b.ma_phien_goi_mon = ?
          AND b.ma_phien_het_han > NOW()
          AND b.trang_thai = 'dang_dung'
          AND p.trang_thai = 'dang_dung'
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array($ma));
        if (!empty($rows)) {
            $rows[0]['phien_goi_mon_id'] = $ma;
        }
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoMaCoDinh($ma)
    {
        return $this->layTheoMaTruyCap($ma);
    }

    private function taoMaPhienGoiMon($maBan)
    {
        // Ví dụ đầu vào: BAN-A1 hoặc A1
        $ban = strtoupper(trim($maBan));

        // Nếu là BAN-A1 thì lấy A1
        $ban = preg_replace('/^BAN[-_]?/i', '', $ban);

        // Chỉ giữ chữ và số
        $ban = preg_replace('/[^A-Z0-9]/', '', $ban);

        // Nếu mã bàn bị rỗng thì dùng mặc định
        if ($ban === '') {
            $ban = 'BAN';
        }

        // Tạo số thứ tự trong ngày
        $dailyKey = 'PH' . date('Ymd');
        $sequenceId = $this->taoId($dailyKey, 3, false);

        $parts = explode('-', $sequenceId);
        $sequence = end($parts);

        // Format: PH-A1-0531-001
        return 'PH-' . $ban . '-' . date('md') . '-' . $sequence;
    }

    public function taoPhienGoiMon($id, $thongTinBill = array())
    {
        $this->damBaoMaPhienBan();

        $ban = $this->layTheoId($id);
        if (!$ban) {
            return null;
        }

        $maBan = !empty($ban['ma_truy_cap']) ? $ban['ma_truy_cap'] : $id;
        $maPhien = $this->taoMaPhienGoiMon($maBan);
        $tenKhach = isset($thongTinBill['ten_khach']) ? trim($thongTinBill['ten_khach']) : '';
        $sdtKhach = isset($thongTinBill['sdt_khach']) ? trim($thongTinBill['sdt_khach']) : '';
        $nguoiLon = isset($thongTinBill['nguoi_lon']) ? (int)$thongTinBill['nguoi_lon'] : 0;
        $treEm = isset($thongTinBill['tre_em']) ? (int)$thongTinBill['tre_em'] : 0;
        $tongTien = isset($thongTinBill['tong_tien']) ? (int)$thongTinBill['tong_tien'] : 0;
        $datBanId = isset($thongTinBill['dat_ban_id']) ? trim($thongTinBill['dat_ban_id']) : null;

        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc');

        $ok = $this->db->query("
        UPDATE ban
        SET ma_phien_goi_mon = ?,
            ma_phien_het_han = DATE_ADD(NOW(), INTERVAL 100 MINUTE),
            phien_ten_khach = ?,
            phien_sdt_khach = ?,
            phien_nguoi_lon = ?,
            phien_tre_em = ?,
            phien_tong_tien = ?,
            phien_bat_dau = NOW(),
            trang_thai = 'dang_dung'
        WHERE id_ban = ?
        ", array($maPhien, $tenKhach, $sdtKhach, $nguoiLon, $treEm, $tongTien, $id));

        if (!$ok) {
            return null;
        }

        $this->db->query("
            INSERT INTO phien_goi_mon
                (id_phien_goi_mon, id_dat_ban, ten_khach, sdt_khach, so_nguoi_lon, so_tre_em,
                 tong_tien_du_kien, bat_dau_luc, het_han_luc, trang_thai)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 100 MINUTE), 'dang_dung')
        ", array($maPhien, $datBanId, $tenKhach, $sdtKhach, $nguoiLon, $treEm, $tongTien));

        $this->db->query("
            INSERT IGNORE INTO phien_ban (id_phien_ban, id_phien_goi_mon, id_ban)
            VALUES (?, ?, ?)
        ", array($this->taoId('PB', 5, true), $maPhien, $id));

        $this->db->query("
            INSERT INTO hoa_don_phien
                (id_hoa_don_phien, id_phien_goi_mon, ten_khach, sdt_khach, so_nguoi_lon, so_tre_em, tong_tien)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ", array($this->taoIdHoaDon(), $maPhien, $tenKhach, $sdtKhach, $nguoiLon, $treEm, $tongTien));

        return $this->layTheoId($id);
    }

    private function capNhatKetThucPhienHienTai($id, $trangThaiPhien, $phuongThucThanhToan = '')
    {
        $this->db->query("
        UPDATE hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        JOIN ban b ON b.id_ban = pb.id_ban
        SET h.ten_khach = COALESCE(NULLIF(b.phien_ten_khach, ''), h.ten_khach),
            h.sdt_khach = COALESCE(NULLIF(b.phien_sdt_khach, ''), h.sdt_khach),
            h.so_nguoi_lon = b.phien_nguoi_lon,
            h.so_tre_em = b.phien_tre_em,
            h.tong_tien = b.phien_tong_tien
        WHERE pb.id_ban = ?
          AND p.trang_thai = 'dang_dung'
        ", array($id));

        if ($phuongThucThanhToan !== '') {
            $this->db->query("
            INSERT INTO thanh_toan_phien (id_thanh_toan_phien, id_hoa_don_phien, phuong_thuc, thanh_toan_luc)
            SELECT ?, h.id_hoa_don_phien, ?, NOW()
            FROM hoa_don_phien h
            JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
            JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
            WHERE pb.id_ban = ?
              AND p.trang_thai = 'dang_dung'
            ON DUPLICATE KEY UPDATE
                phuong_thuc = VALUES(phuong_thuc),
                thanh_toan_luc = VALUES(thanh_toan_luc)
            ", array($this->taoIdThanhToan(), $phuongThucThanhToan, $id));
        }

        return $this->db->query("
        UPDATE phien_goi_mon
        SET trang_thai = ?,
            ket_thuc_luc = NOW()
        WHERE id_phien_goi_mon IN (
            SELECT id_phien_goi_mon FROM phien_ban WHERE id_ban = ?
        )
          AND trang_thai = 'dang_dung'
        ", array($trangThaiPhien, $id));
    }

    public function xoaPhienGoiMon($id, $phuongThucThanhToan = '')
    {
        $this->damBaoMaPhienBan();
        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc', $phuongThucThanhToan);

        return $this->db->query("
        UPDATE ban
        SET ma_phien_goi_mon = NULL,
            ma_phien_het_han = NULL,
            phien_ten_khach = NULL,
            phien_sdt_khach = NULL,
            phien_nguoi_lon = 0,
            phien_tre_em = 0,
            phien_tong_tien = 0,
            phien_bat_dau = NULL,
            trang_thai = 'trong'
        WHERE id_ban = ?
        ", array($id));
    }

    public function layDoanhThuPhienGanNhatTheoBan($id)
    {
        $rows = $this->db->query("
        SELECT h.id_hoa_don_phien AS id, h.tong_tien, h.so_nguoi_lon, h.so_tre_em, h.ten_khach, h.sdt_khach,
               p.id_phien_goi_mon AS phien_goi_mon_id, p.ket_thuc_luc
        FROM phien_goi_mon p
        JOIN hoa_don_phien h ON h.id_phien_goi_mon = p.id_phien_goi_mon
        JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        WHERE pb.id_ban = ?
          AND p.trang_thai = 'da_ket_thuc'
        ORDER BY p.ket_thuc_luc DESC, p.id_phien_goi_mon DESC
        LIMIT 1
        ", array($id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTongHoaDonTichDiemTheoSDTNgay($sdt, $ngay)
    {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        $rows = $this->db->query("
        SELECT
            COALESCE(SUM(h.tong_tien), 0) AS tong_tien,
            COUNT(*) AS so_hoa_don,
            MIN(h.ngay_tao) AS tu_luc,
            MAX(h.ngay_tao) AS den_luc,
            MAX(h.ten_khach) AS ten_khach,
            MAX(h.sdt_khach) AS sdt_khach
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        WHERE h.sdt_khach = ?
          AND (
              DATE(h.ngay_tao) = ?
              OR DATE(p.bat_dau_luc) = ?
              OR DATE(IFNULL(p.ket_thuc_luc, h.ngay_tao)) = ?
          )
          AND h.tong_tien > 0
          AND (t.id_thanh_toan_phien IS NULL OR t.tich_diem_luc IS NULL)
        ", array($sdt, $ngay, $ngay, $ngay));

        $row = !empty($rows) ? $rows[0] : array('tong_tien' => 0, 'so_hoa_don' => 0);
        $tongTien = isset($row['tong_tien']) ? (int)$row['tong_tien'] : 0;
        $row['tong_tien'] = $tongTien;
        $row['so_hoa_don'] = isset($row['so_hoa_don']) ? (int)$row['so_hoa_don'] : 0;
        $row['diem_quy_doi'] = (int)floor($tongTien / 10000);
        $row['ngay'] = $ngay;
        return $row;
    }

    public function layHoaDonChuaSdtTheoNgay($ngay)
    {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        $rows = $this->db->query("
        SELECT
            h.id_hoa_don_phien AS id,
            h.ten_khach,
            h.sdt_khach,
            h.tong_tien,
            h.ngay_tao,
            p.id_phien_goi_mon AS ma_phien,
            pb.id_ban AS ban_id,
            b.so_ban
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        LEFT JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        LEFT JOIN ban b ON b.id_ban = pb.id_ban
        WHERE (h.sdt_khach IS NULL OR h.sdt_khach = '')
          AND (
              DATE(h.ngay_tao) = ?
              OR DATE(p.bat_dau_luc) = ?
              OR DATE(IFNULL(p.ket_thuc_luc, h.ngay_tao)) = ?
          )
          AND h.tong_tien > 0
          AND (t.id_thanh_toan_phien IS NULL OR t.tich_diem_luc IS NULL)
        ORDER BY h.ngay_tao DESC, h.id_hoa_don_phien DESC
        ", array($ngay, $ngay, $ngay));

        foreach ($rows as $i => $row) {
            $tongTien = isset($row['tong_tien']) ? (int)$row['tong_tien'] : 0;
            $rows[$i]['tong_tien'] = $tongTien;
            $rows[$i]['diem_quy_doi'] = (int)floor($tongTien / 10000);
        }
        return $rows;
    }

    public function layHoaDonChuaTichDiemTheoId($id)
    {
        $rows = $this->db->query("
        SELECT h.id_hoa_don_phien AS id, h.*, p.id_phien_goi_mon AS ma_phien, pb.id_ban AS ban_id
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        WHERE h.id_hoa_don_phien = ?
          AND h.tong_tien > 0
          AND (t.id_thanh_toan_phien IS NULL OR t.tich_diem_luc IS NULL)
        LIMIT 1
        ", array($id));

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
        return $this->db->query("
        UPDATE hoa_don_phien
        SET ten_khach = ?, sdt_khach = ?
        WHERE id_hoa_don_phien = ?
          AND id_hoa_don_phien NOT IN (
              SELECT id_hoa_don_phien
              FROM thanh_toan_phien
              WHERE tich_diem_luc IS NOT NULL
          )
        ", array($tenKhach, $sdt, $id));
    }

    public function taoHoaDonTuPhienDangDungTheoSDTNgay($sdt, $ngay)
    {
        return true;
    }

    public function danhDauHoaDonDaTichDiem($sdt, $ngay, $taiKhoanId, $diem)
    {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $ngay)) {
            $ngay = date('Y-m-d');
        }

        return $this->db->query("
        INSERT INTO thanh_toan_phien
            (id_thanh_toan_phien, id_hoa_don_phien, tich_diem_luc, id_khach_tai_khoan, diem_da_cong)
        SELECT ?, h.id_hoa_don_phien, NOW(), ?, ?
        FROM hoa_don_phien h
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        WHERE h.sdt_khach = ?
          AND DATE(h.ngay_tao) = ?
          AND h.tong_tien > 0
          AND (t.id_thanh_toan_phien IS NULL OR t.tich_diem_luc IS NULL)
        ON DUPLICATE KEY UPDATE
            tich_diem_luc = VALUES(tich_diem_luc),
            id_khach_tai_khoan = VALUES(id_khach_tai_khoan),
            diem_da_cong = VALUES(diem_da_cong)
        ", array($this->taoIdThanhToan(), $taiKhoanId, (int)$diem, $sdt, $ngay));
    }

    public function danhDauMotHoaDonDaTichDiem($id, $taiKhoanId, $diem)
    {
        return $this->db->query("
        INSERT INTO thanh_toan_phien
            (id_thanh_toan_phien, id_hoa_don_phien, tich_diem_luc, id_khach_tai_khoan, diem_da_cong)
        SELECT ?, h.id_hoa_don_phien, NOW(), ?, ?
        FROM hoa_don_phien h
        LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
        WHERE h.id_hoa_don_phien = ?
          AND h.tong_tien > 0
          AND (t.id_thanh_toan_phien IS NULL OR t.tich_diem_luc IS NULL)
        ON DUPLICATE KEY UPDATE
            tich_diem_luc = VALUES(tich_diem_luc),
            id_khach_tai_khoan = VALUES(id_khach_tai_khoan),
            diem_da_cong = VALUES(diem_da_cong)
        ", array($this->taoIdThanhToan(), $taiKhoanId, (int)$diem, $id));
    }

    public function demTatCa()
    {
        $rows = $this->db->query("SELECT COUNT(*) AS tong FROM ban");
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function capNhatTrangThai($id, $trang_thai, $phuongThucThanhToan = '')
    {
        $this->damBaoMaPhienBan();
        $ok = $this->db->query("UPDATE ban SET trang_thai = ? WHERE id_ban = ?", array($trang_thai, $id));
        if ($ok && $trang_thai !== 'dang_dung') {
            $this->xoaPhienGoiMon($id, $phuongThucThanhToan);
        }
        return $ok;
    }

    public function layBanTrong($ngay, $gio)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = $this->selectBan() . "
        WHERE b.id_ban NOT IN (
            SELECT ct.id_ban
            FROM chitiet_datban ct
            JOIN dat_ban db ON db.id_dat_ban = ct.id_dat_ban
            WHERE db.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
        )
        ORDER BY b.so_ban
        ";
        return $this->db->query($sql, array($ngay, $gio, $sessionSeconds));
    }

    public function timBanPhuHop($ngay, $gio, $so_khach, $bo_qua_dat_ban_id)
    {
        $rows = $this->timToHopBanPhuHop($ngay, $gio, $so_khach, $bo_qua_dat_ban_id);
        return !empty($rows) ? $rows[0] : null;
    }

    public function timToHopBanPhuHop($ngay, $gio, $so_khach, $bo_qua_dat_ban_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $banTrong = $this->db->query($this->selectBan() . "
        WHERE b.id_ban NOT IN (
            SELECT ct.id_ban
            FROM chitiet_datban ct
            JOIN dat_ban db ON db.id_dat_ban = ct.id_dat_ban
            WHERE db.ngay_dat = ?
              AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
              AND db.trang_thai IN ('cho_xac_nhan','da_xac_nhan')
              AND db.id_dat_ban <> ?
        )
        ORDER BY b.suc_chua ASC, b.so_ban ASC
        ", array($ngay, $gio, $sessionSeconds, $bo_qua_dat_ban_id));

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
                if ($bestCapacity === null || $capacity < $bestCapacity || ($capacity == $bestCapacity && $count < $bestCount)) {
                    $best = $combo;
                    $bestCapacity = $capacity;
                    $bestCount = $count;
                }
            }
        }
        return $best;
    }
}
