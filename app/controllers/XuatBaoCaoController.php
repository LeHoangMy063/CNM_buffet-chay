<?php

require_once dirname(__FILE__) . '/../models/MoHinhCo.php';
require_once dirname(__FILE__) . '/../models/MoHinh.php';
require_once dirname(__FILE__) . '/../core/MongoKetNoi.php';
require_once dirname(__FILE__) . '/BoieuKhienCo.php';

class XuatBaoCaoController extends BoieuKhienCo
{
    protected $moHinhDon;

    // Ký tự phân cách cột — dùng ; cho Excel Việt Nam
    const DELIMITER = ';';

    // Độ rộng "trang" khi in tiêu đề phần (số cột)
    const SO_COT = 6;

    public function __construct()
    {
        parent::__construct();
        $this->moHinhDon = new MoHinhDonMon();
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTION CHÍNH
    // ═══════════════════════════════════════════════════════════════════════

    public function xuatBaoCaoDoanhThu()
    {
        $this->yeuCauQuanLy();

        // ── Đọc & kiểm tra tham số ngày ──────────────────────────────────
        $tuNgay = $this->layNgayHopLe('tu_ngay', date('Y-m-01'));
        $denNgay = $this->layNgayHopLe('den_ngay', date('Y-m-t'));

        // ── Truy vấn dữ liệu ─────────────────────────────────────────────
        $tongQuan = $this->moHinhDon->tongQuanDoanhThu($tuNgay, $denNgay);
        $rows     = $this->moHinhDon->chiTietDoanhThu($tuNgay, $denNgay);
        $topMon   = $this->moHinhDon->topMonBanChayTrongKhoang(10, $tuNgay, $denNgay);
        $danhMuc  = $this->moHinhDon->thongKeDanhMucTrongKhoang($tuNgay, $denNgay);
        $theoGio  = $this->moHinhDon->thongKeDonTheoGio($tuNgay, $denNgay);

        // ── Đồng bộ & lấy từ MongoDB ─────────────────────────────────────
        $mongo = new MongoKetNoi();
        $mongo->dongBoBaoCaoDoanhThu($tuNgay, $denNgay, $tongQuan, $rows, $topMon, $danhMuc, $theoGio);
        $baoCaoMongo = $mongo->layBaoCaoDoanhThu($tuNgay, $denNgay);

        $nguonDuLieu = 'MySQL';
        if ($baoCaoMongo) {
            $nguonDuLieu = 'MongoDB';
            $tongQuan = isset($baoCaoMongo['tong_quan'])      ? $baoCaoMongo['tong_quan']      : array();
            $rows     = $this->chuyenDoanhThuNgayMongoThanhRows(
                isset($baoCaoMongo['doanh_thu_ngay']) ? $baoCaoMongo['doanh_thu_ngay'] : array()
            );
            $topMon   = isset($baoCaoMongo['top_goi_mon'])    ? $baoCaoMongo['top_goi_mon']    : array();
            $danhMuc  = isset($baoCaoMongo['danh_muc'])       ? $baoCaoMongo['danh_muc']       : array();
            $theoGio  = isset($baoCaoMongo['theo_gio'])       ? $baoCaoMongo['theo_gio']       : array();
        }

        // ── HTTP headers ──────────────────────────────────────────────────
        $tenFile = 'BaoCaoDoanhThu_' . str_replace('-', '', $tuNgay)
            . '_' . str_replace('-', '', $denNgay) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tenFile . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // BOM — giúp Excel mở tiếng Việt đúng font
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        // ── Ghi nội dung CSV ──────────────────────────────────────────────
        $this->ghiTrangBia($out, $tuNgay, $denNgay, $nguonDuLieu);
        $this->ghiTongQuan($out, $tongQuan);
        $this->ghiDoanhThuTheoNgay($out, $rows);
        $this->ghiTopMon($out, $topMon);
        $this->ghiDanhMuc($out, $danhMuc);
        $this->ghiTheoGio($out, $theoGio);
        $this->ghiChanBaoCao($out, $tongQuan, $rows);

        fclose($out);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CÁC PHẦN BÁO CÁO
    // ═══════════════════════════════════════════════════════════════════════

    /** Trang bìa / thông tin báo cáo */
    private function ghiTrangBia($out, $tuNgay, $denNgay, $nguonDuLieu)
    {
        $d = self::DELIMITER;

        $this->dongTrong($out);
        $this->dong($out, array('', '╔══════════════════════════════════════════════════════════╗'));
        $this->dong($out, array('', '║          BÁO CÁO DOANH THU — BUFFET CHAY AN LẠC         ║'));
        $this->dong($out, array('', '╚══════════════════════════════════════════════════════════╝'));
        $this->dongTrong($out);

        $this->dong($out, array('Kỳ báo cáo', $this->formatNgay($tuNgay) . ' → ' . $this->formatNgay($denNgay)));
        $this->dong($out, array('Số ngày',    $this->demSoNgay($tuNgay, $denNgay) . ' ngày'));
        $this->dong($out, array('Xuất lúc',   date('d-m-y H:i:s')));
        $this->dong($out, array('Nguồn data', $nguonDuLieu));
        $this->dong($out, array('Phiên bản',  '2.0'));
        $this->dongTrong($out);
    }

    /** Phần 1 — Tổng quan */
    private function ghiTongQuan($out, $tongQuan)
    {
        $this->tieuDePhan($out, '1', 'TỔNG QUAN KINH DOANH');

        $this->dong($out, array('CHỈ SỐ', 'GIÁ TRỊ', 'GHI CHÚ'));
        $this->dongKe($out, '-');

        $danhSach = array(
            array(
                'Tổng doanh thu',
                $this->formatTien(isset($tongQuan['doanh_thu']) ? $tongQuan['doanh_thu'] : 0),
                'Tổng tiền đã thu trong kỳ'
            ),
            array(
                'Lượt thanh toán',
                $this->formatSo(isset($tongQuan['so_phien']) ? $tongQuan['so_phien'] : 0) . ' lượt',
                'Số phiên / bàn đã thanh toán'
            ),
            array(
                'Tổng khách phục vụ',
                $this->formatSo(isset($tongQuan['tong_khach']) ? $tongQuan['tong_khach'] : 0) . ' khách',
                'Tổng số khách trong kỳ'
            ),
            array(
                'DT trung bình / ngày',
                $this->formatTien(isset($tongQuan['doanh_thu_tb_ngay']) ? $tongQuan['doanh_thu_tb_ngay'] : 0),
                'Tính trên số ngày có dữ liệu'
            ),
            array(
                'Khách TB / lượt TT',
                round(isset($tongQuan['khach_tb_moi_phien']) ? $tongQuan['khach_tb_moi_phien'] : 0, 1) . ' khách',
                'Trung bình mỗi lượt thanh toán'
            ),
            array(
                'DT trung bình / khách',
                $this->formatTien(isset($tongQuan['doanh_thu_tb_moi_khach']) ? $tongQuan['doanh_thu_tb_moi_khach'] : 0),
                'Giá trị trung bình trên mỗi khách'
            ),
            array(
                '▲  Ngày cao điểm',
                isset($tongQuan['ngay_cao_diem']) ? $this->formatNgay($tongQuan['ngay_cao_diem']) : '—',
                'Ngày có doanh thu cao nhất'
            ),
            array(
                '▼  Ngày thấp điểm',
                isset($tongQuan['ngay_thap_diem']) ? $this->formatNgay($tongQuan['ngay_thap_diem']) : '—',
                'Ngày có doanh thu thấp nhất'
            ),
        );

        foreach ($danhSach as $r) {
            $this->dong($out, $r);
        }

        $this->dongTrong($out);
    }

    /** Phần 2 — Doanh thu theo ngày */
    private function ghiDoanhThuTheoNgay($out, $rows)
    {
        $this->tieuDePhan($out, '2', 'DOANH THU THEO NGÀY');

        if (empty($rows)) {
            $this->dong($out, array('', '(Không có dữ liệu trong khoảng thời gian này)'));
            $this->dongTrong($out);
            return;
        }

        $this->dong($out, array('STT', 'Ngày', 'Thứ', 'Lượt TT', 'Khách', 'Doanh thu'));
        $this->dongKe($out, '-');

        // Tính tổng để hiển thị dòng tổng cộng
        $tongPhien  = 0;
        $tongKhach  = 0;
        $tongTien   = 0;
        $stt        = 1;

        foreach ($rows as $row) {
            $ngay      = isset($row['ngay']) ? $row['ngay'] : '';
            $soPhien   = isset($row['so_phien']) ? (int)$row['so_phien'] : 0;
            $soKhach   = isset($row['so_khach']) ? (int)$row['so_khach'] : 0;
            $doanhThu  = isset($row['doanh_thu']) ? (int)$row['doanh_thu'] : 0;

            $tongPhien += $soPhien;
            $tongKhach += $soKhach;
            $tongTien  += $doanhThu;

            $this->dong($out, array(
                $stt++,
                $this->formatNgay($ngay),
                $this->layTenThu($ngay),
                $this->formatSo($soPhien),
                $this->formatSo($soKhach),
                $this->formatTien($doanhThu)
            ));
        }

        $this->dongKe($out, '=');
        $this->dong($out, array(
            'TỔNG',
            '',
            '',
            $this->formatSo($tongPhien),
            $this->formatSo($tongKhach),
            $this->formatTien($tongTien)
        ));
        $this->dongTrong($out);
    }

    /** Phần 3 — Top món được gọi nhiều */
    private function ghiTopMon($out, $topMon)
    {
        $this->tieuDePhan($out, '3', 'TOP 10 MÓN ĐƯỢC GỌI NHIỀU NHẤT');

        if (empty($topMon)) {
            $this->dong($out, array('', '(Không có dữ liệu)'));
            $this->dongTrong($out);
            return;
        }

        $this->dong($out, array('STT', 'Tên món', 'Danh mục', 'Lượt gọi', 'Tỷ lệ', 'Biểu đồ'));
        $this->dongKe($out, '-');

        $tongLuot = $this->tongLuotGoi($topMon);
        $luotMax  = $this->luotGoiMax($topMon);
        $stt      = 1;

        foreach ($topMon as $mon) {
            $luot  = $this->layLuotGoi($mon);
            $tyLe  = $tongLuot > 0 ? round(($luot / $tongLuot) * 100, 1) : 0;
            $bieu  = $luotMax > 0 ? $this->thanhBieu($luot, $luotMax, 15) : '';

            $this->dong($out, array(
                $stt++,
                isset($mon['ten']) ? $mon['ten'] : '',
                isset($mon['danh_muc']) ? $mon['danh_muc'] : '',
                $this->formatSo($luot),
                $tyLe . '%',
                $bieu
            ));
        }

        $this->dongKe($out, '=');
        $this->dong($out, array('TỔNG', '', '', $this->formatSo($tongLuot), '100%', ''));
        $this->dongTrong($out);
    }

    /** Phần 4 — Theo danh mục */
    private function ghiDanhMuc($out, $danhMuc)
    {
        $this->tieuDePhan($out, '4', 'PHÂN BỔ LƯỢT GỌI THEO DANH MỤC');

        if (empty($danhMuc)) {
            $this->dong($out, array('', '(Không có dữ liệu)'));
            $this->dongTrong($out);
            return;
        }

        $this->dong($out, array('STT', 'Danh mục', 'Lượt gọi', 'Tỷ lệ', 'Biểu đồ', ''));
        $this->dongKe($out, '-');

        $tongDanhMuc = $this->tongLuotGoi($danhMuc);
        $luotMax     = $this->luotGoiMax($danhMuc);
        $stt         = 1;

        // Sắp xếp giảm dần
        $sorted = $danhMuc;
        usort($sorted, function ($a, $b) {
            return $this->layLuotGoi($b) - $this->layLuotGoi($a);
        });

        foreach ($sorted as $item) {
            $luot = $this->layLuotGoi($item);
            $tyLe = $tongDanhMuc > 0 ? round(($luot / $tongDanhMuc) * 100, 1) : 0;
            $bieu = $luotMax > 0 ? $this->thanhBieu($luot, $luotMax, 20) : '';

            $this->dong($out, array(
                $stt++,
                isset($item['danh_muc']) ? $item['danh_muc'] : '(Không tên)',
                $this->formatSo($luot),
                $tyLe . '%',
                $bieu,
                ''
            ));
        }

        $this->dongKe($out, '=');
        $this->dong($out, array('TỔNG', '', $this->formatSo($tongDanhMuc), '100%', '', ''));
        $this->dongTrong($out);
    }

    /** Phần 5 — Thống kê theo giờ */
    private function ghiTheoGio($out, $theoGio)
    {
        $this->tieuDePhan($out, '5', 'PHÂN BỐ HOẠT ĐỘNG THEO GIỜ');

        if (empty($theoGio)) {
            $this->dong($out, array('', '(Không có dữ liệu)'));
            $this->dongTrong($out);
            return;
        }

        $this->dong($out, array('Giờ', 'Số đơn', 'Số món gọi', 'Tỷ lệ', 'Biểu đồ hoạt động', ''));
        $this->dongKe($out, '-');

        $donMax = 0;
        foreach ($theoGio as $item) {
            $don = isset($item['so_don']) ? (int)$item['so_don'] : 0;
            if ($don > $donMax) $donMax = $don;
        }

        foreach ($theoGio as $item) {
            $gio  = isset($item['gio']) ? $item['gio'] : '';
            $don  = isset($item['so_don']) ? (int)$item['so_don'] : 0;
            $mon  = isset($item['so_mon']) ? (int)$item['so_mon'] : 0;
            $tyLe = isset($item['phan_tram_tong']) ? $item['phan_tram_tong'] : 0;
            $bieu = $donMax > 0 ? $this->thanhBieu($don, $donMax, 20) : '';

            $this->dong($out, array(
                sprintf('%02dh00 - %02dh59', $gio, $gio),
                $this->formatSo($don),
                $this->formatSo($mon),
                $tyLe . '%',
                $bieu,
                ''
            ));
        }

        $this->dongTrong($out);
    }

    /** Chân báo cáo — tóm tắt nhanh */
    private function ghiChanBaoCao($out, $tongQuan, $rows)
    {
        $this->tieuDePhan($out, '✦', 'TÓM TẮT & NHẬN XÉT');

        $soNgayCoDuLieu = count($rows);
        $doanhThu       = isset($tongQuan['doanh_thu'])            ? (int)$tongQuan['doanh_thu']            : 0;
        $soPhien        = isset($tongQuan['so_phien'])             ? (int)$tongQuan['so_phien']             : 0;
        $tbNgay         = $soNgayCoDuLieu > 0 ? round($doanhThu / $soNgayCoDuLieu) : 0;
        $tbPhien        = $soPhien > 0 ? round($doanhThu / $soPhien) : 0;

        $this->dong($out, array('Chỉ tiêu', 'Giá trị'));
        $this->dongKe($out, '-');
        $this->dong($out, array('Số ngày có dữ liệu',            $soNgayCoDuLieu . ' ngày'));
        $this->dong($out, array('Doanh thu bình quân / ngày',    $this->formatTien($tbNgay)));
        $this->dong($out, array('Doanh thu bình quân / lượt TT', $this->formatTien($tbPhien)));

        // Đánh giá hiệu suất đơn giản
        $tbKhach = isset($tongQuan['khach_tb_moi_phien']) ? round($tongQuan['khach_tb_moi_phien'], 1) : 0;
        if ($tbKhach >= 4) {
            $nhanXet = 'Quy mô nhóm khách lớn — tốt cho buffet theo bàn';
        } elseif ($tbKhach >= 2) {
            $nhanXet = 'Quy mô nhóm trung bình — cân đối';
        } else {
            $nhanXet = 'Chủ yếu khách lẻ — xem xét khuyến mãi nhóm';
        }
        $this->dong($out, array('Nhận xét quy mô nhóm', $nhanXet));

        $this->dongTrong($out);
        $this->dongKe($out, '─');
        $this->dong($out, array(
            'Ghi chú',
            'Báo cáo xuất tự động từ hệ thống quản lý Buffet Chay An Lạc'
        ));
        $this->dong($out, array(
            '',
            'Mọi thắc mắc liên hệ bộ phận kế toán / quản lý hệ thống'
        ));
        $this->dongKe($out, '─');
        $this->dongTrong($out);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPER: GHI CSV
    // ═══════════════════════════════════════════════════════════════════════

    /** Ghi một dòng dữ liệu */
    private function dong($out, array $data)
    {
        fputcsv($out, $data, self::DELIMITER);
    }

    /** Ghi dòng trống */
    private function dongTrong($out)
    {
        fputcsv($out, array(''), self::DELIMITER);
    }

    /**
     * Ghi dòng kẻ ngang (dùng ký tự $kyTu lặp lại)
     * Excel sẽ hiện dưới dạng chuỗi ký tự liền nhau trong 1 ô.
     */
    private function dongKe($out, $kyTu = '-', $soKyTu = 55)
    {
        fputcsv($out, array(str_repeat($kyTu, $soKyTu)), self::DELIMITER);
    }

    /**
     * Tiêu đề phần:  ══ N. TÊN PHẦN ══
     */
    private function tieuDePhan($out, $so, $ten)
    {
        $this->dongKe($out, '═');
        fputcsv($out, array('  ► ' . $so . '. ' . $ten), self::DELIMITER);
        $this->dongKe($out, '═');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPER: CHUYỂN ĐỔI DỮ LIỆU
    // ═══════════════════════════════════════════════════════════════════════

    private function chuyenDoanhThuNgayMongoThanhRows($doanhThuNgay)
    {
        $rows = array();
        if (!is_array($doanhThuNgay)) return $rows;

        foreach ($doanhThuNgay as $ngay => $row) {
            if (!is_array($row)) continue;
            $row['ngay'] = $ngay;
            $rows[] = $row;
        }

        usort($rows, function ($a, $b) {
            return strcmp(
                isset($a['ngay']) ? $a['ngay'] : '',
                isset($b['ngay']) ? $b['ngay'] : ''
            );
        });

        return $rows;
    }

    private function tongLuotGoi($rows)
    {
        $tong = 0;
        if (!is_array($rows)) return $tong;
        foreach ($rows as $row) $tong += $this->layLuotGoi($row);
        return $tong;
    }

    private function layLuotGoi($row)
    {
        if (!is_array($row)) return 0;
        if (isset($row['tong_luot_goi'])) return (int)$row['tong_luot_goi'];
        return isset($row['tong_ban']) ? (int)$row['tong_ban'] : 0;
    }

    private function luotGoiMax($rows)
    {
        $max = 0;
        if (!is_array($rows)) return $max;
        foreach ($rows as $row) {
            $v = $this->layLuotGoi($row);
            if ($v > $max) $max = $v;
        }
        return $max;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPER: ĐỊNH DẠNG
    // ═══════════════════════════════════════════════════════════════════════

    private function layNgayHopLe($tenParam, $macDinh)
    {
        return (isset($_GET[$tenParam]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET[$tenParam]))
            ? $_GET[$tenParam]
            : $macDinh;
    }

    private function formatTien($so)
    {
        return number_format((int)$so, 0, ',', '.') . ' đ';
    }

    private function formatSo($so)
    {
        return number_format((int)$so, 0, ',', '.');
    }

    private function formatNgay($ngay)
    {
        if (!$ngay || $ngay === '---') return '—';
        $time = strtotime($ngay);
        return $time ? date('d-m-y', $time) : $ngay;
    }

    private function layTenThu($ngay)
    {
        if (!$ngay) return '';
        $time = strtotime($ngay);
        if (!$time) return '';

        $thu = array('CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7');
        return $thu[(int)date('w', $time)];
    }

    private function demSoNgay($tuNgay, $denNgay)
    {
        $t1 = strtotime($tuNgay);
        $t2 = strtotime($denNgay);
        if (!$t1 || !$t2) return '?';
        return max(1, (int)round(($t2 - $t1) / 86400) + 1);
    }

    /**
     * Tạo thanh biểu đồ ASCII bằng ký tự █
     * $giaTri / $giaTriMax → tỷ lệ, sau đó vẽ $doRong ký tự
     */
    private function thanhBieu($giaTri, $giaTriMax, $doRong = 20)
    {
        if ($giaTriMax <= 0) return '';
        $phan = (int)round(($giaTri / $giaTriMax) * $doRong);
        $phan = max(0, min($phan, $doRong));
        return str_repeat('█', $phan) . str_repeat('░', $doRong - $phan);
    }
}
