@extends('ig-common::layouts.email-plain')

@section('content')
@lang('ig-user::token_auth.intro')


<x-ig-common::emails.button-plain :link="$url">{{ __('ig-user::token_auth.action') }}</x-ig-common::emails.button-plain>

@lang('ig-user::token_auth.expires')

@parent
@endsection
