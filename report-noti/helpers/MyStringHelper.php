<?php

namespace app\helpers;

use Yii;

class MyStringHelper
{
    /**
     * Convert a string with separators to integer.
     *
     * @param string $string Input string with separators (e.g., "1.000.000")
     * @return int Converted integer value
     */
    public static function convertStringToInteger($string = '')
    {
        if ($string !== null && $string !== '') {
            return (int)str_replace('.', '', (string)$string);
        }
        return 0;
    }

    /**
     * Convert integer to price string with separators.
     *
     * @param int|string $int Input integer value
     * @param string $symbol Separator symbol (default '.')
     * @return string Formatted price string
     */
    public static function convertIntegerToPrice($int = '', $symbol = '.')
    {
        $int = (int)$int;
        if ($int === 0) return '0';
        return number_format($int, 0, '', $symbol);
    }
}