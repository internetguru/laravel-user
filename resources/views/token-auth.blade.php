<section class="section section-token-auth">
    <div class="row row-basic row-stretched">
        <div class="card card-token-auth">
            <h2 class="display-6">@lang('ig-user::auth.token_auth.title')</h2>
            <x-ig::form :action="route('token-auth.form')" :recaptcha="false">
                <x-ig::input type="email" name="email" required>@lang('ig-user::auth.token_auth.email')</x-ig::input>
                <x-ig::submit>@lang('ig-user::auth.token_auth.submit')</x-ig::submit>
            </x-ig::form>
            <p class="mt-3 mb-0 text-end"><a href="{{ route('login') }}">@lang('ig-user::auth.back') {{ strtolower(__('ig-user::auth.login.title')) }}</a></p>
        </div>
    </div>
</section>
