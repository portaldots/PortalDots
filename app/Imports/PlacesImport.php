<?php

namespace App\Imports;

use App\Place;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class PlacesImport implements ToModel, WithValidation, WithHeadingRow, SkipsOnError
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
                'name' => $row[1],
                'type' => $this->getTypeValue($row[2]),
                'notes' => $row[3],
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
            '場所名' => ['required', 'string'], // できれば Unique も
            'タイプ' => ['required', Rule::in(["屋内", "屋外", "特殊場所"])],
            'スタッフ用メモ' => ['nullable', 'string'],
        ];
    }

    public function onError(Throwable $e)
    {
        dd($e);
    }

    private function getTypeValue(string $type) {
        if ($type === '屋内') {
            return 1;
        }
        if ($type === '屋外') {
            return 2;
        }
        return 3;
    }

}
