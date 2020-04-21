<?php

namespace App\Http\Requests\Staff\Forms;

use App\Http\Requests\Forms\BaseAnswerRequest;

class AnswerRequest extends BaseAnswerRequest
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
}
