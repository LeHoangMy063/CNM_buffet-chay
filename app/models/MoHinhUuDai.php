<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class MoHinhUuDai extends MoHinhCo
{
    public function layDanhSachMonDoiDiem()
    {
        if (!$this->coBangRewardItems()) {
            return $this->layMonDoiDiemMacDinh();
        }

        $rows = $this->db->query(
            "SELECT id_reward_item, ten_mon, mo_ta, diem_can_doi, hinh_anh, trang_thai, is_active
             FROM reward_items
             WHERE is_active = 1
             ORDER BY diem_can_doi ASC"
        );

        return !empty($rows) ? $rows : $this->layMonDoiDiemMacDinh();
    }

    public function layMonTheoId($id)
    {
        $id = trim((string)$id);
        if ($id === '') {
            return null;
        }

        if (!$this->coBangRewardItems()) {
            return $this->timMonMacDinh($id);
        }

        $rows = $this->db->query(
            "SELECT id_reward_item, ten_mon, mo_ta, diem_can_doi, hinh_anh, trang_thai, is_active
             FROM reward_items
             WHERE id_reward_item = ?
             LIMIT 1",
            array($id)
        );

        return !empty($rows) ? $rows[0] : null;
    }

    public function capNhatDiemKhach($idKhach, $diemMoi)
    {
        if (!$this->coBangKhachTaiKhoan()) {
            return false;
        }

        return $this->db->query(
            "UPDATE khach_tai_khoan SET diem_tich_luy = ? WHERE id_khach_tai_khoan = ?",
            array((int)$diemMoi, $idKhach)
        );
    }

    public function luuLichSuDoiDiem($idKhach, $idRewardItem, $diemDung, $trangThai = 'thanh_cong')
    {
        if (!$this->coBangRewardRedemptions()) {
            return false;
        }

        return $this->db->query(
            "INSERT INTO reward_redemptions
             (id_redemption, id_khach_tai_khoan, id_reward_item, so_diem_da_doi, trang_thai, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            array($this->taoId('RD', 6, true), $idKhach, $idRewardItem, (int)$diemDung, $trangThai)
        );
    }

    protected function coBangRewardItems()
    {
        $rows = $this->db->query("SHOW TABLES LIKE 'reward_items'");
        return !empty($rows);
    }

    protected function coBangRewardRedemptions()
    {
        $rows = $this->db->query("SHOW TABLES LIKE 'reward_redemptions'");
        return !empty($rows);
    }

    protected function coBangKhachTaiKhoan()
    {
        $rows = $this->db->query("SHOW TABLES LIKE 'khach_tai_khoan'");
        return !empty($rows);
    }

    protected function layMonDoiDiemMacDinh()
    {
        return array(
            array(
                'id_reward_item' => 'RD-001',
                'ten_mon' => 'Đậu hũ ngọc bích sốt nấm đông cô',
                'mo_ta' => 'Đậu hũ non mềm mịn dùng kèm sốt nấm đông cô thanh nhẹ, phù hợp làm món khai vị đặc biệt.',
                'diem_can_doi' => 30,
                'hinh_anh' => 'https://images.unsplash.com/photo-1543353071-873f17a7a088?w=800&q=80',
                'trang_thai' => 'available',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-002',
                'ten_mon' => 'Gỏi cuốn ngũ sắc An Lạc',
                'mo_ta' => 'Gỏi cuốn chay gồm rau củ tươi, bún, đậu hũ và nước chấm đặc biệt của nhà hàng.',
                'diem_can_doi' => 40,
                'hinh_anh' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=80',
                'trang_thai' => 'available',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-003',
                'ten_mon' => 'Nấm hoàng kim chiên giòn',
                'mo_ta' => 'Nấm được tẩm bột mỏng và chiên giòn, dùng kèm sốt chay thanh vị.',
                'diem_can_doi' => 50,
                'hinh_anh' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=80',
                'trang_thai' => 'available',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-004',
                'ten_mon' => 'Chả giò sen nấm đặc biệt',
                'mo_ta' => 'Chả giò chay nhân hạt sen, nấm và rau củ, chỉ áp dụng trong chương trình đổi điểm.',
                'diem_can_doi' => 60,
                'hinh_anh' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80',
                'trang_thai' => 'available',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-005',
                'ten_mon' => 'Lẩu nấm mini dưỡng sinh',
                'mo_ta' => 'Phần lẩu mini gồm nước dùng rau củ, nấm tươi và rau xanh, phù hợp cho khách muốn thưởng thức thêm món đặc biệt.',
                'diem_can_doi' => 70,
                'hinh_anh' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800&q=80',
                'trang_thai' => 'sold_out',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-006',
                'ten_mon' => 'Cơm sen An Lạc',
                'mo_ta' => 'Cơm chay hấp lá sen với hạt sen, nấm, rau củ và gia vị thanh nhẹ.',
                'diem_can_doi' => 90,
                'hinh_anh' => 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?w=800&q=80',
                'trang_thai' => 'available',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-007',
                'ten_mon' => 'Combo món chay đặc biệt',
                'mo_ta' => 'Combo gồm 2 đến 3 món chay đặc biệt do nhà hàng chọn theo ngày.',
                'diem_can_doi' => 100,
                'hinh_anh' => 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?w=800&q=80',
                'trang_thai' => 'available',
                'is_active' => 1,
            ),
            array(
                'id_reward_item' => 'RD-008',
                'ten_mon' => 'Set tráng miệng thanh mát',
                'mo_ta' => 'Set gồm chè dưỡng nhan, trái cây tươi hoặc món tráng miệng chay theo mùa.',
                'diem_can_doi' => 120,
                'hinh_anh' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800&q=80',
                'trang_thai' => 'coming_soon',
                'is_active' => 1,
            ),
        );
    }

    protected function timMonMacDinh($id)
    {
        $danhSach = $this->layMonDoiDiemMacDinh();
        foreach ($danhSach as $mon) {
            if ($mon['id_reward_item'] === $id) {
                return $mon;
            }
        }
        return null;
    }
}
