<?php

class MatKhau
{
    public static function maHoa($matKhau)
    {
        return password_hash($matKhau, PASSWORD_DEFAULT);
    }

    public static function hopLe($matKhau, $hashDaLuu)
    {
        $hashDaLuu = (string)$hashDaLuu;

        if (self::laMd5Cu($hashDaLuu)) {
            return md5($matKhau) === $hashDaLuu;
        }

        return password_verify($matKhau, $hashDaLuu);
    }

    public static function canNangCap($hashDaLuu)
    {
        $hashDaLuu = (string)$hashDaLuu;
        return self::laMd5Cu($hashDaLuu) || password_needs_rehash($hashDaLuu, PASSWORD_DEFAULT);
    }

    private static function laMd5Cu($hashDaLuu)
    {
        return preg_match('/^[a-f0-9]{32}$/i', (string)$hashDaLuu) === 1;
    }
}
