@auth
    <div class="dropdown">
        <a class="dropdown-toggle text-white" href="{{ route('users.show', auth()->user()) }}" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-fw {{ auth()->user()->role->icon() }}"></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="userMenu">
            <li><span class="dropdown-item-text">{{ auth()->user()->name }} ({{ auth()->user()->role->translation() }})</span></li>
            <li><a class="dropdown-item" style="border:none;" href="{{ route('users.show', auth()->user()) }}">@lang('ig-user::user.detail')</a></li>
            <li><hr class="dropdown-divider"></li>
            <li class="logout"><a class="dropdown-item" style="border:none;" href="{{ route('logout') }}">{{ Str::ucfirst(__('ig-user::user.logout')) }}</a></li>
        </ul>
    </div>
@else
    <div>
        @if (url()->current() !== route('login'))
            <a href="{{ route('login') }}">
        @else
            <a>
        @endif
                @lang('ig-user::auth.login-register')
            </a>
    </div>
@endauth
