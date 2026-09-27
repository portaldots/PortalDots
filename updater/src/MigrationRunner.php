<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use Illuminate\Contracts\Console\Kernel;
use PDO;
use RuntimeException;

final class MigrationRunner
{
    private ?PDO $guard = null;

    public function __construct(private readonly Config $config)
    {
    }

    /** @param list<array<string, mixed>> $declared */
    public function assertPlan(array $declared): void
    {
        $app = $this->application();
        $migrator = $app->make('migrator');
        $files = $migrator->getMigrationFiles($this->config->basePath . '/database/migrations');
        $ran = $migrator->getRepository()->getRan();
        $pending = array_values(array_diff(array_keys($files), $ran));
        sort($pending, SORT_STRING);
        $expected = array_map(
            static fn (array $migration): string => pathinfo($migration['path'], PATHINFO_FILENAME),
            $declared,
        );
        sort($expected, SORT_STRING);
        if ($pending !== $expected) {
            throw new RuntimeException('未適用DB移行と署名済み移行一覧が一致しません。');
        }
    }

    /** @param array<string, mixed> $migration */
    public function run(array $migration, string $jobId): void
    {
        $path = (string) $migration['path'];
        $absolute = $this->config->basePath . '/' . $path;
        $hash = is_file($absolute) ? hash_file('sha256', $absolute) : false;
        if (!is_string($hash) || !hash_equals((string) $migration['sha256'], $hash)) {
            throw new RuntimeException("DB移行ファイルが署名済み内容と一致しません: {$path}");
        }
        $app = $this->application();
        $this->acquireGuard($app, $jobId);
        try {
            $kernel = $app->make(Kernel::class);
            $status = $kernel->call('migrate', [
                '--path' => $path,
                '--force' => true,
            ]);
            if ($status !== 0) {
                throw new RuntimeException("DB移行に失敗しました: {$path}");
            }
            $pdo = DatabaseConnection::open($this->config);
            foreach ($migration['verify'] as $sql) {
                $value = $pdo->query((string) $sql)->fetchColumn();
                if (!in_array($value, [1, '1', true], true)) {
                    throw new RuntimeException("DB移行の事後検証に失敗しました: {$path}");
                }
            }
        } finally {
            $this->releaseGuard($jobId);
        }
    }

    public function waitForPreviousProcess(string $jobId): bool
    {
        $pdo = DatabaseConnection::open($this->config);
        $name = $this->guardName($jobId);
        $statement = $pdo->prepare('SELECT GET_LOCK(?, 0)');
        $statement->execute([$name]);
        if ((int) $statement->fetchColumn() !== 1) {
            return false;
        }
        $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute([$name]);
        return true;
    }

    private function acquireGuard(mixed $app, string $jobId): void
    {
        $connection = $app->make('db')->connection();
        $connection->setReconnector(static function (): never {
            throw new RuntimeException('DB移行中の再接続を拒否しました。自動復元が必要です。');
        });
        $this->guard = $connection->getPdo();
        $statement = $this->guard->prepare('SELECT GET_LOCK(?, 0)');
        $statement->execute([$this->guardName($jobId)]);
        if ((int) $statement->fetchColumn() !== 1) {
            throw new RuntimeException('以前のDB移行プロセスが終了していません。');
        }
    }

    private function releaseGuard(string $jobId): void
    {
        if ($this->guard === null) {
            return;
        }
        $statement = $this->guard->prepare('SELECT RELEASE_LOCK(?)');
        $statement->execute([$this->guardName($jobId)]);
        $this->guard = null;
    }

    private function guardName(string $jobId): string
    {
        return 'portaldots-updater-' . substr($jobId, 0, 32);
    }

    private function application(): mixed
    {
        require_once $this->config->basePath . '/vendor/autoload.php';
        $app = require $this->config->basePath . '/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }
}
