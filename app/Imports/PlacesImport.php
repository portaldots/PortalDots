<?php

namespace App\Imports;

use App\Eloquents\Place;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

HeadingRowFormatter::default('none');

class PlacesImport implements
    ToModel,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithHeadingRow,
    WithUpserts,
    WithUpsertColumns
{
    use Importable;

    /**
     * ID がセットされている場合は内容を更新する。
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Place([
            'id' => $row['場所ID'],
            'name' => $row['場所名'],
            'type' => $this->getTypeValue($row['タイプ']),
            'notes' => $row['スタッフ用メモ'],
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '場所ID' => ['nullable', 'numeric'],
            '場所名' => ['required', 'string'], // できれば Unique も
            'タイプ' => ['required', Rule::in(['屋内', '屋外', '特殊場所'])],
            'スタッフ用メモ' => ['nullable', 'string'],
        ];
    }

    /**
     * @param Throwable $e
     */
    public function onError(Throwable $e)
    {
        dd($e);
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        dd($failures);
    }

    public function uniqueBy()
    {
        return ['id' => '場所ID'];
    }

    // TODO: Upsert がうまくできないためもう少しドキュメントなどを見る
    public function upsertColumns()
    {
        return ['name' => '場所名', 'type' => 'タイプ', 'notes' => 'スタッフ用メモ'];
    }

    private function getTypeValue(string $type)
    {
        if ($type === '屋内') {
            return 1;
        }
        if ($type === '屋外') {
            return 2;
        }
        return 3;
    }
}
