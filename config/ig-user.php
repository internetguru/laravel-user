<?php

return [

    'login' => env('AUTH_LOGIN_ENABLED', true),

    'demo' => env('AUTH_DEMO', false),

    'lang_domains' => collect(explode(',', env('LANG_DOMAINS', '')))
        ->filter()
        ->mapWithKeys(function (string $item) {
            [$lang, $domain] = explode(':', $item, 2);

            return [$lang => $domain];
        })
        ->toArray(),

];
