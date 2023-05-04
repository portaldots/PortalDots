<?php

namespace App\Http\Requests\Staff\Circles;

use App\Eloquents\Circle;
use App\Eloquents\ParticipationType;

class UpdateCircleRequest extends BaseCircleRequest
{
    public function rules()
    {
        $rules = parent::rules();
        unset($rules['participation_type_id']);
        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $circle = Circle::find($this->circle);
            if (isset($circle) && empty($circle->participation_type_id) && !empty($this->participation_type_id)) {
                // 参加種別が未設定の企画に限り、後から一度だけ参加種別を設定可能とする。
                if (empty(ParticipationType::find($this->participation_type_id))) {
                    $validator->errors()->add('category', '参加種別を選択してください');
                }
            }
        });
    }
}
