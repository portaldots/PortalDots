<?php

namespace App\Http\Requests\Staff\Circles;

class UpdateCircleRequest extends BaseCircleRequest
{
    public function rules()
    {
        $rules = parent::rules();
        unset($rules['participation_type_id']);
        return $rules;
    }
}
