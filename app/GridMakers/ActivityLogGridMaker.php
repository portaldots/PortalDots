<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class ActivityLogGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Activity::select([
            'id',
            'log_name',
            'description',
            'subject_type',
            'subject_id',
            'causer_type',
            'causer_id',
            'properties',
            'created_at',
            'updated_at',
        ])->with(['causer']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'causer_id',
            'log_name',
            'subject_id',
            'description',
            'properties',
            'created_at',
        ];
    }

    public static function getAllLogNames()
    {
        return [
            'answer' => '回答',
            'answer_detail' => '設問への回答',
            'booth' => '企画の使用場所',
            'circle' => '企画',
            'circle_tag' => '企画タグの紐付け',
            'circle_user' => '企画とユーザーの紐付け',
            'document' => '配布資料',
            'form' => 'フォーム',
            'form_answerable_tag' => 'フォームへ回答可能なユーザー(タグ)',
            'page' => 'お知らせ',
            'page_viewable_tag' => 'お知らせを閲覧可能なユーザー(タグ)',
            'page_document' => 'お知らせに関連する配布資料',
            'place' => '場所',
            'question' => 'フォームの設問',
            'tag' => 'タグ',
            'user' => 'ユーザー',
        ];
    }

    public static function getAllDescriptions()
    {
        return [
            'created' => '作成',
            'updated' => '変更',
            'deleted' => '削除',
            'submitted' => '企画参加登録を提出',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        $log_names = array_keys($this->getAllLogNames());
        $descriptions = array_keys($this->getAllDescriptions());

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'causer_id' => FilterableKey::number(),
            'log_name' => FilterableKey::enum($log_names),
            'subject_id' => FilterableKey::number(),
            'description' => FilterableKey::enum($descriptions),
            'created_at' => FilterableKey::datetime(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'log_name',
            'description',
            'subject_id',
            'causer_id',
            'created_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function defaultOrderBy(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public function defaultDirection(): string
    {
        return 'desc';
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $item = [];

        $log_names_dict = $this->getAllLogNames();
        $descriptions_dict = $this->getAllDescriptions();

        foreach ($this->keys() as $key) {
            switch ($key) {
                case 'log_name':
                    $item[$key] = !empty($record->log_name) && isset($log_names_dict[$record->log_name])
                        ? $log_names_dict[$record->log_name] : '不明';
                    break;
                case 'description':
                    $item[$key] = !empty($record->description) && isset($descriptions_dict[$record->description])
                        ? $descriptions_dict[$record->description] : '不明';
                    break;
                case 'causer_id':
                    if (empty($record->causer_type)) {
                        $item[$key] = '非ログインユーザー';
                    } elseif (!isset($record->causer)) {
                        $item[$key] = '削除済みユーザー(ID : ' . $record->causer_id . ')';
                    } else {
                        $item[$key] = $record->causer->student_id . ' - ' .
                            $record->causer->name . '(ID : ' . $record->causer->id . ')';
                    }
                    break;
                case 'created_at':
                    $item[$key] = !empty($record->created_at) ? $record->created_at->format('Y/m/d H:i:s') : null;
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }
        return $item;
    }

    protected function model(): Model
    {
        return new Activity();
    }
}
