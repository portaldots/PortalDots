<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Page;
use App\GridMakers\Concerns\UseEloquent;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class PagesGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @var FormatTextService
     */
    private $formatTextService;

    public function __construct(FormatTextService $formatTextService)
    {
        $this->formatTextService = $formatTextService;
    }

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Page::select([
            'id',
            'title',
            'body',
            'notes',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ])->with(['viewableTags']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'title',
            'viewableTags',
            'body',
            'notes',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): array
    {
        static $tags_choices = null;

        if (empty($tags_choices)) {
            $tags_choices = Tag::all()->toArray();
        }

        $users_type = ['type' => 'belongsTo', 'to' => 'users', 'keys' => [
            'id' => ['translation' => 'ユーザーID', 'type' => 'number'],
            'student_id' => ['translation' => '学籍番号', 'type' => 'string'],
            'name_family' => ['translation' => '姓', 'type' => 'string'],
            'name_family_yomi' => ['translation' => '姓(よみ)', 'type' => 'string'],
            'name_given' => ['translation' => '名', 'type' => 'string'],
            'name_given_yomi' => ['translation' => '名(よみ)', 'type' => 'string'],
            'email' => ['translation' => '連絡先メールアドレス', 'type' => 'string'],
            'tel' => ['translation' => '電話番号', 'type' => 'string'],
            'is_staff' => ['translation' => 'スタッフ', 'type' => 'bool'],
            'is_admin' => ['translation' => '管理者', 'type' => 'bool'],
            'email_verified_at' => ['translation' => 'メール認証', 'type' => 'isNull'],
            'univemail_verified_at' => ['translation' => '本人確認', 'type' => 'isNull'],
            'notes' => ['translation' => 'スタッフ用メモ', 'type' => 'string'],
            'created_at' => ['translation' => '作成日時', 'type' => 'datetime'],
            'updated_at' => ['translation' => '更新日時', 'type' => 'datetime'],
        ]];

        return [
            'id' => ['type' => 'number'],
            'title' => ['type' => 'string'],
            'viewableTags' => [
                'type' => 'belongsToMany',
                'pivot' => 'page_viewable_tags',
                'foreign_key' => 'page_id',
                'related_key' => 'tag_id',
                'choices' => $tags_choices,
                'choices_name' => 'name',
            ],
            'body' => ['type' => 'string'],
            'notes' => ['type' => 'string'],
            'created_at' => ['type' => 'datetime'],
            'created_by' => $users_type,
            'updated_at' => ['type' => 'datetime'],
            'updated_by' => $users_type,
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'title',
            'body',
            'notes',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $item = [];
        foreach ($this->keys() as $key) {
            switch ($key) {
                case 'body':
                    $item[$key] = $this->formatTextService->summary($record->body);
                    break;
                case 'created_at':
                    $item[$key] = $record->created_at->format('Y/m/d H:i:s');
                    break;
                case 'created_by':
                    $item[$key] = $record->userCreatedBy;
                    break;
                case 'updated_at':
                    $item[$key] = $record->updated_at->format('Y/m/d H:i:s');
                    break;
                case 'updated_by':
                    $item[$key] = $record->userUpdatedBy;
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }
        return $item;
    }

    protected function model(): Model
    {
        return new Page();
    }
}
