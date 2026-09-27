<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class UpdateDiscovery
{
    public function __construct(
        private readonly Config $config,
        private readonly Downloader $downloader,
    ) {
    }

    /** @return array<string, mixed> */
    public function discover(string $currentVersion): array
    {
        $separator = str_contains($this->config->manifestUrl, '?') ? '&' : '?';
        $response = $this->downloader->json($this->config->manifestUrl . $separator . http_build_query([
            'current_version' => $currentVersion,
            'runtime' => Config::RUNTIME_VERSION,
        ]));
        if (isset($response['signed'], $response['signatures'])) {
            return $response;
        }
        if (!array_is_list($response)) {
            throw new RuntimeException('更新検出応答が署名マニフェストでもGitHub Releases一覧でもありません。');
        }
        $major = explode('.', $currentVersion, 2)[0];
        $candidates = [];
        foreach ($response as $release) {
            if (!is_array($release) || ($release['draft'] ?? true) || ($release['prerelease'] ?? true)) {
                continue;
            }
            $tag = ltrim((string) ($release['tag_name'] ?? ''), 'v');
            if (!preg_match('/^' . preg_quote($major, '/') . '\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $tag)
                || version_compare($tag, $currentVersion, '<=')) {
                continue;
            }
            foreach (($release['assets'] ?? []) as $asset) {
                if (is_array($asset) && ($asset['name'] ?? null) === 'PortalDots-update-manifest.json'
                    && is_string($asset['browser_download_url'] ?? null)) {
                    $candidates[$tag] = $asset['browser_download_url'];
                }
            }
        }
        if ($candidates === []) {
            throw new RuntimeException('現在のメジャーバージョンに適用できる正式更新はありません。');
        }
        uksort($candidates, 'version_compare');
        $url = end($candidates);
        if (!is_string($url)) {
            throw new RuntimeException('更新マニフェストの取得先を決定できません。');
        }
        return $this->downloader->json($url);
    }
}
