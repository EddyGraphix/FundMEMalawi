<?php

namespace App\Services;

class MoneyService
{
    public static function format(float $amount, string $currency = 'MWK'): string
    {
        if ($currency === 'MWK') {
            return 'MK '.number_format((int) $amount, 0);
        }

        return $currency.' '.number_format($amount, 2);
    }

    public static function calculateFee(float $amount, float $percentage = 5.0): float
    {
        return floor($amount * $percentage / 100 * 100) / 100;
    }
}
