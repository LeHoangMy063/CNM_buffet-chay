<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/../models/MoHinhUuDai.php';
require_once dirname(__FILE__) . '/../models/KhachHangModel.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class TrangChuController extends BoieuKhienCo
{
    protected $moHinhBan;
    protected $moHinhThucDon;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhBan     = new MoHinhBan();
        $this->moHinhThucDon = new MoHinhMonAn();
    }

    // ================= TRANG CHỦ =================
    public function index()
    {
        $monNoiBat   = $this->moHinhThucDon->layNoiBat(8);
        $danhSachBan = $this->moHinhBan->layTatCaBan();

        $data = array(
            'tieuDeTrang' => 'Trang Chủ',
            'monNoiBat'   => $monNoiBat,
            'danhSachBan' => $danhSachBan
        );

        $this->view('home/trang-chu', $data);
    }

    // ================= TRANG THỰC ĐƠN =================
    public function thucDon()
    {
        $tu_khoa = trim($this->get('tim', ''));

        if ($tu_khoa !== '') {
            $danh_sach = $this->moHinhThucDon->timKiem($tu_khoa);
        } else {
            $danh_sach = $this->moHinhThucDon->layDangHienThi();
        }

        $data = array(
            'tieuDeTrang' => 'Thực Đơn',
            'tuKhoa'      => $tu_khoa,
            'items'       => $danh_sach
        );

        $this->view('home/thucdon', $data);
    }

    // ================= TRANG ƯU ĐÃI =================
    public function uuDai()
    {
        $laKhachDangNhap = $this->daDangNhap()
            && isset($_SESSION['nguoi_dung']['vai_tro'])
            && $_SESSION['nguoi_dung']['vai_tro'] === 'khach';

        $tenKhachHang = '';
        $diemHienTai = 0;

        if ($laKhachDangNhap) {
            $khachModel = new KhachHangModel();
            $khach = $khachModel->layTheoId($_SESSION['nguoi_dung']['id']);

            if (!empty($khach)) {
                $tenKhachHang = isset($khach['ho_ten']) ? $khach['ho_ten'] : '';
                $diemHienTai = isset($khach['diem_tich_luy']) ? (int)$khach['diem_tich_luy'] : 0;
            }
        }

        $uuDaiModel = new MoHinhUuDai();
        $monDoiDiem = $uuDaiModel->layDanhSachMonDoiDiem();

        $data = array(
            'tieuDeTrang' => 'Ưu Đãi',
            'laKhachDangNhap' => $laKhachDangNhap,
            'tenKhachHang' => $tenKhachHang,
            'diemHienTai' => $diemHienTai,
            'monDoiDiem' => $monDoiDiem
        );

        $this->view('home/uu-dai', $data);
    }

    public function xuLyDoiMon()
    {
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Yêu cầu không hợp lệ'));
        }

        if (!$this->daDangNhap() || !isset($_SESSION['nguoi_dung']['vai_tro']) || $_SESSION['nguoi_dung']['vai_tro'] !== 'khach') {
            $this->json(array(
                'success' => false,
                'thong_bao' => 'Vui lòng đăng nhập để đổi món',
                'chuyen_huong' => BASE_URL . '/khach/dang-nhap'
            ));
        }

        $idRewardItem = trim($this->post('id_reward_item', ''));
        if ($idRewardItem === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Món đổi điểm không hợp lệ'));
        }

        $khachModel = new KhachHangModel();
        $khach = $khachModel->layTheoId($_SESSION['nguoi_dung']['id']);

        if (empty($khach)) {
            $this->json(array('success' => false, 'thong_bao' => 'Không tìm thấy tài khoản khách hàng.'));
        }

        $uuDaiModel = new MoHinhUuDai();
        $mon = $uuDaiModel->layMonTheoId($idRewardItem);

        if (empty($mon)) {
            $this->json(array('success' => false, 'thong_bao' => 'Món ưu đãi không tồn tại.'));
        }

        $trangThai = isset($mon['trang_thai']) ? $mon['trang_thai'] : 'available';
        if ($trangThai !== 'available') {
            $this->json(array('success' => false, 'thong_bao' => 'Món này hiện không thể đổi.'));
        }

        $diemCanDoi = isset($mon['diem_can_doi']) ? (int)$mon['diem_can_doi'] : 0;
        $diemHienTai = isset($khach['diem_tich_luy']) ? (int)$khach['diem_tich_luy'] : 0;

        if ($diemHienTai < $diemCanDoi) {
            $this->json(array(
                'success' => false,
                'thong_bao' => 'Bạn còn thiếu ' . ($diemCanDoi - $diemHienTai) . ' điểm để đổi món này.'
            ));
        }

        $diemConLai = $diemHienTai - $diemCanDoi;
        $capNhat = $uuDaiModel->capNhatDiemKhach($_SESSION['nguoi_dung']['id'], $diemConLai);
        $luu = $uuDaiModel->luuLichSuDoiDiem($_SESSION['nguoi_dung']['id'], $idRewardItem, $diemCanDoi, 'thanh_cong');

        if (!$capNhat || !$luu) {
            $this->json(array('success' => false, 'thong_bao' => 'Đổi món gặp lỗi, vui lòng thử lại sau.'));
        }

        $this->json(array(
            'success' => true,
            'thong_bao' => 'Đổi món thành công! ' . number_format($diemConLai, 0, ',', '.') . ' điểm còn lại trong tài khoản của bạn.',
        ));
    }
}
