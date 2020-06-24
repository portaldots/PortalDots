<?php

namespace App\Mail\Circles;

use App\Eloquents\Circle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovedMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $circle;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Circle $circle)
    {
        $this->circle = $circle;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.circles.approve');
    }
}
