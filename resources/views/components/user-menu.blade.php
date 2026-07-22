@auth
    <div class="dropdown">
        <button class="btn dropdown-toggle text-white" href="{{ route('users.show', auth()->user()) }}"
            role="button" id="userMenu" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
            title="@lang('ig-user::user.detail')">
            <i class="fas fa-fw {{ auth()->user()->role->icon() }}"></i>
        </button>
        <ul class="dropdown-menu" aria-labelledby="userMenu">
            <li><span class="dropdown-item-text">{{ auth()->user()->name }}</span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" style="border:none;" href="{{ route('users.show', auth()->user()) }}">@lang('ig-user::user.detail')</a></li>
        </ul>
    </div>
@elseif (config('ig-user.login', true))
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
