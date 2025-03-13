@extends('ig-common::layouts.email-plain')

@section('content')
@lang('ig-common::messages.email.hello')

@lang('ig-user::token_auth.intro')


<x-ig-common::components.button-plain :link="$url">{{ __('ig-user::token_auth.action') }}</x-ig-common::components.button-plain>

@lang('ig-user::token_auth.expires', ['expires' => $expires])

@endsection
