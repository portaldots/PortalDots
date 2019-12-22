<?php

declare(strict_types=1);

namespace App\Services\Markdown;

use App;
use cebe\markdown\GithubMarkdown as Parser;

class ParseMarkdownService
{
    public static function render(string $markdown): string
    {
        $parser = App::make(Parser::class);
        $parser->enableNewlines = true;
        return $parser->parse($markdown);
    }
}
