<section class="section section-login">
    <div class="row row-basic row-stretched">
        @if (config('auth.demo'))
            <div class="card">
                <h2 class="display-6">@lang('ig-user::auth.demo.title')</h2>
                <x-ig::form :action="route('auth.login')" :recaptcha="false">
                    <x-ig::input type="select" name="email" :options="$users">@lang('ig-user::auth.demo.email')</x-ig::input>
                    <x-ig::submit>@lang('ig-user::auth.demo.submit')</x-ig::submit>
                </x-ig::form>
            </div>
        @else
            <div class="card">
                <h2 class="display-6">@lang('ig-user::auth.login.title')</h2>
                <x-ig-user::buttons action="login" :showRemember="true" />
                <p class="mt-3 mb-0 text-end"><a href="{{ route('auth.token_auth') }}">@lang('ig-user::auth.token_auth.title')</a></p>
                <p class="mt-1 mb-0 text-end"><a href="{{ route('auth.register') }}">@lang('ig-user::auth.register.title')</a></p>
            </div>
        @endif
    </div>
</section>
