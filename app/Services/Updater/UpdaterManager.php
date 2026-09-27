<?php

declare(strict_types=1);

namespace App\Services\Updater;

use App\Eloquents\User;
use PortalDots\Updater\Config;
use PortalDots\Updater\Downloader;
use PortalDots\Updater\FileBackup;
use PortalDots\Updater\JobFactory;
use PortalDots\Updater\ManifestHighwater;
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
        $highwater = new ManifestHighwater($this->config);
        $highest = $highwater->read();
        $verified = $verifier->verify(
            $envelope,
            $current,
            (int) ($highest['sequence'] ?? 0),
            isset($highest['digest']) ? (string) $highest['digest'] : null,
            (int) ($highest['lease_sequence'] ?? 0),
            isset($highest['lease_digest']) ? (string) $highest['lease_digest'] : null,
        );
        $highwater->observe($verified);
        return $verified;
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
}
