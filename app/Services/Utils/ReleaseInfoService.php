<?php

declare(strict_types=1);

namespace App\Services\Utils;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as Cache;
use App\ReleaseInfo;
use App\Services\Utils\ValueObjects\Version;
use App\Services\Utils\ValueObjects\Release;
use Carbon\CarbonImmutable;
use GuzzleHttp\Exception\ClientException;

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
     * @return Version|null
     */
    public function getCurrentVersion(): ?Version
    {
        if (ReleaseInfo::VERSION === '###VERSION_PLACEHOLDER###') {
            return null;
        }
        return Version::parse(ReleaseInfo::VERSION);
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

        if (empty($current_version_info)) {
            return null;
        }

        try {
            return $this->cache->remember(
                'getReleaseOfLatestVersionWithinSameMajorVersion/' . $current_version_info->getFullVersion(),
                120,
                function () use ($current_version_info) {
                    $path = sprintf(
                        'https://releases.portaldots.com/releases/latest.json?major_version=%d',
                        $current_version_info->getMajor(),
                    );
                    $release = json_decode((string) $this->client->get($path)->getBody());

                    if (!isset($release->version)) {
                        return null;
                    }

                    return new Release(
                        Version::parse($release->version),
                        new CarbonImmutable($release->published_at),
                        $release->html_url,
                        $release->browser_download_url,
                        $release->size,
                        $release->body
                    );
                }
            );
        } catch (ClientException $e) {
            return null;
        }
    }
}
