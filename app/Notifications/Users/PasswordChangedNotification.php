<?php

namespace App\Notifications\Users;

use App\Eloquents\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    /**
     * 受信者
     *
     * @var User
     */
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user  受信者
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        return (new MailMessage)
                    ->subject('パスワードが変更されました')
                    ->greeting('パスワードが変更されました')
                    ->line($this->user->name. ' 様')
                    ->line('最近、'. config('app.name'). 'にログインするためのパスワードが変更されました。この変更がご自身によるものである場合、このメールは無視してください。')
                    ->line('もし、このパスワード変更に心当たりがない場合、第三者が不正にパスワードを変更した可能性があります。
                        その場合、ログイン画面にある「パスワードを忘れた場合」からパスワードのリセットをお願いします。');
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
