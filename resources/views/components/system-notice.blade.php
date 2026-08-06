@props(['sysMessage' => null])

@php
    $user = auth()->user();
@endphp

@if (config('app.demo'))
    <x-ig::demo-info />
@elseif (config('app.readonly', false))
    <x-ig::read-only-mode-info />
@elseif ($sysMessage)
    <div class="container-fluid alert alert-info mb-0 rounded-0" data-testid="sys-message">
        <p class="my-0">
            {!! $sysMessage !!}
        </p>
    </div>
@elseif ($user && $user->socialites()->count() === 0)
    <div class="container-fluid alert alert-info mb-0 rounded-0" data-testid="identity-link">
        <p class="my-0">
            {!! __('ig-user::layouts.identity-link', ['url' => route('users.show', $user)]) !!}
        </p>
    </div>
@elseif ($user && $user->role->level() >= $user::roles()::OPERATOR->level())
    <div class="container-fluid alert alert-info mb-0 rounded-0 use-app" data-testid="use-app">
        <p class="my-0">
            {!! __('ig-user::layouts.use-app') !!}
        </p>
    </div>
@elseif (auth()->guest())
    <div class="container-fluid alert alert-info mb-0 rounded-0 d-none app-login" data-testid="app-login">
        <p class="my-0">
            @lang('ig-user::layouts.app-login') <a href="{{ route('login') }}">@lang('ig-user::auth.login-register')</a>
        </p>
    </div>
@endif
