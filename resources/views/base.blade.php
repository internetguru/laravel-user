@props([
    'view',
    'props' => [],
])

<h1>@lang("ig-user::layouts.$view.title")</h1>
<p>@lang("ig-user::layouts.$view.description")</p>

@include("ig-user::$view", $props)
