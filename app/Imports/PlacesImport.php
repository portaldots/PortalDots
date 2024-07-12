<?php

namespace App\Imports;

use App\Eloquents\Place;
use App\Http\Requests\Staff\Places\PlaceRequest;
use Illuminate\Support\Facades\Validator;
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
        $rawData = [
            'id' => $row['場所ID'],
            'name' => $row['場所名'],
            'type' => $this->getTypeValue($row['タイプ']),
            'notes' => $row['スタッフ用メモ'],
        ];

        $validator = Validator::make($rawData, [
            'id' => ['nullable', 'numeric', 'exists:places'],
            'name' => ['required', 'string', Rule::unique('places')->ignore($rawData['id'])],
            'type' => ['required', Rule::in([1, 2, 3])],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($validator->errors()->messages())) dd($rawData, $validator->errors());

        $validated = $validator->validated();

        $place = new Place([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'notes' => $validated['notes'],
        ]);

        $place->id = $validated['id'];

        return $place;
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
        return ['id'];
    }

    public function upsertColumns()
    {
        return ['name', 'type', 'notes'];
    }

    public function withHeadingRow() {

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
