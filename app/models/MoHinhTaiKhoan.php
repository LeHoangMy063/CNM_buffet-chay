<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';
require_once dirname(__FILE__) . '/../core/MatKhau.php';

class MoHinhTaiKhoan extends MoHinhCo
{
    private function nangCapMatKhauMacDinhCu()
    {
        $this->db->query(
            "UPDATE tai_khoan SET mat_khau = ? WHERE mat_khau = ?",
            array('$2y$10$mYFxzdEx8VM1P2TBvXAc5.ez9b8L9zupQll/yqTZCXVjjHJpjcok.', 'e6e061838856bf47e1de730719fb2609')
        );
        $this->db->query(
            "UPDATE tai_khoan SET mat_khau = ? WHERE mat_khau = ?",
            array('$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', '202cb962ac59075b964b07152d234b70')
        );
        $this->db->query(
            "UPDATE tai_khoan SET mat_khau = ? WHERE mat_khau REGEXP '^[a-f0-9]{32}$'",
            array(MatKhau::maHoa('123'))
        );
    }

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
            $this->nangCapMatKhauMacDinhCu();
            return;
        }

        $this->db->query("
            ALTER TABLE tai_khoan
            MODIFY vai_tro enum('quanly','nhanvien','bep') NOT NULL default 'nhanvien'
        ");
        $this->nangCapMatKhauMacDinhCu();
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
                array(MatKhau::maHoa('123'), 'nhanvien02')
            );
        }

        return $this->db->query(
            "INSERT INTO tai_khoan
                (ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong, ho_ten, email, so_dien_thoai, diem_tich_luy)
             VALUES (?, ?, 'bep', 1, ?, NULL, NULL, 0)",
            array('nhanvien02', MatKhau::maHoa('123'), 'Nhan vien bep')
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
            MatKhau::maHoa($du_lieu['mat_khau']),
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
            $params[] = MatKhau::maHoa($du_lieu['mat_khau']);
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
        return $this->db->query($sql, array(MatKhau::maHoa($mat_khau_moi), (int)$id));
    }

    public function capNhatMatKhauDaMaHoa($id, $matKhauDaMaHoa)
    {
        $sql = "UPDATE tai_khoan SET mat_khau = ? WHERE id = ?";
        return $this->db->query($sql, array($matKhauDaMaHoa, (int)$id));
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
