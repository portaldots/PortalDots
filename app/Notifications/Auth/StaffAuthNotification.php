<?php

namespace App\Notifications\Auth;

use App\Eloquents\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StaffAuthNotification extends Notification
{
    use Queueable;

    /**
     * 受信者
     *
     * @var User
     */
    private $user;

    /**
     * スタッフ認証コード
     *
     * @var string
     */
    private $verify_code;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user  受信者
     * @param  string  $verify_code  認証コード
     * @return void
     */
    public function __construct(User $user, string $verify_code)
    {
        $this->user = $user;
        $this->verify_code = $verify_code;
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
            ->subject('スタッフ認証')
            ->greeting('スタッフ認証')
            ->line($this->user->name . ' 様')
            ->line('スタッフモードにアクセスするには、以下の「認証コード」をスタッフ認証ページで入力してください。')
            ->line('認証コード : ' . $this->verify_code);
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
