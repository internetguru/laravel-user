<section class="section section-user-detail">
    <div class="row row-stretched">
        <div class="card col col-narrow col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.information')</h2>
            <dl class="mb-0">
                <dt>@lang('ig-user::user.name')</dt>
                <dd>{{ $user->name }} <a class="ms-1" href="{{ route('logout') }}">@lang('layouts.header.logout')</a></dd>
                <dt>@lang('ig-user::user.email')</dt>
                <dd>{{ $user->email }}</dd>
                <dt>@lang('ig-user::user.role')</dt>
                <dd>@lang('ig-user::user.roles.' . $user->role->value)</dd>
            </dl>
        </div>
        <div class="card col col-narrow col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.authentication')</h2>
            {{-- TODO list providers --}}
            {{-- TODO allow to connect any provider --}}
        </div>
    </div>
</section>
