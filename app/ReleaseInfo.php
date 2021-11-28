<?php

namespace App;

/**
 * PortalDots のリリース情報
 *
 * 現在インストールされている PortalDots のバージョン情報などを管理するクラス。
 */
class ReleaseInfo
{
    /**
     * PortalDots のバージョン
     *
     * GitHub Actions により、PortalDots リリース ZIP 作成時、以下の定数にバージョンが自動でセットされます。
     * 例 : '1.0.0' (先頭に v をつけて 'v1.0.0' とはしません)
     */
    public const VERSION = '###VERSION_PLACEHOLDER###';
}
