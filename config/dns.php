<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DNS Settings
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('DNS_TIMEOUT', 3),

    'retries' => (int) env('DNS_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | DNS Blacklists
    |--------------------------------------------------------------------------
    |
    | Keep this configurable because DNSBL providers can change their
    | policies, availability, or query requirements.
    |
    */

    'blacklists' => [
        'zen.spamhaus.org',
        'bl.spamcop.net',
        'b.barracudacentral.org',
        'dnsbl.sorbs.net',
    ],

    /*
    |--------------------------------------------------------------------------
    | Common DKIM selectors
    |--------------------------------------------------------------------------
    |
    | DKIM selectors cannot reliably be discovered automatically.
    | These are heuristic selectors only.
    |
    */

    'dkim_selectors' => [
        'default',
        'selector1',
        'selector2',
        'google',
        'k1',
        'dkim',
        'mail',
        's1',
        's2',
    ],

];
