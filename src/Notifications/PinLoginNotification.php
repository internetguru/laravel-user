<?php

namespace InternetGuru\LaravelUser\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use InternetGuru\LaravelCommon\Mail\MailMessage as IgMailMessage;
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
        $formattedPin = User::formatPin($this->pinLogin->pin);

        /** @var IgMailMessage $message */
        $message = parent::toMail($notifiable);

        return $message
            ->withoutRefNumber()
            ->subject(__('ig-user::pin_login.subject'))
            ->view(
                [
                    'html' => 'ig-user::emails.pin_login-html',
                    'text' => 'ig-user::emails.pin_login-plain',
                ],
                [
                    'pin' => $formattedPin,
                    'expires' => $this->pinLogin->expires_at->diffForHumans(),
                ]
            );
    }
}
