<?php

namespace App\Http\Requests\Install;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\Install\DatabaseService;
use App;

class DatabaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(DatabaseService $databaseService)
    {
        return $databaseService->getValidationRules();
    }

    public function attributes()
    {
        $databaseService = App::make(DatabaseService::class);
        return $databaseService->getFormLabels();
    }
}
