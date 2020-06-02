<?php

namespace App\Exports;

use App\Eloquents\Circle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CirclesExport implements FromCollection, WithHeadings
{
    private $headings = [
        'id' => 'id',
        'name' => '企画名',
        'name_yomi' => '企画名（よみ）',
        'group_name' => '団体名',
        'group_name_yomi' => '団体名（よみ）',
        'status' => 'ステータス',
        'notes' => 'スタッフメモ',
    ];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Circle::Submitted()->get(array_keys($this->headings));
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
