<?php

namespace App\Exports;

use App\Eloquents\Document;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DocumentsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Document::with(['schedule'])->get();
    }

    /**
     * @param Document $document
     * @return array
     */
    public function map($document): array
    {
        return [
            $document->id,
            $document->name,
            preg_replace('/^documents\//', '', $document->path),
            $document->size,
            $document->extension,
            $document->schedule ? "{$document->schedule->name}(ID:{$document->schedule->id})" : null,
            $document->description,
            $document->is_public ? 'はい' : 'いいえ',
            $document->is_important ? 'はい' : 'いいえ',
            $document->notes,
            $document->created_at,
            $document->updated_at,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '配布資料ID',
            '配布資料名',
            'ファイル名',
            'サイズ（バイト）',
            'ファイル形式',
            'イベント',
            '説明',
            '公開',
            '重要',
            'スタッフ用メモ',
            '作成日時',
            '更新日時',
        ];
    }
}
