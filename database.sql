
-- ============================================================
-- Bảng: ban
-- ============================================================

CREATE TABLE `ban` (
  `id` int(11) NOT NULL auto_increment,
  `so_ban` varchar(10) default NULL,
  `suc_chua` int(11) default '4',
  `trang_thai` enum('trong','dang_dung','cho_thanh_toan','da_thanh_toan') default 'trong',
  `ma_truy_cap` varchar(10) default NULL,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `so_ban` (`so_ban`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=9;

INSERT INTO `ban` (`id`, `so_ban`, `suc_chua`, `trang_thai`, `ma_truy_cap`, `ngay_tao`) VALUES
(1, 'A1', 4, 'trong', 'BAN-A1', '2026-04-21 18:03:24'),
(2, 'A2', 4, 'trong', 'BAN-A2', '2026-04-21 18:03:24'),
(3, 'A3', 6, 'trong', 'BAN-A3', '2026-04-21 18:03:24'),
(4, 'A4', 6, 'trong', 'BAN-A4', '2026-04-21 18:03:24'),
(5, 'B1', 2, 'trong', 'BAN-B1', '2026-04-21 18:03:24'),
(6, 'B2', 2, 'trong', 'BAN-B2', '2026-04-21 18:03:24'),
(7, 'B3', 8, 'trong', 'BAN-B3', '2026-04-21 18:03:24'),
(8, 'B4', 8, 'trong', 'BAN-B4', '2026-04-21 18:03:24');

-- ============================================================
-- Bảng: danh_gia
-- ============================================================

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL auto_increment,
  `tai_khoan_id` int(11) NOT NULL,
  `mon_an_id` int(11) NOT NULL,
  `so_sao` tinyint(1) NOT NULL default '5',
  `binh_luan` text,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `tai_khoan_id` (`tai_khoan_id`),
  KEY `mon_an_id` (`mon_an_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- ============================================================
-- Bảng: dat_ban
-- Thêm cột ban_xac_nhan: 0 = hệ thống tự gán chưa duyệt
--                        1 = nhân viên đã xác nhận
-- ============================================================

CREATE TABLE `dat_ban` (
  `id` int(11) NOT NULL auto_increment,
  -- Cot phu giu ban dau tien de tuong thich code cu.
  -- Nguon gan ban chinh la bang chitiet_datban.
  `ban_id` int(11) default NULL,
  `ten_khach` varchar(100) default NULL,
  `sdt_khach` varchar(20) default NULL,
  `so_nguoi_lon` int(11) default '1',
  `so_tre_em` int(11) default '0',
  `tong_tien` decimal(10,2) default NULL,
  `ngay_dat` date default NULL,
  `gio_dat` time default NULL,
  `ghi_chu` text,
  `trang_thai` enum('cho_xac_nhan','da_xac_nhan','da_huy','cancelled','expired','hoan_thanh') default 'cho_xac_nhan',
  `ma_dat_ban` varchar(20) default NULL,
  `ban_xac_nhan` tinyint(1) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ma_dat_ban` (`ma_dat_ban`),
  KEY `ban_id` (`ban_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=14;

INSERT INTO `dat_ban` (`id`, `ban_id`, `ten_khach`, `sdt_khach`, `so_nguoi_lon`, `so_tre_em`, `tong_tien`, `ngay_dat`, `gio_dat`, `ghi_chu`, `trang_thai`, `ma_dat_ban`, `ban_xac_nhan`, `ngay_tao`) VALUES
(9,  1, 'My',     '01827132',   2, 0, '398000.00', '2026-04-28', '10:00:00', '', 'da_xac_nhan', 'RES-20260427-69961', 0, '2026-04-27 19:26:16'),
(10, 2, 'My Mi',  '0826893126', 2, 0, '398000.00', '2026-04-28', '10:00:00', '', 'da_xac_nhan', 'RES-20260427-55670', 0, '2026-04-27 19:26:55'),
(11, 3, 'tric',   '01827132',   2, 0, '398000.00', '2026-04-28', '10:30:00', '', 'da_xac_nhan', 'RES-20260427-27597', 0, '2026-04-27 19:28:19'),
(12, 1, 'Le Anh', '0826893126', 2, 0, '398000.00', '2026-04-28', '12:00:00', '', 'da_xac_nhan', 'RES-20260427-03565', 0, '2026-04-27 19:29:04'),
(13, 5, 'My MII', '0826893126', 2, 0, '398000.00', '2026-04-28', '12:00:00', '', 'da_xac_nhan', 'RES-20260427-01200', 0, '2026-04-27 19:30:44');

-- ============================================================
-- Bảng: chitiet_datban  (gán nhiều bàn cho 1 đặt bàn)
-- ============================================================

CREATE TABLE `chitiet_datban` (
  `id` int(11) NOT NULL auto_increment,
  `dat_ban_id` int(11) NOT NULL,
  `ban_id` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_chitiet_datban` (`dat_ban_id`, `ban_id`),
  KEY `ban_id` (`ban_id`),
  KEY `dat_ban_id` (`dat_ban_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Đồng bộ ban_id cũ sang bảng liên kết
INSERT IGNORE INTO `chitiet_datban` (`dat_ban_id`, `ban_id`)
SELECT `id`, `ban_id`
FROM `dat_ban`
WHERE `ban_id` IS NOT NULL;

-- ============================================================
-- Bảng: don_mon
-- ============================================================

CREATE TABLE `don_mon` (
  `id` int(11) NOT NULL auto_increment,
  `ban_id` int(11) default NULL,
  `trang_thai` enum('cho_phuc_vu','dang_che_bien','da_phuc_vu','da_huy') default 'cho_phuc_vu',
  `tong_tien` decimal(10,2) default '0.00',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `ban_id` (`ban_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

INSERT INTO `don_mon` (`id`, `ban_id`, `trang_thai`, `tong_tien`, `ngay_tao`) VALUES
(1, 1, 'da_phuc_vu', '0.00', '2026-04-21 18:33:31'),
(2, 1, 'da_phuc_vu', '0.00', '2026-04-21 18:58:23'),
(3, 1, 'da_phuc_vu', '0.00', '2026-04-21 18:58:23'),
(4, 1, 'da_phuc_vu', '0.00', '2026-04-21 18:58:23');

-- ============================================================
-- Bảng: chitiet_donmon  (cac mon trong 1 don)
-- ============================================================

CREATE TABLE `chitiet_donmon` (
  `id` int(11) NOT NULL auto_increment,
  `don_mon_id` int(11) NOT NULL,
  `mon_an_id` int(11) NOT NULL,
  `so_luong` int(11) default '1',
  `ghi_chu` text,
  `trang_thai` enum('cho_phuc_vu','dang_che_bien','da_phuc_vu','da_huy') default 'cho_phuc_vu',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `don_mon_id` (`don_mon_id`),
  KEY `mon_an_id` (`mon_an_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

INSERT INTO `chitiet_donmon` (`id`, `don_mon_id`, `mon_an_id`, `so_luong`, `ghi_chu`, `trang_thai`, `ngay_tao`) VALUES
(1, 1, 2,  1, '', 'da_phuc_vu', '2026-04-21 18:33:31'),
(2, 2, 1,  1, '', 'da_phuc_vu', '2026-04-21 18:58:23'),
(3, 3, 33, 1, '', 'da_phuc_vu', '2026-04-21 18:58:23'),
(4, 4, 3,  1, '', 'da_phuc_vu', '2026-04-21 18:58:23');

-- ============================================================
-- Bảng: mon_an
-- ============================================================

CREATE TABLE `mon_an` (
  `id` int(11) NOT NULL auto_increment,
  `ten` varchar(150) default NULL,
  `mo_ta` text,
  `danh_muc` varchar(50) default NULL,
  `anh_url` varchar(255) default NULL,
  `gia` decimal(10,0) default '0',
  `con_mon` tinyint(1) default '1',
  `noi_bat` tinyint(1) default '0',
  `thu_tu` int(11) default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=43;

INSERT INTO `mon_an` (`id`, `ten`, `mo_ta`, `danh_muc`, `anh_url`, `gia`, `con_mon`, `noi_bat`, `thu_tu`, `ngay_tao`) VALUES
(1,  'Gỏi cuốn chay',    'Rau sống, đậu hũ cuốn bánh tráng',       'Khai vi',   'https://cdn.tgdd.vn/Files/2021/09/06/1380699/huong-dan-cach-lam-goi-cuon.jpg',                            '0', 1, 1,  1, '2026-04-21 18:03:24'),
(2,  'Chả giò chay',     'Nhân rau củ chiên giòn',                  'Khai vi',   'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9?w=600&q=80',                                  '0', 1, 0,  2, '2026-04-21 18:03:24'),
(3,  'Súp bí đỏ',        'Bí đỏ nấu mềm với nước cốt dừa',         'Khai vi',   'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80',                                     '0', 1, 0,  3, '2026-04-21 18:03:24'),
(4,  'Salad rau',        'Rau mầm, cà chua, sốt chanh',             'Khai vi',   'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',                                  '0', 1, 1,  4, '2026-04-21 18:03:24'),
(5,  'Cơm chiên chay',   'Cơm xào rau củ',                          'Mon chinh', 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&q=80',                                  '0', 1, 1, 11, '2026-04-21 18:03:24'),
(6,  'Mì xào chay',      'Mì xào nấm và rau',                       'Mon chinh', 'https://product.hstatic.net/200000408931/product/mi-chay.jpg',                                             '0', 1, 0, 12, '2026-04-21 18:03:24'),
(7,  'Cà ri chay',       'Cà ri rau củ nước dừa',                   'Mon chinh', 'https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=600&q=80',                                  '0', 1, 1, 13, '2026-04-21 18:03:24'),
(8,  'Đậu hũ sốt cà',   'Đậu hũ chiên sốt cà chua',                'Mon chinh', 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=600&q=80',                                  '0', 1, 0, 14, '2026-04-21 18:03:24'),
(9,  'Nấm kho tiêu',     'Nấm đông cô kho tiêu đậm đà',             'Mon chinh', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80',                                  '0', 1, 1, 15, '2026-04-21 18:03:24'),
(10, 'Thanh cua chay',   'Giả cua, vị nhẹ',                         'Mon chinh', 'https://images.unsplash.com/photo-1576402187878-974f70c890a5?w=600&q=80',                                  '0', 1, 0, 32, '2026-04-21 18:03:24'),
(11, 'Bún xào chay',     'Bún xào rau củ và đậu hũ',                'Mon chinh', 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=600&q=80',                                  '0', 1, 0, 16, '2026-04-21 18:03:24'),
(12, 'Canh rong biển',   'Canh rong biển thanh nhẹ',                'Mon chinh', 'https://assets.unileversolutions.com/recipes-v2/157820.jpg',                                               '0', 1, 1, 17, '2026-04-21 18:03:24'),
(13, 'Đậu hũ non',       'Mềm mịn thấm nước lẩu',                  'Topping',   'https://thaisumo.vn/wp-content/uploads/2025/12/Tau-Hu-Non.jpg',                                            '0', 1, 0, 20, '2026-04-21 18:03:24'),
(14, 'Tàu hũ ky',        'Dai mềm, thấm nước dùng',                 'Topping',   'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',                                     '0', 1, 1, 26, '2026-04-21 18:03:24'),
(15, 'Chả viên chay',    'Viên dai, vị đậm đà',                     'Topping',   'https://tubahi.com/wp-content/uploads/2022/11/ngoc-tam-bam.jpg',                                           '0', 1, 1, 27, '2026-04-21 18:03:24'),
(16, 'Há cảo chay',      'Nhân rau củ bọc bột',                     'Topping',   'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=600&q=80',                                     '0', 1, 0, 28, '2026-04-21 18:03:24'),
(17, 'Chả lụa chay',     'Dai mềm, vị thanh nhẹ',                   'Topping',   'https://thoaian.vn/vnt_upload/product/12_2024/ChaLuaOtXiemChay.png',                                      '0', 1, 0, 29, '2026-04-21 18:03:24'),
(18, 'Phù trúc cuốn',    'Tàu hũ ky cuốn sẵn',                      'Topping',   'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600&q=80',                                  '0', 1, 1, 30, '2026-04-21 18:03:24'),
(19, 'Sườn non chay',    'Dai mềm, thấm vị nước lẩu',               'Topping',   'https://cdn2.fptshop.com.vn/unsafe/suon_non_chay_kho_tieu_4_7a878594e5.jpg',                              '0', 1, 1, 38, '2026-04-21 18:03:24'),
(20, 'Bún tươi',         'Ăn kèm lẩu',                              'Topping',   'https://byvn.net/05E4',                                                                                    '0', 1, 1, 24, '2026-04-21 18:03:24'),
(21, 'Mì trứng',         'Sợi dai',                                 'Topping',   'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=600&q=80',                                  '0', 1, 0, 25, '2026-04-21 18:03:24'),
(22, 'Rau muống',        'Giòn xanh',                               'Rau',       'https://res.ketnoiocop.vn/user-4905/rau-muong-xao-toi-1.png',                                              '0', 1, 1, 30, '2026-04-21 18:03:24'),
(23, 'Cải thảo',         'Ngọt thanh',                              'Rau',       'https://images.unsplash.com/photo-1518977676405-d06616eadbb4?w=600&q=80',                                  '0', 1, 0, 31, '2026-04-21 18:03:24'),
(24, 'Rau cải ngọt',     'Thanh nhẹ, dễ ăn',                        'Rau',       'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&q=80',                                  '0', 1, 0, 32, '2026-04-21 18:03:24'),
(25, 'Giá đỗ',           'Giòn mát',                                'Rau',       'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=600&q=80',                                  '0', 1, 1, 33, '2026-04-21 18:03:24'),
(26, 'Nấm kim châm',     'Giòn ngọt',                               'Rau',       'https://cdn.tgdd.vn/Files/2020/12/12/1313297/nam-kim-cham-la-gi.jpg',                                      '0', 1, 1, 22, '2026-04-21 18:03:24'),
(27, 'Nấm đông cô',      'Thơm đặc trưng',                          'Rau',       'https://shop.annam-gourmet.com/pub/media/catalog/product/F/1/F138640_7649.jpg',                           '0', 1, 0, 23, '2026-04-21 18:03:24'),
(28, 'Rau mồng tơi',     'Mềm mát, dễ ăn',                          'Rau',       'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=600&q=80',                                  '0', 1, 1, 34, '2026-04-21 18:03:24'),
(29, 'Rau tần ô',        'Thơm nhẹ, đặc trưng lẩu',                 'Rau',       'https://bizweb.dktcdn.net/thumb/large/100/469/751/products/tan-o-1669530948604.jpg',                       '0', 1, 1, 36, '2026-04-21 18:03:24'),
(30, 'Bắp cải',          'Giòn, ngọt khi nấu',                      'Rau',       'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=600&q=80',                                     '0', 1, 0, 37, '2026-04-21 18:03:24'),
(31, 'Rau lang',         'Mềm, hơi bùi',                            'Rau',       'https://images.unsplash.com/photo-1622205313162-be1d5712a43f?w=600&q=80',                                  '0', 1, 0, 38, '2026-04-21 18:03:24'),
(32, 'Lẩu nấm',          'Nước lẩu thanh ngọt từ nấm',              'Nuoc lau',  'https://cdn.tgdd.vn/2021/02/CookProduct/1114-1200x676.jpg',                                               '0', 1, 1,  9, '2026-04-21 18:03:24'),
(33, 'Lẩu dashi',        'Thanh nhẹ',                               'Nuoc lau',  'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80',                                     '0', 1, 1, 40, '2026-04-21 18:03:24'),
(34, 'Lẩu tomyum',       'Chua cay',                                'Nuoc lau',  'https://images.unsplash.com/photo-1562565652-a0d8f0c59eb4?w=600&q=80',                                     '0', 1, 1, 41, '2026-04-21 18:03:24'),
(35, 'Lẩu kim chi',      'Cay nồng',                                'Nuoc lau',  'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?w=600&q=80',                                  '0', 1, 1, 42, '2026-04-21 18:03:24'),
(36, 'Nước cam',         'Tươi mát',                                'Do uong',   'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=600&q=80',                                  '0', 1, 1, 50, '2026-04-21 18:03:24'),
(37, 'Nước ép dưa hấu',  'Mát lạnh, giải khát',                     'Do uong',   'https://images.unsplash.com/photo-1563746098251-d35aef196e83?w=600&q=80',                                  '0', 1, 1, 51, '2026-04-21 18:03:24'),
(38, 'Nước chanh',       'Chua nhẹ giải nhiệt',                     'Do uong',   'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&q=80',                                     '0', 1, 1, 52, '2026-04-21 18:03:24'),
(39, 'Trà tắc',          'Chua ngọt, thơm mùi tắc',                 'Do uong',   'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&q=80',                                     '0', 1, 1, 53, '2026-04-21 18:03:24'),
(40, 'Trà đào',          'Ngọt dịu, thơm mát',                      'Do uong',   'https://cdn.s99.vn/ss1/prod/thumb/a3dad287a87194c944c0b976295ec16f.jpg',                                  '0', 1, 1, 54, '2026-04-21 18:03:24'),
(41, 'Sinh tố xoài',     'Béo mịn bổ dưỡng',                        'Do uong',   'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?w=600&q=80',                                  '0', 1, 1, 55, '2026-04-21 18:03:24'),
(42, 'Sinh tố dâu',      'Chua ngọt dễ uống',                       'Do uong',   'https://images.unsplash.com/photo-1570197788417-0e82375c9371?w=600&q=80',                                  '0', 1, 0, 56, '2026-04-21 18:03:24');

-- ============================================================
-- Bảng: tai_khoan
-- ============================================================

CREATE TABLE `tai_khoan` (
  `id` int(11) NOT NULL auto_increment,
  `ten_dang_nhap` varchar(50) default NULL,
  `mat_khau` varchar(255) default NULL,
  `vai_tro` enum('admin','nhan_vien','bep','khach') NOT NULL default 'khach',
  `dang_hoat_dong` tinyint(1) NOT NULL default '1',
  `ho_ten` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `so_dien_thoai` varchar(20) default NULL,
  `diem_tich_luy` int(11) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6;

INSERT INTO `tai_khoan` (`id`, `ten_dang_nhap`, `mat_khau`, `vai_tro`, `dang_hoat_dong`, `ho_ten`, `email`, `so_dien_thoai`, `diem_tich_luy`, `ngay_tao`) VALUES
(1, 'admin',       'e6e061838856bf47e1de730719fb2609', 'admin',     1, 'Quan tri vien',    NULL, NULL,          0, '2026-04-27 14:09:41'),
(2, 'nhanvien01',  '202cb962ac59075b964b07152d234b70', 'nhan_vien', 1, 'Nguyen Thanh Truc',NULL, NULL,          0, '2026-04-27 14:09:41'),
(3, '0932396610',  'e10adc3949ba59abbe56e057f20f883e', 'khach',     1, 'Le Hoang My',      '', '0932396610',   0, '2026-04-27 15:51:20'),
(4, '01871638136', 'e10adc3949ba59abbe56e057f20f883e', 'khach',     1, 'Nguyen AA',        '', '01871638136',  0, '2026-04-27 15:52:31'),
(5, '0187193123',  'e10adc3949ba59abbe56e057f20f883e', 'khach',     1, 'Nguyen AA',        '', '0187193123',   0, '2026-04-27 15:57:39');

-- ============================================================
-- Khóa ngoại (Foreign Keys)
-- ============================================================

ALTER TABLE `danh_gia`
  ADD CONSTRAINT `danh_gia_fk_mon_an`    FOREIGN KEY (`mon_an_id`)    REFERENCES `mon_an`    (`id`),
  ADD CONSTRAINT `danh_gia_fk_tai_khoan` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`id`);

ALTER TABLE `dat_ban`
  ADD CONSTRAINT `dat_ban_fk_ban` FOREIGN KEY (`ban_id`) REFERENCES `ban` (`id`);

ALTER TABLE `chitiet_datban`
  ADD CONSTRAINT `chitiet_datban_fk_dat_ban` FOREIGN KEY (`dat_ban_id`) REFERENCES `dat_ban` (`id`),
  ADD CONSTRAINT `chitiet_datban_fk_ban`     FOREIGN KEY (`ban_id`)     REFERENCES `ban`     (`id`);
ALTER TABLE `don_mon`
  ADD CONSTRAINT `don_mon_fk_ban` FOREIGN KEY (`ban_id`) REFERENCES `ban` (`id`);

ALTER TABLE `chitiet_donmon`
  ADD CONSTRAINT `chitiet_donmon_fk_don_mon` FOREIGN KEY (`don_mon_id`) REFERENCES `don_mon` (`id`),
  ADD CONSTRAINT `chitiet_donmon_fk_mon_an`  FOREIGN KEY (`mon_an_id`)  REFERENCES `mon_an`  (`id`);
