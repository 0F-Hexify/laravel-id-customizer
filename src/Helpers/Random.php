<?php

namespace Hexify\LaraIdCustomizer\Helpers;

class Random
{
    const ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const NUMERIC = '0123456789';
    const ALPHA_NUMERIC = self::ALPHA . self::NUMERIC;

    public static function generate(int $length, string $set = self::ALPHA_NUMERIC, string $extra = '')
    {
        $set .= $extra;
        $_length = strlen($set);
        $str = '';
        for ($i=0; $i < $length; $i++) {
            $str .= str_shuffle($set)[mt_rand(0,$_length - 1)];
        }
        return $str;
    }
}
