<?php
class SinhMa
{
    public static function tao($prefix)
    {
        return $prefix . '-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function ban($soBan)
    {
        return 'BAN-' . strtoupper(trim($soBan));
    }

    public static function mon($stt)
    {
        return 'MON-' . str_pad((int)$stt, 3, '0', STR_PAD_LEFT);
    }
}
