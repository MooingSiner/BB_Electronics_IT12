<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Draws Code 128 (subset B) barcodes as inline SVG, so no barcode package is needed.
 */
class Code128
{
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_B = 104;

    private const STOP = 106;

    /**
     * The bar and space widths (in modules) for the text, starting with a bar.
     *
     * @return array<int, int>
     */
    public static function modules(string $text): array
    {
        if ($text === '' || preg_match('/^[\x20-\x7E]+$/', $text) !== 1) {
            throw new InvalidArgumentException('Code 128 subset B only supports printable ASCII text.');
        }

        $codes = [self::START_B];
        $checksum = self::START_B;

        foreach (str_split($text) as $position => $character) {
            $value = ord($character) - 32;
            $codes[] = $value;
            $checksum += $value * ($position + 1);
        }

        $codes[] = $checksum % 103;
        $codes[] = self::STOP;

        return array_map('intval', str_split(implode('', array_map(fn (int $code) => self::PATTERNS[$code], $codes))));
    }

    public static function svg(string $text, int $height = 60, int $moduleWidth = 2): string
    {
        $quietZone = 10;
        $x = $quietZone * $moduleWidth;
        $bars = '';

        foreach (self::modules($text) as $index => $width) {
            $barWidth = $width * $moduleWidth;

            if ($index % 2 === 0) {
                $bars .= '<rect x="'.$x.'" y="0" width="'.$barWidth.'" height="'.$height.'"/>';
            }

            $x += $barWidth;
        }

        $totalWidth = $x + $quietZone * $moduleWidth;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$totalWidth.' '.$height.'" width="'.$totalWidth.'" height="'.$height.'" role="img" aria-label="Barcode '.e($text).'" fill="#000" shape-rendering="crispEdges"><rect width="'.$totalWidth.'" height="'.$height.'" fill="#fff"/>'.$bars.'</svg>';
    }
}
