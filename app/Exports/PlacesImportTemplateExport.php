<?php

namespace App\Exports;

use App\Eloquents\Place;
use App\Support\CsvFormulaEscaper;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PlacesImportTemplateExport extends StringValueBinder implements
    FromCollection,
    WithCustomValueBinder,
    WithHeadings,
    WithMapping
{
    public function collection(): Enumerable
    {
        return Place::query()->orderBy('id')->get();
    }

    public function map($place): array
    {
        return [
            $place->id,
            CsvFormulaEscaper::escape($place->name),
            match ($place->type) {
                1 => '屋内',
                2 => '屋外',
                default => '特殊場所',
            },
            CsvFormulaEscaper::escape($place->notes),
        ];
    }

    public function headings(): array
    {
        return [
            '場所ID',
            '場所名',
            'タイプ',
            'スタッフ用メモ',
        ];
    }
}
