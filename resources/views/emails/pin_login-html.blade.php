@extends('ig-common::layouts.email-html')

@section('content')
<p>@lang('ig-user::pin_login.intro')</p>

<p style="font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align: center; padding: 16px; background: #f4f4f4; border-radius: 8px; font-family: monospace;">{{ $pin }}</p>

<p>@lang('ig-user::pin_login.login_page_label')</p>
<p><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

<p>@lang('ig-user::pin_login.expires')</p>

@parent
@endsection
