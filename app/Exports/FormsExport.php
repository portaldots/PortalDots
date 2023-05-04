<?php

namespace App\Exports;

use App\Eloquents\Form;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FormsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Form::withoutParticipationForms()
            ->with(['answerableTags'])
            ->get();
    }

    public function map($form): array
    {
        return [
            $form->id,
            $form->name,
            $form->description,
            $form->answerableTags->implode('name', ','),
            $form->open_at,
            $form->close_at,
            $form->max_answers,
            $form->is_public ? 'はい' : 'いいえ',
            $form->created_at,
            $form->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'フォームID',
            'フォーム名',
            '説明',
            '回答可能なタグ',
            '受付開始日時',
            '受付終了日時',
            '回答可能数',
            '公開',
            '作成日時',
            '更新日時',
        ];
    }
}
