<?php

namespace Tests\Feature\Services\Forms;

use App\Http\Requests\Forms\AnswerRequestInterface;
use Illuminate\Http\Request;

class TableAnswerRequestFake extends Request implements AnswerRequestInterface
{
    private array $validatedAnswers;

    public function __construct(array $answers)
    {
        $this->validatedAnswers = $answers;
        parent::__construct([], [], [], [], ['answers' => $answers]);
    }

    public function validated(): array
    {
        return ['answers' => $this->validatedAnswers];
    }
}
