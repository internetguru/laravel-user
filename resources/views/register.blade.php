<div class="section section-register">
    <div class="row row-basic row-stretched">
        <div class="card card-register">
            <h2 class="display-6">@lang('ig-user::auth.register.title')</h2>
            <x-ig-user::buttons action="register" :showRemember="true" />
            <p class="mt-3 mb-1 text-end"><a href="{{ route('register.email') }}">@lang('ig-user::auth.register-email.title')</a></p>
            <p class="mb-0 text-end"><a href="{{ route('login') }}">@lang('ig-user::auth.back') {{ strtolower(__('ig-user::auth.login.title')) }}</a></p>
        </div>
    </div>
</div>
