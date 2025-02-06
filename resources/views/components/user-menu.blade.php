<div class="dropdown">
    <a class="dropdown-toggle text-white" href="{{ route('users.show', auth()->user()) }}" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
        @switch(auth()->user()->role)
            @case(Role::ADMIN)
                <i class="fa-solid fa-fw fa-user-shield"></i>
                @break
            @case(Role::MANAGER)
                <i class="fa-solid fa-fw fa-user-tie"></i>
                @break
            @case(Role::MANAGER)
            @case(Role::OPERATOR)
                <i class="fa-solid fa-fw fa-user-nurse"></i>
                @break
            @case(Role::PENDING)
                <i class="fa-solid fa-fw fa-user-clock"></i>
                @break
            @default
                <i class="fa-solid fa-fw fa-user"></i>
        @endswitch
    </a>
    <ul class="dropdown-menu" aria-labelledby="userMenu">
        <li><span class="dropdown-item-text">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span></li>
        <li><a class="dropdown-item" style="border:none;" href="{{ route('users.show', auth()->user()) }}">@lang('user.title')</a></li>
        <li><a class="dropdown-item" style="border:none;" href="{{ route('logout') }}">{{ Str::ucfirst(__('ig-user::user.logout')) }}</a></li>
    </ul>
</div>
