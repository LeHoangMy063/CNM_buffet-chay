<?php
require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhKhach extends MoHinhCo
{
    private function damBaoBangKhachTaiKhoan()
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS khach_tai_khoan (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->damBaoCotKhachTaiKhoan('ten_dang_nhap', "ALTER TABLE khach_tai_khoan ADD COLUMN ten_dang_nhap varchar(50) DEFAULT NULL");
        $this->damBaoCotKhachTaiKhoan('mat_khau', "ALTER TABLE khach_tai_khoan ADD COLUMN mat_khau varchar(255) DEFAULT NULL");
        $this->damBaoCotKhachTaiKhoan('vai_tro', "ALTER TABLE khach_tai_khoan ADD COLUMN vai_tro enum('khach') NOT NULL default 'khach'");
        $this->damBaoCotKhachTaiKhoan('dang_hoat_dong', "ALTER TABLE khach_tai_khoan ADD COLUMN dang_hoat_dong tinyint(1) NOT NULL default 1");
        $this->damBaoCotKhachTaiKhoan('ho_ten', "ALTER TABLE khach_tai_khoan ADD COLUMN ho_ten varchar(100) DEFAULT NULL");
        $this->damBaoCotKhachTaiKhoan('email', "ALTER TABLE khach_tai_khoan ADD COLUMN email varchar(100) DEFAULT NULL");
        $this->damBaoCotKhachTaiKhoan('so_dien_thoai', "ALTER TABLE khach_tai_khoan ADD COLUMN so_dien_thoai varchar(20) DEFAULT NULL");
        $this->damBaoCotKhachTaiKhoan('diem_tich_luy', "ALTER TABLE khach_tai_khoan ADD COLUMN diem_tich_luy int(11) NOT NULL default 0");
        $this->damBaoCotKhachTaiKhoan('ngay_tao', "ALTER TABLE khach_tai_khoan ADD COLUMN ngay_tao timestamp NOT NULL default CURRENT_TIMESTAMP");
        $this->dongBoKhachCuTuTaiKhoan();
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
        $sql = "SELECT * FROM khach_tai_khoan WHERE so_dien_thoai = ? LIMIT 1";
        $rows = $this->db->query($sql, array($sdt));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoEmail($email)
    {
        $this->damBaoBangKhachTaiKhoan();
        $sql = "SELECT * FROM khach_tai_khoan WHERE email = ? LIMIT 1";
        $rows = $this->db->query($sql, array($email));
        return !empty($rows) ? $rows[0] : null;
    }

    public function layTheoId($id)
    {
        $this->damBaoBangKhachTaiKhoan();
        $sql = "SELECT * FROM khach_tai_khoan WHERE id = ? LIMIT 1";
        $rows = $this->db->query($sql, array((int)$id));
        return !empty($rows) ? $rows[0] : null;
    }

    public function dangKy($ho_ten, $so_dien_thoai, $email, $mat_khau)
    {
        $this->damBaoBangKhachTaiKhoan();

        $ton_tai = $this->layTheoSDT($so_dien_thoai);
        if ($ton_tai) {
            return false;
        }

        $sql = "INSERT INTO khach_tai_khoan
            (ten_dang_nhap, mat_khau, vai_tro, dang_hoat_dong,
             ho_ten, email, so_dien_thoai, diem_tich_luy)
         VALUES (?, ?, 'khach', 1, ?, ?, ?, 0)";

        $ok = $this->db->query($sql, array(
            $so_dien_thoai,
            md5($mat_khau),
            $ho_ten,
            $email,
            $so_dien_thoai
        ));

        return $ok ? $this->db->lastInsertId() : false;
    }

    public function congDiem($id, $diem)
    {
        $this->damBaoBangKhachTaiKhoan();

        $sql = "UPDATE khach_tai_khoan SET diem_tich_luy = diem_tich_luy + ? WHERE id = ?";
        return $this->db->query($sql, array((int)$diem, (int)$id));
    }
}
