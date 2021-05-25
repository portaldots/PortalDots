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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (
                !isset($this->PORTAL_PRIMARY_COLOR_H) ||
                !isset($this->PORTAL_PRIMARY_COLOR_S) || !isset($this->PORTAL_PRIMARY_COLOR_L)
            ) {
                return;
            }

            if (
                $this->PORTAL_PRIMARY_COLOR_H === 'null' &&
                $this->PORTAL_PRIMARY_COLOR_S === 'null' && $this->PORTAL_PRIMARY_COLOR_L === 'null'
            ) {
                return;
            }

            $h = (int)$this->PORTAL_PRIMARY_COLOR_H;
            $s = (int)$this->PORTAL_PRIMARY_COLOR_S;
            $l = (int)$this->PORTAL_PRIMARY_COLOR_L;

            if ($h < 0 || $h > 360 || $s < 0 || $s > 100 || $l < 0 || $l > 100) {
                $validator->errors()->add('primary_color', 'お問い合わせ項目を選択肢から選んでください');
            }
        });
    }
}
