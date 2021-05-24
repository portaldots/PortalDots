<?php

namespace App\Exports;

use App\Eloquents\Page;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PagesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Page::with(['viewableTags'])->orderBy('id')->get();
    }

    /**
     * @param Page $page
     * @return array
     */
    public function map($page): array
    {
        return [
            $page->id,
            $page->title,
            $page->viewableTags->implode('name', ','),
            $page->body,
            $page->is_pinned ? 'はい' : 'いいえ',
            $page->is_public ? 'はい' : 'いいえ',
            $page->notes,
            $page->created_at,
            $page->updated_at,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'お知らせID',
            'タイトル',
            '閲覧可能なタグ',
            '本文',
            '固定',
            '公開',
            'スタッフ用メモ',
            '作成日時',
            '更新日時',
        ];
    }
}
