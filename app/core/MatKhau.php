<?php

class MatKhau
{
    public static function maHoa($matKhau)
    {
        return password_hash($matKhau, PASSWORD_DEFAULT);
    }

    public static function hopLe($matKhau, $hashDaLuu)
    {
        return password_verify($matKhau, (string)$hashDaLuu);
    }

    public static function canNangCap($hashDaLuu)
    {
        return password_needs_rehash((string)$hashDaLuu, PASSWORD_DEFAULT);
    }
}
