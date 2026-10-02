<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use Illuminate\Contracts\Console\Kernel;
use RuntimeException;

final class HealthChecker
{
    public function __construct(private readonly Config $config)
    {
    }

    public function check(string $expectedVersion): void
    {
        CacheCleaner::clear($this->config->basePath);
        if (VersionReader::current($this->config->basePath) !== $expectedVersion) {
            throw new RuntimeException('配置後のPortalDotsバージョンが更新先と一致しません。');
        }
        require_once $this->config->basePath . '/vendor/autoload.php';
        $app = require $this->config->basePath . '/bootstrap/app.php';
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();
        if ((int) $app->make('db')->connection()->selectOne('SELECT 1 AS healthy')->healthy !== 1) {
            throw new RuntimeException('更新後のDB接続確認に失敗しました。');
        }
        if ($kernel->call('migrate:status') !== 0) {
            throw new RuntimeException('更新後のDB移行状態を確認できません。');
        }
        $migrator = $app->make('migrator');
        $files = $migrator->getMigrationFiles($this->config->basePath . '/database/migrations');
        $pending = array_diff(array_keys($files), $migrator->getRepository()->getRan());
        if ($pending !== []) {
            throw new RuntimeException('更新後に未適用のDB移行が残っています。');
        }
    }
}
