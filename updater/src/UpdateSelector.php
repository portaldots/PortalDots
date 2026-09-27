<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class UpdateSelector
{
    public function __construct(
        private readonly Config $config,
        private readonly Downloader $downloader,
        private readonly ManifestVerifier $verifier,
        private readonly ManifestHighwater $highwater,
    ) {
    }

    /**
     * 更新検出から候補を取得し、順に適用可能かを確認する。
     *
     * @return array<string, mixed>
     */
    public function select(string $currentVersion): array
    {
        $candidates = (new UpdateDiscovery($this->config, $this->downloader))->discoverCandidates($currentVersion);
        return $this->selectFrom($candidates, $currentVersion);
    }

    /**
     * 候補を [全体の最新版, 現在のメジャー内の最新版] の順に検証し、署名・更新元・PHP・
     * ランタイム・MySQLの各要件を満たす最初の候補を採用する。全候補が失敗した場合は、
     * 最も新しい候補（先頭）の例外を投げ直す。
     *
     * @param list<array<string, mixed>|callable(): array<string, mixed>> $candidates
     * @return array<string, mixed>
     */
    public function selectFrom(array $candidates, string $currentVersion): array
    {
        $firstFailure = null;
        foreach ($candidates as $candidate) {
            try {
                $envelope = is_callable($candidate) ? $candidate() : $candidate;
                $verified = $this->verifyCandidate($envelope, $currentVersion);
                $this->highwater->observe($verified);
                return $verified;
            } catch (RuntimeException $exception) {
                $firstFailure ??= $exception;
            }
        }
        throw $firstFailure ?? new RuntimeException('適用できる更新がありません。');
    }

    /** @param array<string, mixed> $envelope @return array<string, mixed> */
    private function verifyCandidate(array $envelope, string $currentVersion): array
    {
        $signed = is_array($envelope['signed'] ?? null) ? $envelope['signed'] : [];
        $targetMajor = is_string($signed['target_version'] ?? null)
            ? (int) explode('.', $signed['target_version'], 2)[0] : 0;
        $highest = $this->highwater->read($targetMajor);
        $verified = $this->verifier->verify(
            $envelope,
            $currentVersion,
            (int) ($highest['sequence'] ?? 0),
            isset($highest['digest']) ? (string) $highest['digest'] : null,
            (int) ($highest['lease_sequence'] ?? 0),
            isset($highest['lease_digest']) ? (string) $highest['lease_digest'] : null,
        );
        DatabaseConnection::assertMinimumVersion(
            DatabaseConnection::open($this->config),
            (string) $verified['signed']['minimum_mysql'],
        );
        return $verified;
    }
}
