<?php

declare(strict_types=1);

namespace App\Services\Utils;

use App;
use cebe\markdown\GithubMarkdown as Parser;
use HTMLPurifier;
use HTMLPurifier_Config;

class ParseMarkdownService
{
    /**
     * @var HTMLPurifier|null
     */
    private static $purifier;

    public static function render(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }
        $parser = App::make(Parser::class);
        $parser->enableNewlines = true;
        return self::purifier()->purify($parser->parse($markdown));
    }

    /**
     * Markdown 内に書かれた HTML から、script 要素、on* 属性、javascript: URL など
     * スクリプトを実行できる記述を取り除くための HTMLPurifier
     */
    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            // 定義キャッシュをファイルに書き出さない（storage の書込権限に依存させない）
            $config->set('Cache.DefinitionImpl', null);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
                'mailto' => true,
                'tel' => true,
            ]);
            self::$purifier = new HTMLPurifier($config);
        }
        return self::$purifier;
    }
}
