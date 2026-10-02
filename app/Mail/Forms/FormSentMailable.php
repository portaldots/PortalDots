<?php

namespace App\Mail\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormSentMailable extends Mailable
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
    public $dueAtText;

    /**
     * @var string
     */
    public $url;

    public function __construct(string $formName, string $dueAtText, string $url)
    {
        $this->formName = $formName;
        $this->dueAtText = $dueAtText;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.forms.form_sent');
    }
}
