<section class="section section-pin-login">
    <div class="row row-basic row-stretched">
        <div class="card card-pin-login">
            <h2 class="display-6">@lang('ig-user::auth.pin_login.title')</h2>
            <x-ig::form :action="route('pin-login.form')" class="editable-skip">
                <x-ig::input type="email" name="email" autocomplete="email" required>@lang('ig-user::auth.pin_login.email')</x-ig::input>
                <x-ig::submit>@lang('ig-user::auth.pin_login.submit')</x-ig::submit>
            </x-ig::form>
            <p class="mt-3 mb-0 text-end"><a href="{{ route('login') }}">@lang('ig-user::auth.back')</a></p>
        </div>
    </div>
</section>
