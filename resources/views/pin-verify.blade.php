<section class="section section-pin-verify">
    <div class="row row-basic row-stretched">
        <div class="card card-pin-verify">
            <h2 class="display-6">@lang('ig-user::auth.pin_verify.title')</h2>
            <x-ig::form :action="route('pin-login.verify.submit')" class="editable-skip">
                <x-ig-user::pin-input name="pin" />
                <x-ig::submit>@lang('ig-user::auth.pin_verify.submit')</x-ig::submit>
            </x-ig::form>
            <p class="mt-3 mb-0"><a href="{{ route('pin-login') }}">@lang('ig-user::auth.pin_verify.resend')</a></p>
            <p class="mt-3 mb-0 text-end"><a href="{{ route('login') }}">@lang('ig-user::auth.back')</a></p>
        </div>
    </div>
</section>
