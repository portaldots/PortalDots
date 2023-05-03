<?php

declare(strict_types=1);

namespace App\Services\Tags;

use App\Eloquents\Tag;
use App\Services\Tags\Exceptions\DenyCreateTagsException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TagsService
{
    /**
     * 引数に渡されたタグの名前の配列から、データベースに保存されているタグの一覧を取得する。
     * データベースで未作成のタグについては作成する。
     *
     * @param array $tags
     * @param boolean $allow_create タグの新規作成を許可するかどうか
     * @throws DenyCreateTagsException $allow_create が false なのに企画タグの新規作成が必要になった場合に発生する例外
     * @return Collection
     */
    public function getOrCreateTags(array $tags, bool $allow_create): Collection
    {
        return DB::transaction(function () use ($tags, $allow_create) {
            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::whereIn('name', $tags)->get();

            $diff = array_udiff($tags, $exist_tags->pluck('name')->all(), 'strcasecmp');

            foreach ($diff as $insert) {
                if (!$allow_create) {
                    throw new DenyCreateTagsException('企画タグの作成は許可されていません');
                }

                Tag::create(['name' => $insert]);
            }

            // $tags 配列の順番でreturnするため、もう一度 DB からタグ一覧を取得する
            return Tag::whereIn('name', $tags)->get();
        });
    }
}
