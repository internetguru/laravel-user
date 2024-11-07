@props([
    'view',
    'props' => [],
    'componentName' => "auth::{{ $view }}"
])

<x-dynamic-component
    :component="$componentName"
    @foreach ($props as $key => $value)
        {{ $key }}="{{ $value }}"
    @endforeach
/>
