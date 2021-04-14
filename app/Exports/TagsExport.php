<?php

namespace App\Exports;

use App\Eloquents\Tag;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TagsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Tag::with('circles:id,name')->get();
    }

    /**
     * @param Tag $tag
     * @return array
     */
    public function map($tag): array
    {
        return [
            $tag->id,
            $tag->name,
            $tag->created_at,
            $tag->updated_at,
            $tag->circles->makeHidden('pivot')->toJson(JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'タグID',
            'タグ',
            '作成日時',
            '更新日時',
            '付与されている企画',
        ];
    }
}
