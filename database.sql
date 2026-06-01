-- =========================================================
-- Buffet Chay An Lac - Database chuan ID chuoi
-- Muc tieu:
-- - Moi bang co khoa chinh dang id_<ten_bang> kieu NVARCHAR
-- - Gia tri ID la ma nghiep vu co tien to + ngay/so thu tu
-- - Khoa ngoai dung cung ten voi bang duoc tham chieu
-- - MySQL/WAMP cu: DEFAULT CHARSET=utf8
-- =========================================================

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS `buffet_chay` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `buffet_chay`;

DROP TABLE IF EXISTS `goi_y_mon_batch`;
DROP TABLE IF EXISTS `hanh_vi_goi_mon`;
DROP TABLE IF EXISTS `danh_gia`;
DROP TABLE IF EXISTS `chitiet_donmon`;
DROP TABLE IF EXISTS `don_mon`;
DROP TABLE IF EXISTS `chi_tiet_thanh_toan_phien`;
DROP TABLE IF EXISTS `thanh_toan_phien`;
DROP TABLE IF EXISTS `hoa_don_phien`;
DROP TABLE IF EXISTS `phien_ban`;
DROP TABLE IF EXISTS `phien_goi_mon`;
DROP TABLE IF EXISTS `chitiet_datban`;
DROP TABLE IF EXISTS `dat_ban`;
DROP TABLE IF EXISTS `khach_tai_khoan`;
DROP TABLE IF EXISTS `tai_khoan`;
DROP TABLE IF EXISTS `mon_an`;
DROP TABLE IF EXISTS `danh_muc_mon`;
DROP TABLE IF EXISTS `ban`;
DROP TABLE IF EXISTS `bo_dem_ma`;

