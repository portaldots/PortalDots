<?php

namespace App\Mail\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Eloquents\Answer;

class AnswerConfirmationMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * 回答したフォーム
     *
     * @var Form
     */
    public $form;

    /**
     * 設問
     *
     * @var Collection
     */
    public $questions;

    /**
     * 回答した企画
     *
     * @var Circle
     */
    public $circle;

    /**
     * 回答したユーザー
     *
     * @var User
     */
    public $applicant;

    /**
     * 回答
     *
     * @var Answer
     */
    public $answer;

    /**
     * 回答内容の配列
     *
     * @var array
     */
    public $answer_details;

    /**
     * スタッフによって回答が編集されたことを伝えるための確認メールであるか
     *
     * @var bool
     */
    public $isEditedByStaff;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        Form $form,
        Collection $questions,
        Circle $circle,
        User $applicant,
        Answer $answer,
        array $answer_details,
        bool $isEditedByStaff
    ) {
        $this->form = $form;
        $this->questions = $questions;
        $this->circle = $circle;
        $this->applicant = $applicant;
        $this->answer = $answer;
        $this->answer_details = $answer_details;
        $this->isEditedByStaff = $isEditedByStaff;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.forms.answer_confirmation');
    }
}
