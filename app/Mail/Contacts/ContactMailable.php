<?php

namespace App\Mail\Contacts;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Eloquents\Circle;
use App\Eloquents\User;

class ContactMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * お問い合わせする対象の企画
     *
     * @var Circle
     */
    public $circle;

    /**
     * お問い合わせの送信者
     *
     * @var User
     */
    public $sender;

    /**
     * お問い合わせの本文
     *
     * @var string
     */
    public $contactBody;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(?Circle $circle, User $sender, string $contactBody)
    {
        $this->circle = $circle;
        $this->sender = $sender;
        $this->contactBody = $contactBody;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.contacts.contact');
    }
}
