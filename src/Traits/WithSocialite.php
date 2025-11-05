<?php

namespace InternetGuru\LaravelUser\Traits;

trait WithSocialite
{
    public function withSocialite($provider, $providerId, $name, $email)
    {
        return $this->afterCreating(function ($user) use ($provider, $providerId, $name, $email) {
            $user->socialites()->create([
                'provider' => $provider,
                'provider_id' => $providerId,
                'name' => $name,
                'email' => $email,
            ]);
        });
    }
}
