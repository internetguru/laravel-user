@php
    $providers = App\Models\User::providers()::enabledCases();
    $prev_url = App\Models\User::getPreviousUrl();
@endphp

<section class="section section-login">
    <div class="row row-basic row-stretched">
        <div class="card card-login" x-data="{
            remember: {{ request()->boolean('remember') ? 'true' : 'false' }},
            register: {{ request()->boolean('register') ? 'true' : 'false' }},
        }">
            <h2 class="display-6">@lang('ig-user::auth.login.choose_method')</h2>

            <div class="socialite socialite-buttons">
                @foreach ($providers as $provider)
                    <a @class([
                            'btn',
                            'btn-primary',
                            "btn-socialite-{$provider->value}",
                        ])
                        x-bind:href="register
                            ? `{{ route('socialite.action', ['provider' => $provider, 'action' => 'register']) }}?remember=${remember}&prev_url={{ $prev_url }}`
                            : `{{ route('socialite.action', ['provider' => $provider, 'action' => 'login']) }}?remember=${remember}&prev_url={{ $prev_url }}`"
                    >
                        <i class="{{ config("services.{$provider->value}.icon") }}"></i>
                        {{ ucfirst($provider->value) }}
                    </a>
                @endforeach
            </div>

            <x-ig::form :action="route('pin-login.form')" class="editable-skip">
                <x-ig::input type="email" name="email" autocomplete="email" required>@lang('ig-user::auth.login.email')</x-ig::input>
                <input type="hidden" name="remember" x-bind:value="remember" />
                <input type="hidden" name="register" x-bind:value="register" />
                <x-ig::submit>@lang('ig-user::auth.login.send_pin')</x-ig::submit>
            </x-ig::form>

            <x-ig::input type="checkbox" name="remember_check" id="remember_check"
                x-bind:checked="remember"
                x-on:change="remember = !remember"
            >@lang('ig-user::messages.remember_me')</x-ig::input>

            <x-ig::input type="checkbox" name="register_check" id="register_check"
                x-bind:checked="register"
                x-on:change="register = !register"
            >@lang('ig-user::auth.login.register_if_not_found')</x-ig::input>
        </div>
    </div>
</section>
