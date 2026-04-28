<?php

require_once dirname(__FILE__) . '/MoHinhCo.php';

class CustomerModel extends BaseModel
{
    // Lay danh sach khach hang
    public function getAll()
    {
        $sql = "SELECT * FROM customers";
        return $this->db->query($sql);
    }

    // Them khach hang
    public function create($ten, $sdt)
    {
        $sql = "INSERT INTO customers (name, phone) VALUES (?, ?)";
        return $this->db->query($sql, array($ten, $sdt));
    }
}
