<?php

namespace App\Mail\Threads;

use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffMessageMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var Thread
     */
    public $thread;

    /**
     * @var ThreadEntry
     */
    public $entry;

    public function __construct(Thread $thread, ThreadEntry $entry)
    {
        $this->thread = $thread;
        $this->entry = $entry;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.threads.staff_message');
    }
}
