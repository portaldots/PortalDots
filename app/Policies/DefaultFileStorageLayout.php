<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\FileStorageLayout;

/**
 * OSS版における FileStorageLayout の初期実装。領域名をそのままディレクトリ名として使う。
 */
class DefaultFileStorageLayout implements FileStorageLayout
{
    public function directoryFor(string $area): string
    {
        return $area;
    }
}
