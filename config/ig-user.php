<?php

return [

    'login' => env('AUTH_LOGIN_ENABLED', true),

    'demo' => env('AUTH_DEMO', false),

    'merge' => env('AUTH_MERGE_ENABLED', false),

    // Up to this many merge candidates travel inside the user detail page and are filtered
    // in the browser. Above it the picker searches the server instead, so the page never
    // carries the whole user table.
    'merge_inline_limit' => (int) env('AUTH_MERGE_INLINE_LIMIT', 100),

    'system_notice_role' => env('AUTH_SYSTEM_NOTICE_ROLE', 'operator'),

    'lang_domains' => collect(explode(',', env('LANG_DOMAINS', '')))
        ->filter()
        ->mapWithKeys(function (string $item) {
            [$lang, $domain] = explode(':', $item, 2);

            return [$lang => $domain];
        })
        ->toArray(),

];
