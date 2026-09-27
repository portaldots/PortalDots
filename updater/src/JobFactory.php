<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class JobFactory
{
    public function __construct(
        private readonly Config $config,
        private readonly StateStore $store,
    ) {
    }

    /**
     * @param array{id: int|string|null, email: string|null} $actor
     * @return array{job_id: string, recovery_code: string}
     */
    public function create(array $actor, ?array $verifiedManifest = null): array
    {
        if ($this->store->activeId() !== null) {
            throw new RuntimeException('別の更新処理が進行中です。');
        }
        $id = bin2hex(random_bytes(16));
        $rawCode = strtoupper(bin2hex(random_bytes(16)));
        $recoveryCode = implode('-', str_split($rawCode, 8));
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        // signed.files は数万件になりうるため state.json には載せず files.json へ分離する。
        $files = null;
        $slimManifest = $verifiedManifest;
        if ($verifiedManifest !== null) {
            $files = $verifiedManifest['signed']['files'];
            unset($slimManifest['signed']['files']);
        }
        $state = [
            'schema' => 1,
            'id' => $id,
            'phase' => 'updating',
            'current_step' => $verifiedManifest === null ? 'fetch_manifest' : 'download_package',
            'step_status' => 'pending',
            'from_version' => VersionReader::current($this->config->basePath),
            'target_version' => $verifiedManifest['signed']['target_version'] ?? null,
            'manifest_url' => $this->config->manifestUrl,
            'manifest' => $slimManifest,
            'artifact' => [],
            'download' => [],
            'extract' => [],
            'database_backup' => [],
            'file_backup' => [],
            'file_apply' => [],
            'migration' => ['index' => 0, 'plan_checked' => false, 'attempted' => false],
            'file_plan' => null,
            'destructive_started' => false,
            'maintenance_started' => false,
            'started_at' => gmdate(DATE_ATOM),
            'updated_at' => gmdate(DATE_ATOM),
            'actor' => $actor,
            'recovery_hash' => password_hash($recoveryCode, $algorithm),
            'auth_failures' => 0,
            'auth_locked_until' => null,
            'sessions' => [],
            'history' => [],
            'last_error' => null,
        ];
        $this->store->create($state);
        if ($files !== null) {
            $this->store->writeJobData($id, 'files.json', ['files' => $files]);
        }
        return ['job_id' => $id, 'recovery_code' => $recoveryCode];
    }
}
