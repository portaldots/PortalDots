<?php

namespace App\Mail\Contacts;

use App\Eloquents\ContactEmails;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailsMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * 項目の名前
     *
     * @var string
     */
    private $name;

    /**
     * お問い合わせ先のメールアドレス
     *
     * @var string
     */
    private $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ContactEmails $contactEmails)
    {
        $this->name = $contactEmails->name;
        $this->email = $contactEmails->email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.contacts.emails');
    }
}
