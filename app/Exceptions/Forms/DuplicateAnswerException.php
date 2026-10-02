<?php

namespace App\Exceptions\Forms;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use RuntimeException;

/**
 * requires_review かつ max_answers = 1 のフォームに対し、すでに回答が
 * 存在する状態で新たに回答を作成しようとした場合にthrowされる
 */
class DuplicateAnswerException extends RuntimeException
{
    public Form $form;
    public Circle $circle;

    public function __construct(Form $form, Circle $circle)
    {
        parent::__construct('すでに回答が存在するため、新しい回答を作成できません。');
        $this->form = $form;
        $this->circle = $circle;
    }
}
