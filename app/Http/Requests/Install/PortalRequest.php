<?php

namespace App\Http\Requests\Install;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\Install\PortalService;
use App;

class PortalRequest extends FormRequest
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
    public function rules(PortalService $portalService)
    {
        return $portalService->getValidationRules();
    }

    public function attributes()
    {
        $portalService = App::make(PortalService::class);
        return $portalService->getFormLabels();
    }
}
