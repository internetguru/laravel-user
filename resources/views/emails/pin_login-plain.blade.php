@extends('ig-common::layouts.email-plain')

@section('content')
@lang('ig-user::pin_login.intro')


<x-ig-common::emails.button-plain :link="$loginUrl">{{ __('ig-user::pin_login.action') }}</x-ig-common::emails.button-plain>

@lang('ig-user::pin_login.pin_label')
{{ $pin }}

@lang('ig-user::pin_login.expires')


@parent
@endsection
