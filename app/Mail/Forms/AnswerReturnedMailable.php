<?php

namespace App\Mail\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnswerReturnedMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    public $formName;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var string
     */
    public $url;

    public function __construct(string $formName, string $reason, string $url)
    {
        $this->formName = $formName;
        $this->reason = $reason;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.forms.answer_returned');
    }
}
