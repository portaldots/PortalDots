<?php

class MY_Model extends CI_Model
{

    const DAYS = [ '日', '月', '火', '水', '木', '金', '土' ];

  /**
   * コンストラクタ
   */
    public function __construct()
    {
        parent::__construct();
    }

  /**
   * n月d日(曜日) H:i 形式の日付文字列を作成する
   * @param  string $datetime     PHPにおいて日付として認識される文字列。
   * @param  bool   $include_year 年を含めるか
   * @return string               整形された日付文字列。
   */
    protected function arrange_datetime($datetime, $include_year = false)
    {
        $format = 'n月j日';
        if ($include_year === true) {
            $format = "Y年{$format}";
        }
        $date = (new DateTime($datetime))->format($format);
        $day_id = (new DateTime($datetime))->format('w');
        $day = self::DAYS[ $day_id ];
        $time = (new DateTime($datetime))->format('H:i');
        return "{$date}({$day}) {$time}";
    }

  /**
   * Markdown テキストを HTML に変換する
   * @param  string $markdown Markdown テキスト
   * @return string           与えられた Markdown テキストから生成された HTML
   */
    protected function parse_markdown($markdown)
    {
        $parser = new \cebe\markdown\GithubMarkdown();
        $parser->enableNewlines = true;
        return $parser->parse($markdown);
    }

  /**
   * 指定された日付は，現在から3日以内のものか？
   * @param  string  $datetime 調べたい日付
   * @return bool              3日以内なら true
   */
    protected function is_new($datetime)
    {
        $now_datetime = new DateTime();
        $future_datetime = (new DateTime($datetime))->modify('+3 days');
        if ($future_datetime > $now_datetime) {
            return true;
        }
        return false;
    }

  /**
   * HTML を HTML Purifier により無害化する
   *
   * @param  string $dirty_html 無害化したい HTML
   * @return string             無害化された HTML
   */
    protected function purify($dirty_html)
    {
      // HTML Purifier の設定用クラス
        $config = HTMLPurifier_Config::createDefault();
      // target="_blank" を許可
        $config->set('HTML.TargetBlank', true);
      // インラインスタイルを許可
        $config->set('HTML.AllowedAttributes', '*.style');

        $purifier = new HTMLPurifier($config);
        return $purifier->purify($dirty_html);
    }
}
