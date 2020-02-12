<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Circle;

class StoreAnswerRequest extends BaseAnswerRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $form = $this->route('form');
        return Gate::allows('circle.belongsTo', Circle::findOrFail($this->circle_id)) &&
            $form->is_public && $form->isOpen();
    }
}
