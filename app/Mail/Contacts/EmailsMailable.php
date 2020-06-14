<?php

namespace App\Mail\Contacts;

use App\Eloquents\ContactEmail;
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
    public $name;

    /**
     * お問い合わせ先のメールアドレス
     *
     * @var string
     */
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ContactEmail $contact_email)
    {
        $this->name = $contact_email->name;
        $this->email = $contact_email->email;
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
