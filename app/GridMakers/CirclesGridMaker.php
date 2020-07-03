<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Circle;
use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class CirclesGridMaker implements GridMakable
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
        return Circle::submitted()->select([
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
        ])->with(['tags']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'tags',
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
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
            'name' => ['type' => 'string'],
            'name_yomi' => ['type' => 'string'],
            'group_name' => ['type' => 'string'],
            'group_name_yomi' => ['type' => 'string'],
            'tags' => [
                'type' => 'belongsToMany',
                'pivot' => 'circle_tag',
                'foreign_key' => 'circle_id',
                'related_key' => 'tag_id',
                'choices' => $tags_choices,
                'choices_name' => 'name',
            ],
            'submitted_at' => ['type' => 'datetime'],
            'status' => [
                'type' => 'enum',
                'choices' => [
                    'rejected' => '不受理',
                    'approved' => '受理',
                    'NULL' => '確認中',
                ]
            ],
            'status_set_at' => ['type' => 'datetime'],
            'status_set_by' => $users_type,
            'notes' => ['type' => 'string'],
            'created_at' => ['type' => 'datetime'],
            'updated_at' => ['type' => 'datetime'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
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
                case 'status_set_by':
                    $item[$key] = $record->statusSetBy;
                    break;
                case 'status_set_at':
                    $item[$key] = !empty($record->status_set_at) ? $record->status_set_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'created_at':
                    $item[$key] = $record->created_at->format('Y/m/d H:i:s');
                    break;
                case 'updated_at':
                    $item[$key] = $record->updated_at->format('Y/m/d H:i:s');
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }
        return $item;
    }

    protected function model(): Model
    {
        return new Circle();
    }
}
