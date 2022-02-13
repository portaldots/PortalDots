<?php

declare(strict_types=1);

namespace App\Services\Utils;

class UIThemeService
{
    public static function getCurrentTheme(): string
    {
        // light, dark, system のどれか
        return 'system';
    }

    public static function getCssColorScheme(): string
    {
        if (static::getCurrentTheme() === 'light') {
            return 'light';
        } elseif (static::getCurrentTheme() === 'dark') {
            return 'dark';
        } else {
            return 'light dark';
        }
    }

    public static function getCssPrimaryColor(float $alpha = 1, bool $isDark = false): string
    {
        $hsl = config('portal.primary_color_hsl');

        if (!isset($hsl[0]) && !isset($hsl[1]) && !isset($hsl[2])) {
            $hsl = [214, 91, 53];
        }

        if ($isDark) {
            $s = max($hsl[1] - 10, 0);
            $l = min($hsl[2] + 20, 100);
            return "hsla({$hsl[0]}, {$s}%, {$l}%, {$alpha})";
        } else {
            return "hsla({$hsl[0]}, {$hsl[1]}%, {$hsl[2]}%, {$alpha})";
        }
    }
}
