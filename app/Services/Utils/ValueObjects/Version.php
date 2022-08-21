<?php

declare(strict_types=1);

namespace App\Services\Utils\ValueObjects;

final class Version
{
    /**
     * セマンティックバージョニングにマッチする正規表現
     *
     * @see https://semver.org/lang/ja/
     */
    // phpcs:ignore Generic.Files.LineLength.TooLong
    public const SEMVER_REGEX = '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    /**
     * メジャーバージョン
     *
     * @var int
     */
    private $major;

    /**
     * マイナーバージョン
     *
     * @var int
     */
    private $minor;

    /**
     * パッチバージョン
     *
     * @var int
     */
    private $patch;

    /**
     * プレリリースバージョン
     *
     * @var string
     */
    private $prerelease;

    public function __construct(
        int $major,
        int $minor,
        int $patch,
        string $prerelease = ''
    ) {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->prerelease = $prerelease;
    }

    /**
     * バージョン文字列からバージョンオブジェクトを生成する
     *
     * @return self|null
     */
    public static function parse(string $version_string): ?self
    {
        // 1文字目に v がついていれば削除
        $version_string = preg_replace('/^v/', '', $version_string);
        preg_match(self::SEMVER_REGEX, $version_string, $matches);
        if (
            !isset($matches['major']) ||
            !isset($matches['minor']) ||
            !isset($matches['patch'])
        ) {
            return null;
        }
        return new self(
            (int) $matches['major'],
            (int) $matches['minor'],
            (int) $matches['patch'],
            $matches['prerelease'] ?? ''
        );
    }

    public function equals(Version $version_info): bool
    {
        return $this->getMajor() === $version_info->getMajor() &&
            $this->getMinor() === $version_info->getMinor() &&
            $this->getPatch() === $version_info->getPatch() &&
            $this->getPrerelease() === $version_info->getPrerelease();
    }

    public function getFullVersion()
    {
        if ($this->getPrerelease() === '') {
            return sprintf(
                '%d.%d.%d',
                $this->getMajor(),
                $this->getMinor(),
                $this->getPatch()
            );
        }
        return sprintf(
            '%d.%d.%d-%s',
            $this->getMajor(),
            $this->getMinor(),
            $this->getPatch(),
            $this->getPrerelease()
        );
    }

    public function getMajor()
    {
        return $this->major;
    }

    public function getMinor()
    {
        return $this->minor;
    }

    public function getPatch()
    {
        return $this->patch;
    }

    public function getPrerelease()
    {
        return $this->prerelease;
    }
}
