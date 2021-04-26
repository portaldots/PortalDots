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
        return Place::with('circles')->get();
    }

    /**
     * @param Place $place
     * @return array
     */
    public function map($place): array
    {
        $firstCircle = $place->circles->shift();
        $circles = [];

        foreach ($place->circles as $circle) {
            $circles[] = [
                null,
                null,
                null,
                null,
                $circle->id,
                $circle->name,
                $circle->name_yomi,
                $circle->group_name,
                $circle->group_name_yomi,
            ];
        }

        if ($place->type === 1) {
            $type = '屋内';
        } elseif ($place->type === 2) {
            $type = '屋外';
        } else {
            $type = '特殊場所';
        }

        return array_merge(
            [
                [
                    $place->id,
                    $place->name,
                    $type,
                    $place->notes,
                    $firstCircle->id,
                    $firstCircle->name,
                    $firstCircle->name_yomi,
                    $firstCircle->group_name,
                    $firstCircle->group_name_yomi,
                ],
            ],
            $circles,
        );
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
            '企画ID',
            '企画名',
            '企画名（よみ）',
            '企画を出店する団体の名称',
            '企画を出店する団体の名称（よみ）',
        ];
    }
}
