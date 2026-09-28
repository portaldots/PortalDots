<?php

namespace App\Exceptions\Forms;

use App\Eloquents\Answer;
use RuntimeException;

/**
 * lock_version が現在のDB上の値と一致しない場合、つまり別の操作によって
 * 回答がすでに更新されている場合にthrowされる
 */
class StaleAnswerException extends RuntimeException
{
    public Answer $answer;

    public function __construct(Answer $answer)
    {
        parent::__construct('回答が別の操作によって更新されているため、この操作を行えません。');
        $this->answer = $answer;
    }
}
