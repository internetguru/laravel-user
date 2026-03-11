<section class="section section-pin-verify">
    <div class="row row-basic row-stretched">
        <div class="card card-pin-verify">
            <h2 class="display-6">@lang('ig-user::auth.pin_verify.title')</h2>
            <x-ig::form :action="route('pin-login.verify.submit', ['email' => request()->query('email')])" class="editable-skip">
                <x-ig-user::pin-input name="pin" />
                <x-ig::submit>@lang('ig-user::auth.pin_verify.submit')</x-ig::submit>
            </x-ig::form>
            <x-ig::form :action="route('pin-login.form')" class="editable-skip d-inline">
                <input type="hidden" name="email" value="{{ request()->query('email') }}" />
                <button type="submit" class="btn btn-link">@lang('ig-user::auth.pin_verify.resend')</button>
            </x-ig::form>
            <p class="mt-3 mb-0 text-end"><a href="{{ route('login') }}">@lang('ig-user::auth.back')</a></p>
        </div>
    </div>
</section>
