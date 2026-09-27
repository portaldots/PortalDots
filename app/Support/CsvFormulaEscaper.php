<?php

namespace App\Support;

final class CsvFormulaEscaper
{
    public static function escape(?string $value): ?string
    {
        return $value !== null && self::needsEscape($value) ? "'" . $value : $value;
    }

    public static function unescape(string $value): string
    {
        if (str_starts_with($value, "'") && self::needsEscape(substr($value, 1))) {
            return substr($value, 1);
        }

        return $value;
    }

    private static function needsEscape(string $value): bool
    {
        // Also protect whitespace/control prefixes and full-width formula markers.
        // Escape literal apostrophes so removing one protection character is reversible.
        return preg_match('/\A[\x00-\x20\x27=+\-@＝＋－＠]/u', $value) === 1;
    }
}
