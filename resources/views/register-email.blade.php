<div class="section section-register">
    <div class="row row-basic row-stretched">
        <div class="card card-register">
            <h2 class="display-6">@lang('ig-user::auth.register-email.title')</h2>
            <x-ig::form action="register-email" :route="route('register.email.handle')">
                <x-ig::input type="text" name="name" required>@lang('ig-user::auth.register-email.name')</x-ig::input>
                <x-ig::input type="email" name="email" required>@lang('ig-user::auth.register-email.email')</x-ig::input>
                <x-ig::submit>@lang('ig-user::auth.register-email.submit')</x-ig::submit>
            </x-ig::form>
            <p class="mb-0 text-end"><a href="{{ route('register') }}">@lang('ig-user::auth.back') {{ strtolower(__('ig-user::auth.register.title')) }}</a></p>
            <p class="mb-0 text-end"><a href="{{ route('login') }}">@lang('ig-user::auth.back') {{ strtolower(__('ig-user::auth.login.title')) }}</a></p>
        </div>
    </div>
</div>
