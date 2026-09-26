<?php

namespace Tests\Feature\Services\Utils;

use App\Services\Utils\ReleaseInfoService;
use App\Services\Utils\ValueObjects\Version;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class ReleaseInfoServiceTest extends TestCase
{
    private string $cachePath;
    private Repository $cache;

    public function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . '/portaldots-release-cache-' . bin2hex(random_bytes(8));
        config(['cache.stores.release_info_test' => [
            'driver' => 'file',
            'path' => $this->cachePath,
        ]]);
        $this->cache = app('cache')->store('release_info_test');
    }

    public function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->cachePath);

        parent::tearDown();
    }

    public function test_release_information_can_be_restored_from_file_cache(): void
    {
        $handler = new MockHandler([new Response(200, [], json_encode([
            'version' => '6.0.1',
            'published_at' => '2026-09-01T12:34:56+09:00',
            'html_url' => 'https://example.test/releases/6.0.1',
            'browser_download_url' => 'https://example.test/releases/6.0.1.zip',
            'size' => 1024,
            'body' => 'リリース内容',
        ]))]);
        $client = new Client(['handler' => HandlerStack::create($handler)]);
        $service = new class ($client, $this->cache) extends ReleaseInfoService {
            public function getCurrentVersion(): ?Version
            {
                return new Version(6, 0, 0);
            }
        };

        $first = $service->getReleaseOfLatestVersionWithinSameMajorVersion();
        $cached = $service->getReleaseOfLatestVersionWithinSameMajorVersion();

        $this->assertNotSame($first, $cached);
        $this->assertSame('6.0.1', $cached->getVersion()->getFullVersion());
        $this->assertSame('2026-09-01T12:34:56+09:00', $cached->getPublishedAt()->toIso8601String());
        $this->assertSame('https://example.test/releases/6.0.1', $cached->getHtmlUrl());
        $this->assertSame('https://example.test/releases/6.0.1.zip', $cached->getBrowserDownloadUrl());
        $this->assertSame(1024, $cached->getSize());
        $this->assertSame('リリース内容', $cached->getBody());
        $this->assertCount(0, $handler);
    }

    public function test_unlisted_classes_are_not_restored_from_file_cache(): void
    {
        $this->cache->put('unlisted-object', (object) ['value' => 'test'], 120);

        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $this->cache->get('unlisted-object'));
    }
}
