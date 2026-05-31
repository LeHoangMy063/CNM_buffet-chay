<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhDatBan extends MoHinhCo
{
    private function taoMaDatBan()
    {
        return $this->taoId('DB', 5, true);
    }

    private function taoIdChiTietDatBan()
    {
        return $this->taoId('PBD', 5, true);
    }

    private function taoIdLichSuDieuPhoi()
    {
        return $this->taoId('LSDP', 5, true);
    }

    private function chuanHoaIdBoQua($bo_qua_id)
    {
        $id = trim((string)$bo_qua_id);
        return $id === '0' ? '' : $id;
    }

    private function selectDatBanVoiBan()
    {
        return "
        SELECT
            r.id_dat_ban AS id,
            (
                SELECT MIN(ct1.id_ban)
                FROM chitiet_datban ct1
                WHERE ct1.id_dat_ban = r.id_dat_ban
            ) AS ban_id,
            r.ten_khach,
            r.sdt_khach,
            r.so_nguoi_lon,
            r.so_tre_em,
            ((r.so_nguoi_lon * 199000) + (r.so_tre_em * 99000)) AS tong_tien,
            r.ngay_dat,
            r.gio_dat,
            r.ghi_chu,
            r.trang_thai,
            r.id_dat_ban AS ma_dat_ban,
            CASE WHEN COUNT(dbb.id_chitiet_datban) > 0 THEN 1 ELSE 0 END AS ban_xac_nhan,
            r.created_at AS ngay_tao,
            CASE
                WHEN r.trang_thai IN ('da_huy', 'cancelled', 'expired', 'hoan_thanh') THEN NULL
                ELSE GROUP_CONCAT(DISTINCT b.so_ban ORDER BY b.so_ban SEPARATOR ', ')
            END AS so_ban,
            CASE
                WHEN r.trang_thai IN ('da_huy', 'cancelled', 'expired', 'hoan_thanh') THEN NULL
                ELSE GROUP_CONCAT(DISTINCT b.id_ban ORDER BY b.so_ban SEPARATOR ',')
            END AS ban_ids,
            CASE
                WHEN r.trang_thai IN ('da_huy', 'cancelled', 'expired', 'hoan_thanh') THEN 0
                WHEN COUNT(dbb.id_chitiet_datban) > 0 THEN 1
                ELSE 0
            END AS ban_da_xac_nhan
        FROM dat_ban r
        LEFT JOIN chitiet_datban dbb ON dbb.id_dat_ban = r.id_dat_ban
        LEFT JOIN ban b ON b.id_ban = dbb.id_ban
        ";
    }

    private function nhomVaSapXep($order)
    {
        return " GROUP BY r.id_dat_ban " . $order;
    }

    public function layTatCa()
    {
        $sql = $this->selectDatBanVoiBan() . $this->nhomVaSapXep("ORDER BY r.ngay_dat DESC, r.gio_dat DESC");
        return $this->db->query($sql);
    }

    public function timKiem($tu_khoa)
    {
        $k = '%' . $tu_khoa . '%';
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.ten_khach LIKE ? OR r.sdt_khach LIKE ? OR r.id_dat_ban LIKE ?
        " . $this->nhomVaSapXep("ORDER BY r.ngay_dat DESC");
        return $this->db->query($sql, array($k, $k, $k));
    }

    public function locTheoTrangThai($trang_thai)
    {
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.trang_thai = ?
        " . $this->nhomVaSapXep("ORDER BY r.ngay_dat DESC");
        return $this->db->query($sql, array($trang_thai));
    }

    public function layTheoId($id)
    {
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.id_dat_ban = ?
        GROUP BY r.id_dat_ban
        LIMIT 1
        ";
        $rows = $this->db->query($sql, array($id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoMaDatBan($ma)
    {
        return $this->layTheoId($ma);
    }

    public function banBiTrungLich($ban_id, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $boQuaId = $this->chuanHoaIdBoQua($bo_qua_id);
        $sql = "
        SELECT COUNT(*) AS tong
        FROM chitiet_datban ct
        JOIN dat_ban db ON db.id_dat_ban = ct.id_dat_ban
        WHERE ct.id_ban = ?
          AND db.ngay_dat = ?
          AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
          AND (? = '' OR db.id_dat_ban <> ?)
          AND db.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        ";
        $rows = $this->db->query($sql, array($ban_id, $ngay_dat, $gio_dat, $sessionSeconds, $boQuaId, $boQuaId));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function banDaXacNhanTrungLich($ban_id, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $boQuaId = $this->chuanHoaIdBoQua($bo_qua_id);
        $sql = "
        SELECT COUNT(*) AS tong
        FROM chitiet_datban ct
        JOIN dat_ban db ON db.id_dat_ban = ct.id_dat_ban
        WHERE ct.id_ban = ?
          AND db.ngay_dat = ?
          AND ABS(TIME_TO_SEC(db.gio_dat) - TIME_TO_SEC(?)) < ?
          AND (? = '' OR db.id_dat_ban <> ?)
          AND db.trang_thai = 'da_xac_nhan'
        ";
        $rows = $this->db->query($sql, array($ban_id, $ngay_dat, $gio_dat, $sessionSeconds, $boQuaId, $boQuaId));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function tongKhachTrungLich($ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $boQuaId = $this->chuanHoaIdBoQua($bo_qua_id);
        $sql = "
        SELECT COALESCE(SUM(so_nguoi_lon + so_tre_em), 0) AS tong_khach
        FROM dat_ban
        WHERE ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND (? = '' OR id_dat_ban <> ?)
          AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        ";
        $rows = $this->db->query($sql, array($ngay_dat, $gio_dat, $sessionSeconds, $boQuaId, $boQuaId));
        return !empty($rows) ? (int)$rows[0]['tong_khach'] : 0;
    }

    public function tongKhachDaXacNhanTrungLich($ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $boQuaId = $this->chuanHoaIdBoQua($bo_qua_id);
        $sql = "
        SELECT COALESCE(SUM(so_nguoi_lon + so_tre_em), 0) AS tong_khach
        FROM dat_ban
        WHERE ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND (? = '' OR id_dat_ban <> ?)
          AND trang_thai = 'da_xac_nhan'
        ";
        $rows = $this->db->query($sql, array($ngay_dat, $gio_dat, $sessionSeconds, $boQuaId, $boQuaId));
        return !empty($rows) ? (int)$rows[0]['tong_khach'] : 0;
    }

    public function khachDaXacNhanTrungLich($sdt_khach, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $boQuaId = $this->chuanHoaIdBoQua($bo_qua_id);
        $sql = "
        SELECT COUNT(*) AS tong
        FROM dat_ban
        WHERE sdt_khach = ?
          AND ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND (? = '' OR id_dat_ban <> ?)
          AND trang_thai = 'da_xac_nhan'
        ";
        $rows = $this->db->query($sql, array($sdt_khach, $ngay_dat, $gio_dat, $sessionSeconds, $boQuaId, $boQuaId));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function khachBiTrungLich($sdt_khach, $ngay_dat, $gio_dat, $bo_qua_id)
    {
        $sessionSeconds = defined('BUFFET_SESSION_MINUTES') ? BUFFET_SESSION_MINUTES * 60 : 5400;
        $boQuaId = $this->chuanHoaIdBoQua($bo_qua_id);
        $sql = "
        SELECT COUNT(*) AS tong
        FROM dat_ban
        WHERE sdt_khach = ?
          AND ngay_dat = ?
          AND ABS(TIME_TO_SEC(gio_dat) - TIME_TO_SEC(?)) < ?
          AND (? = '' OR id_dat_ban <> ?)
          AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')
        ";
        $rows = $this->db->query($sql, array($sdt_khach, $ngay_dat, $gio_dat, $sessionSeconds, $boQuaId, $boQuaId));
        return !empty($rows) && (int)$rows[0]['tong'] > 0;
    }

    public function layGanDay($gioi_han)
    {
        $sql = $this->selectDatBanVoiBan() . $this->nhomVaSapXep("ORDER BY r.created_at DESC LIMIT " . (int)$gioi_han);
        return $this->db->query($sql);
    }

    public function thongKeHomNay()
    {
        $sql = "
        SELECT
            COUNT(*) AS tong,
            SUM(CASE WHEN trang_thai = 'da_xac_nhan' THEN 1 ELSE 0 END) AS da_xac_nhan,
            SUM(CASE WHEN trang_thai = 'cho_xac_nhan' THEN 1 ELSE 0 END) AS cho_xac_nhan,
            SUM(CASE WHEN trang_thai IN ('da_huy', 'cancelled') THEN 1 ELSE 0 END) AS da_huy,
            SUM(CASE WHEN trang_thai = 'expired' THEN 1 ELSE 0 END) AS expired,
            SUM(CASE WHEN trang_thai = 'hoan_thanh' THEN 1 ELSE 0 END) AS hoan_thanh,
            COALESCE(SUM((so_nguoi_lon * 199000) + (so_tre_em * 99000)), 0) AS doanh_thu
        FROM dat_ban
        WHERE ngay_dat = CURDATE()
        ";
        $rows = $this->db->query($sql);
        return !empty($rows) ? $rows[0] : array();
    }

    public function them($du_lieu)
    {
        $sdtKhach = isset($du_lieu['sdt_khach']) ? trim((string)$du_lieu['sdt_khach']) : '';
        $ngayDat = isset($du_lieu['ngay_dat']) ? trim((string)$du_lieu['ngay_dat']) : '';
        $gioDat = isset($du_lieu['gio_dat']) ? trim((string)$du_lieu['gio_dat']) : '';

        if ($sdtKhach !== '' && $ngayDat !== '' && $gioDat !== '' && $this->khachBiTrungLich($sdtKhach, $ngayDat, $gioDat, 0)) {
            return array(
                'success' => false,
                'error' => 'trung_sdt_khung_gio',
                'thong_bao' => 'So dien thoai nay da co dat ban trong cung phien 90 phut'
            );
        }

        $id = $this->taoMaDatBan();

        $sql = "
    INSERT INTO dat_ban
        (id_dat_ban, ma_dat_ban, id_khach_tai_khoan, ten_khach, sdt_khach, ngay_dat, gio_dat,
         so_nguoi_lon, so_tre_em, ghi_chu, trang_thai)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

        $idKhachTaiKhoan = null;

        if (
            isset($du_lieu['id_khach_tai_khoan']) &&
            trim((string)$du_lieu['id_khach_tai_khoan']) !== ''
        ) {
            $idKhachTaiKhoan = $du_lieu['id_khach_tai_khoan'];
        }

        $ok = $this->db->query($sql, array(
            $id,
            $id,
            $idKhachTaiKhoan,
            $du_lieu['ten_khach'],
            $du_lieu['sdt_khach'],
            $du_lieu['ngay_dat'],
            $du_lieu['gio_dat'],
            (int)$du_lieu['so_nguoi_lon'],
            (int)$du_lieu['so_tre_em'],
            $du_lieu['ghi_chu'],
            $du_lieu['trang_thai']
        ));

        if ($ok) {
            return array('id' => $id, 'ma_dat_ban' => $id);
        }

        return false;
    }

    public function capNhatTrangThai($id, $trang_thai)
    {
        if ($trang_thai === 'cancelled') {
            $trang_thai = 'da_huy';
        }
        $sql = "UPDATE dat_ban SET trang_thai = ?, updated_at = NOW() WHERE id_dat_ban = ?";
        $ok = $this->db->query($sql, array($trang_thai, $id));

        if ($ok && ($trang_thai === 'da_huy' || $trang_thai === 'expired' || $trang_thai === 'hoan_thanh')) {
            $this->capNhatNhieuBan($id, array());
        }
        return $ok;
    }

    public function capNhatBan($id, $ban_id)
    {
        return $this->capNhatNhieuBan($id, $ban_id !== '' ? array($ban_id) : array());
    }

    public function capNhatNhieuBan($id, $ban_ids)
    {
        $danhSachHopLe = array();
        foreach ($ban_ids as $ban_id) {
            $ban_id = trim((string)$ban_id);
            if ($ban_id !== '' && !in_array($ban_id, $danhSachHopLe)) {
                $danhSachHopLe[] = $ban_id;
            }
        }

        $banCuRows = $this->db->query("SELECT id_ban FROM chitiet_datban WHERE id_dat_ban = ?", array($id));
        $this->db->query("DELETE FROM chitiet_datban WHERE id_dat_ban = ?", array($id));
        foreach ($banCuRows as $row) {
            if (!in_array($row['id_ban'], $danhSachHopLe)) {
                $this->db->query("
                    INSERT INTO lich_su_dieu_phoi_ban
                        (id_lich_su_dieu_phoi, id_dat_ban, id_ban_cu, hanh_dong, ghi_chu)
                    VALUES (?, ?, ?, 'HUY_GAN_BAN', 'Huy gan ban dat truoc')
                ", array($this->taoIdLichSuDieuPhoi(), $id, $row['id_ban']));
            }
        }
        foreach ($danhSachHopLe as $ban_id) {
            $this->db->query(
                "INSERT IGNORE INTO chitiet_datban
                    (id_chitiet_datban, id_dat_ban, id_ban, thoi_gian_bat_dau, thoi_gian_ket_thuc, trang_thai)
                 SELECT ?, id_dat_ban, ?, TIMESTAMP(ngay_dat, gio_dat), DATE_ADD(TIMESTAMP(ngay_dat, gio_dat), INTERVAL 90 MINUTE), 'dang_gan'
                 FROM dat_ban WHERE id_dat_ban = ?",
                array($this->taoIdChiTietDatBan(), $ban_id, $id)
            );
            $this->db->query("
                INSERT INTO lich_su_dieu_phoi_ban
                    (id_lich_su_dieu_phoi, id_dat_ban, id_ban_moi, hanh_dong, ghi_chu)
                VALUES (?, ?, ?, 'GAN_BAN', 'Gan ban cho dat truoc')
            ", array($this->taoIdLichSuDieuPhoi(), $id, $ban_id));
        }
        return true;
    }

    public function layBanIdsTheoDatBan($id)
    {
        $rows = $this->db->query(
            "SELECT id_ban AS ban_id FROM chitiet_datban WHERE id_dat_ban = ? ORDER BY id_chitiet_datban ASC",
            array($id)
        );
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row['ban_id'];
        }
        return $ids;
    }

    public function xacNhanGanBan($id)
    {
        $sql = "UPDATE dat_ban SET trang_thai = 'da_xac_nhan', updated_at = NOW() WHERE id_dat_ban = ?";
        return $this->db->query($sql, array($id));
    }

    public function boXacNhanGanBan($id)
    {
        return true;
    }

    public function layChuaXacNhanBan()
    {
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.trang_thai = 'cho_xac_nhan'
          AND EXISTS (SELECT 1 FROM chitiet_datban ct WHERE ct.id_dat_ban = r.id_dat_ban)
        " . $this->nhomVaSapXep("ORDER BY r.ngay_dat ASC, r.gio_dat ASC");
        return $this->db->query($sql);
    }

    public function layDanhSachLocNang($trang_thai, $tu_khoa, $chi_chua_xac_nhan_ban, $ngay_dat)
    {
        $where = array();
        $params = array();

        if ($trang_thai !== '') {
            if ($trang_thai === 'cancelled' || $trang_thai === 'da_huy') {
                $where[] = "r.trang_thai IN ('da_huy', 'expired')";
            } else {
                $where[] = 'r.trang_thai = ?';
                $params[] = $trang_thai;
            }
        } else {
            $where[] = "r.trang_thai NOT IN ('da_huy', 'expired')";
        }

        if ($tu_khoa !== '') {
            $k = '%' . $tu_khoa . '%';
            $where[] = '(r.ten_khach LIKE ? OR r.sdt_khach LIKE ? OR r.id_dat_ban LIKE ?)';
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
        $sql .= ' GROUP BY r.id_dat_ban ORDER BY r.created_at ASC, r.id_dat_ban ASC';
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

    public function layLichSuTheoSDT($sdt)
    {
        $sql = $this->selectDatBanVoiBan() . "
        WHERE r.sdt_khach = ?
        " . $this->nhomVaSapXep("ORDER BY r.created_at DESC");
        return $this->db->query($sql, array($sdt));
    }
}
