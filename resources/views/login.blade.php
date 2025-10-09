<section class="section section-login">
    <div class="row row-basic row-stretched">
        <div class="card card-login">
            <h2 class="display-6">@lang('ig-user::auth.login.title')</h2>
            <x-ig-user::buttons action="login" :showRemember="true" />
            <p class="mt-3 mb-0 text-end"><a href="{{ route('token_auth') }}">@lang('ig-user::layouts.token-auth.title')</a></p>
            <p class="mt-1 mb-0 text-end"><a href="{{ route('register') }}">@lang('ig-user::layouts.register.title')</a></p>
        </div>
    </div>
</section>
