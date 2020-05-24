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

    private $from_address;
    private $from_name;

    public function __construct(string $from_address, string $from_name)
    {
        $this->from_address = $from_address;
        $this->from_name = $from_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from($this->from_address, $this->from_name)
            ->markdown('emails.install.test_mail');
    }
}
