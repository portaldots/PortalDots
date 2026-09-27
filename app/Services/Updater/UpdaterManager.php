<?php

declare(strict_types=1);

namespace App\Services\Updater;

use App\Eloquents\User;
use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\Config;
use PortalDots\Updater\Downloader;
use PortalDots\Updater\FileBackup;
use PortalDots\Updater\JobFactory;
use PortalDots\Updater\ManifestVerifier;
use PortalDots\Updater\StateStore;
use PortalDots\Updater\TrustedKeyStore;
use PortalDots\Updater\UpdateDiscovery;
use PortalDots\Updater\VersionReader;
use RuntimeException;

final class UpdaterManager
{
    private Config $config;
    private StateStore $store;

    public function __construct()
    {
        require_once base_path('updater/bootstrap.php');
        $this->config = Config::fromEnvironment(base_path());
        $this->store = new StateStore($this->config);
    }

    /** @return array<string, mixed> */
    public function overview(): array
    {
        try {
            $version = VersionReader::current(base_path());
            $files = new FileBackup($this->config);
            $installed = $files->installedManifest($version);
            $files->verifyInstallation($installed);
            $keys = (new TrustedKeyStore($this->config, $this->store))->all(1);
            return [
                'enabled' => $keys !== [],
                'current_version' => $version,
                'active_job_id' => $this->store->activeId(),
                'diagnostic' => $keys === [] ? '更新署名公開鍵が未設定です。手動更新で鍵を導入してください。' : null,
            ];
        } catch (\Throwable $exception) {
            return [
                'enabled' => false,
                'current_version' => null,
                'active_job_id' => $this->store->activeId(),
                'diagnostic' => $this->safeMessage($exception),
            ];
        }
    }

    /** @return array<string, mixed> */
    public function check(): array
    {
        $current = VersionReader::current(base_path());
        $keys = new TrustedKeyStore($this->config, $this->store);
        $verifier = new ManifestVerifier($this->config, $keys);
        $downloader = new Downloader($this->config, $verifier);
        $envelope = (new UpdateDiscovery($this->config, $downloader))->discover($current);
        $highest = $this->readHighest();
        return $verifier->verify(
            $envelope,
            $current,
            (int) ($highest['sequence'] ?? 0),
            isset($highest['digest']) ? (string) $highest['digest'] : null,
        );
    }

    /** @return array{job_id: string, recovery_code: string, target_version: string} */
    public function start(User $actor, string $expectedDigest): array
    {
        if (!$actor->is_admin || config('portal.enable_demo_mode')) {
            throw new RuntimeException('実在する管理者だけが更新を開始できます。');
        }
        $lock = fopen($this->config->privatePath . '/start.lock', 'c+');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            throw new RuntimeException('別の管理者が更新開始を処理中です。');
        }
        try {
            $verified = $this->check();
            if (
                !preg_match('/^[a-f0-9]{64}$/', $expectedDigest)
                || !hash_equals($expectedDigest, $verified['digest'])
            ) {
                throw new RuntimeException('確認後に更新マニフェストが変更されました。もう一度更新を確認してください。');
            }
            $keys = new TrustedKeyStore($this->config, $this->store);
            $keys->applyRotation($verified['signed'], $verified['signing_key_id']);
            $this->writeHighest([
                'sequence' => $verified['signed']['sequence'],
                'digest' => $verified['digest'],
                'target_version' => $verified['signed']['target_version'],
            ]);
            $result = (new JobFactory($this->config, $this->store))->create([
                'id' => $actor->getKey(),
                'email' => $actor->email,
            ], $verified);
            $result['target_version'] = $verified['signed']['target_version'];
            return $result;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function safeMessage(\Throwable $exception): string
    {
        return get_class($exception) === RuntimeException::class
            ? $exception->getMessage() : '更新処理の内部診断に失敗しました。';
    }

    /** @return array<string, mixed> */
    private function readHighest(): array
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        if (!is_file($path)) {
            return [];
        }
        $value = json_decode((string) file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
        return is_array($value) ? $value : [];
    }

    /** @param array<string, mixed> $value */
    private function writeHighest(array $value): void
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $contents = CanonicalJson::encode($value) . "\n";
        $handle = fopen($temporary, 'xb');
        if (
            $handle === false || fwrite($handle, $contents) !== strlen($contents)
            || !fflush($handle) || (function_exists('fsync') && !fsync($handle))
        ) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($temporary);
            throw new RuntimeException('更新連番を保存できません。');
        }
        fclose($handle);
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('更新連番を確定できません。');
        }
    }
}
