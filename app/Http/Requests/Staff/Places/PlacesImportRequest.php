<?php

namespace App\Http\Requests\Staff\Places;

use Illuminate\Foundation\Http\FormRequest;

class PlacesImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'importFile' => ['required', 'file', 'extensions:csv'],
        ];
    }

    public function attributes(): array
    {
        return [
            'importFile' => 'CSVファイル',
        ];
    }
}
