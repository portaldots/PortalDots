<?php

declare(strict_types=1);

namespace App\Services\Utils\ValueObjects;

use Carbon\CarbonImmutable;

final class Release
{
    /**
     * バージョン
     *
     * @var Version
     */
    private $version;

    /**
     * リリース公開日時
     *
     * @var CarbonImmutable
     */
    private $publishedAt;

    /**
     * リリースノートのURL
     *
     * @var string
     */
    private $htmlUrl;

    /**
     * リリースのダウンロードURL(ZIP)
     *
     * @var string
     */
    private $browserDownloadUrl;

    /**
     * ダウンロード ZIP のサイズ(単位 : バイト)
     *
     * @var int
     */
    private $size;

    /**
     * リリースノートテキスト(Markdownテキスト)
     *
     * @var string
     */
    private $body;

    public function __construct(
        Version $version,
        CarbonImmutable $publishedAt,
        string $htmlUrl,
        string $browserDownloadUrl,
        int $size,
        string $body
    ) {
        $this->version = $version;
        $this->publishedAt = $publishedAt;
        $this->htmlUrl = $htmlUrl;
        $this->browserDownloadUrl = $browserDownloadUrl;
        $this->size = $size;
        $this->body = $body;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getPublishedAt(): CarbonImmutable
    {
        return $this->publishedAt;
    }

    public function getHtmlUrl(): string
    {
        return $this->htmlUrl;
    }

    public function getBrowserDownloadUrl(): string
    {
        return $this->browserDownloadUrl;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
