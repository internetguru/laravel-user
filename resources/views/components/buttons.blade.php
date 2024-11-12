@props([
    'providers' => InternetGuru\LaravelUser\Enums\Provider::cases(),
    'action' => InternetGuru\LaravelUser\Enums\ProviderAction::LOGIN,
    'prev_url' => url()->previous(),
    'showRemember' => false,
    'disabled' => false,
])

<div class="socialite" x-data="{
    remember: false,
}">
    <div class="socialite-{{ $action }} socialite-buttons">
        @foreach ($providers as $provider)
            <a class="btn btn-primary btn-socialite-{{ $provider->value }}"
                @if(!$disabled)
                    x-bind:href="`{{ route('socialite.action', ['provider' => $provider, 'action' => $action]) }}?remember=${remember}&prev_url={{ $prev_url }}`"
                @endif
                @class(['disabled' => $disabled])
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
