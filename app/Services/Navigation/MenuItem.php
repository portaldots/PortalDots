<?php

declare(strict_types=1);

namespace App\Services\Navigation;

use Closure;
use Illuminate\Support\Facades\Request;

/**
 * ドロワー・ボトムタブに表示する1つのメニュー項目
 */
final class MenuItem
{
    /**
     * @param string $key MenuRegistry::remove() や add() の $after で指定する一意な識別子
     * @param string|Closure(): string $label 表示テキスト。文字列ならそのまま、
     *   クロージャなら呼び出した戻り値を使う（term() を使った用語の組み立てに使う）
     * @param string $route route() に渡すルート名
     * @param array $routeParams route() に渡すパラメータ
     * @param string $activePattern Request::is() に渡すパターン。現在のページかどうかの判定に使う
     * @param string $icon Font Awesome のアイコンクラス
     * @param Closure(): bool $visible 表示するかどうかを判定するクロージャ
     * @param bool $showInBottomTabs ボトムタブにも表示するかどうか
     * @param (Closure(): int|null)|null $badge バッジに表示する件数を返すクロージャ。null または 0 以下ならバッジを表示しない
     */
    public function __construct(
        public readonly string $key,
        public readonly string|Closure $label,
        public readonly string $route,
        public readonly array $routeParams,
        public readonly string $activePattern,
        public readonly string $icon,
        public readonly Closure $visible,
        public readonly bool $showInBottomTabs = false,
        public readonly ?Closure $badge = null,
    ) {
    }

    public function label(): string
    {
        return $this->label instanceof Closure ? ($this->label)() : $this->label;
    }

    public function href(): string
    {
        return route($this->route, $this->routeParams);
    }

    public function isActive(): bool
    {
        return Request::is($this->activePattern);
    }

    public function isVisible(): bool
    {
        return ($this->visible)();
    }

    /**
     * バッジに表示する件数。表示しない場合は null
     */
    public function badgeCount(): ?int
    {
        if ($this->badge === null) {
            return null;
        }

        $count = ($this->badge)();
        return $count !== null && $count > 0 ? $count : null;
    }
}
