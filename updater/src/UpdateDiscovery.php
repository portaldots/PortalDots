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

    /**
     * 適用候補のマニフェストを [全体の最新版, (異なる場合)現在のメジャー内の最新版] の順で返す。
     * 直接署名済みマニフェストが返された場合は候補1件のみ。
     * 各候補は呼び出した時点で取得する。代替候補の取得失敗で最新版まで使えなくならないようにするため。
     *
     * @return list<array<string, mixed>|callable(): array<string, mixed>>
     */
    public function discoverCandidates(string $currentVersion): array
    {
        $separator = str_contains($this->config->manifestUrl, '?') ? '&' : '?';
        $response = $this->downloader->json($this->config->manifestUrl . $separator . http_build_query([
            'current_version' => $currentVersion,
            'runtime' => Config::RUNTIME_VERSION,
        ]));
        if (isset($response['signed'], $response['signatures'])) {
            return [$response];
        }
        $urls = self::selectCandidateUrls($response, $currentVersion);
        return array_map(fn (string $url): callable => fn (): array => $this->downloader->json($url), $urls);
    }

    /**
     * GitHub Releases一覧から適用候補を選び、URLを [全体の最新版, (異なる場合)現在のメジャー内の
     * 最新版] の順で返す。ネットワークI/Oを伴わない純粋なロジックとして切り出してある。
     *
     * @param array<mixed> $response
     * @return list<string>
     */
    public static function selectCandidateUrls(array $response, string $currentVersion): array
    {
        if (!array_is_list($response)) {
            throw new RuntimeException('更新検出応答が署名マニフェストでもGitHub Releases一覧でもありません。');
        }
        $currentMajor = explode('.', $currentVersion, 2)[0];
        $crossMajorAllowed = (int) $currentMajor >= Config::CROSS_MAJOR_MINIMUM_MAJOR;

        $overall = [];
        $sameMajor = [];
        foreach ($response as $release) {
            if (!is_array($release) || ($release['draft'] ?? true) || ($release['prerelease'] ?? true)) {
                continue;
            }
            $tag = ltrim((string) ($release['tag_name'] ?? ''), 'v');
            if (!preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $tag)
                || version_compare($tag, $currentVersion, '<=')) {
                continue;
            }
            $releaseMajor = explode('.', $tag, 2)[0];
            $isSameMajor = $releaseMajor === $currentMajor;
            if (!$isSameMajor && !$crossMajorAllowed) {
                continue;
            }
            $url = null;
            foreach (($release['assets'] ?? []) as $asset) {
                if (is_array($asset) && ($asset['name'] ?? null) === 'PortalDots-update-manifest.json'
                    && is_string($asset['browser_download_url'] ?? null)) {
                    $url = $asset['browser_download_url'];
                }
            }
            if ($url === null) {
                continue;
            }
            $overall[$tag] = $url;
            if ($isSameMajor) {
                $sameMajor[$tag] = $url;
            }
        }
        if ($overall === []) {
            throw new RuntimeException('適用できる正式更新はありません。');
        }
        uksort($overall, 'version_compare');
        $newestTag = array_key_last($overall);
        $urls = [$overall[$newestTag]];
        if ($sameMajor !== [] && !array_key_exists($newestTag, $sameMajor)) {
            uksort($sameMajor, 'version_compare');
            $urls[] = $sameMajor[array_key_last($sameMajor)];
        }
        return $urls;
    }
}
