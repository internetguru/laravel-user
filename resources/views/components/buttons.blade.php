@props([
    'providers' => InternetGuru\LaravelSocialite\Enums\Provider::cases(),
    'action' => InternetGuru\LaravelSocialite\Enums\ProviderAction::LOGIN,
    'prev_url' => url()->previous(),
    'showRemember' => false,
])

<div class="socialite" x-data="{
    remember: false,
}">
    <div class="socialite-{{ $action }}">
        @foreach ($providers as $provider)
            <a class="btn btn-primary btn-socialite-{{ $provider->value }}" x-bind:href="`{{
                route('socialite.action', ['provider' => $provider, 'action' => $action])
            }}?remember=${remember}&prev_url={{ $prev_url }}`">
                <i class="{{ config("services.{$provider->value}.icon") }}"></i>
                {{ $provider }}
            </a>
        @endforeach
    </div>

    @if ($showRemember)
        <x-ig::input type="checkbox" name="remember" id="remember"
            x-on:change="remember = !remember"
        >@lang('socialite::messages.remember_me')</x-ig::input>
    @endif
</div>
