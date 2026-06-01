<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/../models/MoHinhKhach.php';
require_once dirname(__FILE__) . '/../core/MatKhau.php';
require_once dirname(__FILE__) . '/../core/DichVuEmail.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class XacThucController extends BoieuKhienCo
{
    protected $moHinhTaiKhoan;
    protected $moHinhKhach;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhTaiKhoan = new MoHinhTaiKhoan();
        $this->moHinhKhach = new MoHinhKhach();
    }

    // ================= DANG NHAP STAFF/ADMIN =================
    public function hienThiDangNhap()
    {
        if ($this->daDangNhap()) {
            $vai_tro = isset($_SESSION['nguoi_dung']['vai_tro']) ? $_SESSION['nguoi_dung']['vai_tro'] : '';
            if ($vai_tro === 'quanly') {
                $this->chuyenHuong(BASE_URL . '/quan-ly/bao-cao');
            } elseif ($vai_tro === 'nhanvien' || $vai_tro === 'bep') {
                $this->chuyenHuong(BASE_URL . '/nhan-vien/tong-quan');
            }
        }
        $this->view('auth/dang-nhap');
    }

    public function xuLyDangNhap()
    {
        // Debug log: ghi request để chẩn đoán AJAX/redirect
        @mkdir(dirname(__FILE__) . '/../../tmp');
        $logFile = dirname(__FILE__) . '/../../tmp/login-debug.log';
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $headers = function_exists('getallheaders') ? getallheaders() : array();
        $log = date('[Y-m-d H:i:s]') . " METHOD=" . $method . " URI=" . $uri . " POST=" . json_encode($_POST) . " HEADERS=" . json_encode($headers) . "\n";
        @file_put_contents($logFile, $log, FILE_APPEND);

        $ten_dang_nhap = trim($this->post('ten_dang_nhap', ''));
        $mat_khau      = $this->post('mat_khau', '');

        if ($ten_dang_nhap === '' || $mat_khau === '') {
            echo json_encode(array('success' => false, 'thong_bao' => 'Vui long nhap day du thong tin'));
            return;
        }

        if ($ten_dang_nhap === 'nhanvien02') {
            $this->moHinhTaiKhoan->damBaoTaiKhoanBepMacDinh();
        }

        $tai_khoan = $this->moHinhTaiKhoan->layTheoTenDangNhap($ten_dang_nhap);

        if (!$tai_khoan) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Sai ten dang nhap hoac mat khau'));
            return;
        }

        if (!MatKhau::hopLe($mat_khau, $tai_khoan['mat_khau'])) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Sai ten dang nhap hoac mat khau'));
            return;
        }

        if (MatKhau::canNangCap($tai_khoan['mat_khau'])) {
            $this->moHinhTaiKhoan->capNhatMatKhauDaMaHoa($tai_khoan['id'], MatKhau::maHoa($mat_khau));
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

        // Mirror session for older/common checks that use `user`
        $_SESSION['user'] = array(
            'id'       => $tai_khoan['id'],
            'username' => $tai_khoan['ten_dang_nhap'],
            'role'     => $tai_khoan['vai_tro'],
            'ho_ten'   => $tai_khoan['ho_ten']
        );

        $vai_tro = $tai_khoan['vai_tro'];
        if ($vai_tro === 'quanly') {
            $url = BASE_URL . '/quan-ly/bao-cao';
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

        $tai_khoan = $this->moHinhKhach->layTheoSDT($dang_nhap);

        if (!$tai_khoan) {
            $tai_khoan = $this->moHinhKhach->layTheoEmail($dang_nhap);
        }

        if (!$tai_khoan) {
            echo json_encode(array('success' => false, 'thong_bao' => 'So dien thoai/Gmail chua duoc dang ky'));
            return;
        }

        if ($tai_khoan['vai_tro'] !== 'khach') {
            echo json_encode(array('success' => false, 'thong_bao' => 'Tai khoan nay khong co quyen truy cap'));
            return;
        }

        if (!MatKhau::hopLe($mat_khau, $tai_khoan['mat_khau'])) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Mat khau khong chinh xac'));
            return;
        }

        if (MatKhau::canNangCap($tai_khoan['mat_khau'])) {
            $this->moHinhKhach->capNhatMatKhauDaMaHoa($tai_khoan['id'], MatKhau::maHoa($mat_khau));
        }

        if ($tai_khoan['dang_hoat_dong'] == 0) {
            echo json_encode(array('success' => false, 'thong_bao' => 'Tai khoan da bi khoa'));
            return;
        }

        $_SESSION['nguoi_dung'] = array(
            'id'              => $tai_khoan['id'],
            'ten_dang_nhap'   => $tai_khoan['ten_dang_nhap'],
            'vai_tro'         => $tai_khoan['vai_tro'],
            'ho_ten'          => $tai_khoan['ho_ten'],
            'so_dien_thoai'   => $tai_khoan['so_dien_thoai'],
            'email'           => $tai_khoan['email'],
            'dang_hoat_dong'  => $tai_khoan['dang_hoat_dong']
        );
        $_SESSION['thoi_gian_hoat_dong'] = time();

        // Mirror session for code that expects `user`
        $_SESSION['user'] = array(
            'id'             => $tai_khoan['id'],
            'username'       => $tai_khoan['ten_dang_nhap'],
            'role'           => $tai_khoan['vai_tro'],
            'ho_ten'         => $tai_khoan['ho_ten'],
            'so_dien_thoai'  => $tai_khoan['so_dien_thoai'],
            'email'          => $tai_khoan['email']
        );

        echo json_encode(array('success' => true, 'chuyen_huong' => BASE_URL . '/'));
    }

    // ================= QUEN MAT KHAU KHACH HANG =================
    public function hienThiQuenMatKhau()
    {
        $this->view('auth/quen-mat-khau');
    }

    public function guiLienKetDatLaiMatKhau()
    {
        $email = trim($this->post('email', ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap Gmail/email hop le'));
        }

        $khach = $this->moHinhKhach->layTheoEmail($email);

        if (!$khach || $khach['vai_tro'] !== 'khach') {
            $this->json(array(
                'success' => true,
                'thong_bao' => 'Neu email ton tai, he thong se gui ma OTP dat lai mat khau.'
            ));
        }

        if ((int)$khach['dang_hoat_dong'] === 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Tai khoan da bi khoa, vui long lien he nhan vien'));
        }

        $otp = $this->taoOtpEmail();
        $otpHash = hash('sha256', $otp);
        $soPhut = PASSWORD_RESET_MINUTES > 0 ? PASSWORD_RESET_MINUTES : 15;
        if ($soPhut < 2) {
            $soPhut = 2;
        } elseif ($soPhut > 30) {
            $soPhut = 30;
        }
        $hetHanLuc = date('Y-m-d H:i:s', time() + ($soPhut * 60));

        $this->moHinhKhach->huyTokenDatLaiMatKhauCu($khach['id']);
        $idReset = $this->moHinhKhach->taoYeuCauDatLaiMatKhau($khach['id'], 'email', $email, $otpHash, $hetHanLuc);

        if (!$idReset) {
            $this->json(array('success' => false, 'thong_bao' => 'Khong tao duoc yeu cau dat lai mat khau, vui long thu lai'));
        }

        $daGui = $this->guiEmailOtpDatLaiMatKhau($email, $khach['ho_ten'], $otp, $soPhut);

        if (!$daGui) {
            $this->moHinhKhach->danhDauTokenDatLaiMatKhauDaDung($idReset);
            $this->json(array('success' => false, 'thong_bao' => 'Khong gui duoc email OTP. Vui long kiem tra cau hinh mail tren server.'));
        }

        $this->json(array(
            'success' => true,
            'thong_bao' => 'Da gui ma OTP den email da dang ky. Ma co hieu luc trong ' . $soPhut . ' phut.',
            'chuyen_huong' => BASE_URL . '/khach/dat-lai-mat-khau?yeu_cau=' . rawurlencode($idReset)
        ));
    }

    public function hienThiDatLaiMatKhau()
    {
        $idYeuCau = trim($this->get('yeu_cau', ''));
        $hopLe = false;

        if ($idYeuCau !== '') {
            $yeuCau = $this->moHinhKhach->layYeuCauDatLaiMatKhauTheoId($idYeuCau);
            $hopLe = ($yeuCau && $yeuCau['kenh_gui'] === 'email') ? true : false;
        }

        $this->view('auth/dat-lai-mat-khau', array(
            'idYeuCau' => $idYeuCau,
            'hopLe' => $hopLe
        ));
    }

    public function xuLyDatLaiMatKhau()
    {
        $idYeuCau = trim($this->post('yeu_cau', ''));
        $otp = trim($this->post('otp', ''));
        $matKhau = $this->post('mat_khau', '');
        $xacNhan = $this->post('xac_nhan_mat_khau', '');

        if ($idYeuCau === '' || !preg_match('/^[0-9]{6}$/', $otp)) {
            $this->json(array('success' => false, 'thong_bao' => 'Ma OTP khong hop le'));
        }

        $yeuCauHienTai = $this->moHinhKhach->layYeuCauDatLaiMatKhauTheoId($idYeuCau);
        if (!$yeuCauHienTai || $yeuCauHienTai['kenh_gui'] !== 'email') {
            $this->json(array('success' => false, 'thong_bao' => 'Yeu cau OTP da het han hoac da duoc su dung'));
        }

        if ((int)$yeuCauHienTai['so_lan_thu'] >= 5) {
            $this->moHinhKhach->danhDauTokenDatLaiMatKhauDaDung($idYeuCau);
            $this->json(array('success' => false, 'thong_bao' => 'Ban da nhap sai OTP qua nhieu lan. Vui long gui lai OTP moi.'));
        }

        if ($matKhau === '' || strlen($matKhau) < 6) {
            $this->json(array('success' => false, 'thong_bao' => 'Mat khau moi phai co it nhat 6 ky tu'));
        }

        if ($matKhau !== $xacNhan) {
            $this->json(array('success' => false, 'thong_bao' => 'Mat khau xac nhan khong khop'));
        }

        $otpHash = hash('sha256', $otp);
        $yeuCau = $this->moHinhKhach->layYeuCauDatLaiMatKhauOtp($idYeuCau, $otpHash, 'email');

        if (!$yeuCau) {
            $this->moHinhKhach->tangSoLanThuDatLaiMatKhau($idYeuCau);
            $this->json(array('success' => false, 'thong_bao' => 'Ma OTP khong dung hoac da het han'));
        }

        $ok = $this->moHinhKhach->capNhatMatKhauDaMaHoa($yeuCau['id_khach_tai_khoan'], MatKhau::maHoa($matKhau));

        if ($ok) {
            $this->moHinhKhach->danhDauTokenDatLaiMatKhauDaDung($yeuCau['id_dat_lai_mat_khau']);
            $this->json(array(
                'success' => true,
                'thong_bao' => 'Dat lai mat khau thanh cong. Vui long dang nhap lai.',
                'chuyen_huong' => BASE_URL . '/khach/dang-nhap'
            ));
        }

        $this->json(array('success' => false, 'thong_bao' => 'Khong cap nhat duoc mat khau, vui long thu lai'));
    }

    private function taoOtpEmail()
    {
        if (function_exists('random_int')) {
            return (string)random_int(100000, 999999);
        }

        return (string)mt_rand(100000, 999999);
    }

    private function guiEmailOtpDatLaiMatKhau($email, $hoTen, $otp, $soPhut)
    {
        $tenNguoiNhan = trim($hoTen) !== '' ? trim($hoTen) : 'khach hang';
        $subject = 'Ma OTP dat lai mat khau - ' . APP_NAME;
        $body = "Xin chao " . $tenNguoiNhan . ",\n\n"
            . "Ban vua yeu cau dat lai mat khau tai " . APP_NAME . ".\n"
            . "Ma OTP dat lai mat khau cua ban la: " . $otp . "\n\n"
            . "Ma OTP co hieu luc trong " . $soPhut . " phut va chi duoc su dung mot lan.\n"
            . "Neu ban khong yeu cau, hay bo qua email nay.\n";

        $dichVuEmail = new DichVuEmail();
        return $dichVuEmail->gui($email, $subject, $body);
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
        $dungMatKhauMacDinh = $this->post('mat_khau_mac_dinh', '') === '1';

        if ($hoTen === '' || $soDienThoai === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap day du thong tin bat buoc'));
            return;
        }

        if (!preg_match('/^0[0-9]{9}$/', $soDienThoai)) {
            $this->json(array('success' => false, 'thong_bao' => 'So dien thoai khong hop le (phai bat dau 0, 10 chu so)'));
            return;
        }

        if ($dungMatKhauMacDinh) {
            $matKhau = substr(preg_replace('/[^0-9]/', '', $soDienThoai), -6);
            $xacNhanMk = $matKhau;
        }

        if ($matKhau === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui long nhap mat khau'));
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

        $ton_tai = $this->moHinhKhach->layTheoSDT($soDienThoai);

        if ($ton_tai) {
            $this->json(array('success' => false, 'thong_bao' => 'So dien thoai nay da duoc dang ky'));
            return;
        }

        $id = $this->moHinhKhach->dangKy($hoTen, $soDienThoai, $email, $matKhau);

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
