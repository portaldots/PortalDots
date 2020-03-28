<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CustomForm extends Model
{
    private const ALLOWED_TYPES = ['circle', 'user'];

    private function form()
    {
        return $this->belongsTo(Form::class);
    }

    public static function getFormByType(string $type): Form
    {
        static $forms = [];

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException();
        }

        if (empty($forms[$type])) {
            $forms[$type] = self::where('type', $type)->first()->form;
        }

        return $forms[$type];
    }
}
