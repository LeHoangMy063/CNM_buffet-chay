<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

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
