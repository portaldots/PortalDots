<?php

declare(strict_types=1);

namespace App\Services\Utils;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as Cache;
use App\ReleaseInfo;
use App\Services\Utils\ValueObjects\Version;
use App\Services\Utils\ValueObjects\Release;
use Carbon\CarbonImmutable;

class ReleaseInfoService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Client $client
     * @param Cache  $cache
     */
    public function __construct(Client $client, Cache $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * この PortalDots のバージョン情報を配列で取得
     *
     * @return Version
     */
    public function getCurrentVersion(): Version
    {
        if (ReleaseInfo::VERSION === '###VERSION_PLACEHOLDER###') {
            return $this->version('1.0.0');
        }
        return $this->version(ReleaseInfo::VERSION);
    }

    /**
     * この PortalDots と同じメジャーバージョン内のリリース情報を取得
     *
     * 例えば、v3.2.3 と v1.2.3 がリリースされている場合で、この PortalDots の
     * バージョンが v1.2.1 の場合、このメソッドが返すバージョンは v1.2.3
     *
     * @return Version|null
     */
    public function getReleaseOfLatestVersionWithinSameMajorVersion(): ?Release
    {
        $current_version_info = $this->getCurrentVersion();

        return $this->cache->remember(
            'getReleaseOfLatestVersionWithinSameMajorVersion/' . $current_version_info->getFullVersion(),
            120,
            function () use ($current_version_info) {
                $found_latest_version = false;
                $result = null;

                // APIから5ページ分取得
                for ($page = 1; $page <= 5; $page++) {
                    $path = sprintf(
                        'https://api.github.com/repos/portal-dots/PortalDots/releases?per_page=%d&page=%d',
                        10,
                        $page
                    );
                    $releases = json_decode((string) $this->client->get($path)->getBody());

                    foreach ($releases as $release) {
                        $version_info = $this->version($release->tag_name);

                        if (empty($version_info)) {
                            continue;
                        }

                        if ($version_info->getMajor() === $current_version_info->getMajor() && !$release->prerelease) {
                            $found_latest_version = true;
                            $result = new Release(
                                $version_info,
                                new CarbonImmutable($release->published_at),
                                $release->html_url,
                                $release->assets[0]->browser_download_url,
                                $release->assets[0]->size,
                                $release->body
                            );
                            break;
                        }
                    }

                    if ($found_latest_version) {
                        break;
                    }
                }

                return $result;
            }
        );
    }

    /**
     * バージョン文字列からバージョン情報配列を取得
     *
     * @return Version|null
     */
    public function version(string $version_string): ?Version
    {
        preg_match('/(\d+)\.(\d+)\.(\d+)/', $version_string, $matches);
        if (!isset($matches[1]) || !isset($matches[2]) || !isset($matches[3])) {
            return null;
        }
        return new Version((int)$matches[1], (int)$matches[2], (int)$matches[3]);
    }
}
