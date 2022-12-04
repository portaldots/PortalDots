<?php

namespace App\Exports;

use App\Eloquents\Place;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlacesExport implements FromCollection, WithHeadings, WithMapping
{
    private $withCircle;

    /**
     * @param boolean $withCircle 企画情報も出力するかどうか
     */
    public function __construct(bool $withCircle = true)
    {
        $this->withCircle = $withCircle;
    }

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
        if (!$this->withCircle) {
            return [
                $place->id,
                $place->name,
                $place->getTypeName(),
                $place->notes,
            ];
        }

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

        return array_merge(
            [
                [
                    $place->id,
                    $place->name,
                    $place->getTypeName(),
                    $place->notes,
                    $firstCircle->id ?? null,
                    $firstCircle->name ?? null,
                    $firstCircle->name_yomi ?? null,
                    $firstCircle->group_name ?? null,
                    $firstCircle->group_name_yomi ?? null,
                ],
            ],
            $circles
        );
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // インポート時ヘッダーを Key として扱うようにしているが
        // 日本語などの文字が変換されないため英字でも記載しておく。
        $heading = ['場所ID', '場所名（NAME）', 'タイプ（TYPE）', 'スタッフ用メモ（NOTES）'];

        if ($this->withCircle) {
            array_push(
                $heading,
                '企画ID',
                '企画名',
                '企画名（よみ）',
                '企画を出店する団体の名称',
                '企画を出店する団体の名称（よみ）'
            );
        }
        return $heading;
    }
}
