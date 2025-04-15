<section class="section section-login">
    <div class="row row-basic row-stretched">
        @if (config('app.demo'))
            <div class="card card-login">
                <h2 class="display-6">@lang('ig-user::auth.demo.title')</h2>
                <x-ig::form :action="route('login')" :recaptcha="false" class="editable-skip">
                    <x-ig::input type="select" name="email" :options="$users">@lang('ig-user::auth.demo.email')</x-ig::input>
                    <x-ig::submit>@lang('ig-user::auth.demo.submit')</x-ig::submit>
                </x-ig::form>
            </div>
        @else
            <div class="card card-login">
                <h2 class="display-6">@lang('ig-user::auth.login.title')</h2>
                <x-ig-user::buttons action="login" :showRemember="true" />
                <p class="mt-3 mb-0 text-end"><a href="{{ route('token_auth') }}">@lang('ig-user::layouts.token-auth.title')</a></p>
                <p class="mt-1 mb-0 text-end"><a href="{{ route('register') }}">@lang('ig-user::layouts.register.title')</a></p>
            </div>
        @endif
    </div>
</section>
