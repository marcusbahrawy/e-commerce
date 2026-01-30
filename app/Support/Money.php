<?php

declare(strict_types=1);

namespace App\Support;

final class Money
{
    /**
     * Format øre as NOK string (e.g. 19900 -> "199,00").
     */
    public static function format(int $ore, string $currency = 'NOK'): string
    {
        $whole = (int) floor($ore / 100);
        $frac = $ore % 100;
        return number_format($whole, 0, '', ' ') . ',' . str_pad((string) $frac, 2, '0', STR_PAD_LEFT) . ' ' . $currency;
    }
}
