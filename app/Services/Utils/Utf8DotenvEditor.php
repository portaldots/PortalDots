<?php

declare(strict_types=1);

namespace App\Services\Utils;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Jackiedo\DotenvEditor\DotenvEditor;
use Jackiedo\DotenvEditor\DotenvReader;
use Jackiedo\DotenvEditor\DotenvWriter;
use Jackiedo\DotenvEditor\Workers\Formatters\Formatter;

class Utf8DotenvEditor extends DotenvEditor
{
    public function __construct(Container $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->reader = new DotenvReader(new Utf8ParserV3());
        $this->writer = new DotenvWriter(new Formatter());

        $this->configBackuping();
        $this->load();
    }
}
