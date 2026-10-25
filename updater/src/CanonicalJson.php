<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class CanonicalJson
{
    public static function encode(mixed $value): string
    {
        $normalized = self::normalize($value);
        $json = json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
        if (!is_string($json)) {
            throw new RuntimeException('JSONの正規化に失敗しました。');
        }

        return $json;
    }

    private static function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                return array_map(self::normalize(...), $value);
            }
            ksort($value, SORT_STRING);
            foreach ($value as $key => $item) {
                $value[$key] = self::normalize($item);
            }
            return $value;
        }
        if (is_object($value)) {
            return self::normalize((array) $value);
        }
        if (is_float($value) && (!is_finite($value) || floor($value) === $value)) {
            throw new RuntimeException('署名対象JSONに非正規の数値があります。');
        }
        return $value;
    }
}
