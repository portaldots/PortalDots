<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Documents\DocumentConfirmationDecided;
use App\Events\Documents\DocumentConfirmationRequested;
use App\Events\Documents\DocumentConfirmationReset;
use App\Events\Forms\AnswerAccepted;
use App\Events\Forms\AnswerReturned;
use App\Events\Forms\AnswerSubmitted;
use App\Events\Forms\FormDueDateChanged;
use App\Events\Forms\FormSent;
use App\Listeners\Threads\AppendThreadEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AnswerSubmitted::class => [
            AppendThreadEventListener::class,
        ],
        AnswerReturned::class => [
            AppendThreadEventListener::class,
        ],
        AnswerAccepted::class => [
            AppendThreadEventListener::class,
        ],
        DocumentConfirmationRequested::class => [
            AppendThreadEventListener::class,
        ],
        DocumentConfirmationDecided::class => [
            AppendThreadEventListener::class,
        ],
        DocumentConfirmationReset::class => [
            AppendThreadEventListener::class,
        ],
        FormSent::class => [
            AppendThreadEventListener::class,
        ],
        FormDueDateChanged::class => [
            AppendThreadEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
