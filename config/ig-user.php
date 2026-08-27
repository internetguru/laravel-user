<?php

return [

    'login' => env('AUTH_LOGIN_ENABLED', true),

    'demo' => env('AUTH_DEMO', false),

    'merge' => env('AUTH_MERGE_ENABLED', false),

    'system_notice_role' => env('AUTH_SYSTEM_NOTICE_ROLE', 'operator'),

    'lang_domains' => collect(explode(',', env('LANG_DOMAINS', '')))
        ->filter()
        ->mapWithKeys(function (string $item) {
            [$lang, $domain] = explode(':', $item, 2);

            return [$lang => $domain];
        })
        ->toArray(),

];
