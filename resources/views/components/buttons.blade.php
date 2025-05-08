@props([
    'providers' => InternetGuru\LaravelUser\Enums\Provider::enabledCases(),
    'action' => InternetGuru\LaravelUser\Enums\ProviderAction::LOGIN,
    'prev_url' => (session('prevPage') ?? url()->previous()) === url()
        ? url()->previous()
        : (session('prevPage') ?? url()->previous()),
    'showRemember' => false,
    'disabled' => false,
])

<div class="socialite" x-data="{
    remember: false,
}">
    <div class="socialite-{{ $action }} socialite-buttons">
        @foreach ($providers as $provider)
            <a @class([
                    'btn',
                    'btn-primary',
                    "btn-socialite-{$provider->value}",
                    'disabled' => $disabled,
                ])
                @if(! $disabled)
                    x-bind:href="`{{ route('socialite.action', ['provider' => $provider, 'action' => $action]) }}?remember=${remember}&prev_url={{ $prev_url }}`"
                @endif
            >
                <i class="{{ config("services.{$provider->value}.icon") }}"></i>
                {{ ucfirst($provider->value) }}
            </a>
        @endforeach
    </div>

    @if ($showRemember)
        <x-ig::input type="checkbox" name="remember" id="remember"
            x-on:change="remember = !remember"
        >@lang('ig-user::messages.remember_me')</x-ig::input>
    @endif
</div>
