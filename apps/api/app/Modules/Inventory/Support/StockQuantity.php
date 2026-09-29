<?php

namespace App\Modules\Inventory\Support;

use Illuminate\Validation\ValidationException;

final class StockQuantity
{
    public static function milli(string|int|float $value): int
    {
        $value = (string) $value;
        if (! preg_match('/^(-?)(\d{1,9})(?:\.(\d{1,3}))?$/', $value, $matches)) {
            throw ValidationException::withMessages(['quantity' => ['Use at most 9 integer and 3 fractional digits.']]);
        }
        $fraction = str_pad($matches[3] ?? '', 3, '0');
        $milli = ((int) $matches[2] * 1000) + (int) $fraction;

        return ($matches[1] ?? '') === '-' ? -$milli : $milli;
    }

    public static function format(int $milli): string
    {
        $sign = $milli < 0 ? '-' : '';
        $absolute = abs($milli);

        return $sign.intdiv($absolute, 1000).'.'.str_pad((string) ($absolute % 1000), 3, '0', STR_PAD_LEFT);
    }
}
