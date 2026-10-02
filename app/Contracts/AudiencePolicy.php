<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * お知らせ・配布資料の公開範囲 (audience) を制限するための拡張ポイント
 */
interface AudiencePolicy
{
    /**
     * 未ログインの利用者を含む、誰でも閲覧できる
     */
    public const EVERYONE = 'everyone';

    /**
     * ログインしているユーザーであれば誰でも閲覧できる
     */
    public const SIGNED_IN = 'signed_in';

    /**
     * 指定したタグ・企画に該当する企画のみ閲覧できる
     */
    public const SELECTED = 'selected';

    /**
     * 公開範囲として選択可能な値の一覧
     *
     * @return string[]
     */
    public function allowedAudiences(): array;

    /**
     * タグによる公開範囲の指定を許可するかどうか
     * falseの場合、公開範囲「selected」はタグではなく企画を直接指定する必要がある
     */
    public function allowsTagTargets(): bool;
}
