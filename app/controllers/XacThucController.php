<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class XacThucController extends BoieuKhienCo
{
    protected $moHinhTaiKhoan;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhTaiKhoan = new MoHinhTaiKhoan();
    }

    // ================= DANG NHAP STAFF/ADMIN =================
    public function hienThiDangNhap()
    {
        if ($this->daDangNhap()) {
            $vai_tro = isset($_SESSION['nguoi_dung']['vai_tro']) ? $_SESSION['nguoi_dung']['vai_tro'] : '';
            if ($vai_tro === 'admin') {
                $this->chuyenHuong(BASE_URL . '/quan-tri/tong-quan');
            } elseif ($vai_tro === 'nhan_vien' || $vai_tro === 'bep') {
                $this->chuyenHuong(BASE_URL . '/nhan-vien/tong-quan');
            }
        }
        $this->view('auth/dang-nhap');
    }

    public function xuLyDangNhap()
    {
        $ten_dang_nhap = trim($this->post('ten_dang_nhap', ''));
        $mat_khau      = $this->post('mat_khau', '');

        if ($ten_dang_nhap === '' || $mat_khau === '') {
            echo json_encode(array('success' => false, 'thong_bao' => 'Vui long nhap day du thong tin'));
            return;
        }

        $tai_khoan = $this->moHinhTaiKhoan->layTheoTenDangNhap($ten_dang_nhap);

        if (!$tai_khoan) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Sai ten dang nhap hoac mat khau'));
            return;
        }

        if (md5($mat_khau) !== $tai_khoan['mat_khau']) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Sai ten dang nhap hoac mat khau'));
            return;
        }

        if ($tai_khoan['dang_hoat_dong'] == 0) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Tai khoan da bi khoa'));
            return;
        }

        if ($tai_khoan['vai_tro'] === 'khach') {
            echo json_encode(array('success' => false, 'thong_bao' => 'Vui long dung trang dang nhap khach hang'));
            return;
        }

        $_SESSION['nguoi_dung'] = array(
            'id'             => $tai_khoan['id'],
            'ten_dang_nhap'  => $tai_khoan['ten_dang_nhap'],
            'vai_tro'        => $tai_khoan['vai_tro'],
            'ho_ten'         => $tai_khoan['ho_ten'],
            'dang_hoat_dong' => $tai_khoan['dang_hoat_dong']
        );
        $_SESSION['thoi_gian_hoat_dong'] = time();

        $vai_tro = $tai_khoan['vai_tro'];
        if ($vai_tro === 'admin') {
            $url = BASE_URL . '/quan-tri/tong-quan';
        } else {
            $url = BASE_URL . '/nhan-vien/tong-quan';
        }

        echo json_encode(array('success' => true, 'chuyen_huong' => $url));
    }

    // ================= DANG NHAP KHACH HANG =================
    public function hienThiDangNhapKhach()
    {
        $this->view('auth/dang-nhap-khach');
    }

    public function xuLyDangNhapKhach()
    {
        $dang_nhap = trim($this->post('dang_nhap', ''));
        $mat_khau  = $this->post('mat_khau', '');

        if ($dang_nhap === '' || $mat_khau === '') {
            echo json_encode(array('success' => false, 'thong_bao' => 'Vui long nhap day du thong tin'));
            return;
        }

        $tai_khoan = $this->moHinhTaiKhoan->layTheoSDT($dang_nhap);

        if (!$tai_khoan) {
            $tai_khoan = $this->moHinhTaiKhoan->layTheoEmail($dang_nhap);
        }

        if (!$tai_khoan) {
            echo json_encode(array('success' => false, 'thong_bao' => 'So dien thoai/Gmail chua duoc dang ky'));
            return;
        }

        if ($tai_khoan['vai_tro'] !== 'khach') {
            echo json_encode(array('success' => false, 'thong_bao' => 'Tai khoan nay khong co quyen truy cap'));
            return;
        }

        if (md5($mat_khau) !== $tai_khoan['mat_khau']) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Mat khau khong chinh xac'));
            return;
        }

        if ($tai_khoan['dang_hoat_dong'] == 0) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Tai khoan da bi khoa'));
            return;
        }

        $_SESSION['nguoi_dung'] = array(
            'id'             => $tai_khoan['id'],
            'ten_dang_nhap'  => $tai_khoan['ten_dang_nhap'],
            'vai_tro'        => $tai_khoan['vai_tro'],
            'ho_ten'         => $tai_khoan['ho_ten'],
            'dang_hoat_dong' => $tai_khoan['dang_hoat_dong']
        );
        $_SESSION['thoi_gian_hoat_dong'] = time();

        echo json_encode(array('success' => true, 'chuyen_huong' => BASE_URL . '/'));
    }

    // ================= DANG KY =================
    public function hienThiDangKy()
    {
        $this->view('auth/dang-ky');
    }

    public function xuLyDangKy()
    {
        $hoTen       = trim($this->post('ho_ten', ''));
        $soDienThoai = trim($this->post('so_dien_thoai', ''));
        $email       = trim($this->post('email', ''));
        $matKhau     = $this->post('mat_khau', '');
        $xacNhanMk   = $this->post('xac_nhan_mat_khau', '');

        if ($hoTen === '' || $soDienThoai === '' || $matKhau === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap day du thong tin bat buoc'));
            return;
        }

        if ($matKhau !== $xacNhanMk) {
            $this->json(array('success' => false, 'thong_bao' => 'Mat khau xac nhan khong khop'));
            return;
        }

        if (strlen($matKhau) < 6) {
            $this->json(array('success' => false, 'thong_bao' => 'Mat khau phai co it nhat 6 ky tu'));
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(array('success' => false, 'thong_bao' => 'Email khong hop le'));
            return;
        }

        $ton_tai = $this->moHinhTaiKhoan->layTheoSDT($soDienThoai);
        if ($ton_tai) {
            $this->json(array('success' => false, 'thong_bao' => 'So dien thoai nay da duoc dang ky'));
            return;
        }

        $id = $this->moHinhTaiKhoan->dangKy($hoTen, $soDienThoai, $email, $matKhau);

        if ($id) {
            $this->json(array(
                'success'      => true,
                'thong_bao'    => 'Dang ky thanh cong! Vui long dang nhap de tiep tuc.',
                'chuyen_huong' => BASE_URL . '/khach/dang-nhap'
            ));
        } else {
            $this->json(array('success' => false, 'thong_bao' => 'Loi he thong, vui long thu lai'));
        }
    }

    // ================= DANG XUAT =================
    public function dangXuat()
    {
        $vai_tro = isset($_SESSION['nguoi_dung']['vai_tro']) ? $_SESSION['nguoi_dung']['vai_tro'] : '';

        $_SESSION = array();

        if (ini_get('session.use_cookies')) {
            $thamSo = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $thamSo['path'],
                $thamSo['domain'],
                $thamSo['secure'],
                $thamSo['httponly']
            );
        }

        session_destroy();
        session_start();
        session_regenerate_id(true);

        if ($vai_tro === 'khach') {
            $this->chuyenHuong(BASE_URL . '/');
        }

        $this->chuyenHuong(BASE_URL . '/dang-nhap');
    }
}
