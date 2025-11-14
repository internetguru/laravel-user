<?php

namespace InternetGuru\LaravelUser\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use InternetGuru\LaravelCommon\Notifications\BaseNotification;
use InternetGuru\LaravelUser\Models\TokenAuth;

class TokenAuthNotification extends BaseNotification
{
    public function __construct(
        public TokenAuth $tokenAuth
    ) {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::signedRoute('token-auth.callback', ['token' => $this->tokenAuth->token]);

        return parent::toMail($notifiable)
            ->subject(__('ig-user::token_auth.subject'))
            ->view(
                [
                    'html' => 'ig-user::emails.token_auth-html',
                    'text' => 'ig-user::emails.token_auth-plain',
                ],
                [
                    'loginUrl' => $url,
                    'expires' => $this->tokenAuth->expires_at->diffForHumans(),
                ]
            );
    }
}
