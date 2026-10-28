<?php

namespace Tests\Feature\Services\Utils;

use App\Services\Utils\ParseMarkdownService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParseMarkdownServiceTest extends TestCase
{
    public static function 危険なHTML(): array
    {
        return [
            'imgのonerror' => ['<img src=x onerror=alert(document.domain)>', ['onerror', 'alert(']],
            'script要素' => ['<script>alert(1)</script>', ['<script', 'alert(']],
            'javascriptスキームのリンク' => ['[click](javascript:alert(1))', ['javascript:']],
            'aタグのjavascript href' => ['<a href="javascript:alert(1)">x</a>', ['javascript:']],
            'iframe' => ['<iframe src="https://example.com"></iframe>', ['<iframe']],
            'svgのonload' => ['<svg onload=alert(1)></svg>', ['onload', 'alert(']],
        ];
    }

    #[Test]
    #[DataProvider('危険なHTML')]
    public function render_スクリプトを実行できるHTMLを出力しない(string $markdown, array $forbidden)
    {
        $html = ParseMarkdownService::render($markdown);

        foreach ($forbidden as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $html);
        }
    }

    #[Test]
    public function render_通常のMarkdownの装飾は残る()
    {
        $html = ParseMarkdownService::render(
            "# 見出し\n\n**太字** [リンク](https://example.com) ![画像](/img.png)\n\n| a | b |\n|---|---|\n| 1 | 2 |"
        );

        $this->assertStringContainsString('<h1>見出し</h1>', $html);
        $this->assertStringContainsString('<strong>太字</strong>', $html);
        $this->assertStringContainsString('<a href="https://example.com">リンク</a>', $html);
        $this->assertStringContainsString('<img src="/img.png" alt="画像"', $html);
        $this->assertStringContainsString('<table>', $html);
    }
}
