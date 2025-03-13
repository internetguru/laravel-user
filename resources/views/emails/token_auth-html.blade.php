@extends('ig-common::layouts.email-html')

@php
$buttonText = __('ig-user::token_auth.action');
@endphp

@section('content')
<p>@lang('ig-common::messages.email.hello')<br>@lang('ig-user::token_auth.intro')</p>

<x-ig-common::components.button-html :link="$url">{{ $buttonText }}</x-ig-common::components.button-html>

<p>@lang('ig-user::token_auth.expires', ['expires' => $expires])</p>
@endsection

@section('footer')
<x-ig-common::components.subcopy-html :link="$url" :text="$buttonText" />
@endsection
