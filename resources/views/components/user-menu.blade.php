<div class="dropdown">
    <a class="dropdown-toggle text-white" href="{{ route('users.show', auth()->user()) }}" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-fw {{ auth()->user()->role->icon() }}"></i>
    </a>
    <ul class="dropdown-menu" aria-labelledby="userMenu">
        <li><span class="dropdown-item-text">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span></li>
        <li><a class="dropdown-item" style="border:none;" href="{{ route('users.show', auth()->user()) }}">@lang('user.title')</a></li>
        <li><a class="dropdown-item" style="border:none;" href="{{ route('logout') }}">{{ Str::ucfirst(__('ig-user::user.logout')) }}</a></li>
    </ul>
</div>
