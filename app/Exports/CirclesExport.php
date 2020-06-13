<?php

namespace App\Exports;

use App\Eloquents\Circle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CirclesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Circle::submitted()->with('leader', 'tags')->get();
    }

    /**
    * @var Circle $circle
    */
    public function map($circle): array
    {
        $tags = [];

        foreach ($circle->tags->all() as $tag) {
            $tags[] = $tag->name;
        }

        $leader = $circle->leader->first();

        if ($circle->status === 'approved') {
            $status = '受理';
        } elseif ($circle->status === 'rejected') {
            $status = '不受理';
        } else {
            $status = '確認中';
        }

        return [
            $circle->id,
            $circle->name,
            $circle->name_yomi,
            $circle->group_name,
            $circle->group_name_yomi,
            join(',', $tags),
            $circle->submitted_at,
            $status,
            $circle->status_set_by,
            $circle->created_at,
            $circle->updated_at,
            $circle->notes,
            $leader->id ?? null,
            $leader->student_id ?? null,
            $leader->name ?? null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '企画ID',
            '企画の名前',
            '企画の名前（よみ）',
            '企画団体の名前',
            '企画団体の名前（よみ）',
            'タグ',
            '参加登録提出日時',
            '登録受理状況',
            '登録受理状況設定者ID',
            '作成日時',
            '更新日時',
            'スタッフ用メモ',
            '責任者 ユーザーID',
            '責任者 学籍番号',
            '責任者 氏名',
        ];
    }
}
