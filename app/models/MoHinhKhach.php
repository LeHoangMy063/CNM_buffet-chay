<?php
require_once dirname(__FILE__) . '/MoHinhCo.php';
require_once dirname(__FILE__) . '/../core/MatKhau.php';

class MoHinhKhach extends MoHinhCo
{
    private function taoIdKhachHang()
    {
        $ma = $this->taoId('KH' . date('Ym'));
        return preg_replace('/^KH([0-9]{6})-/', 'KH-$1-', $ma);
    }

    private function nangCapMatKhauMacDinhCu()
    {
        $this->db->query(
            "UPDATE khach_tai_khoan SET mat_khau = ? WHERE mat_khau = ?",
            array('$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'e10adc3949ba59abbe56e057f20f883e')
        );
        $this->db->query(
            "UPDATE khach_tai_khoan SET mat_khau = ? WHERE mat_khau REGEXP '^[a-f0-9]{32}$'",
            array(MatKhau::maHoa('123456'))
        );
    }

    private function damBaoBangKhachTaiKhoan()
    {
        $this->nangCapMatKhauMacDinhCu();
    }

    private function dongBoKhachCuTuTaiKhoan()
    {
        $bangTaiKhoan = $this->db->query(
            "SELECT COUNT(*) AS co
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tai_khoan'"
        );

        if (empty($bangTaiKhoan) || (int)$bangTaiKhoan[0]['co'] === 0) {
            return;
        }

        $this->db->query(
            "INSERT IGNORE INTO khach_tai_khoan
                (ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
                 ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao)
             SELECT ten_dang_nhap, mat_khau, 'khach', dang_hoat_dong,
                    ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao
             FROM tai_khoan
             WHERE vai_tro = 'khach'
               AND so_dien_thoai IS NOT NULL
               AND so_dien_thoai <> ''"
        );
    }

    private function damBaoCotKhachTaiKhoan($cot, $lenhThem)
    {
        $rows = $this->db->query(
            "SELECT COUNT(*) AS co
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'khach_tai_khoan'
               AND COLUMN_NAME = ?",
            array($cot)
        );

        if (empty($rows) || (int)$rows[0]['co'] === 0) {
            $this->db->query($lenhThem);
        }
    }

    public function layTheoSDT($sdt)
    {
        $this->damBaoBangKhachTaiKhoan();
        $sql = "SELECT id_khach_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro,
                       dang_hoat_dong, ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao
                FROM khach_tai_khoan WHERE so_dien_thoai = ? LIMIT 1";
        $rows = $this->db->query($sql, array($sdt));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoEmail($email)
    {
        $this->damBaoBangKhachTaiKhoan();
        $sql = "SELECT id_khach_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro,
                       dang_hoat_dong, ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao
                FROM khach_tai_khoan WHERE email = ? LIMIT 1";
        $rows = $this->db->query($sql, array($email));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoId($id)
    {
        $this->damBaoBangKhachTaiKhoan();
        $sql = "SELECT id_khach_tai_khoan AS id, ten_dang_nhap, mat_khau, vai_tro,
                       dang_hoat_dong, ho_ten, email, so_dien_thoai, diem_tich_luy, ngay_tao
                FROM khach_tai_khoan WHERE id_khach_tai_khoan = ? LIMIT 1";
        $rows = $this->db->query($sql, array($id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoDangNhapQuenMatKhau($dangNhap)
    {
        $dangNhap = trim($dangNhap);
        if ($dangNhap === '') {
            return null;
        }

        if (strpos($dangNhap, '@') !== false) {
            return $this->layTheoEmail($dangNhap);
        }

        return $this->layTheoSDT($dangNhap);
    }

    public function dangKy($ho_ten, $so_dien_thoai, $email, $mat_khau)
    {
        $this->damBaoBangKhachTaiKhoan();

        $ton_tai = $this->layTheoSDT($so_dien_thoai);
        if ($ton_tai) {
            return false;
        }

        $id = $this->taoIdKhachHang();
        $sql = "INSERT INTO khach_tai_khoan
            (id_khach_tai_khoan, ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
             ho_ten, email, so_dien_thoai, diem_tich_luy)
         VALUES (?, ?, ?, 'khach', 1, ?, ?, ?, 0)";

        $ok = $this->db->query($sql, array(
            $id,
            $so_dien_thoai,
            MatKhau::maHoa($mat_khau),
            $ho_ten,
            $email,
            $so_dien_thoai
        ));

        return $ok ? $id : false;
    }

    public function congDiem($id, $diem)
    {
        $this->damBaoBangKhachTaiKhoan();

        $diem = (int)$diem;
        $sql = "UPDATE khach_tai_khoan SET diem_tich_luy = diem_tich_luy + ? WHERE id_khach_tai_khoan = ?";
        return $this->db->query($sql, array($diem, $id));
    }

    public function truDiem($id, $diem)
    {
        $this->damBaoBangKhachTaiKhoan();

        $diem = (int)$diem;
        $sql = "UPDATE khach_tai_khoan SET diem_tich_luy = diem_tich_luy - ? WHERE id_khach_tai_khoan = ?";
        return $this->db->query($sql, array($diem, $id));
    }

    public function capNhatMatKhauDaMaHoa($id, $matKhauDaMaHoa)
    {
        $this->damBaoBangKhachTaiKhoan();

        $sql = "UPDATE khach_tai_khoan SET mat_khau = ? WHERE id_khach_tai_khoan = ?";
        return $this->db->query($sql, array($matKhauDaMaHoa, $id));
    }

    public function huyTokenDatLaiMatKhauCu($idKhach)
    {
        $sql = "UPDATE dat_lai_mat_khau
                SET da_su_dung = 1, used_at = NOW()
                WHERE id_khach_tai_khoan = ?
                  AND da_su_dung = 0";
        return $this->db->query($sql, array($idKhach));
    }

    public function taoYeuCauDatLaiMatKhau($idKhach, $kenhGui, $diaChiGui, $tokenHash, $hetHanLuc)
    {
        $id = $this->taoId('RESET' . date('Ymd'), 4, false);
        $sql = "INSERT INTO dat_lai_mat_khau
                    (id_dat_lai_mat_khau, id_khach_tai_khoan, kenh_gui, dia_chi_gui, token_hash, het_han_luc)
                VALUES (?, ?, ?, ?, ?, ?)";
        $ok = $this->db->query($sql, array($id, $idKhach, $kenhGui, $diaChiGui, $tokenHash, $hetHanLuc));
        return $ok ? $id : false;
    }

    public function layYeuCauDatLaiMatKhauTheoHash($tokenHash)
    {
        $sql = "SELECT r.id_dat_lai_mat_khau, r.id_khach_tai_khoan, r.het_han_luc, r.da_su_dung,
                       k.id_khach_tai_khoan AS id, k.ten_dang_nhap, k.vai_tro, k.dang_hoat_dong,
                       k.ho_ten, k.email, k.so_dien_thoai
                FROM dat_lai_mat_khau r
                INNER JOIN khach_tai_khoan k ON k.id_khach_tai_khoan = r.id_khach_tai_khoan
                WHERE r.token_hash = ?
                  AND r.da_su_dung = 0
                  AND r.het_han_luc >= NOW()
                  AND k.dang_hoat_dong = 1
                LIMIT 1";
        $rows = $this->db->query($sql, array($tokenHash));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layYeuCauDatLaiMatKhauOtp($idDatLaiMatKhau, $otpHash, $kenhGui)
    {
        $sql = "SELECT r.id_dat_lai_mat_khau, r.id_khach_tai_khoan, r.het_han_luc, r.da_su_dung,
                       r.so_lan_thu, k.id_khach_tai_khoan AS id, k.ten_dang_nhap, k.vai_tro,
                       k.dang_hoat_dong, k.ho_ten, k.email, k.so_dien_thoai
                FROM dat_lai_mat_khau r
                INNER JOIN khach_tai_khoan k ON k.id_khach_tai_khoan = r.id_khach_tai_khoan
                WHERE r.id_dat_lai_mat_khau = ?
                  AND r.token_hash = ?
                  AND r.kenh_gui = ?
                  AND r.da_su_dung = 0
                  AND r.het_han_luc >= NOW()
                  AND k.dang_hoat_dong = 1
                LIMIT 1";
        $rows = $this->db->query($sql, array($idDatLaiMatKhau, $otpHash, $kenhGui));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layYeuCauDatLaiMatKhauTheoId($idDatLaiMatKhau)
    {
        $sql = "SELECT r.id_dat_lai_mat_khau, r.id_khach_tai_khoan, r.kenh_gui, r.het_han_luc,
                       r.da_su_dung, r.so_lan_thu, k.ho_ten, k.so_dien_thoai
                FROM dat_lai_mat_khau r
                INNER JOIN khach_tai_khoan k ON k.id_khach_tai_khoan = r.id_khach_tai_khoan
                WHERE r.id_dat_lai_mat_khau = ?
                  AND r.da_su_dung = 0
                  AND r.het_han_luc >= NOW()
                LIMIT 1";
        $rows = $this->db->query($sql, array($idDatLaiMatKhau));
        return !empty($rows) ? $rows[0] : null;
    }

    public function tangSoLanThuDatLaiMatKhau($idDatLaiMatKhau)
    {
        $sql = "UPDATE dat_lai_mat_khau SET so_lan_thu = so_lan_thu + 1 WHERE id_dat_lai_mat_khau = ?";
        return $this->db->query($sql, array($idDatLaiMatKhau));
    }

    public function danhDauTokenDatLaiMatKhauDaDung($idDatLaiMatKhau)
    {
        $sql = "UPDATE dat_lai_mat_khau
                SET da_su_dung = 1, used_at = NOW()
                WHERE id_dat_lai_mat_khau = ?";
        return $this->db->query($sql, array($idDatLaiMatKhau));
    }
}
