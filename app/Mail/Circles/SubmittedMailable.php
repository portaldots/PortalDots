<?php

namespace App\Mail\Circles;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubmittedMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var Circle
     */
    public $circle;

    /**
     * @var Form
     */
    public $participation_form;

    /**
     * @var Collection
     */
    public $questions;

    /**
     * @var Answer
     */
    public $answer;

    /**
     * @var array
     */
    public $answer_details;

    /**
     * Create a new message instance.
     *
     * @param Circle $circle
     * @param Form|null $participationForm
     * @param Collection|null $questions
     * @param Answer|null $answer
     * @param array|null $answerDetails
     * @return void
     */
    public function __construct(
        Circle $circle,
        Form $participationForm = null,
        Collection $questions = null,
        Answer $answer = null,
        array $answerDetails = null
    ) {
        $this->circle = $circle;
        $this->participation_form = $participationForm;
        $this->questions = $questions;
        $this->answer = $answer;
        $this->answer_details = $answerDetails;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.circles.submit');
    }
}
