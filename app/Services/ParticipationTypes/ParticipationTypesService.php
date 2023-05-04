<?php

declare(strict_types=1);

namespace App\Services\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Eloquents\User;
use App\Services\Tags\TagsService;
use App\Services\Utils\ActivityLogService;
use Illuminate\Support\Facades\DB;

class ParticipationTypesService
{
    public function __construct(
        private ActivityLogService $activityLogService,
        private TagsService $tagsService
    ) {
    }

    /**
     * 指定された参加種別についてタグを保存する
     *
     * @param ParticipationType $participationType
     * @param array $tags
     * @param boolean $allow_create タグの新規作成を許可するかどうか
     * @param User $caused_by タグを保存したユーザー
     * @throws DenyCreateTagsException $allow_create が false なのに企画タグの新規作成が必要になった場合に発生する例外
     * @return void
     */
    public function saveTags(
        ParticipationType $participationType,
        array $tags,
        bool $allow_create,
        User $caused_by
    ) {
        DB::transaction(function () use ($participationType, $tags, $allow_create, $caused_by) {
            $old_tags = $participationType->tags;

            $tags_on_db = $this->tagsService->getOrCreateTags($tags, $allow_create);

            $participationType->tags()->sync($tags_on_db->pluck('id')->all());

            // ログに残す
            $map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'participation_type_tag',
                $caused_by,
                $participationType,
                $old_tags->map($map_function)->toArray(),
                $tags_on_db->map($map_function)->toArray()
            );
        });
    }
}
