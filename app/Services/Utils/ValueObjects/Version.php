<?php

declare(strict_types=1);

namespace App\Services\Utils\ValueObjects;

final class Version
{
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

    public function __construct(int $major, int $minor, int $patch)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
    }

    public function equals(Version $version_info): bool
    {
        return $this->getMajor() === $version_info->getMajor() &&
            $this->getMinor() === $version_info->getMinor() &&
            $this->getPatch() === $version_info->getPatch();
    }

    public function getFullVersion()
    {
        return sprintf('%d.%d.%d', $this->getMajor(), $this->getMinor(), $this->getPatch());
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
}
