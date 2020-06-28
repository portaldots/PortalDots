<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Document;
use App\GridMakers\Concerns\UseEloquent;
use Illuminate\Database\Eloquent\Model;

class DocumentsGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Document::select($this->keys())->with(['schedule', 'userCreatedBy', 'userUpdatedBy']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'path',
            'size',
            'extension',
            'schedule_id',
            'description',
            'is_public',
            'is_important',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): array
    {
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
            'size' => ['type' => 'number'],
            'extension' => ['type' => 'number'],
            'schedule_id' => ['type' => 'belongsTo', 'to' => 'schedules', 'keys' => [
                'id' => ['translation' => '予定ID', 'type' => 'number'],
                'name' => ['translation' => '予定名', 'type' => 'string'],
                'start_at' => ['translation' => '開始日時', 'type' => 'datetime'],
                'place' => ['translation' => '場所', 'type' => 'string'],
                'notes' => ['translation' => 'スタッフ用メモ', 'type' => 'string'],
                'created_at' => ['translation' => '作成日時', 'type' => 'datetime'],
                'updated_at' => ['translation' => '更新日時', 'type' => 'datetime'],
            ]],
            'description' => ['type' => 'string'],
            'is_public' => ['type' => 'bool'],
            'is_important' => ['type' => 'bool'],
            'created_at' => ['type' => 'datetime'],
            'created_by' => $users_type,
            'updated_at' => ['type' => 'datetime'],
            'updated_by' => $users_type,
            'notes' => ['type' => 'string'],
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
            'size',
            'extension',
            'schedule_id',
            'description',
            'is_public',
            'is_important',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes',
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
                case 'extension':
                    $item[$key] = mb_strtoupper($record->extension);
                    break;
                case 'schedule_id':
                    $item[$key] = $record->schedule;
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
        return new Document();
    }
}
