<?php

namespace InternetGuru\LaravelUser\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use InternetGuru\LaravelCommon\Notifications\BaseNotification;
use InternetGuru\LaravelUser\Models\PinLogin;

class PinLoginNotification extends BaseNotification
{
    public function __construct(
        public PinLogin $pinLogin
    ) {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('pin-login.verify', ['email' => $this->pinLogin->user->email]);
        $formattedPin = User::formatPin($this->pinLogin->pin);

        return parent::toMail($notifiable)
            ->subject(__('ig-user::pin_login.subject'))
            ->view(
                [
                    'html' => 'ig-user::emails.pin_login-html',
                    'text' => 'ig-user::emails.pin_login-plain',
                ],
                [
                    'loginUrl' => $url,
                    'pin' => $formattedPin,
                    'expires' => $this->pinLogin->expires_at->diffForHumans(),
                ]
            );
    }
}
