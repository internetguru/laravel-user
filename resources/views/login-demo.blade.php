<section class="section section-login">
    <div class="row row-basic row-stretched">
        <div class="card card-login">
            <h2 class="display-6">@lang('ig-user::auth.demo.title')</h2>
            <x-ig::form :action="route('login')" :recaptcha="false" class="editable-skip">
                <input type="hidden" name="prev_url" value="{{ App\Models\User::getPreviousUrl() }}" />
                <x-ig::input type="select" name="email" :options="$users">@lang('ig-user::auth.demo.email')</x-ig::input>
                <x-ig::submit>@lang('ig-user::auth.demo.submit')</x-ig::submit>
            </x-ig::form>
        </div>
    </div>
</section>
