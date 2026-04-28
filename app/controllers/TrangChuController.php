<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
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
            $danh_sach = $this->moHinhThucDon->layTatCa();
        }

        $data = array(
            'tieuDeTrang' => 'Thực Đơn',
            'tuKhoa'      => $tu_khoa,
            'items'       => $danh_sach
        );

        $this->view('home/thucdon', $data);
    }
}
