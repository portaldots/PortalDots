<?php

declare(strict_types=1);

namespace App\Services\Utils;

use App;
use cebe\markdown\GithubMarkdown as Parser;

class ParseMarkdownService
{
    public static function render(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }
        $parser = App::make(Parser::class);
        $parser->enableNewlines = true;
        return $parser->parse($markdown);
    }
}
