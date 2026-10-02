<?php

namespace App\Mail\Documents;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentConfirmationRequestedMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    public $documentName;

    /**
     * @var int
     */
    public $version;

    /**
     * @var string
     */
    public $url;

    public function __construct(string $documentName, int $version, string $url)
    {
        $this->documentName = $documentName;
        $this->version = $version;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.documents.confirmation_requested');
    }
}
