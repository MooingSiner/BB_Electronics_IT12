<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * How many rows a list table shows per page: 10 on phones, 15 everywhere else.
 */
class PerPage
{
    public const DESKTOP = 15;

    public const MOBILE = 10;

    public static function rows(?Request $request = null): int
    {
        $request ??= request();

        return self::isMobile((string) $request->userAgent()) ? self::MOBILE : self::DESKTOP;
    }

    public static function isMobile(string $userAgent): bool
    {
        return preg_match('/Mobi|iPhone|iPod|Android.*Mobile|Windows Phone|BlackBerry|Opera Mini/i', $userAgent) === 1;
    }
}
