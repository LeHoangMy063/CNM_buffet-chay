<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class QuanLyController extends BoieuKhienCo
{
    protected $moHinhThucDon;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhThucDon = new MoHinhMonAn();
    }

    public function trangChu()
    {
        $this->chuyenHuong(BASE_URL . '/quan-ly/thuc-don');
    }

    public function thucDon()
    {
        $this->yeuCauQuanLy();

        $this->view('manager/thuc-don', array(
            'nguoiDung' => $this->layNguoiDung(),
            'danhSachMon' => $this->moHinhThucDon->layTatCa()
        ));
    }

    public function luuMon()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $ten = trim($this->post('ten', ''));
        $danhMuc = trim($this->post('danh_muc', ''));
        if ($ten === '' || $danhMuc === '') {
            $this->json(array('success' => false, 'thong_bao' => 'Vui lòng nhập tên món và danh mục'));
        }

        $ok = $this->moHinhThucDon->luu($_POST);
        $this->json(array(
            'success' => (bool)$ok,
            'thong_bao' => $ok ? 'Đã lưu món ăn' : 'Không thể lưu món ăn'
        ));
    }

    public function xoaMon()
    {
        $this->yeuCauQuanLy();
        if (!$this->isPost()) {
            $this->json(array('success' => false, 'thong_bao' => 'Phương thức không hợp lệ'));
        }

        $id = intval($this->post('id', 0));
        if ($id <= 0) {
            $this->json(array('success' => false, 'thong_bao' => 'Món ăn không hợp lệ'));
        }

        $ok = $this->moHinhThucDon->xoa($id);
        $this->json(array(
            'success' => (bool)$ok,
            'thong_bao' => $ok ? 'Đã xóa món ăn' : 'Không thể xóa món ăn'
        ));
    }
}
