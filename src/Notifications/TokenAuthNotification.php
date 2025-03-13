<?php

namespace InternetGuru\LaravelUser\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use InternetGuru\LaravelUser\Models\TokenAuth;

class TokenAuthNotification extends Notification
{
    use Queueable;

    public function __construct(public TokenAuth $tokenAuth) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $url = URL::signedRoute('token-auth.callback', ['token' => $this->tokenAuth->token]);

        return (new MailMessage)
            ->subject(__('ig-user::token_auth.subject'))
            ->view(
                [
                    'html' => 'ig-user::emails.token_auth',
                    'text' => 'ig-user::emails.token_auth_plain',
                ],
                [
                    'url' => $url,
                    'expires' => $this->tokenAuth->expires_at->diffForHumans(),
                ]
            );
    }
}
