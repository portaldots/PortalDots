<?php

declare(strict_types=1);

namespace App\Services\Navigation;

/**
 * ドロワー・ボトムタブのメニュー項目を、企画向け（circle）・スタッフ向け（staff）・
 * 管理者向け（admin）のセクションごとに保持するレジストリ。
 *
 * 初期状態の項目は NavigationServiceProvider で登録する。プライベートなデプロイ用
 * パッケージは、このレジストリに対して add() / remove() を呼ぶことで、OSS側の
 * ファイルを変更せずにメニュー項目を追加・削除できる
 */
class MenuRegistry
{
    public const SECTION_CIRCLE = 'circle';
    public const SECTION_STAFF = 'staff';
    public const SECTION_ADMIN = 'admin';

    /**
     * @var array<string, array<string, MenuItem>>
     */
    private array $items = [
        self::SECTION_CIRCLE => [],
        self::SECTION_STAFF => [],
        self::SECTION_ADMIN => [],
    ];

    /**
     * $section に項目を追加する
     *
     * @param string $section self::SECTION_* のいずれか
     * @param MenuItem $item
     * @param string|null $after 指定した場合、そのキーを持つ項目の直後に挿入する。
     *   見つからない場合、または未指定の場合は末尾に追加する
     */
    public function add(string $section, MenuItem $item, ?string $after = null): void
    {
        $items = $this->items[$section] ?? [];
        unset($items[$item->key]);

        if ($after !== null && array_key_exists($after, $items)) {
            $reordered = [];
            foreach ($items as $key => $existing) {
                $reordered[$key] = $existing;
                if ($key === $after) {
                    $reordered[$item->key] = $item;
                }
            }
            $items = $reordered;
        } else {
            $items[$item->key] = $item;
        }

        $this->items[$section] = $items;
    }

    /**
     * $section から、$key を持つ項目を取り除く
     *
     * @param string $section self::SECTION_* のいずれか
     * @param string $key
     */
    public function remove(string $section, string $key): void
    {
        unset($this->items[$section][$key]);
    }

    /**
     * 表示条件に関係なく、セクションの全項目を取り除く。
     */
    public function clear(string $section): void
    {
        $this->items[$section] = [];
    }

    /**
     * $section に登録されている項目のうち、表示可能なものだけを登録順で返す
     *
     * @param string $section self::SECTION_* のいずれか
     * @return MenuItem[]
     */
    public function get(string $section): array
    {
        return array_values(array_filter(
            $this->items[$section] ?? [],
            fn (MenuItem $item) => $item->isVisible()
        ));
    }
}
