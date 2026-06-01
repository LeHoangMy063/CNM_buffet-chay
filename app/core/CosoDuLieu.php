<?php
// Thay the: Database.php
// Doi ten class: Database -> CosoDuLieu

class CosoDuLieu
{
    protected $conn;

    public function layKetNoi()
    {
        return $this->conn;
    }

    public function __construct()
    {
        $this->ketNoi();
    }

    private function ketNoi()
    {
        $host   = defined('DB_HOST') ? DB_HOST : "localhost";
        $user   = defined('DB_USER') ? DB_USER : "root";
        $pass   = defined('DB_PASS') ? DB_PASS : "";
        $dbname = defined('DB_NAME') ? DB_NAME : "buffet_chay";
        $charset = defined('DB_CHARSET') ? DB_CHARSET : "utf8";

        $this->conn = new mysqli($host, $user, $pass, $dbname);

        if ($this->conn->connect_error) {
            die("Ket noi CSDL that bai: " . $this->conn->connect_error);
        }

        $this->conn->set_charset($charset);
        $this->conn->query("SET time_zone = '+07:00'");
    }

    // Thuc thi truy van SQL voi tham so (PHP 5.3 safe - khong dung get_result)
    public function query($sql, $params = array())
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("Prepare that bai: " . $this->conn->error);
        }

        if (!empty($params)) {
            $kieu = $this->layKieu($params);

            $ten_bind = array($kieu);
            for ($i = 0; $i < count($params); $i++) {
                $ten = 'bind' . $i;
                $$ten = $params[$i];
                $ten_bind[] = &$$ten;
            }

            call_user_func_array(array($stmt, 'bind_param'), $ten_bind);
        }

        if (!$stmt->execute()) {
            die("Execute thất bại: " . $stmt->error . " SQL: " . $sql);
        }

        $meta = $stmt->result_metadata();

        // Khong co metadata -> la INSERT / UPDATE / DELETE
        if (!$meta) {
            $stmt->close();
            return true;
        }

        $fields     = array();
        $row        = array();
        $bind_ketqua = array();

        while ($field = $meta->fetch_field()) {
            $fields[] = $field->name;
            $bind_ketqua[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $bind_ketqua);

        $ket_qua = array();

        while ($stmt->fetch()) {
            $dong = array();
            foreach ($row as $khoa => $gia_tri) {
                $dong[$khoa] = $gia_tri;
            }
            $ket_qua[] = $dong;
        }

        $stmt->close();
        return $ket_qua;
    }

    // Tu dong xac dinh kieu tham so (i=int, d=double, s=string)
    private function layKieu($params)
    {
        $kieu = "";
        foreach ($params as $p) {
            if (is_int($p)) {
                $kieu .= "i";
            } elseif (is_double($p)) {
                $kieu .= "d";
            } else {
                $kieu .= "s";
            }
        }
        return $kieu;
    }

    // Lay ID vua insert
    public function lastInsertId()
    {
        return $this->conn->insert_id;
    }

    // Dong ket noi
    public function dongKetNoi()
    {
        $this->conn->close();
    }
}
