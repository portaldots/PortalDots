<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class Engine
{
    private const STEPS = [
        'fetch_manifest', 'download_package', 'inspect_package', 'extract_package', 'preflight',
        'enter_maintenance', 'wait_for_drain', 'backup_database', 'backup_files', 'apply_files',
        'migrate_database', 'health_check', 'finalize',
    ];

    private Downloader $downloader;
    private ManifestVerifier $verifier;
    private TrustedKeyStore $keys;
    private ZipPackage $zip;
    private DatabaseBackup $database;
    private FileBackup $files;
    private Preflight $preflight;
    private MigrationRunner $migrations;
    private HealthChecker $health;

    public function __construct(
        private readonly Config $config,
        private readonly StateStore $store,
    ) {
        $this->keys = new TrustedKeyStore($config, $store);
        $this->verifier = new ManifestVerifier($config, $this->keys);
        $this->downloader = new Downloader($config, $this->verifier);
        $this->zip = new ZipPackage($config);
        $this->database = new DatabaseBackup($config);
        $this->files = new FileBackup($config);
        $this->preflight = new Preflight($config);
        $this->migrations = new MigrationRunner($config);
        $this->health = new HealthChecker($config);
    }

    /** @return array<string, mixed> */
    public function step(string $id): array
    {
        $lock = $this->store->lock($id);
        try {
            $state = $this->store->load($id);
            if (in_array($state['phase'], ['completed', 'rolled_back', 'failed'], true)) {
                return $state;
            }
            if ($state['phase'] === 'restoring') {
                return $this->restore($state);
            }
            if ($state['step_status'] === 'running' && $state['current_step'] === 'migrate_database') {
                $state = $this->beginRestore(
                    $state,
                    'DB移行の完了状態を確認できないため、自動復元します。'
                );
                $this->store->save($state);
                return $this->restore($state);
            }

            $state['step_status'] = 'running';
            $lastHistory = $state['history'][array_key_last($state['history'])] ?? null;
            if (!is_array($lastHistory) || ($lastHistory['step'] ?? null) !== $state['current_step']
                || isset($lastHistory['completed_at'])) {
                $state['history'][] = [
                    'step' => $state['current_step'],
                    'started_at' => gmdate(DATE_ATOM),
                ];
                $state['history'] = array_slice($state['history'], -64);
            }
            $this->store->save($state);
            try {
                $complete = $this->execute($state);
                if ($complete) {
                    $state['history'][array_key_last($state['history'])]['completed_at'] = gmdate(DATE_ATOM);
                    $this->advance($state);
                } else {
                    $state['step_status'] = 'pending';
                }
                $this->store->save($state);
                return $state;
            } catch (\Throwable $exception) {
                return $this->handleFailure($state, $exception);
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @param array<string, mixed> $state */
    private function execute(array &$state): bool
    {
        $jobPath = $this->store->jobPath($state['id']);
        $signed = $state['manifest']['signed'] ?? [];
        switch ($state['current_step']) {
            case 'fetch_manifest':
                $envelope = (new UpdateDiscovery($this->config, $this->downloader))
                    ->discover($state['from_version']);
                $highest = $this->readHighest();
                $verified = $this->verifier->verify(
                    $envelope,
                    $state['from_version'],
                    (int) ($highest['sequence'] ?? 0),
                    isset($highest['digest']) ? (string) $highest['digest'] : null,
                );
                $state['manifest'] = $verified;
                $state['target_version'] = $verified['signed']['target_version'];
                $this->writeHighest([
                    'sequence' => $verified['signed']['sequence'],
                    'digest' => $verified['digest'],
                    'target_version' => $verified['signed']['target_version'],
                ]);
                $this->keys->applyRotation($verified['signed'], $verified['signing_key_id']);
                return true;

            case 'download_package':
                $artifact = $signed['artifact'];
                $archive = $jobPath . '/update.zip';
                $complete = $this->downloader->downloadChunk(
                    $artifact['url'],
                    $archive,
                    $artifact['size'],
                    $state['download'],
                );
                if ($complete) {
                    $hash = hash_file('sha256', $archive);
                    if (!is_string($hash) || !hash_equals($artifact['sha256'], $hash)) {
                        throw new RuntimeException('更新ZIPのSHA-256が一致しません。');
                    }
                }
                return $complete;

            case 'inspect_package':
                $this->zip->inspect($jobPath . '/update.zip', $signed['files']);
                return true;

            case 'extract_package':
                return $this->zip->extractUntil(
                    $jobPath . '/update.zip',
                    $jobPath . '/staging',
                    $signed['files'],
                    $state['extract'],
                    microtime(true) + 4.0,
                );

            case 'preflight':
                $installed = $this->files->installedManifest($state['from_version']);
                $this->files->verifyInstallation($installed);
                $state['file_plan'] = $this->files->plan(
                    $installed,
                    $signed['files'],
                    $state['target_version'],
                    $signed['sequence'],
                );
                $this->preflight->run($signed, $state['file_plan']);
                return true;

            case 'enter_maintenance':
                UpdateGate::beginMaintenance($this->config, $state['id']);
                $state['maintenance_started'] = true;
                $state['drain_started_at'] = gmdate(DATE_ATOM);
                $this->store->appendAudit($state['id'], 'maintenance_started');
                return true;

            case 'wait_for_drain':
                if (UpdateGate::waitForDrain($this->config)) {
                    $state['drained_at'] = gmdate(DATE_ATOM);
                    return true;
                }
                if (time() - strtotime((string) $state['drain_started_at']) > $this->config->drainTimeoutSeconds) {
                    throw new RuntimeException('実行中のWeb・キュー・スケジューラ・CLI処理が終了しませんでした。');
                }
                return false;

            case 'backup_database':
                return $this->database->backupStep(
                    $state['database_backup'],
                    $jobPath,
                    $state['id'],
                );

            case 'backup_files':
                $deadline = microtime(true) + 4.0;
                do {
                    $complete = $this->files->backupStep(
                        $state['file_plan'],
                        $state['file_backup'],
                        $jobPath,
                    );
                } while (!$complete && microtime(true) < $deadline);
                return $complete;

            case 'apply_files':
                if (!$state['destructive_started']) {
                    $state['destructive_started'] = true;
                    $this->store->save($state);
                }
                CacheCleaner::clear($this->config->basePath);
                $deadline = microtime(true) + 4.0;
                do {
                    $complete = $this->files->applyStep(
                        $state['file_plan'],
                        $state['file_apply'],
                        $jobPath . '/staging',
                    );
                } while (!$complete && microtime(true) < $deadline);
                return $complete;

            case 'migrate_database':
                $applicableMigrations = array_values(array_filter(
                    $signed['migrations'],
                    static fn (array $migration): bool => in_array(
                        $state['from_version'],
                        $migration['from_versions'],
                        true,
                    ),
                ));
                if (!$state['migration']['plan_checked']) {
                    $this->migrations->assertPlan($applicableMigrations);
                    $state['migration']['plan_checked'] = true;
                }
                $index = (int) $state['migration']['index'];
                if ($index >= count($applicableMigrations)) {
                    return true;
                }
                $state['migration']['attempted'] = true;
                $this->store->save($state);
                $this->migrations->run($applicableMigrations[$index], $state['id']);
                $state['migration']['index'] = $index + 1;
                return $state['migration']['index'] >= count($applicableMigrations);

            case 'health_check':
                $this->health->check($state['target_version']);
                return true;

            case 'finalize':
                if (!($state['release_intent'] ?? false)) {
                    $state['release_intent'] = true;
                    $this->store->save($state);
                }
                UpdateGate::endMaintenance($this->config);
                $state['maintenance_started'] = false;
                $state['phase'] = 'completed';
                $state['completed_at'] = gmdate(DATE_ATOM);
                $state['recovery_hash'] = null;
                $state['sessions'] = [];
                $this->store->save($state);
                $this->store->finish($state['id']);
                try {
                    $this->store->appendAudit($state['id'], 'completed', [
                        'target_version' => $state['target_version'],
                    ]);
                } catch (\Throwable) {
                    // Completion is already durable and traffic is open. Audit failure must not trigger rollback.
                }
                return false;
        }
        throw new RuntimeException('更新ステップが不正です。');
    }

    /** @param array<string, mixed> $state @return array<string, mixed> */
    private function handleFailure(array $state, \Throwable $exception): array
    {
        $safeError = $this->safeFailure($exception);
        $state['last_error'] = $safeError;
        $this->store->appendAudit($state['id'], 'step_failed', [
            'step' => $state['current_step'],
            'error' => $safeError,
            'exception' => get_class($exception),
        ]);
        if (!$state['maintenance_started']) {
            $state['phase'] = 'failed';
            $state['step_status'] = 'failed';
            $state['recovery_hash'] = null;
            $state['sessions'] = [];
            $this->store->save($state);
            $this->store->finish($state['id']);
            return $state;
        }
        $state = $this->beginRestore($state, $safeError);
        $this->store->save($state);
        return $state;
    }

    /** @param array<string, mixed> $state @return array<string, mixed> */
    private function restore(array $state): array
    {
        $state['restore']['paused'] = false;
        $state['step_status'] = 'running';
        $this->store->save($state);
        try {
            $complete = $this->executeRestore($state);
            $state['restore']['last_error'] = null;
            $state['step_status'] = 'pending';
            if ($complete) {
                $state['phase'] = 'rolled_back';
                $state['rolled_back_at'] = gmdate(DATE_ATOM);
                $state['recovery_hash'] = null;
                $state['sessions'] = [];
                $this->store->appendAudit($state['id'], 'rolled_back', ['error' => $state['last_error']]);
                $this->store->save($state);
                $this->store->finish($state['id']);
                return $state;
            }
            $this->store->save($state);
            return $state;
        } catch (\Throwable $exception) {
            $safeError = $this->safeFailure($exception);
            $state['restore']['attempts'] = (int) ($state['restore']['attempts'] ?? 0) + 1;
            $state['restore']['last_error'] = $safeError;
            $state['restore']['paused'] = true;
            $state['step_status'] = 'pending';
            $this->store->appendAudit($state['id'], 'restore_step_failed', [
                'step' => $state['restore']['step'],
                'error' => $safeError,
                'exception' => get_class($exception),
            ]);
            $this->store->save($state);
            return $state;
        }
    }

    /** @param array<string, mixed> $state */
    private function executeRestore(array &$state): bool
    {
        $restore =& $state['restore'];
        $jobPath = $this->store->jobPath($state['id']);
        switch ($restore['step']) {
            case 'wait_migration_exit':
                if ($state['migration']['attempted'] && !$this->migrations->waitForPreviousProcess($state['id'])) {
                    return false;
                }
                $restore['step'] = 'restore_files';
                return false;
            case 'restore_files':
                if ($state['destructive_started']) {
                    $deadline = microtime(true) + 4.0;
                    do {
                        $complete = $this->files->restoreStep(
                            $state['file_plan'],
                            $restore['files'],
                            $jobPath,
                        );
                    } while (!$complete && microtime(true) < $deadline);
                    if (!$complete) {
                        return false;
                    }
                }
                CacheCleaner::clear($this->config->basePath);
                $restore['step'] = 'restore_database';
                return false;
            case 'restore_database':
                if ($state['migration']['attempted']) {
                    if (($state['database_backup']['stage'] ?? null) !== 'complete') {
                        throw new RuntimeException('完全なDBバックアップがないため自動復元できません。');
                    }
                    if (!$this->database->restoreStep(
                        $state['database_backup'],
                        $restore['database'],
                        $jobPath,
                    )) {
                        return false;
                    }
                }
                $restore['step'] = 'cleanup_proof';
                return false;
            case 'cleanup_proof':
                if ($state['database_backup'] !== []
                    && !$this->database->cleanupProofStep($state['database_backup'], $restore['proof_cleanup'])) {
                    return false;
                }
                $restore['step'] = 'verify';
                return false;
            case 'verify':
                $this->files->verifyInstallation($state['file_plan']['old_manifest']);
                $this->health->check($state['from_version']);
                $restore['step'] = 'reopen';
                return false;
            case 'reopen':
                if (!($restore['release_intent'] ?? false)) {
                    $restore['release_intent'] = true;
                    $this->store->save($state);
                }
                UpdateGate::endMaintenance($this->config);
                $state['maintenance_started'] = false;
                $restore['step'] = 'complete';
                return true;
            case 'complete':
                return true;
        }
        throw new RuntimeException('復元ステップが不正です。');
    }

    /** @param array<string, mixed> $state */
    private function advance(array &$state): void
    {
        $index = array_search($state['current_step'], self::STEPS, true);
        if ($index === false || !isset(self::STEPS[$index + 1])) {
            return;
        }
        $state['current_step'] = self::STEPS[$index + 1];
        $state['step_status'] = 'pending';
    }

    /** @param array<string, mixed> $state @return array<string, mixed> */
    private function beginRestore(array $state, string $error): array
    {
        $state['last_error'] = $error;
        $state['phase'] = 'restoring';
        $state['step_status'] = 'pending';
        $state['restore'] ??= [
            'step' => 'wait_migration_exit',
            'proof_cleanup' => [],
            'files' => [],
            'database' => [],
            'attempts' => 0,
        ];
        return $state;
    }

    private function safeFailure(\Throwable $exception): string
    {
        return get_class($exception) === RuntimeException::class
            ? $exception->getMessage()
            : '内部処理に失敗しました。バックアップを保持したまま安全に停止しました。';
    }

    /** @return array<string, mixed> */
    private function readHighest(): array
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        if (!is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string, mixed> $value */
    private function writeHighest(array $value): void
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $handle = fopen($temporary, 'xb');
        $contents = CanonicalJson::encode($value) . "\n";
        if ($handle === false || fwrite($handle, $contents) !== strlen($contents)
            || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
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
