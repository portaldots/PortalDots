<?php

namespace App\Exports;

use App\Eloquents\Circle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CirclesExport implements FromCollection, WithHeadings, WithMapping
{
    private $headings = [
        'id' => 'id',
        'name' => '企画名',
        'name_yomi' => '企画名（よみ）',
        'group_name' => '団体名',
        'group_name_yomi' => '団体名（よみ）',
        'status' => 'ステータス',
        'notes' => 'スタッフメモ',
        'leader_id' => '責任者 ユーザーID',
        'leader_student_id' => '責任者 学籍番号',
        'leader_name' => '責任者 氏名',
    ];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Circle::Submitted()->with('leader')->get();
    }

    /**
    * @var Circle $circle
    */
    public function map($circle): array
    {
        $leader = $circle->leader->first();

        return [
            'id' => $circle->id,
            'name' => $circle->name,
            'name_yomi' => $circle->name_yomi,
            'group_name' => $circle->group_name,
            'group_name_yomi' => $circle->group_name_yomi,
            'status' => $circle->status,
            'notes' => $circle->notes,
            'leader_id' => $leader->id ?? null,
            'leader_student_id' => $leader->student_id ?? null,
            'leader_name' => $leader->name ?? null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
