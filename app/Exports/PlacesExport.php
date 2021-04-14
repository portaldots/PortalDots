<?php

namespace App\Exports;

use App\Eloquents\Place;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlacesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Place::with('circles:id,name')->get();
    }

    /**
     * @param Place $place
     * @return array
     */
    public function map($place): array
    {
        if ($place->type === 1) {
            $type = '屋内';
        } elseif ($place->type === 2) {
            $type = '屋外';
        } else {
            $type = '特殊場所';
        }

        return [
            $place->id,
            $place->name,
            $type,
            $place->notes,
            $place->circles->makeHidden('pivot')->toJson(JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '場所ID',
            '場所名',
            'タイプ',
            'スタッフ用メモ',
            '使用企画(企画ID+企画名)',
        ];
    }
}
