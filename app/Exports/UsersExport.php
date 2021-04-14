<?php

namespace App\Exports;

use App\Eloquents\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::all();
    }

    /**
     * @param User $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            "\"{$user->student_id}\"",
            $user->name,
            $user->name_yomi,
            $user->email,
            "\"{$user->tel}\"",
            $user->is_staff ? 'はい' : 'いいえ',
            $user->is_admin ? 'はい' : 'いいえ',
            $user->email_verified_at ? '認証済み' : '未認証',
            $user->univemail_verified_at ? '認証済み' : '未認証',
            $user->formatLastAccessedAt(),
            $user->notes,
            $user->created_at,
            $user->updated_at,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ユーザーID',
            '学籍番号',
            '氏名',
            '氏名（よみ）',
            '連絡先メールアドレス',
            '電話番号',
            'スタッフ',
            '管理者',
            'メール認証',
            '本人確認',
            '最終アクセス',
            'スタッフ用メモ',
            '作成日時',
            '更新日時',
        ];
    }
}
