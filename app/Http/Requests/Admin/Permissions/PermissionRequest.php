<?php

namespace App\Http\Requests\Admin\Permissions;

use App\Eloquents\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
    public function rules()
    {
        return [
            'permissions' => ['array'],
            'permissions.*' => Rule::in(array_keys(Permission::getDefinedPermissions())),
        ];
    }

    public function attributes()
    {
        return [
            'permissions' => '付与する権限',
        ];
    }

    public function messages()
    {
        return [
            'permissions.*.in' => '利用できない権限が選択されました',
        ];
    }
}