-- Luu bo dem de ung dung tao ma moi theo tien to, vi MySQL khong auto increment cho chuoi.
CREATE TABLE `bo_dem_ma` (
  `tien_to` varchar(20) NOT NULL,
  `gia_tri_hien_tai` int(11) NOT NULL default '0',
  `mo_ta` varchar(100) default NULL,
  PRIMARY KEY (`tien_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ban` (
  `id_ban` nvarchar(20) NOT NULL,
  `so_ban` varchar(10) NOT NULL,
  `khu_vuc` varchar(50) default NULL,
  `suc_chua` int(11) NOT NULL default '4',
  `trang_thai` enum('trong','dang_dung','bao_tri') NOT NULL default 'trong',
  `ma_truy_cap` nvarchar(20) default NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_ban`),
  UNIQUE KEY `uq_ban_so_ban` (`so_ban`),
  UNIQUE KEY `uq_ban_ma_truy_cap` (`ma_truy_cap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `danh_muc_mon` (
  `id_danh_muc_mon` nvarchar(30) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `mo_ta` varchar(255) default NULL,
  `thu_tu` int(11) NOT NULL default '0',
  `dang_hien_thi` tinyint(1) NOT NULL default '1',
  PRIMARY KEY (`id_danh_muc_mon`),
  UNIQUE KEY `uq_danh_muc_ten` (`ten_danh_muc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mon_an` (
  `id_mon_an` nvarchar(30) NOT NULL,
  `id_danh_muc_mon` nvarchar(30) NOT NULL,
  `ten_mon` varchar(150) NOT NULL,
  `mo_ta` text,
  `anh_url` varchar(255) default NULL,
  `con_mon` tinyint(1) NOT NULL default '1',
  `noi_bat` tinyint(1) NOT NULL default '0',
  `thu_tu` int(11) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mon_an`),
  KEY `idx_mon_an_danh_muc` (`id_danh_muc_mon`),
  CONSTRAINT `fk_mon_an_danh_muc`
    FOREIGN KEY (`id_danh_muc_mon`) REFERENCES `danh_muc_mon` (`id_danh_muc_mon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tai_khoan` (
  `id_tai_khoan` nvarchar(30) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` enum('quanly','nhanvien','bep') NOT NULL default 'nhanvien',
  `dang_hoat_dong` tinyint(1) NOT NULL default '1',
  `ho_ten` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `so_dien_thoai` varchar(20) default NULL,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tai_khoan`),
  UNIQUE KEY `uq_tai_khoan_ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `uq_tai_khoan_so_dien_thoai` (`so_dien_thoai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `khach_tai_khoan` (
  `id_khach_tai_khoan` nvarchar(30) NOT NULL,
  `ten_dang_nhap` varchar(50) default NULL,
  `mat_khau` varchar(255) default NULL,
  `vai_tro` enum('khach') NOT NULL default 'khach',
  `dang_hoat_dong` tinyint(1) NOT NULL default '1',
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) default NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `diem_tich_luy` int(11) NOT NULL default '0',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_khach_tai_khoan`),
  UNIQUE KEY `uq_khach_ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `uq_khach_so_dien_thoai` (`so_dien_thoai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dat_ban` (
  `id_dat_ban` nvarchar(30) NOT NULL,
  `ma_dat_ban` nvarchar(30) NOT NULL,
  `id_khach_tai_khoan` nvarchar(30) default NULL,
  `ten_khach` varchar(100) NOT NULL,
  `sdt_khach` varchar(20) NOT NULL,
  `ngay_dat` date NOT NULL,
  `gio_dat` time NOT NULL,
  `so_nguoi_lon` int(11) NOT NULL default '1',
  `so_tre_em` int(11) NOT NULL default '0',
  `ghi_chu` text,
  `trang_thai` enum('cho_xac_nhan','da_xac_nhan','da_huy','expired','hoan_thanh') NOT NULL default 'cho_xac_nhan',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_dat_ban`),
  UNIQUE KEY `uq_dat_ban_ma` (`ma_dat_ban`),
  KEY `idx_dat_ban_khach` (`id_khach_tai_khoan`),
  KEY `idx_dat_ban_sdt` (`sdt_khach`),
  KEY `idx_dat_ban_lich` (`ngay_dat`, `gio_dat`),
  CONSTRAINT `fk_dat_ban_khach`
    FOREIGN KEY (`id_khach_tai_khoan`) REFERENCES `khach_tai_khoan` (`id_khach_tai_khoan`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chitiet_datban` (
  `id_chitiet_datban` nvarchar(30) NOT NULL,
  `id_dat_ban` nvarchar(30) NOT NULL,
  `id_ban` nvarchar(20) NOT NULL,
  `thoi_gian_bat_dau` datetime NOT NULL,
  `thoi_gian_ket_thuc` datetime NOT NULL,
  `trang_thai` enum('dang_gan','da_huy','hoan_thanh') NOT NULL default 'dang_gan',
  PRIMARY KEY (`id_chitiet_datban`),
  UNIQUE KEY `uq_chitiet_datban` (`id_dat_ban`, `id_ban`),
  KEY `idx_chitiet_datban_ban` (`id_ban`),
  KEY `idx_chitiet_datban_lich` (`id_ban`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `trang_thai`),
  CONSTRAINT `fk_chitiet_datban_dat_ban`
    FOREIGN KEY (`id_dat_ban`) REFERENCES `dat_ban` (`id_dat_ban`) ON DELETE CASCADE,
  CONSTRAINT `fk_chitiet_datban_ban`
    FOREIGN KEY (`id_ban`) REFERENCES `ban` (`id_ban`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `phien_goi_mon` (
  `id_phien_goi_mon` nvarchar(30) NOT NULL,
  `ma_phien` nvarchar(30) NOT NULL,
  `id_dat_ban` nvarchar(30) default NULL,
  `id_khach_tai_khoan` nvarchar(30) default NULL,
  `ten_khach` varchar(100) default NULL,
  `sdt_khach` varchar(20) default NULL,
  `so_nguoi_lon` int(11) NOT NULL default '0',
  `so_tre_em` int(11) NOT NULL default '0',
  `gio_bat_dau` datetime NOT NULL,
  `gio_ket_thuc_du_kien` datetime NOT NULL,
  `gio_ket_thuc` datetime default NULL,
  `trang_thai` enum('dang_dung','het_han','da_ket_thuc','da_huy') NOT NULL default 'dang_dung',
  `tong_tien_tam_tinh` decimal(12,0) NOT NULL default '0',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_phien_goi_mon`),
  UNIQUE KEY `uq_phien_ma` (`ma_phien`),
  KEY `idx_phien_dat_ban` (`id_dat_ban`),
  KEY `idx_phien_khach` (`id_khach_tai_khoan`),
  KEY `idx_phien_trang_thai` (`trang_thai`),
  CONSTRAINT `fk_phien_goi_mon_dat_ban`
    FOREIGN KEY (`id_dat_ban`) REFERENCES `dat_ban` (`id_dat_ban`) ON DELETE SET NULL,
  CONSTRAINT `fk_phien_goi_mon_khach`
    FOREIGN KEY (`id_khach_tai_khoan`) REFERENCES `khach_tai_khoan` (`id_khach_tai_khoan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `phien_ban` (
  `id_phien_ban` nvarchar(30) NOT NULL,
  `id_phien_goi_mon` nvarchar(30) NOT NULL,
  `id_ban` nvarchar(20) NOT NULL,
  `thoi_gian_gan` datetime NOT NULL,
  `thoi_gian_roi_ban` datetime default NULL,
  `trang_thai` enum('dang_gan','da_roi','da_huy') NOT NULL default 'dang_gan',
  PRIMARY KEY (`id_phien_ban`),
  UNIQUE KEY `uq_phien_ban` (`id_phien_goi_mon`, `id_ban`),
  KEY `idx_phien_ban_ban` (`id_ban`, `trang_thai`),
  CONSTRAINT `fk_phien_ban_phien`
    FOREIGN KEY (`id_phien_goi_mon`) REFERENCES `phien_goi_mon` (`id_phien_goi_mon`) ON DELETE CASCADE,
  CONSTRAINT `fk_phien_ban_ban`
    FOREIGN KEY (`id_ban`) REFERENCES `ban` (`id_ban`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `hoa_don_phien` (
  `id_hoa_don_phien` nvarchar(30) NOT NULL,
  `ma_hoa_don` nvarchar(30) NOT NULL,
  `id_phien_goi_mon` nvarchar(30) NOT NULL,
  `id_khach_tai_khoan` nvarchar(30) default NULL,
  `ten_khach` varchar(100) default NULL,
  `sdt_khach` varchar(20) default NULL,
  `tong_tien_buffet` decimal(12,0) NOT NULL default '0',
  `tong_tien` decimal(12,0) NOT NULL default '0',
  `giam_gia` decimal(12,0) NOT NULL default '0',
  `thanh_tien` decimal(12,0) NOT NULL default '0',
  `da_tich_diem` tinyint(1) NOT NULL default '0',
  `trang_thai` enum('chua_thanh_toan','da_thanh_toan','da_huy') NOT NULL default 'chua_thanh_toan',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_hoa_don_phien`),
  UNIQUE KEY `uq_hoa_don_ma` (`ma_hoa_don`),
  UNIQUE KEY `uq_hoa_don_phien` (`id_phien_goi_mon`),
  KEY `idx_hoa_don_khach` (`id_khach_tai_khoan`),
  CONSTRAINT `fk_hoa_don_phien_phien`
    FOREIGN KEY (`id_phien_goi_mon`) REFERENCES `phien_goi_mon` (`id_phien_goi_mon`) ON DELETE CASCADE,
  CONSTRAINT `fk_hoa_don_phien_khach`
    FOREIGN KEY (`id_khach_tai_khoan`) REFERENCES `khach_tai_khoan` (`id_khach_tai_khoan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `thanh_toan_phien` (
  `id_thanh_toan` nvarchar(30) NOT NULL,
  `id_hoa_don_phien` nvarchar(30) NOT NULL,
  `tong_tien_can_thanh_toan` decimal(12,0) NOT NULL default '0',
  `tong_tien_da_thanh_toan` decimal(12,0) NOT NULL default '0',
  `trang_thai` enum('chua_thanh_toan','thanh_toan_mot_phan','da_thanh_toan','da_huy') NOT NULL default 'chua_thanh_toan',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_thanh_toan`),
  UNIQUE KEY `uq_thanh_toan_hoa_don` (`id_hoa_don_phien`),
  CONSTRAINT `fk_thanh_toan_hoa_don`
    FOREIGN KEY (`id_hoa_don_phien`) REFERENCES `hoa_don_phien` (`id_hoa_don_phien`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chi_tiet_thanh_toan_phien` (
  `id_chi_tiet_thanh_toan` nvarchar(30) NOT NULL,
  `id_thanh_toan` nvarchar(30) NOT NULL,
  `phuong_thuc` enum('TIEN_MAT','CHUYEN_KHOAN') NOT NULL,
  `so_tien` decimal(12,0) NOT NULL default '0',
  `ma_giao_dich` varchar(100) default NULL,
  `ghi_chu` text,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_chi_tiet_thanh_toan`),
  KEY `idx_cttt_thanh_toan` (`id_thanh_toan`),
  CONSTRAINT `fk_cttt_thanh_toan`
    FOREIGN KEY (`id_thanh_toan`) REFERENCES `thanh_toan_phien` (`id_thanh_toan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `don_mon` (
  `id_don_mon` nvarchar(30) NOT NULL,
  `id_phien_goi_mon` nvarchar(30) NOT NULL,
  `id_ban` nvarchar(20) default NULL,
  `trang_thai` enum('cho_phuc_vu','dang_che_bien','da_phuc_vu','da_huy') NOT NULL default 'cho_phuc_vu',
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_don_mon`),
  KEY `idx_don_mon_phien` (`id_phien_goi_mon`),
  KEY `idx_don_mon_ban` (`id_ban`),
  CONSTRAINT `fk_don_mon_phien`
    FOREIGN KEY (`id_phien_goi_mon`) REFERENCES `phien_goi_mon` (`id_phien_goi_mon`) ON DELETE CASCADE,
  CONSTRAINT `fk_don_mon_ban`
    FOREIGN KEY (`id_ban`) REFERENCES `ban` (`id_ban`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chitiet_donmon` (
  `id_chitiet_donmon` nvarchar(30) NOT NULL,
  `id_don_mon` nvarchar(30) NOT NULL,
  `id_mon_an` nvarchar(30) NOT NULL,
  `so_luong` int(11) NOT NULL default '1',
  `ghi_chu` text,
  `trang_thai_hien_tai` enum('cho_phuc_vu','dang_che_bien','da_phuc_vu','da_huy') NOT NULL default 'cho_phuc_vu',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_chitiet_donmon`),
  KEY `idx_chitiet_donmon_don` (`id_don_mon`),
  KEY `idx_chitiet_donmon_mon` (`id_mon_an`),
  CONSTRAINT `fk_chitiet_donmon_don`
    FOREIGN KEY (`id_don_mon`) REFERENCES `don_mon` (`id_don_mon`) ON DELETE CASCADE,
  CONSTRAINT `fk_chitiet_donmon_mon`
    FOREIGN KEY (`id_mon_an`) REFERENCES `mon_an` (`id_mon_an`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `danh_gia` (
  `id_danh_gia` nvarchar(30) NOT NULL,
  `id_khach_tai_khoan` nvarchar(30) NOT NULL,
  `id_mon_an` nvarchar(30) NOT NULL,
  `so_sao` tinyint(1) NOT NULL default '5',
  `binh_luan` text,
  `ngay_tao` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_danh_gia`),
  KEY `idx_danh_gia_khach` (`id_khach_tai_khoan`),
  KEY `idx_danh_gia_mon` (`id_mon_an`),
  CONSTRAINT `fk_danh_gia_khach`
    FOREIGN KEY (`id_khach_tai_khoan`) REFERENCES `khach_tai_khoan` (`id_khach_tai_khoan`) ON DELETE CASCADE,
  CONSTRAINT `fk_danh_gia_mon`
    FOREIGN KEY (`id_mon_an`) REFERENCES `mon_an` (`id_mon_an`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `hanh_vi_goi_mon` (
  `id_hanh_vi_goi_mon` nvarchar(30) NOT NULL,
  `id_phien_goi_mon` nvarchar(30) NOT NULL,
  `id_mon_an` nvarchar(30) NOT NULL,
  `loai_hanh_vi` enum('xem_mon','them_mon','goi_mon','huy_mon') NOT NULL,
  `gia_tri` int(11) NOT NULL default '1',
  `thoi_gian` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_hanh_vi_goi_mon`),
  KEY `idx_hanh_vi_phien` (`id_phien_goi_mon`),
  KEY `idx_hanh_vi_mon` (`id_mon_an`),
  CONSTRAINT `fk_hanh_vi_phien`
    FOREIGN KEY (`id_phien_goi_mon`) REFERENCES `phien_goi_mon` (`id_phien_goi_mon`) ON DELETE CASCADE,
  CONSTRAINT `fk_hanh_vi_mon`
    FOREIGN KEY (`id_mon_an`) REFERENCES `mon_an` (`id_mon_an`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `goi_y_mon_batch` (
  `id_goi_y_mon_batch` nvarchar(40) NOT NULL,
  `id_mon_goc` nvarchar(30) default NULL,
  `id_mon_goi_y` nvarchar(30) NOT NULL,
  `diem_pho_bien` decimal(10,2) NOT NULL default '0.00',
  `diem_di_cung` decimal(10,2) NOT NULL default '0.00',
  `diem_danh_gia` decimal(10,2) NOT NULL default '0.00',
  `diem_batch` decimal(10,2) NOT NULL default '0.00',
  `ngay_cap_nhat` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_goi_y_mon_batch`),
  UNIQUE KEY `uq_goi_y` (`id_mon_goc`, `id_mon_goi_y`),
  KEY `idx_goi_y_mon_goc` (`id_mon_goc`),
  KEY `idx_goi_y_mon_goi_y` (`id_mon_goi_y`),
  CONSTRAINT `fk_goi_y_mon_goc`
    FOREIGN KEY (`id_mon_goc`) REFERENCES `mon_an` (`id_mon_an`) ON DELETE CASCADE,
  CONSTRAINT `fk_goi_y_mon_goi_y`
    FOREIGN KEY (`id_mon_goi_y`) REFERENCES `mon_an` (`id_mon_an`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================================================
-- Du lieu mau
-- Mat khau mau:
-- - quanly: admin
-- - nhanvien01/nhanvien02/quanly01: 123
-- - khach: 123456
-- =========================================================

INSERT INTO `bo_dem_ma` (`tien_to`, `gia_tri_hien_tai`, `mo_ta`) VALUES
('BAN', 8, 'Ban an'),
('DM', 6, 'Danh muc mon'),
('MON', 20, 'Mon an'),
('TK-QL', 2, 'Tai khoan quan ly'),
('TK-NV', 1, 'Tai khoan nhan vien'),
('TK-BEP', 1, 'Tai khoan bep'),
('KH', 3, 'Khach hang'),
('DB', 5, 'Dat ban'),
('PBD', 6, 'Chi tiet dat ban'),
('PH', 0, 'Phien goi mon'),
('PB', 0, 'Ban trong phien'),
('LSDP', 0, 'Lich su dieu phoi ban'),
('HD', 0, 'Hoa don phien'),
('HDM', 0, 'Ma hoa don phien'),
('TT', 0, 'Thanh toan phien'),
('CTTT', 0, 'Chi tiet thanh toan phien'),
('DMON', 0, 'Don mon'),
('CTDM', 0, 'Chi tiet don mon'),
('LSTTM', 0, 'Lich su trang thai mon'),
('DG', 0, 'Danh gia'),
('LSD', 0, 'Lich su diem'),
('HV', 0, 'Hanh vi goi mon'),
('GY', 0, 'Goi y mon batch');

INSERT INTO `ban` (`id_ban`, `so_ban`, `khu_vuc`, `suc_chua`, `trang_thai`, `ma_truy_cap`, `created_at`) VALUES
('BAN-A1', 'A1', 'Khu A', 4, 'trong', 'BAN-A1', '2026-04-21 18:03:24'),
('BAN-A2', 'A2', 'Khu A', 4, 'trong', 'BAN-A2', '2026-04-21 18:03:24'),
('BAN-A3', 'A3', 'Khu A', 6, 'trong', 'BAN-A3', '2026-04-21 18:03:24'),
('BAN-A4', 'A4', 'Khu A', 6, 'trong', 'BAN-A4', '2026-04-21 18:03:24'),
('BAN-B1', 'B1', 'Khu B', 2, 'trong', 'BAN-B1', '2026-04-21 18:03:24'),
('BAN-B2', 'B2', 'Khu B', 2, 'trong', 'BAN-B2', '2026-04-21 18:03:24'),
('BAN-B3', 'B3', 'Khu B', 8, 'trong', 'BAN-B3', '2026-04-21 18:03:24'),
('BAN-B4', 'B4', 'Khu B', 8, 'trong', 'BAN-B4', '2026-04-21 18:03:24');

INSERT INTO `danh_muc_mon` (`id_danh_muc_mon`, `ten_danh_muc`, `mo_ta`, `thu_tu`, `dang_hien_thi`) VALUES
('DM-KHAIVI', 'Khai vi', NULL, 1, 1),
('DM-MONCHINH', 'Mon chinh', NULL, 2, 1),
('DM-TOPPING', 'Topping', NULL, 3, 1),
('DM-RAU', 'Rau', NULL, 4, 1),
('DM-NUOCLAU', 'Nuoc lau', NULL, 5, 1),
('DM-DOUONG', 'Do uong', NULL, 6, 1);

INSERT INTO `mon_an` (`id_mon_an`, `id_danh_muc_mon`, `ten_mon`, `mo_ta`, `anh_url`, `con_mon`, `noi_bat`, `thu_tu`, `ngay_tao`) VALUES
('MON-001', 'DM-KHAIVI', 'Goi cuon chay', 'Rau song, dau hu cuon banh trang', NULL, 1, 1, 1, '2026-04-21 18:03:24'),
('MON-002', 'DM-KHAIVI', 'Cha gio chay', 'Nhan rau cu chien gion', NULL, 1, 0, 2, '2026-04-21 18:03:24'),
('MON-003', 'DM-KHAIVI', 'Sup bi do', 'Bi do nau mem voi nuoc cot dua', NULL, 1, 0, 3, '2026-04-21 18:03:24'),
('MON-004', 'DM-KHAIVI', 'Salad rau', 'Rau mam, ca chua, sot chanh', NULL, 1, 1, 4, '2026-04-21 18:03:24'),
('MON-005', 'DM-MONCHINH', 'Com chien chay', 'Com xao rau cu', NULL, 1, 1, 11, '2026-04-21 18:03:24'),
('MON-006', 'DM-MONCHINH', 'Mi xao chay', 'Mi xao nam va rau', NULL, 1, 0, 12, '2026-04-21 18:03:24'),
('MON-007', 'DM-MONCHINH', 'Ca ri chay', 'Ca ri rau cu nuoc dua', NULL, 1, 1, 13, '2026-04-21 18:03:24'),
('MON-008', 'DM-MONCHINH', 'Dau hu sot ca', 'Dau hu chien sot ca chua', NULL, 1, 0, 14, '2026-04-21 18:03:24'),
('MON-009', 'DM-MONCHINH', 'Nam kho tieu', 'Nam dong co kho tieu dam da', NULL, 1, 1, 15, '2026-04-21 18:03:24'),
('MON-010', 'DM-MONCHINH', 'Thanh cua chay', 'Gia cua, vi nhe', NULL, 1, 0, 32, '2026-04-21 18:03:24'),
('MON-011', 'DM-TOPPING', 'Dau hu non', 'Mem min, tham nuoc lau', NULL, 1, 0, 20, '2026-04-21 18:03:24'),
('MON-012', 'DM-TOPPING', 'Tau hu ky', 'Dai mem, tham nuoc dung', NULL, 1, 1, 26, '2026-04-21 18:03:24'),
('MON-013', 'DM-TOPPING', 'Cha vien chay', 'Vien dai, vi dam da', NULL, 1, 1, 27, '2026-04-21 18:03:24'),
('MON-014', 'DM-RAU', 'Rau muong', 'Gion xanh', NULL, 1, 1, 30, '2026-04-21 18:03:24'),
('MON-015', 'DM-RAU', 'Cai thao', 'Ngot thanh', NULL, 1, 0, 31, '2026-04-21 18:03:24'),
('MON-016', 'DM-RAU', 'Nam kim cham', 'Gion ngot', NULL, 1, 1, 22, '2026-04-21 18:03:24'),
('MON-017', 'DM-NUOCLAU', 'Lau nam', 'Nuoc lau thanh ngot tu nam', NULL, 1, 1, 9, '2026-04-21 18:03:24'),
('MON-018', 'DM-NUOCLAU', 'Lau dashi', 'Thanh nhe', NULL, 1, 1, 40, '2026-04-21 18:03:24'),
('MON-019', 'DM-DOUONG', 'Nuoc cam', 'Tuoi mat', NULL, 1, 1, 50, '2026-04-21 18:03:24'),
('MON-020', 'DM-DOUONG', 'Tra tac', 'Chua ngot, thom mui tac', NULL, 1, 1, 53, '2026-04-21 18:03:24');

INSERT INTO `tai_khoan` (`id_tai_khoan`, `ten_dang_nhap`, `mat_khau`, `vai_tro`, `dang_hoat_dong`, `ho_ten`, `email`, `so_dien_thoai`, `ngay_tao`) VALUES
('TK-QL-001', 'quanly', '$2y$10$mYFxzdEx8VM1P2TBvXAc5.ez9b8L9zupQll/yqTZCXVjjHJpjcok.', 'quanly', 1, 'Quản trị viên', NULL, NULL, '2026-04-27 14:09:41'),
('TK-NV-001', 'nhanvien01', '$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', 'nhanvien', 1, 'Nguyễn Thanh Trúc', NULL, NULL, '2026-04-27 14:09:41'),
('TK-QL-002', 'quanly01', '$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', 'quanly', 1, 'Quản lý nhà hàng', NULL, NULL, '2026-05-14 00:00:00'),
('TK-BEP-001', 'nhanvien02', '$2y$10$iPMW0P.XD1BA3XBP4SxOwOKT7DUp1GRjaz5FK/meFGjO/X/4RL4y6', 'bep', 1, 'Nhân viên bếp', NULL, NULL, '2026-05-26 00:00:00');

INSERT INTO `khach_tai_khoan` (`id_khach_tai_khoan`, `ten_dang_nhap`, `mat_khau`, `vai_tro`, `dang_hoat_dong`, `ho_ten`, `email`, `so_dien_thoai`, `diem_tich_luy`, `ngay_tao`) VALUES
('KH-001', '0932396610', '$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'khach', 1, 'Le Hoang My', '', '0932396610', 0, '2026-04-27 15:51:20'),
('KH-002', '01871638136', '$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'khach', 1, 'Nguyen AA', '', '01871638136', 0, '2026-04-27 15:52:31'),
('KH-003', '0187193123', '$2y$10$.3jEZn7fLH96eyDrrx1eGOHW/61HnDyehRTrSkBQQxMJTxOkXsxDi', 'khach', 1, 'Nguyen AA', '', '0187193123', 0, '2026-04-27 15:57:39');

INSERT INTO `dat_ban` (`id_dat_ban`, `ma_dat_ban`, `id_khach_tai_khoan`, `ten_khach`, `sdt_khach`, `ngay_dat`, `gio_dat`, `so_nguoi_lon`, `so_tre_em`, `ghi_chu`, `trang_thai`, `created_at`) VALUES
('DB-20260430-52765', 'DB-20260430-52765', NULL, 'My', '0826893126', '2026-05-01', '10:00:00', 13, 0, '', 'expired', '2026-04-30 21:43:39'),
('DB-20260430-98422', 'DB-20260430-98422', NULL, 'Le Anh', '01827132', '2026-05-01', '10:00:00', 20, 0, '', 'da_xac_nhan', '2026-04-30 21:44:18'),
('DB-20260430-63833', 'DB-20260430-63833', NULL, 'Le Anh', '018271321', '2026-05-01', '11:30:00', 10, 0, '', 'da_huy', '2026-04-30 21:45:33'),
('DB-20260430-67602', 'DB-20260430-67602', NULL, 'My Mi', '01827132', '2026-05-01', '12:30:00', 2, 0, '', 'cho_xac_nhan', '2026-04-30 21:57:42'),
('DB-20260430-60104', 'DB-20260430-60104', NULL, 'My MII', '01827132', '2026-05-01', '15:30:00', 2, 0, '', 'cho_xac_nhan', '2026-04-30 21:58:02');

INSERT INTO `chitiet_datban` (`id_chitiet_datban`, `id_dat_ban`, `id_ban`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `trang_thai`) VALUES
('PBD-20260430-001', 'DB-20260430-98422', 'BAN-A1', '2026-05-01 10:00:00', '2026-05-01 11:30:00', 'dang_gan'),
('PBD-20260430-002', 'DB-20260430-98422', 'BAN-A3', '2026-05-01 10:00:00', '2026-05-01 11:30:00', 'dang_gan'),
('PBD-20260430-003', 'DB-20260430-98422', 'BAN-A4', '2026-05-01 10:00:00', '2026-05-01 11:30:00', 'dang_gan'),
('PBD-20260430-004', 'DB-20260430-98422', 'BAN-B4', '2026-05-01 10:00:00', '2026-05-01 11:30:00', 'dang_gan'),
('PBD-20260430-005', 'DB-20260430-67602', 'BAN-B1', '2026-05-01 12:30:00', '2026-05-01 14:00:00', 'dang_gan'),
('PBD-20260430-006', 'DB-20260430-60104', 'BAN-B1', '2026-05-01 15:30:00', '2026-05-01 17:00:00', 'dang_gan');

SET FOREIGN_KEY_CHECKS=1;
