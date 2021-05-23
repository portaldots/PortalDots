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
        return Page::with(['viewableTags', 'userCreatedBy', 'userUpdatedBy'])->orderBy('id')->get();
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
            $page->is_pinned,
            $page->is_public,
            $page->notes,
            $page->created_at,
            "{$page->userCreatedBy->name}(ID:{$page->userCreatedBy->id},{$page->userCreatedBy->student_id})",
            $page->updated_at,
            "{$page->userUpdatedBy->name}(ID:{$page->userUpdatedBy->id},{$page->userUpdatedBy->student_id})",
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
            '作成者',
            '更新日時',
            '更新者',
        ];
    }
}
