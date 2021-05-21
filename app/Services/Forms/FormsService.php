<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\User;
use App\Eloquents\Tag;
use App\Services\Utils\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FormsService
{
    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * フォームを作成する
     *
     * @param string $name フォーム名
     * @param string $description フォームの説明
     * @param Carbon $open_at フォームの受付開始日
     * @param Carbon $close_at フォームの受付終了日
     * @param int $max_answers 企画毎に回答可能とする回答数
     * @param bool $is_public フォームを公開するか
     * @param array|null $answerable_tags フォームを回答可能とする企画のタグ
     * @return Form
     */
    public function createForm(
        string $name,
        string $description,
        Carbon $open_at,
        Carbon $close_at,
        User $created_by,
        int $max_answers,
        bool $is_public,
        ?array $answerable_tags = null
    ): Form {
        return DB::transaction(function () use (
            $name,
            $description,
            $open_at,
            $close_at,
            $created_by,
            $max_answers,
            $is_public,
            $answerable_tags
        ) {
            $form = Form::create([
                'name' => $name,
                'description' => $description,
                'open_at' => $open_at,
                'close_at' => $close_at,
                'max_answers' => $max_answers,
                'is_public' => $is_public,
            ]);

            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id', 'name')->whereIn('name', $answerable_tags)->get();
            $form->answerableTags()->sync($exist_tags->pluck('id')->all());

            // ログに残す
            $map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'form_answerable_tag',
                $created_by,
                $form,
                [],
                $exist_tags->map($map_function)->toArray()
            );

            return $form;
        });
    }

    /**
     * フォームを更新する
     *
     * @param Form $form 更新するフォーム
     * @param string $description フォームの説明
     * @param Carbon $open_at フォームの受付開始日
     * @param Carbon $close_at フォームの受付終了日
     * @param User $created_by 作成者
     * @param int $max_answers 企画毎に回答可能とする回答数
     * @param bool $is_public フォームを公開するか
     * @param array|null $answerable_tags フォームを回答可能とする企画のタグ
     * @return boolean
     */
    public function updateForm(
        Form $form,
        string $name,
        string $description,
        Carbon $open_at,
        Carbon $close_at,
        User $created_by,
        int $max_answers,
        bool $is_public,
        ?array $answerable_tags = null
    ): bool {
        return DB::transaction(function () use (
            $form,
            $name,
            $description,
            $open_at,
            $close_at,
            $created_by,
            $max_answers,
            $is_public,
            $answerable_tags
        ) {
            $form->update([
                'name' => $name,
                'description' => $description,
                'open_at' => $open_at,
                'close_at' => $close_at,
                'max_answers' => $max_answers,
                'is_public' => $is_public,
            ]);

            $old_tags = $form->answerableTags()->orderBy('id')->get();

            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id', 'name')->whereIn('name', $answerable_tags)->orderBy('id')->get();
            $form->answerableTags()->sync($exist_tags->pluck('id')->all());

            // ログに残す
            $map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'form_answerable_tag',
                $created_by,
                $form,
                $old_tags->map($map_function)->toArray(),
                $exist_tags->map($map_function)->toArray()
            );

            return true;
        });
    }

    public function removeForm(Form $form)
    {
        return DB::transaction(function () use ($form) {
            $form->answerableTags()->detach();
            return $form->delete();
        });
    }

    /**
     * フォームを複製する
     *
     * @param Form $form
     * @return Form|null
     */
    public function copyForm(Form $form)
    {
        return DB::transaction(function () use ($form) {
            $form_copy = $form->replicate()->fill([
                'name' => $form->name . 'のコピー',
                'is_public' => false,
            ]);

            $form_copy->save();

            $questions = $form->questions()->get();
            $questions_copy = $questions->map(function ($question) {
                return $question->replicate(['form_id']);
            });

            $form_copy->questions()->createMany($questions_copy->toArray());
            return $form_copy;
        });

        return null;
    }
}
