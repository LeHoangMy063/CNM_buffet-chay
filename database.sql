-- Server: localhost -  Database: buffet_chay
-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 01, 2026 at 12:17 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `buffet_chay`
--

-- --------------------------------------------------------

--
-- Table structure for table `ban`
--

CREATE TABLE `ban` (
  `id` int(11) NOT NULL auto_increment,
  `so_ban` varchar(10) default NULL,
  `suc_chua` int(11) default '4',
  `trang_thai` enum('trong','dang_dung') default 'trong',
  `ma_truy_cap` varchar(10) default NULL,
  `ma_phien_goi_mon` varchar(30) default NULL,
  `ma_phien_het_han` datetime default NULL,
  `phien_ten_khach` varchar(100) default NULL,
  `phien_sdt_khach` varchar(20) default NULL,
  `phien_nguoi_lon` int(11) NOT NULL default '0',
  `phien_tre_em` int(11) NOT NULL default '0',
  `phien_tong_tien` decimal(12,0) NOT NULL default '0',
  `phien_bat_dau` datetime default NULL,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `so_ban` (`so_ban`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `ban`
--

INSERT INTO `ban` (`id`, `so_ban`, `suc_chua`, `trang_thai`, `ma_truy_cap`, `ngay_tao`) VALUES
(1, 'A1', 4, 'dang_dung', 'BAN-A1', '2026-04-21 18:03:24'),
(2, 'A2', 4, 'trong', 'BAN-A2', '2026-04-21 18:03:24'),
(3, 'A3', 6, 'trong', 'BAN-A3', '2026-04-21 18:03:24'),
(4, 'A4', 6, 'trong', 'BAN-A4', '2026-04-21 18:03:24'),
(5, 'B1', 2, 'trong', 'BAN-B1', '2026-04-21 18:03:24'),
(6, 'B2', 2, 'trong', 'BAN-B2', '2026-04-21 18:03:24'),
(7, 'B3', 8, 'trong', 'BAN-B3', '2026-04-21 18:03:24'),
(8, 'B4', 8, 'trong', 'BAN-B4', '2026-04-21 18:03:24');

-- --------------------------------------------------------

--
-- Table structure for table `phien_goi_mon`
--

CREATE TABLE `phien_goi_mon` (
  `id` int(11) NOT NULL auto_increment,
  `ban_id` int(11) NOT NULL,
  `ma_phien` varchar(30) NOT NULL,
  `bat_dau_luc` datetime NOT NULL,
  `het_han_luc` datetime NOT NULL,
  `ket_thuc_luc` datetime default NULL,
  `trang_thai` enum('dang_dung','het_han','da_ket_thuc','da_huy') NOT NULL default 'dang_dung',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_phien` (`ma_phien`),
  KEY `ban_id` (`ban_id`),
  KEY `trang_thai` (`trang_thai`),
  KEY `bat_dau_luc` (`bat_dau_luc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hoa_don_phien`
--

CREATE TABLE `hoa_don_phien` (
  `id` int(11) NOT NULL auto_increment,
  `phien_goi_mon_id` int(11) NOT NULL,
  `ten_khach` varchar(100) default NULL,
  `sdt_khach` varchar(20) default NULL,
  `so_nguoi_lon` int(11) NOT NULL default '0',
  `so_tre_em` int(11) NOT NULL default '0',
  `tong_tien` decimal(12,0) NOT NULL default '0',
  `ghi_chu` text,
  `tich_diem_luc` datetime default NULL,
  `tich_diem_tai_khoan_id` int(11) default NULL,
  `diem_da_cong` int(11) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phien_goi_mon_id` (`phien_goi_mon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `doanh_thu_ngay`
--

CREATE TABLE `doanh_thu_ngay` (
  `id` int(11) NOT NULL auto_increment,
  `ngay` date NOT NULL,
  `so_khach` int(11) NOT NULL default '0',
  `so_phien` int(11) NOT NULL default '0',
  `doanh_thu` decimal(12,0) NOT NULL default '0',
  `nguon` varchar(30) NOT NULL default 'he_thong',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ngay_cap_nhat` datetime default NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_doanh_thu_ngay` (`ngay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `doanh_thu_ngay`
--

INSERT INTO `doanh_thu_ngay` (`id`, `ngay`, `so_khach`, `so_phien`, `doanh_thu`, `nguon`, `ngay_cap_nhat`) VALUES
(1, '2026-04-01', 34, 9, '6766000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(2, '2026-04-02', 41, 11, '8159000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(3, '2026-04-03', 48, 12, '9552000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(4, '2026-04-04', 76, 18, '15124000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(5, '2026-04-05', 69, 17, '13731000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(6, '2026-04-06', 32, 8, '6368000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(7, '2026-04-07', 37, 9, '7363000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(8, '2026-04-08', 45, 11, '8955000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(9, '2026-04-09', 43, 10, '8557000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(10, '2026-04-10', 52, 13, '10348000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(11, '2026-04-11', 82, 20, '16318000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(12, '2026-04-12', 74, 18, '14726000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(13, '2026-04-13', 35, 9, '6965000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(14, '2026-04-14', 39, 10, '7761000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(15, '2026-04-15', 47, 12, '9353000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(16, '2026-04-16', 44, 11, '8756000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(17, '2026-04-17', 58, 14, '11542000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(18, '2026-04-18', 88, 21, '17512000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(19, '2026-04-19', 79, 19, '15721000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(20, '2026-04-20', 36, 9, '7164000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(21, '2026-04-21', 42, 10, '8358000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(22, '2026-04-22', 49, 12, '9751000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(23, '2026-04-23', 46, 11, '9154000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(24, '2026-04-24', 57, 14, '11343000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(25, '2026-04-25', 91, 22, '18109000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(26, '2026-04-26', 84, 20, '16716000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(27, '2026-04-27', 38, 9, '7562000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(28, '2026-04-28', 44, 11, '8756000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(29, '2026-04-29', 63, 15, '12537000', 'du_lieu_mau', '2026-05-01 00:00:00'),
(30, '2026-04-30', 96, 23, '19104000', 'du_lieu_mau', '2026-05-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_datban`
--

CREATE TABLE `chitiet_datban` (
  `id` int(11) NOT NULL auto_increment,
  `dat_ban_id` int(11) NOT NULL,
  `ban_id` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_chitiet_datban` (`dat_ban_id`,`ban_id`),
  KEY `ban_id` (`ban_id`),
  KEY `dat_ban_id` (`dat_ban_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;

--
-- Dumping data for table `chitiet_datban`
--

INSERT INTO `chitiet_datban` (`id`, `dat_ban_id`, `ban_id`, `ngay_tao`) VALUES
(40, 25, 5, '2026-04-30 21:44:18'),
(41, 25, 1, '2026-04-30 21:44:18'),
(42, 25, 4, '2026-04-30 21:44:18'),
(43, 25, 8, '2026-04-30 21:44:18'),
(48, 27, 5, '2026-04-30 21:57:42'),
(50, 28, 5, '2026-04-30 21:58:02');

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_donmon`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `chitiet_donmon`
--

INSERT INTO `chitiet_donmon` (`id`, `don_mon_id`, `mon_an_id`, `so_luong`, `ghi_chu`, `trang_thai`, `ngay_tao`) VALUES
(14, 7, 36, 1, '', 'da_phuc_vu', '2026-04-30 22:51:13'),
(15, 7, 41, 1, '', 'da_phuc_vu', '2026-04-30 22:51:13'),
(16, 7, 42, 1, '', 'da_phuc_vu', '2026-04-30 22:51:13');

-- --------------------------------------------------------

--
-- Table structure for table `danh_gia`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `danh_gia`
--


-- --------------------------------------------------------

--
-- Table structure for table `dat_ban`
--

CREATE TABLE `dat_ban` (
  `id` int(11) NOT NULL auto_increment,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `dat_ban`
--

INSERT INTO `dat_ban` (`id`, `ban_id`, `ten_khach`, `sdt_khach`, `so_nguoi_lon`, `so_tre_em`, `tong_tien`, `ngay_dat`, `gio_dat`, `ghi_chu`, `trang_thai`, `ma_dat_ban`, `ban_xac_nhan`, `ngay_tao`) VALUES
(24, NULL, 'My', '0826893126', 13, 0, '2587000.00', '2026-05-01', '10:00:00', '', 'expired', 'RES-20260430-52765', 0, '2026-04-30 21:43:39'),
(25, 1, 'Le Anh', '01827132', 20, 0, '3980000.00', '2026-05-01', '10:00:00', '', 'da_xac_nhan', 'RES-20260430-98422', 1, '2026-04-30 21:44:18'),
(26, NULL, 'Le Anh', '018271321', 10, 0, '1990000.00', '2026-05-01', '11:30:00', '', 'cancelled', 'RES-20260430-63833', 0, '2026-04-30 21:45:33'),
(27, 5, 'My Mi', '01827132', 2, 0, '398000.00', '2026-05-01', '12:30:00', '', 'cho_xac_nhan', 'RES-20260430-67602', 0, '2026-04-30 21:57:42'),
(28, 5, 'My MII', '01827132', 2, 0, '398000.00', '2026-05-01', '15:30:00', '', 'cho_xac_nhan', 'RES-20260430-60104', 0, '2026-04-30 21:58:02');

-- --------------------------------------------------------

--
-- Table structure for table `don_mon`
--

CREATE TABLE `don_mon` (
  `id` int(11) NOT NULL auto_increment,
  `ban_id` int(11) default NULL,
  `phien_goi_mon_id` int(11) default NULL,
  `trang_thai` enum('cho_phuc_vu','dang_che_bien','da_phuc_vu','da_huy') default 'cho_phuc_vu',
  `tong_tien` decimal(10,2) default '0.00',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `ban_id` (`ban_id`),
  KEY `phien_goi_mon_id` (`phien_goi_mon_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `don_mon`
--

INSERT INTO `don_mon` (`id`, `ban_id`, `trang_thai`, `tong_tien`, `ngay_tao`) VALUES
(3, 1, 'da_phuc_vu', '0.00', '2026-04-28 22:14:43'),
(4, 1, 'da_phuc_vu', '0.00', '2026-04-28 22:15:25'),
(5, 1, 'da_phuc_vu', '0.00', '2026-04-28 22:15:52'),
(6, 1, 'da_phuc_vu', '0.00', '2026-04-28 22:16:09'),
(7, 1, 'da_phuc_vu', '0.00', '2026-04-30 22:51:13');

-- --------------------------------------------------------

--
-- Table structure for table `mon_an`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

--
-- Dumping data for table `mon_an`
--

INSERT INTO `mon_an` (`id`, `ten`, `mo_ta`, `danh_muc`, `anh_url`, `gia`, `con_mon`, `noi_bat`, `thu_tu`, `ngay_tao`) VALUES
(1, 'Gỏi cuốn chay', 'Rau sống, đậu hũ cuốn bánh tráng', 'Khai vi', 'https://cdn.tgdd.vn/Files/2021/09/06/1380699/huong-dan-cach-lam-goi-cuon-ngu-sac-ngon-dep-mat-de-lam-tai-nha-202109062206054450.jpg', '0', 1, 1, 1, '2026-04-21 18:03:24'),
(2, 'Chả giò chay', 'Nhân rau củ chiên giòn', 'Khai vi', 'https://cooponline.vn/tin-tuc/wp-content/uploads/2025/10/cong-thuc-cha-gio-chay-gion-rum-thanh-dam-va-de-lam-tai-nha.png', '0', 1, 0, 2, '2026-04-21 18:03:24'),
(3, 'Súp bí đỏ', 'Bí đỏ nấu mềm với nước cốt dừa', 'Khai vi', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80', '0', 1, 0, 3, '2026-04-21 18:03:24'),
(4, 'Salad rau', 'Rau mầm, cà chua, sốt chanh', 'Khai vi', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80', '0', 1, 1, 4, '2026-04-21 18:03:24'),
(5, 'Cơm chiên chay', 'Cơm xào rau củ', 'Mon chinh', 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&q=80', '0', 1, 1, 11, '2026-04-21 18:03:24'),
(6, 'Mì xào chay', 'Mì xào nấm và rau', 'Mon chinh', 'https://helenrecipes.com/wp-content/uploads/2021/10/Screenshot-2021-10-06-101702-1200x675.png', '0', 1, 0, 12, '2026-04-21 18:03:24'),
(7, 'Cà ri chay', 'Cà ri rau củ nước dừa', 'Mon chinh', 'https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=600&q=80', '0', 1, 1, 13, '2026-04-21 18:03:24'),
(8, 'Đậu hũ sốt cà', 'Đậu hũ chiên sốt cà chua', 'Mon chinh', 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=600&q=80', '0', 1, 0, 14, '2026-04-21 18:03:24'),
(9, 'Nấm kho tiêu', 'Nấm đông cô kho tiêu đậm đà', 'Mon chinh', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80', '0', 1, 1, 15, '2026-04-21 18:03:24'),
(10, 'Thanh cua chay', 'Giả cua, vị nhẹ', 'Mon chinh', 'https://images.unsplash.com/photo-1576402187878-974f70c890a5?w=600&q=80', '0', 1, 0, 32, '2026-04-21 18:03:24'),
(11, 'Bún xào chay', 'Bún xào rau củ và đậu hũ', 'Mon chinh', 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=600&q=80', '0', 1, 0, 16, '2026-04-21 18:03:24'),
(12, 'Canh rong biển', 'Canh rong biển thanh nhẹ', 'Mon chinh', 'https://assets.unileversolutions.com/recipes-v2/157820.jpg', '0', 1, 1, 17, '2026-04-21 18:03:24'),
(13, 'Đậu hũ non', 'Mềm mịn thấm nước lẩu', 'Topping', 'https://thaisumo.vn/wp-content/uploads/2025/12/Tau-Hu-Non.jpg', '0', 1, 0, 20, '2026-04-21 18:03:24'),
(14, 'Tàu hũ ky', 'Dai mềm, thấm nước dùng', 'Topping', 'https://cdnv2.tgdd.vn/mwg-static/common/Common/_%281200%20x%20676%20px%29%20%281200%20x%20676%20px%29%20%28620%20x%20620%20px%29.jpg', '0', 1, 1, 26, '2026-04-21 18:03:24'),
(15, 'Chả viên chay', 'Viên dai, vị đậm đà', 'Topping', 'https://tubahi.com/wp-content/uploads/2022/11/ngoc-tam-bam.jpg', '0', 1, 1, 27, '2026-04-21 18:03:24'),
(16, 'Há cảo chay', 'Nhân rau củ bọc bột', 'Topping', 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=600&q=80', '0', 1, 0, 28, '2026-04-21 18:03:24'),
(17, 'Chả lụa chay', 'Dai mềm, vị thanh nhẹ', 'Topping', 'https://lh3.googleusercontent.com/tdfTqvgzfo4KD7bMNh2NvIOIbdnXhYElmAmqFTPeUvn-vjAtx5q1oLqbwW51arfg3EfVo1it4ZBgzppmoSXxR-LqrmgOxl-kBA', '0', 1, 0, 29, '2026-04-21 18:03:24'),
(18, 'Phù trúc cuốn', 'Tàu hũ ky cuốn sẵn', 'Topping', 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600&q=80', '0', 1, 1, 30, '2026-04-21 18:03:24'),
(19, 'Sườn non chay', 'Dai mềm, thấm vị nước lẩu', 'Topping', 'https://cdn2.fptshop.com.vn/unsafe/suon_non_chay_kho_tieu_4_7a878594e5.jpg', '0', 1, 1, 38, '2026-04-21 18:03:24'),
(20, 'Bún tươi', 'Ăn kèm lẩu', 'Topping', 'https://byvn.net/05E4', '0', 1, 1, 24, '2026-04-21 18:03:24'),
(21, 'Mì trứng', 'Sợi dai', 'Topping', 'https://sieuthiminitunjp.com/wp-content/uploads/2024/08/M%C3%AC-Tr%E1%BB%A9ng-400g-e1725106322340.jpg', '0', 1, 0, 25, '2026-04-21 18:03:24'),
(22, 'Rau muống', 'Giòn xanh', 'Rau', 'https://res.ketnoiocop.vn/user-4905/rau-muong-xao-toi-1.png', '0', 1, 1, 30, '2026-04-21 18:03:24'),
(23, 'Cải thảo', 'Ngọt thanh', 'Rau', 'https://suckhoedoisong.qltns.mediacdn.vn/324455921873985536/2021/11/2/photo-1-1624343134770111869319-1635818661034145472479.jpeg', '0', 1, 0, 31, '2026-04-21 18:03:24'),
(24, 'Rau cải ngọt', 'Thanh nhẹ, dễ ăn', 'Rau', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&q=80', '0', 1, 0, 32, '2026-04-21 18:03:24'),
(25, 'Giá đỗ', 'Giòn mát', 'Rau', 'https://cdn.tgdd.vn/Files/2019/12/17/1227098/loi-ich-va-cach-lam-nuoc-gia-do-don-gian-tai-nha-201912171044280351.jpg', '0', 1, 1, 33, '2026-04-21 18:03:24'),
(26, 'Nấm kim châm', 'Giòn ngọt', 'Rau', 'https://suckhoedoisong.qltns.mediacdn.vn/324455921873985536/2022/6/19/photo-1655456362258-16554563647291898456211-1655607472210-16556074723031445388236.jpg', '0', 1, 1, 22, '2026-04-21 18:03:24'),
(27, 'Nấm đông cô', 'Thơm đặc trưng', 'Rau', 'https://shop.annam-gourmet.com/pub/media/catalog/product/F/1/F138640_7649.jpg', '0', 1, 0, 23, '2026-04-21 18:03:24'),
(28, 'Rau mồng tơi', 'Mềm mát, dễ ăn', 'Rau', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=600&q=80', '0', 1, 1, 34, '2026-04-21 18:03:24'),
(29, 'Rau tần ô', 'Thơm nhẹ, đặc trưng lẩu', 'Rau', 'https://bizweb.dktcdn.net/thumb/large/100/469/751/products/tan-o-1669530948604.jpg', '0', 1, 1, 36, '2026-04-21 18:03:24'),
(30, 'Bắp cải', 'Giòn, ngọt khi nấu', 'Rau', 'https://giadinh.mediacdn.vn/296230595582509056/2023/5/25/photo-1682914879708346672440-1684985972447-16849859725291324845276.jpg', '0', 1, 0, 37, '2026-04-21 18:03:24'),
(31, 'Rau lang', 'Mềm, hơi bùi', 'Rau', 'https://images.unsplash.com/photo-1622205313162-be1d5712a43f?w=600&q=80', '0', 1, 0, 38, '2026-04-21 18:03:24'),
(32, 'Lẩu nấm', 'Nước lẩu thanh ngọt từ nấm', 'Nuoc lau', 'https://cdn.tgdd.vn/2021/02/CookProduct/1114-1200x676.jpg', '0', 1, 1, 9, '2026-04-21 18:03:24'),
(33, 'Lẩu dashi', 'Thanh nhẹ', 'Nuoc lau', 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80', '0', 1, 1, 40, '2026-04-21 18:03:24'),
(34, 'Lẩu tomyum', 'Chua cay', 'Nuoc lau', 'https://images.unsplash.com/photo-1562565652-a0d8f0c59eb4?w=600&q=80', '0', 1, 1, 41, '2026-04-21 18:03:24'),
(35, 'Lẩu kim chi', 'Cay nồng', 'Nuoc lau', 'https://cdn11.dienmaycholon.vn/filewebdmclnew/public/userupload/files/kien-thuc/cach-lam-lau-nam-kim-chi-chay/cach-lam-lau-nam-kim-chi-chay-8.jpg', '0', 1, 1, 42, '2026-04-21 18:03:24'),
(36, 'Nước cam', 'Tươi mát', 'Do uong', 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=600&q=80', '0', 1, 1, 50, '2026-04-21 18:03:24'),
(37, 'Nước ép dưa hấu', 'Mát lạnh, giải khát', 'Do uong', 'https://tandoorvietnam.com/wp-content/uploads/2020/06/Watermelon-juice.jpg', '0', 1, 1, 51, '2026-04-21 18:03:24'),
(38, 'Nước chanh', 'Chua nhẹ giải nhiệt', 'Do uong', 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&q=80', '0', 1, 1, 52, '2026-04-21 18:03:24'),
(39, 'Trà tắc', 'Chua ngọt, thơm mùi tắc', 'Do uong', 'https://cdn.tgdd.vn/2021/10/CookRecipe/Avatar/tra-tac-mat-ong-sa-que-thumbnail.jpg', '0', 1, 1, 53, '2026-04-21 18:03:24'),
(40, 'Trà đào', 'Ngọt dịu, thơm mát', 'Do uong', 'https://cdn.s99.vn/ss1/prod/thumb/a3dad287a87194c944c0b976295ec16f.jpg', '0', 1, 1, 54, '2026-04-21 18:03:24'),
(41, 'Sinh tố xoài', 'Béo mịn bổ dưỡng', 'Do uong', 'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?w=600&q=80', '0', 1, 1, 55, '2026-04-21 18:03:24'),
(42, 'Sinh tố dâu', 'Chua ngọt dễ uống', 'Do uong', 'https://product.hstatic.net/200000874293/product/dau300_c42940ceb9c243a08ede19b2640ffe96_grande.jpg', '0', 1, 0, 56, '2026-04-21 18:03:24');

-- --------------------------------------------------------

--
-- Table structure for table `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `id` int(11) NOT NULL auto_increment,
  `ten_dang_nhap` varchar(50) default NULL,
  `mat_khau` varchar(255) default NULL,
  `vai_tro` enum('quanly','nhanvien','bep') NOT NULL default 'nhanvien',
  `dang_hoat_dong` tinyint(1) NOT NULL default '1',
  `ho_ten` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `so_dien_thoai` varchar(20) default NULL,
  `diem_tich_luy` int(11) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `tai_khoan`
--

INSERT INTO `tai_khoan` (`id`, `ten_dang_nhap`, `mat_khau`, `vai_tro`, `dang_hoat_dong`, `ho_ten`, `email`, `so_dien_thoai`, `diem_tich_luy`, `ngay_tao`) VALUES
(1, 'quanly', '$2y$10$mYFxzdEx8VM1P2TBvXAc5.ez9b8L9zupQll/yqTZCXVjjHJpjcok.', 'quanly', 1, 'Quan tri vien', NULL, NULL, 0, '2026-04-27 14:09:41'),
(2, 'nhanvien01', '$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', 'nhanvien', 1, 'Nguyen Thanh Truc', NULL, NULL, 0, '2026-04-27 14:09:41'),
(6, 'quanly01', '$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', 'quanly', 1, 'Quan ly nha hang', NULL, NULL, 0, '2026-05-14 00:00:00'),
(7, 'nhanvien02', '$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', 'bep', 1, 'Nhan vien bep', NULL, NULL, 0, '2026-05-26 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `khach_tai_khoan`
--

CREATE TABLE `khach_tai_khoan` (
  `id` int(11) NOT NULL auto_increment,
  `ten_dang_nhap` varchar(50) default NULL,
  `mat_khau` varchar(255) default NULL,
  `vai_tro` enum('khach') NOT NULL default 'khach',
  `dang_hoat_dong` tinyint(1) NOT NULL default '1',
  `ho_ten` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `so_dien_thoai` varchar(20) default NULL,
  `diem_tich_luy` int(11) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `khach_tai_khoan`
--

INSERT INTO `khach_tai_khoan` (`id`, `ten_dang_nhap`, `mat_khau`, `vai_tro`, `dang_hoat_dong`, `ho_ten`, `email`, `so_dien_thoai`, `diem_tich_luy`, `ngay_tao`) VALUES
(1, '0932396610', '$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'khach', 1, 'Le Hoang My', '', '0932396610', 0, '2026-04-27 15:51:20'),
(2, '01871638136', '$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'khach', 1, 'Nguyen AA', '', '01871638136', 0, '2026-04-27 15:52:31'),
(3, '0187193123', '$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'khach', 1, 'Nguyen AA', '', '0187193123', 0, '2026-04-27 15:57:39');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitiet_datban`
--
ALTER TABLE `chitiet_datban`
  ADD CONSTRAINT `chitiet_datban_fk_ban` FOREIGN KEY (`ban_id`) REFERENCES `ban` (`id`),
  ADD CONSTRAINT `chitiet_datban_fk_dat_ban` FOREIGN KEY (`dat_ban_id`) REFERENCES `dat_ban` (`id`);

--
-- Constraints for table `chitiet_donmon`
--
ALTER TABLE `chitiet_donmon`
  ADD CONSTRAINT `chitiet_donmon_fk_don_mon` FOREIGN KEY (`don_mon_id`) REFERENCES `don_mon` (`id`),
  ADD CONSTRAINT `chitiet_donmon_fk_mon_an` FOREIGN KEY (`mon_an_id`) REFERENCES `mon_an` (`id`);

--
-- Constraints for table `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD CONSTRAINT `danh_gia_fk_mon_an` FOREIGN KEY (`mon_an_id`) REFERENCES `mon_an` (`id`),
  ADD CONSTRAINT `danh_gia_fk_tai_khoan` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`id`);

--
-- Constraints for table `dat_ban`
--
ALTER TABLE `dat_ban`
  ADD CONSTRAINT `dat_ban_fk_ban` FOREIGN KEY (`ban_id`) REFERENCES `ban` (`id`);

--
-- Constraints for table `don_mon`
--
ALTER TABLE `don_mon`
  ADD CONSTRAINT `don_mon_fk_ban` FOREIGN KEY (`ban_id`) REFERENCES `ban` (`id`),
  ADD CONSTRAINT `don_mon_fk_phien` FOREIGN KEY (`phien_goi_mon_id`) REFERENCES `phien_goi_mon` (`id`);

--
-- Constraints for table `phien_goi_mon`
--
ALTER TABLE `phien_goi_mon`
  ADD CONSTRAINT `phien_goi_mon_fk_ban` FOREIGN KEY (`ban_id`) REFERENCES `ban` (`id`);

--
-- Constraints for table `hoa_don_phien`
--
ALTER TABLE `hoa_don_phien`
  ADD CONSTRAINT `hoa_don_phien_fk_phien` FOREIGN KEY (`phien_goi_mon_id`) REFERENCES `phien_goi_mon` (`id`);

