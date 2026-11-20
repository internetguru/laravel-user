<?php

namespace InternetGuru\LaravelUser\Traits;

trait WithSocialite // @phpstan-ignore trait.unused (used by the application's UserFactory)
{
    public function withSocialite($provider, $providerId, $name, $email = null)
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
