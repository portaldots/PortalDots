<?php

namespace App\Imports;

use App\Eloquents\Place;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class PlacesImport implements ToModel, WithValidation, SkipsOnError, SkipsOnFailure, WithHeadingRow
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!isset($row[0])) {
            return new Place([
                'name' => $row['name'],
                'type' => $this->getTypeValue($row['type']),
                'notes' => $row['notes'],
            ]);
        }
        // else {
        //     $place = Place::find($row[0]);
        //     $place->type = $row[2];
        //     $place->notes = $row[3];
        //     return $place;
        // }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'numeric'], // ID
            'name' => ['required', 'string', Rule::unique('places', 'name')], // Name // できれば Unique も
            'type' => ['required', Rule::in(['屋内', '屋外', '特殊場所'])], // Type
            'notes' => ['nullable', 'string'], // Note
        ];
    }

    /**
     * @return array
     */
    public function customValidationAttributes(): array
    {
        return [
            'id' => '場所ID',
            'name' => '場所名',
            'type' => 'タイプ',
            'notes' => 'スタッフ用メモ'
        ];
    }

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
