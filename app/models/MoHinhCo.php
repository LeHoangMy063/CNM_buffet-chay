<?php
class MoHinhCo
{
    protected $db;

    public function __construct()
    {
        require_once dirname(__FILE__) . '/../core/CosoDuLieu.php';
        $this->db = new CosoDuLieu();
    }
}
