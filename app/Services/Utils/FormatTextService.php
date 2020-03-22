<?php

declare(strict_types=1);

namespace App\Services\Utils;

use DateTime;

class FormatTextService
{
    private const DAYS = [ '日', '月', '火', '水', '木', '金', '土' ];
    private const DAYS_FULL = [ '日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日' ];

    /**
     * 100文字で要約されたテキストを生成する
     *
     * このメソッドでやっていることは以下の通り
     *
     * 1. $text を Markdown として解釈し HTML に変換する
     * 2. 1. で生成されたテキストから HTML を取り除くことで、元々 HTML タグだったテキストだけでなく Markdown の画像構文などを除去する
     * 3. 2. で生成されたテキストから先頭100 文字のみ残し、残りを「...」で省略する
     */
    public static function summary(?string $text): string
    {
        return empty($text) ? ''
            : mb_strimwidth(strip_tags(ParseMarkdownService::render($text)), 0, 100, '...');
    }

    /**
     * Y年n月d日(曜日) H:i 形式の日付文字列を作成する
     *
     * @param  string $datetime     PHPにおいて日付として認識される文字列。
     * @return string               整形された日付文字列。
     */
    public static function datetime(string $datetime): string
    {
        $format = 'Y年n月j日';
        $date = (new DateTime($datetime))->format($format);
        $dayId = (int)(new DateTime($datetime))->format('w');
        $day = self::getDayByDayId($dayId);
        $time = (new DateTime($datetime))->format('H:i');
        return "{$date}({$day}) {$time}";
    }

    /**
     * 曜日番号から曜日文字列を取得する
     */
    public static function getDayByDayId(int $dayId, bool $full = false): string
    {
        if ($full) {
            return self::DAYS_FULL[$dayId];
        }
        return self::DAYS[$dayId];
    }
}
