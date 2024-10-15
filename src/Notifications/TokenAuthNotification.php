<?php

namespace InternetGuru\LaravelSocialite\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use InternetGuru\LaravelSocialite\Models\TokenAuth;

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
        $url = URL::signedRoute('socialite.token-auth.callback', ['token' => $this->tokenAuth->token]);

        return (new MailMessage)
            ->subject(__('socialite::messages.token_auth.subject', ['url' => config('app.url')]))
            ->action(__('socialite::messages.token_auth.action'), $url)
            ->line(__('socialite::messages.token_auth.expires', ['expires' => $this->tokenAuth->expires_at->diffForHumans()]));
    }
}
