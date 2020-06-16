<?php

namespace App\Mail\Contacts;

use App\Eloquents\ContactCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCategoryMailable extends Mailable
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
    public function __construct(ContactCategory $category)
    {
        $this->name = $category->name;
        $this->email = $category->email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.contacts.category');
    }
}
