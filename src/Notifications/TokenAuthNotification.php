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
            ->line(__('ig-user::token_auth.intro'))
            ->action(__('ig-user::token_auth.action'), $url)
            ->line(__('ig-user::token_auth.expires', ['expires' => $this->tokenAuth->expires_at->diffForHumans()]));
    }
}
