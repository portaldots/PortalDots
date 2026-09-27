<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class ReleaseCandidateSelector
{
    /**
     * @param list<mixed> $releases
     * @param callable(string): array<string, mixed> $fetchManifest
     * @return list<array<string, mixed>>
     */
    public function select(array $releases, string $targetVersion, string $rootPublicKey, callable $fetchManifest): array
    {
        if (!$this->isStable($targetVersion)) {
            throw new RuntimeException('Target release version is invalid.');
        }
        $major = explode('.', $targetVersion, 2)[0];
        $candidates = [];
        foreach ($this->flattenPages($releases) as $release) {
            if (!is_array($release) || !is_string($release['tag_name'] ?? null)
                || !is_bool($release['draft'] ?? null) || !is_bool($release['prerelease'] ?? null)
                || !is_array($release['assets'] ?? null) || !array_is_list($release['assets'])) {
                throw new RuntimeException('GitHub Releases response contains a malformed release.');
            }
            if ($release['draft'] || $release['prerelease']) {
                continue;
            }
            $tag = (string) ($release['tag_name'] ?? '');
            $version = str_starts_with($tag, 'v') ? substr($tag, 1) : '';
            if (!$this->isStable($version) || explode('.', $version, 2)[0] !== $major
                || version_compare($version, $targetVersion, '>=')) {
                continue;
            }
            $assets = $release['assets'];
            $manifestUrl = $this->assetUrl($assets, 'PortalDots-update-manifest.json');
            if ($manifestUrl === null) {
                continue;
            }
            $fullUrl = $this->assetUrl($assets, 'PortalDots.zip');
            if ($fullUrl === null) {
                throw new RuntimeException("Release {$tag} has updater metadata but no full release ZIP.");
            }
            try {
                $verified = ReleaseMetadata::verifyRoot($fetchManifest($manifestUrl), $rootPublicKey);
            } catch (\Throwable $exception) {
                throw new RuntimeException("Release {$tag} updater metadata cannot be trusted: "
                    . $exception->getMessage(), 0, $exception);
            }
            $signed = $verified['signed'];
            if (!hash_equals($version, (string) $signed['target_version'])
                || !hash_equals($fullUrl, (string) $signed['full_artifact']['url'])) {
                throw new RuntimeException("Release {$tag} updater metadata does not match its release assets.");
            }
            $updateName = basename((string) parse_url((string) $signed['artifact']['url'], PHP_URL_PATH));
            $updateUrl = $this->assetUrl($assets, $updateName);
            if ($updateUrl === null || !hash_equals($updateUrl, (string) $signed['artifact']['url'])) {
                throw new RuntimeException("Release {$tag} updater ZIP is missing or mismatched.");
            }
            $candidates[$version] = [
                'version' => $version,
                'manifest_url' => $manifestUrl,
                'root_digest' => $verified['digest'],
                'full_artifact' => $signed['full_artifact'],
            ];
        }
        uksort($candidates, 'version_compare');
        return array_values($candidates);
    }

    /** @param list<mixed> $input @return list<mixed> */
    private function flattenPages(array $input): array
    {
        if ($input === [] || (is_array($input[0] ?? null) && !array_is_list($input[0]))) {
            return $input;
        }
        $result = [];
        foreach ($input as $page) {
            if (!is_array($page) || !array_is_list($page)) {
                throw new RuntimeException('GitHub Releases pagination response is malformed.');
            }
            array_push($result, ...$page);
        }
        return $result;
    }

    /** @param list<mixed> $assets */
    private function assetUrl(array $assets, string $name): ?string
    {
        foreach ($assets as $asset) {
            if (!is_array($asset) || !is_string($asset['name'] ?? null)) {
                throw new RuntimeException('GitHub Release contains a malformed asset.');
            }
            if ($asset['name'] === $name) {
                if (!is_string($asset['browser_download_url'] ?? null)
                    || $asset['browser_download_url'] === '') {
                    throw new RuntimeException("GitHub Release asset {$name} has no download URL.");
                }
                return $asset['browser_download_url'];
            }
        }
        return null;
    }

    private function isStable(string $version): bool
    {
        return (bool) preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $version);
    }
}
