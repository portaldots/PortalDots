<?php

namespace App\Exports;

use App\Eloquents\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SchedulesExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Schedule::all();
    }

    public function headings(): array
    {
        return [
            '予定ID',
            '予定',
            '開始日時',
            '場所',
            '説明',
            'スタッフ用メモ',
            '作成日時',
            '更新日時',
        ];
    }
}
