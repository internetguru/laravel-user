@props([
    'view',
    'props' => [],
])

<x-dynamic-component
    component="auth::{{ $view }}"
    @foreach ($props as $key => $value)
        {{ $key }}="{{ $value }}"
    @endforeach
/>
