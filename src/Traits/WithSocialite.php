<?php

namespace InternetGuru\LaravelUser\Traits;

trait WithSocialite
{
    public function withSocialite($provider, $providerId, $name)
    {
        return $this->afterCreating(function ($user) use ($provider, $providerId, $name) {
            $user->socialites()->create([
                'provider' => $provider,
                'provider_id' => $providerId,
                'name' => $name,
            ]);
        });
    }
}
