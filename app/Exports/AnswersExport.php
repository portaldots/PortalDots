<?php

namespace App\Exports;

use App\Eloquents\Form;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnswersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @var Form
     */
    private $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->form->load('questions')->answers()->with('circle', 'details')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return array_merge(
            [
                '回答ID',
                '企画ID',
                '企画名',
            ],
            $this->form->questions->sortBy('priority')->pluck('name')->toArray()
        );
    }

    /**
     * @return array
     */
    public function map($answer): array
    {
        foreach ($this->form->questions->sortBy('priority') as $q) {
            $details[] = $answer->details->where('question_id', $q->id)->first()->answer ?? null;
        }

        return array_merge(
            [
                $answer->id,
                $answer->circle->id,
                $answer->circle->name,
            ],
            $details
        );
    }
}
