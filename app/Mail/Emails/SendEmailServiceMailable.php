<?php

declare(strict_types=1);

namespace App\Mail\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailServiceMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $subject;
    public $body;

    /**
     * Create a new message instance.
     *
     * @param  string  $subject  タイトル
     * @param  string  $body  本文
     * @return void
     */
    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.emails.send_email_service')
            ->subject($this->subject);
    }
}
