@props([
    'view',
    'props' => [],
])

@include("auth::$view", $props)
