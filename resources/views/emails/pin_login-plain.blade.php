@extends('ig-common::layouts.email-plain')

@section('content')
@lang('ig-user::pin_login.intro')

{{ $pin }}

@lang('ig-user::pin_login.expires')
@parent
@endsection
