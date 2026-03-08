@extends('ig-common::layouts.email-html')
@php
    $buttonText = __('ig-user::pin_login.action');
@endphp

@section('content')
<p>@lang('ig-user::pin_login.intro')</p>

<x-ig-common::emails.button-html :link="$loginUrl">{{ $buttonText }}</x-ig-common::emails.button-html>

<p>@lang('ig-user::pin_login.pin_label')</p>
<p style="font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align: center; padding: 16px; background: #f4f4f4; border-radius: 8px; font-family: monospace;">{{ $pin }}</p>

<p>@lang('ig-user::pin_login.expires')</p>

@parent
@endsection

@section('footer')
<x-ig-common::emails.subcopy-html :link="$loginUrl" :text="$buttonText" />
@parent
@endsection
