<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhBan extends MoHinhCo
{
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
        UPDATE phien_ban pb
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = pb.id_phien_goi_mon
        SET pb.trang_thai = 'da_roi',
            pb.thoi_gian_roi_ban = IFNULL(pb.thoi_gian_roi_ban, NOW())
        WHERE p.trang_thai = 'dang_dung'
          AND p.gio_ket_thuc_du_kien <= NOW()
          AND pb.trang_thai = 'dang_gan'
        ");

        $this->db->query("
        UPDATE ban b
        SET b.trang_thai = 'trong'
        WHERE b.trang_thai = 'dang_dung'
          AND NOT EXISTS (
              SELECT 1
              FROM phien_ban pb
              JOIN phien_goi_mon p ON p.id_phien_goi_mon = pb.id_phien_goi_mon
              WHERE pb.id_ban = b.id_ban
                AND pb.trang_thai = 'dang_gan'
                AND p.trang_thai = 'dang_dung'
                AND p.gio_ket_thuc_du_kien > NOW()
          )
        ");

        $this->db->query("
        UPDATE phien_goi_mon
        SET trang_thai = 'het_han',
            gio_ket_thuc = IFNULL(gio_ket_thuc, NOW()),
            updated_at = NOW()
        WHERE trang_thai = 'dang_dung'
          AND gio_ket_thuc_du_kien <= NOW()
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
        p.id_phien_goi_mon AS phien_goi_mon_id,
        p.ma_phien AS ma_phien_goi_mon,
        p.gio_ket_thuc_du_kien AS ma_phien_het_han,
        p.ten_khach AS phien_ten_khach,
        p.sdt_khach AS phien_sdt_khach,
        COALESCE(p.so_nguoi_lon, 0) AS phien_nguoi_lon,
        COALESCE(p.so_tre_em, 0) AS phien_tre_em,
        COALESCE(h.thanh_tien, p.tong_tien_tam_tinh, 0) AS phien_tong_tien,
        p.gio_bat_dau AS phien_bat_dau,
        CASE
            WHEN p.id_phien_goi_mon IS NOT NULL
             AND p.gio_ket_thuc_du_kien > NOW()
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
        rs.dat_ban_ghi_chu AS dat_ban_sap_toi_ghi_chu,

        CASE
            WHEN IFNULL(dm.so_don_cho, 0) > 0 THEN 'dang_dung'
            WHEN b.trang_thai = 'dang_dung' THEN 'dang_dung'
            ELSE 'trong'
        END AS trang_thai
    FROM ban b
    LEFT JOIN phien_ban pb ON pb.id_ban = b.id_ban AND pb.trang_thai = 'dang_gan'
    LEFT JOIN phien_goi_mon p ON p.id_phien_goi_mon = pb.id_phien_goi_mon
                             AND p.trang_thai = 'dang_dung'
                             AND p.gio_ket_thuc_du_kien > NOW()
    LEFT JOIN hoa_don_phien h ON h.id_phien_goi_mon = p.id_phien_goi_mon
    LEFT JOIN (
        SELECT id_ban, COUNT(*) AS so_don_cho
        FROM don_mon
        WHERE trang_thai = 'cho_phuc_vu'
        GROUP BY id_ban
    ) dm ON dm.id_ban = b.id_ban
    LEFT JOIN (
        SELECT
            ct.id_ban,
            SUBSTRING_INDEX(GROUP_CONCAT(db.id_dat_ban ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_id,
            SUBSTRING_INDEX(GROUP_CONCAT(db.ten_khach ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_ten_khach,
            SUBSTRING_INDEX(GROUP_CONCAT(db.sdt_khach ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_sdt_khach,
            SUBSTRING_INDEX(GROUP_CONCAT(DATE_FORMAT(db.ngay_dat, '%Y-%m-%d') ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_ngay,
            SUBSTRING_INDEX(GROUP_CONCAT(DATE_FORMAT(db.gio_dat, '%H:%i') ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_gio,
            SUBSTRING_INDEX(GROUP_CONCAT((db.so_nguoi_lon + db.so_tre_em) ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_so_khach,
            SUBSTRING_INDEX(GROUP_CONCAT(db.trang_thai ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_trang_thai,
            SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(db.ghi_chu, '') ORDER BY db.ngay_dat ASC, db.gio_dat ASC SEPARATOR '||'), '||', 1) AS dat_ban_ghi_chu
        FROM chitiet_datban ct
        JOIN dat_ban db ON db.id_dat_ban = ct.id_dat_ban
        WHERE db.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
          AND ct.trang_thai = 'dang_gan'
          AND ct.thoi_gian_bat_dau <= DATE_ADD(NOW(), INTERVAL 90 MINUTE)
          AND ct.thoi_gian_ket_thuc >= NOW()
        GROUP BY ct.id_ban
    ) rs ON rs.id_ban = b.id_ban
    ";
    }

    public function layTheoId($id)
    {
        $this->capNhatPhienHetHan();
        $sql = $this->selectBan() . " WHERE b.id_ban = ? LIMIT 1";
        $rows = $this->db->query($sql, array($id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTatCaBan()
    {
        $this->capNhatPhienHetHan();
        return $this->db->query($this->selectBan() . " ORDER BY b.so_ban");
    }

    public function layTheoMaTruyCap($ma)
    {
        $this->capNhatPhienHetHan();
        $sql = $this->selectBan() . "
        WHERE (p.ma_phien = ? AND p.gio_ket_thuc_du_kien > NOW())
           OR (b.ma_truy_cap = ? AND p.id_phien_goi_mon IS NULL)
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array($ma, $ma));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoMaPhienGoiMon($ma)
    {
        $this->capNhatPhienHetHan();
        $sql = $this->selectBan() . "
        WHERE p.ma_phien = ?
          AND p.gio_ket_thuc_du_kien > NOW()
          AND b.trang_thai = 'dang_dung'
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array($ma));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoMaCoDinh($ma)
    {
        return $this->layTheoMaTruyCap($ma);
    }

    private function taoMaPhienGoiMon($maBan)
    {
        $ban = strtoupper(trim($maBan));
        $ban = preg_replace('/^BAN[-_]?/i', '', $ban);
        $ban = preg_replace('/[^A-Z0-9]/', '', $ban);
        if ($ban === '') {
            $ban = 'BAN';
        }

        $sequenceId = $this->taoId('PH' . date('Ymd'), 3, false);
        $parts = explode('-', $sequenceId);
        return 'PH-' . $ban . '-' . date('md') . '-' . end($parts);
    }

    public function taoPhienGoiMon($id, $thongTinBill = array())
    {
        $this->capNhatPhienHetHan();

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
        $datBanId = isset($thongTinBill['dat_ban_id']) && trim((string)$thongTinBill['dat_ban_id']) !== ''
            ? trim($thongTinBill['dat_ban_id'])
            : null;

        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc');

        $ok = $this->db->query("
            INSERT INTO phien_goi_mon
                (id_phien_goi_mon, ma_phien, id_dat_ban, ten_khach, sdt_khach, so_nguoi_lon, so_tre_em,
                 gio_bat_dau, gio_ket_thuc_du_kien, trang_thai, tong_tien_tam_tinh)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 100 MINUTE), 'dang_dung', ?)
        ", array($maPhien, $maPhien, $datBanId, $tenKhach, $sdtKhach, $nguoiLon, $treEm, $tongTien));

        if (!$ok) {
            return null;
        }

        $this->db->query("
            INSERT INTO phien_ban (id_phien_ban, id_phien_goi_mon, id_ban, thoi_gian_gan, trang_thai)
            VALUES (?, ?, ?, NOW(), 'dang_gan')
        ", array($this->taoId('PB', 5, true), $maPhien, $id));

        $this->db->query("
            INSERT INTO hoa_don_phien
                (id_hoa_don_phien, ma_hoa_don, id_phien_goi_mon, ten_khach, sdt_khach,
                 tong_tien_buffet, tong_tien, thanh_tien, trang_thai)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'chua_thanh_toan')
        ", array($this->taoIdHoaDon(), $this->taoId('HDM', 5, true), $maPhien, $tenKhach, $sdtKhach, $tongTien, $tongTien, $tongTien));

        $this->db->query("UPDATE ban SET trang_thai = 'dang_dung', updated_at = NOW() WHERE id_ban = ?", array($id));

        return $this->layTheoId($id);
    }

    private function capNhatKetThucPhienHienTai($id, $trangThaiPhien, $phuongThucThanhToan = '')
    {
        $this->db->query("
        UPDATE hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        SET h.ten_khach = COALESCE(NULLIF(p.ten_khach, ''), h.ten_khach),
            h.sdt_khach = COALESCE(NULLIF(p.sdt_khach, ''), h.sdt_khach),
            h.tong_tien_buffet = p.tong_tien_tam_tinh,
            h.tong_tien = p.tong_tien_tam_tinh,
            h.thanh_tien = p.tong_tien_tam_tinh,
            h.updated_at = NOW()
        WHERE pb.id_ban = ?
          AND pb.trang_thai = 'dang_gan'
          AND p.trang_thai = 'dang_dung'
        ", array($id));

        if ($phuongThucThanhToan !== '') {
            $phuongThuc = $phuongThucThanhToan === 'chuyen_khoan' ? 'CHUYEN_KHOAN' : 'TIEN_MAT';
            $this->db->query("
            INSERT INTO thanh_toan_phien
                (id_thanh_toan, id_hoa_don_phien, tong_tien_can_thanh_toan, tong_tien_da_thanh_toan, trang_thai)
            SELECT ?, h.id_hoa_don_phien, h.thanh_tien, h.thanh_tien, 'da_thanh_toan'
            FROM hoa_don_phien h
            JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
            JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
            WHERE pb.id_ban = ?
              AND pb.trang_thai = 'dang_gan'
              AND p.trang_thai = 'dang_dung'
            ON DUPLICATE KEY UPDATE
                tong_tien_can_thanh_toan = VALUES(tong_tien_can_thanh_toan),
                tong_tien_da_thanh_toan = VALUES(tong_tien_da_thanh_toan),
                trang_thai = VALUES(trang_thai),
                updated_at = NOW()
            ", array($this->taoIdThanhToan(), $id));

            $this->db->query("
            INSERT INTO chi_tiet_thanh_toan_phien
                (id_chi_tiet_thanh_toan, id_thanh_toan, phuong_thuc, so_tien, ghi_chu)
            SELECT ?, t.id_thanh_toan, ?, h.thanh_tien, 'Thanh toan khi ket thuc phien'
            FROM thanh_toan_phien t
            JOIN hoa_don_phien h ON h.id_hoa_don_phien = t.id_hoa_don_phien
            JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
            JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
            WHERE pb.id_ban = ?
              AND pb.trang_thai = 'dang_gan'
              AND p.trang_thai = 'dang_dung'
            ", array($this->taoId('CTTT', 5, true), $phuongThuc, $id));
        }

        $this->db->query("
        UPDATE phien_ban pb
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = pb.id_phien_goi_mon
        SET pb.trang_thai = 'da_roi',
            pb.thoi_gian_roi_ban = NOW()
        WHERE pb.id_ban = ?
          AND pb.trang_thai = 'dang_gan'
          AND p.trang_thai = 'dang_dung'
        ", array($id));

        return $this->db->query("
        UPDATE phien_goi_mon
        SET trang_thai = ?,
            gio_ket_thuc = NOW(),
            updated_at = NOW()
        WHERE id_phien_goi_mon IN (
            SELECT id_phien_goi_mon FROM phien_ban WHERE id_ban = ?
        )
          AND trang_thai = 'dang_dung'
        ", array($trangThaiPhien, $id));
    }

    public function xoaPhienGoiMon($id, $phuongThucThanhToan = '')
    {
        $this->capNhatPhienHetHan();
        $this->capNhatKetThucPhienHienTai($id, 'da_ket_thuc', $phuongThucThanhToan);
        return $this->db->query("UPDATE ban SET trang_thai = 'trong', updated_at = NOW() WHERE id_ban = ?", array($id));
    }

    public function layDoanhThuPhienGanNhatTheoBan($id)
    {
        $rows = $this->db->query("
        SELECT h.id_hoa_don_phien AS id, h.thanh_tien AS tong_tien,
               p.so_nguoi_lon, p.so_tre_em, h.ten_khach, h.sdt_khach,
               p.id_phien_goi_mon AS phien_goi_mon_id, p.gio_ket_thuc AS ket_thuc_luc
        FROM phien_goi_mon p
        JOIN hoa_don_phien h ON h.id_phien_goi_mon = p.id_phien_goi_mon
        JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        WHERE pb.id_ban = ?
          AND p.trang_thai = 'da_ket_thuc'
        ORDER BY p.gio_ket_thuc DESC, p.id_phien_goi_mon DESC
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
            COALESCE(SUM(h.thanh_tien), 0) AS tong_tien,
            COUNT(*) AS so_hoa_don,
            MIN(h.created_at) AS tu_luc,
            MAX(h.created_at) AS den_luc,
            MAX(h.ten_khach) AS ten_khach,
            MAX(h.sdt_khach) AS sdt_khach
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        WHERE h.sdt_khach = ?
          AND (DATE(h.created_at) = ? OR DATE(p.gio_bat_dau) = ? OR DATE(IFNULL(p.gio_ket_thuc, h.created_at)) = ?)
          AND h.thanh_tien > 0
          AND h.da_tich_diem = 0
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
            h.thanh_tien AS tong_tien,
            h.created_at AS ngay_tao,
            p.ma_phien,
            pb.id_ban AS ban_id,
            b.so_ban
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        LEFT JOIN ban b ON b.id_ban = pb.id_ban
        WHERE (h.sdt_khach IS NULL OR h.sdt_khach = '')
          AND (DATE(h.created_at) = ? OR DATE(p.gio_bat_dau) = ? OR DATE(IFNULL(p.gio_ket_thuc, h.created_at)) = ?)
          AND h.thanh_tien > 0
          AND h.da_tich_diem = 0
        ORDER BY h.created_at DESC, h.id_hoa_don_phien DESC
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
        SELECT h.id_hoa_don_phien AS id, h.*, h.thanh_tien AS tong_tien, p.ma_phien, pb.id_ban AS ban_id
        FROM hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        LEFT JOIN phien_ban pb ON pb.id_phien_goi_mon = p.id_phien_goi_mon
        WHERE h.id_hoa_don_phien = ?
          AND h.thanh_tien > 0
          AND h.da_tich_diem = 0
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
        UPDATE hoa_don_phien h
        SET h.ten_khach = ?, h.sdt_khach = ?, h.updated_at = NOW()
        WHERE h.id_hoa_don_phien = ?
          AND h.da_tich_diem = 0
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
        UPDATE hoa_don_phien h
        JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
        SET h.da_tich_diem = 1,
            h.id_khach_tai_khoan = ?,
            h.updated_at = NOW()
        WHERE h.sdt_khach = ?
          AND (DATE(h.created_at) = ? OR DATE(p.gio_bat_dau) = ? OR DATE(IFNULL(p.gio_ket_thuc, h.created_at)) = ?)
          AND h.thanh_tien > 0
          AND h.da_tich_diem = 0
        ", array($taiKhoanId, $sdt, $ngay, $ngay, $ngay));
    }

    public function danhDauMotHoaDonDaTichDiem($id, $taiKhoanId, $diem)
    {
        return $this->db->query("
        UPDATE hoa_don_phien
        SET da_tich_diem = 1,
            id_khach_tai_khoan = ?,
            updated_at = NOW()
        WHERE id_hoa_don_phien = ?
          AND thanh_tien > 0
          AND da_tich_diem = 0
        ", array($taiKhoanId, $id));
    }

    public function demTatCa()
    {
        $rows = $this->db->query("SELECT COUNT(*) AS tong FROM ban");
        return !empty($rows) ? (int)$rows[0]['tong'] : 0;
    }

    public function capNhatTrangThai($id, $trang_thai, $phuongThucThanhToan = '')
    {
        $this->capNhatPhienHetHan();
        if ($trang_thai !== 'dang_dung') {
            return $this->xoaPhienGoiMon($id, $phuongThucThanhToan);
        }
        return $this->db->query("UPDATE ban SET trang_thai = ?, updated_at = NOW() WHERE id_ban = ?", array($trang_thai, $id));
    }

    public function layBanTrong($ngay, $gio)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $sql = $this->selectBan() . "
        WHERE b.id_ban NOT IN (
            SELECT ct.id_ban
            FROM chitiet_datban ct
            WHERE ABS(TIMESTAMPDIFF(SECOND, ct.thoi_gian_bat_dau, TIMESTAMP(?, ?))) < ?
              AND ct.trang_thai = 'dang_gan'
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
            WHERE ABS(TIMESTAMPDIFF(SECOND, ct.thoi_gian_bat_dau, TIMESTAMP(?, ?))) < ?
              AND ct.id_dat_ban <> ?
              AND ct.trang_thai = 'dang_gan'
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
