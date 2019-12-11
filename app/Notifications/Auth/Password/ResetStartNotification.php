<?php

namespace App\Notifications\Auth\Password;

use App\Eloquents\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetStartNotification extends Notification
{
    use Queueable;

    /**
     * 受信者
     *
     * @var User
     */
    private $user;

    /**
     * パスワード再設定手続きを進めるための URL
     *
     * @var string
     */
    private $reset_url;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user  受信者
     * @return void
     */
    public function __construct(User $user, string $reset_url)
    {
        $this->user = $user;
        $this->reset_url = $reset_url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('パスワード再設定のご案内')
            ->greeting('パスワード再設定のご案内')
            ->line($this->user->name . ' 様')
            ->line('「' . config('app.name') . '」にログインするためのパスワードを変更するには、以下のボタンを選んでください。')
            ->action('パスワードを変更する', $this->reset_url)
            ->line('このメールに心当たりがない場合、このメールはそのまま破棄してください。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
