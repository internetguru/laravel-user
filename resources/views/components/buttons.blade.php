@props([
    'providers' => App\Models\User::providers()::enabledCases(),
    'action' => InternetGuru\LaravelUser\Enums\ProviderAction::LOGIN,
    'prev_url' => App\Models\User::getPreviousUrl(),
    'disabled' => false,
])

<div class="socialite">
    <div class="socialite-{{ $action }} socialite-buttons">
        @foreach ($providers as $provider)
            <a @class([
                    'btn',
                    'btn-primary',
                    "btn-socialite-{$provider->value}",
                    'disabled' => $disabled,
                ])
                @if(! $disabled)
                    href="{{ route('socialite.action', ['provider' => $provider, 'action' => $action]) }}?prev_url={{ $prev_url }}"
                @endif
            >
                <x-ig-user::provider-icon :provider="$provider->value" />
                {{ ucfirst($provider->value) }}
            </a>
        @endforeach
    </div>
</div>
