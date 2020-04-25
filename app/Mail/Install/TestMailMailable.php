<?php

namespace App\Mail\Install;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class TestMailMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * PortalDots のインストールを完了させるためのパスワード
     *
     * @var string
     */
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        string $password
    ) {
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.install.test_mail');
    }
}
