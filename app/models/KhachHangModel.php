<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class KhachHangModel extends MoHinhCo
{
    public function layDanhSach($tuKhoa, $trangThai)
    {
        $tuKhoa = trim((string)$tuKhoa);
        $trangThai = trim((string)$trangThai);

        $where = " WHERE 1 = 1";
        $params = array();

        if ($tuKhoa !== '') {
            $like = '%' . $tuKhoa . '%';
            $where .= " AND (
                id_khach_tai_khoan LIKE ?
                OR ho_ten LIKE ?
                OR so_dien_thoai LIKE ?
                OR email LIKE ?
            )";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($trangThai === 'dang_hoat_dong') {
            $where .= " AND dang_hoat_dong = ?";
            $params[] = 1;
        } elseif ($trangThai === 'ngung_hoat_dong') {
            $where .= " AND dang_hoat_dong = ?";
            $params[] = 0;
        }

        $sql = "
            SELECT id_khach_tai_khoan, ho_ten, so_dien_thoai, email,
                   diem_tich_luy, dang_hoat_dong, ngay_tao
            FROM khach_tai_khoan
            " . $where . "
            ORDER BY ngay_tao DESC, id_khach_tai_khoan DESC
            LIMIT 200
        ";

        return $this->db->query($sql, $params);
    }

    public function layTheoId($id)
    {
        $rows = $this->db->query("
            SELECT id_khach_tai_khoan, ten_dang_nhap, vai_tro, dang_hoat_dong,
                   ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao
            FROM khach_tai_khoan
            WHERE id_khach_tai_khoan = ?
            LIMIT 1
        ", array($id));

        return !empty($rows) ? $rows[0] : null;
    }

    public function layThongKe($id)
    {
        $datBan = $this->db->query("
            SELECT COUNT(*) AS so_lan_dat_ban
            FROM dat_ban
            WHERE id_khach_tai_khoan = ?
        ", array($id));

        $thanhToan = $this->db->query("
            SELECT
                COUNT(DISTINCT CASE
                    WHEN h.trang_thai = 'da_thanh_toan' OR t.trang_thai = 'da_thanh_toan'
                    THEN h.id_hoa_don_phien
                    ELSE NULL
                END) AS so_lan_thanh_toan,
                COALESCE(SUM(CASE
                    WHEN t.trang_thai = 'da_thanh_toan' THEN t.tong_tien_da_thanh_toan
                    WHEN h.trang_thai = 'da_thanh_toan' THEN h.thanh_tien
                    ELSE 0
                END), 0) AS tong_tien_da_thanh_toan
            FROM hoa_don_phien h
            LEFT JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
            LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
            WHERE h.id_khach_tai_khoan = ? OR p.id_khach_tai_khoan = ?
        ", array($id, $id));

        return array(
            'so_lan_dat_ban' => !empty($datBan) ? (int)$datBan[0]['so_lan_dat_ban'] : 0,
            'so_lan_thanh_toan' => !empty($thanhToan) ? (int)$thanhToan[0]['so_lan_thanh_toan'] : 0,
            'tong_tien_da_thanh_toan' => !empty($thanhToan) ? (int)$thanhToan[0]['tong_tien_da_thanh_toan'] : 0
        );
    }

    public function layLichSuDatBanGanDay($id, $gioiHan)
    {
        $gioiHan = (int)$gioiHan;
        if ($gioiHan <= 0) {
            $gioiHan = 5;
        }

        return $this->db->query("
            SELECT id_dat_ban, ma_dat_ban, ten_khach, sdt_khach, ngay_dat, gio_dat,
                   so_nguoi_lon, so_tre_em, trang_thai, created_at
            FROM dat_ban
            WHERE id_khach_tai_khoan = ?
            ORDER BY ngay_dat DESC, gio_dat DESC, created_at DESC
            LIMIT " . $gioiHan . "
        ", array($id));
    }

    public function layLichSuHoaDonGanDay($id, $gioiHan)
    {
        $gioiHan = (int)$gioiHan;
        if ($gioiHan <= 0) {
            $gioiHan = 5;
        }

        return $this->db->query("
            SELECT
                h.id_hoa_don_phien, h.ma_hoa_don, h.ten_khach, h.sdt_khach,
                h.tong_tien_buffet, h.tong_tien, h.thanh_tien,
                h.da_tich_diem, h.trang_thai, h.created_at,
                p.ma_phien, p.gio_bat_dau, p.gio_ket_thuc,
                t.trang_thai AS trang_thai_thanh_toan,
                t.tong_tien_can_thanh_toan, t.tong_tien_da_thanh_toan,
                COALESCE((
                    SELECT SUM(ct.so_tien)
                    FROM chi_tiet_thanh_toan_phien ct
                    WHERE ct.id_thanh_toan = t.id_thanh_toan
                ), 0) AS tong_chi_tiet_thanh_toan,
                (
                    SELECT GROUP_CONCAT(ct.phuong_thuc ORDER BY ct.created_at SEPARATOR ', ')
                    FROM chi_tiet_thanh_toan_phien ct
                    WHERE ct.id_thanh_toan = t.id_thanh_toan
                ) AS phuong_thuc_thanh_toan
            FROM hoa_don_phien h
            LEFT JOIN phien_goi_mon p ON p.id_phien_goi_mon = h.id_phien_goi_mon
            LEFT JOIN thanh_toan_phien t ON t.id_hoa_don_phien = h.id_hoa_don_phien
            WHERE h.id_khach_tai_khoan = ? OR p.id_khach_tai_khoan = ?
            ORDER BY h.created_at DESC, h.id_hoa_don_phien DESC
            LIMIT " . $gioiHan . "
        ", array($id, $id));
    }

    public function layChiTiet($id)
    {
        $khach = $this->layTheoId($id);
        if (!$khach) {
            return null;
        }

        return array(
            'khach' => $khach,
            'thongKe' => $this->layThongKe($id),
            'lichSuDatBan' => $this->layLichSuDatBanGanDay($id, 8),
            'lichSuHoaDon' => $this->layLichSuHoaDonGanDay($id, 8)
        );
    }
}
