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
        $this->nangCapMatKhauMacDinhCu();
    }

    // Dang nhap staff/admin bang ten_dang_nhap
    public function layTheoTenDangNhap($ten_dang_nhap)
    {
        $this->damBaoVaiTroQuanLy();

        $sql  = "SELECT id_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                        ho_ten, email, so_dien_thoai, 0 AS diem_tich_luy, ngay_tao
                 FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1";
        $rows = $this->db->query($sql, array($ten_dang_nhap));
        return !empty($rows) ? $rows[0] : null;
    }

    public function damBaoTaiKhoanBepMacDinh()
    {
        $this->damBaoVaiTroQuanLy();

        $rows = $this->db->query(
            "SELECT id_tai_khoan AS id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1",
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
                (id_tai_khoan, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong, ho_ten, email, so_dien_thoai)
             VALUES (?, ?, ?, 'bep', 1, ?, NULL, NULL)",
            array($this->taoId('TK-BEP'), 'nhanvien02', MatKhau::maHoa('123'), 'Nhan vien bep')
        );
    }

    // Tim tai khoan nhan su theo so dien thoai
    public function layTheoSDT($sdt)
    {
        $sql  = "SELECT id_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                        ho_ten, email, so_dien_thoai, 0 AS diem_tich_luy, ngay_tao
                 FROM tai_khoan WHERE so_dien_thoai = ? LIMIT 1";
        $rows = $this->db->query($sql, array($sdt));
        return !empty($rows) ? $rows[0] : null;
    }

    // Tim tai khoan nhan su theo email
    public function layTheoEmail($email)
    {
        $sql  = "SELECT id_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                        ho_ten, email, so_dien_thoai, 0 AS diem_tich_luy, ngay_tao
                 FROM tai_khoan WHERE email = ? LIMIT 1";
        $rows = $this->db->query($sql, array($email));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTatCa()
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "SELECT id_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                       ho_ten, email, so_dien_thoai, 0 AS diem_tich_luy, ngay_tao
                FROM tai_khoan ORDER BY vai_tro, ngay_tao DESC";
        return $this->db->query($sql);
    }

    public function layNhanVienQuanLy()
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "
        SELECT id_tai_khoan AS id, ten_dang_nhap, vai_tro, dang_hoat_dong,
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

        $sql = "SELECT id_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                       ho_ten, email, so_dien_thoai, 0 AS diem_tich_luy, ngay_tao
                FROM tai_khoan WHERE id_tai_khoan = ? LIMIT 1";
        $rows = $this->db->query($sql, array($id));
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
            (id_tai_khoan, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
             ho_ten, email, so_dien_thoai)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $prefix = $du_lieu['vai_tro'] === 'quanly' ? 'TK-QL' : ($du_lieu['vai_tro'] === 'bep' ? 'TK-BEP' : 'TK-NV');
        $id = $this->taoId($prefix);
        $ok = $this->db->query($sql, array(
            $id,
            $du_lieu['ten_dang_nhap'],
            MatKhau::maHoa($du_lieu['mat_khau']),
            $du_lieu['vai_tro'],
            isset($du_lieu['dang_hoat_dong']) ? (int)$du_lieu['dang_hoat_dong'] : 1,
            $du_lieu['ho_ten'],
            $du_lieu['email'],
            $du_lieu['so_dien_thoai']
        ));
        return $ok ? $id : false;
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

        $sql .= " WHERE id_tai_khoan = ?";
        $params[] = $id;

        return $this->db->query($sql, $params);
    }

    public function xoaNhanVien($id)
    {
        $this->damBaoVaiTroQuanLy();

        $sql = "DELETE FROM tai_khoan WHERE id_tai_khoan = ? AND vai_tro IN ('quanly', 'nhanvien', 'bep')";
        return $this->db->query($sql, array($id));
    }

    // Khoa / mo khoa tai khoan (Usecase 115)
    public function capNhatTrangThai($id, $trang_thai)
    {
        $sql = "UPDATE tai_khoan SET dang_hoat_dong = ? WHERE id_tai_khoan = ?";
        return $this->db->query($sql, array((int)$trang_thai, $id));
    }

    // Diem tich luy cua khach nam trong khach_tai_khoan.
    public function congDiem($id, $diem)
    {
        return false;
    }

    // Doi mat khau (Usecase 110)
    public function doiMatKhau($id, $mat_khau_moi)
    {
        $sql = "UPDATE tai_khoan SET mat_khau = ? WHERE id_tai_khoan = ?";
        return $this->db->query($sql, array(MatKhau::maHoa($mat_khau_moi), $id));
    }

    public function capNhatMatKhauDaMaHoa($id, $matKhauDaMaHoa)
    {
        $sql = "UPDATE tai_khoan SET mat_khau = ? WHERE id_tai_khoan = ?";
        return $this->db->query($sql, array($matKhauDaMaHoa, $id));
    }

    // Cap nhat thong tin ca nhan (Usecase 109)
    public function capNhatThongTin($id, $ho_ten, $email)
    {
        $sql = "UPDATE tai_khoan SET ho_ten = ?, email = ? WHERE id_tai_khoan = ?";
        return $this->db->query($sql, array($ho_ten, $email, $id));
    }
}

// ================= MO HINH DANH GIA =================
// Usecase 116: Danh gia va binh luan mon an
