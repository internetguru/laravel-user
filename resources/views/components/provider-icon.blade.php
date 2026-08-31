@props(['provider'])

{{--
    Renders the provider's own brand mark when this package ships one, and falls back to the
    Font Awesome class configured in `services.<provider>.icon` for every other provider.
--}}
@php
    $logo = 'icons.' . $provider;
@endphp

@if (view()->exists('ig-user::components.' . $logo))
    <x-dynamic-component :component="'ig-user::' . $logo" :attributes="$attributes" />
@else
    <i {{ $attributes->merge(['class' => config("services.{$provider}.icon")]) }}></i>
@endif
