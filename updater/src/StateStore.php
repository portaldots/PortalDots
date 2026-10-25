<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class StateStore
{
    public function __construct(private readonly Config $config)
    {
        PrivateStorage::ensure($this->config->privatePath);
        $this->ensureDirectory($this->jobsPath());
    }

    /** @param array<string, mixed> $state */
    public function create(array $state): void
    {
        $id = (string) ($state['id'] ?? '');
        if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
            throw new RuntimeException('更新IDが不正です。');
        }
        if ($this->activeId() !== null) {
            throw new RuntimeException('別の更新処理が進行中です。');
        }

        $this->ensureDirectory($this->jobPath($id));
        $this->writeJson($this->statePath($id), $state, false);
        $this->writeJson($this->config->privatePath . '/current.json', ['id' => $id], true);
        $this->appendAudit($id, 'created', [
            'actor_id' => $state['actor']['id'] ?? null,
            'from_version' => $state['from_version'] ?? null,
        ]);
    }

    public function activeId(): ?string
    {
        $pointer = $this->readJson($this->config->privatePath . '/current.json', false);
        $id = $pointer['id'] ?? null;
        return is_string($id) && preg_match('/^[a-f0-9]{32}$/', $id) ? $id : null;
    }

    /** @return array<string, mixed> */
    public function load(string $id): array
    {
        $state = $this->readJson($this->statePath($id), true);
        if (($state['id'] ?? null) !== $id) {
            throw new RuntimeException('更新状態ファイルが一致しません。');
        }
        return $state;
    }

    /** @param array<string, mixed> $state */
    public function save(array $state): void
    {
        $id = (string) ($state['id'] ?? '');
        $state['updated_at'] = gmdate(DATE_ATOM);
        $this->writeJson($this->statePath($id), $state, true);
    }

    public function finish(string $id): void
    {
        $active = $this->activeId();
        if ($active === $id) {
            $this->writeJson($this->config->privatePath . '/last.json', ['id' => $id], true);
            if (is_file($this->config->privatePath . '/current.json')
                && !unlink($this->config->privatePath . '/current.json')) {
                throw new RuntimeException('更新中状態を終了できません。');
            }
        }
    }

    public function jobPath(string $id, string $suffix = ''): string
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
            throw new RuntimeException('更新IDが不正です。');
        }
        $path = $this->jobsPath() . '/' . $id;
        return $suffix === '' ? $path : $path . '/' . ltrim($suffix, '/');
    }

    /**
     * state.json に載せない大きな一度書きデータをジョブディレクトリ内の別ファイルに保存する。
     *
     * @param array<string, mixed> $value
     */
    public function writeJobData(string $id, string $name, array $value): void
    {
        $this->assertJobDataName($name);
        $this->writeJson($this->jobPath($id, $name), $value, false);
    }

    /** @return array<string, mixed> */
    public function readJobData(string $id, string $name): array
    {
        $this->assertJobDataName($name);
        return $this->readJson($this->jobPath($id, $name), true);
    }

    private function assertJobDataName(string $name): void
    {
        if (!preg_match('/^[a-z-]+\.json$/', $name)) {
            throw new RuntimeException('更新ジョブデータ名が不正です。');
        }
    }

    /** @return resource */
    public function lock(string $id)
    {
        $handle = fopen($this->jobPath($id, 'job.lock'), 'c+');
        if ($handle === false) {
            throw new RuntimeException('更新ロックを開けません。');
        }
        @chmod($this->jobPath($id, 'job.lock'), 0600);
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            throw new RuntimeException('更新処理が別の要求で実行中です。');
        }
        return $handle;
    }

    /** @param array<string, mixed> $context */
    public function appendAudit(string $id, string $event, array $context = []): void
    {
        $record = CanonicalJson::encode([
            'at' => gmdate(DATE_ATOM),
            'event' => $event,
            'job_id' => $id,
            'context' => $context,
        ]) . "\n";
        $path = $this->config->privatePath . '/audit.jsonl';
        $handle = fopen($path, 'ab');
        if ($handle === false) {
            throw new RuntimeException('更新監査ログを書き込めません。');
        }
        @chmod($path, 0600);
        if (flock($handle, LOCK_EX)) {
            fwrite($handle, $record);
            fflush($handle);
            flock($handle, LOCK_UN);
        }
        fclose($handle);
    }

    /** @return array<string, mixed> */
    private function readJson(string $path, bool $required): array
    {
        if (!is_file($path)) {
            if ($required) {
                throw new RuntimeException('更新状態ファイルがありません。');
            }
            return [];
        }
        $contents = file_get_contents($path);
        if (!is_string($contents)) {
            throw new RuntimeException('更新状態ファイルを読み取れません。');
        }
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('更新状態ファイルが不正です。');
        }
        return $decoded;
    }

    /** @param array<string, mixed> $value */
    private function writeJson(string $path, array $value, bool $replace): void
    {
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(6));
        // state.json やジョブデータは署名・ハッシュ対象ではないため、CanonicalJson の
        // 再帰的なコピーとksortを避けて素の json_encode で書き出す（メモリ削減）。
        $contents = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR) . "\n";
        $handle = fopen($temporary, 'xb');
        if ($handle === false || ($replace && !flock($handle, LOCK_EX))) {
            throw new RuntimeException('更新状態を書き込めません。');
        }
        $offset = 0;
        while ($offset < strlen($contents)) {
            $written = fwrite($handle, substr($contents, $offset));
            if ($written === false || $written === 0) {
                fclose($handle);
                @unlink($temporary);
                throw new RuntimeException('更新状態を完全に書き込めません。');
            }
            $offset += $written;
        }
        if (!fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
            fclose($handle);
            @unlink($temporary);
            throw new RuntimeException('更新状態を永続化できません。');
        }
        if ($replace) {
            flock($handle, LOCK_UN);
        }
        fclose($handle);
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('更新状態を確定できません。');
        }
    }

    private function statePath(string $id): string
    {
        return $this->jobPath($id, 'state.json');
    }

    private function jobsPath(): string
    {
        return $this->config->privatePath . '/jobs';
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0700, true) && !is_dir($path)) {
            throw new RuntimeException('更新用の非公開ディレクトリを作成できません。');
        }
        @chmod($path, 0700);
    }
}
