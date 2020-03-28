<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\UploadedFile;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Circle;

abstract class BaseAnswerRequest extends FormRequest implements AnswerRequestInterface
{
    private $validationRulesService;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        $all = $this->all();

        // 改行(\r\n)が2文字と認識されてしまわないよう、\n に置換する
        if (isset($all['answers'])) {
            $all['answers'] = array_map(function ($item) {
                if (is_array($item) || $item instanceof UploadedFile) {
                    // 配列とファイルは処理しない
                    return $item;
                }
                return str_replace("\r\n", "\n", $item);
            }, $all['answers']);
        }

        return $all;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(ValidationRulesService $validationRulesService)
    {
        return $validationRulesService->getRulesFromForm($this->route('form'), $this);
    }

    public function attributes()
    {
        $validationRulesService = App::make(ValidationRulesService::class);
        return $validationRulesService->getAttributesFromForm($this->route('form'))->toArray();
    }
}
