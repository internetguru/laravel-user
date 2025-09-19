@extends('ig-common::layouts.email-html')
@php
    $buttonText = __('ig-user::token_auth.action');
@endphp

@section('content')
<p>@lang('ig-user::token_auth.intro')</p>

<x-ig-common::emails.button-html :link="$url">{{ $buttonText }}</x-ig-common::emails.button-html>

<p>@lang('ig-user::token_auth.expires')</p>

@parent
@endsection

@section('footer')
<x-ig-common::emails.subcopy-html :link="$url" :text="$buttonText" />
@parent
@endsection
