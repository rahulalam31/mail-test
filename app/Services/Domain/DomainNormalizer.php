<?php

namespace App\Services\Domain;

class DomainNormalizer
{
    public function normalize(string $input): ?string
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Email address
        |--------------------------------------------------------------------------
        */

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $domain = strrchr($input, '@');

            if ($domain === false) {
                return null;
            }

            $input = substr($domain, 1);
        }

        /*
        |--------------------------------------------------------------------------
        | Remove protocol
        |--------------------------------------------------------------------------
        */

        $input = preg_replace(
            '#^[a-z][a-z0-9+\-.]*://#i',
            '',
            $input
        );

        /*
        |--------------------------------------------------------------------------
        | Remove path/query/fragment
        |--------------------------------------------------------------------------
        */

        $input = explode('/', $input)[0];
        $input = explode('?', $input)[0];
        $input = explode('#', $input)[0];

        /*
        |--------------------------------------------------------------------------
        | Remove port
        |--------------------------------------------------------------------------
        */

        if (str_contains($input, ':')) {
            $input = explode(':', $input)[0];
        }

        $input = strtolower(
            trim($input, " \t\n\r\0\x0B.")
        );

        /*
        |--------------------------------------------------------------------------
        | Validate hostname
        |--------------------------------------------------------------------------
        */

        if (! filter_var(
            $input,
            FILTER_VALIDATE_DOMAIN,
            FILTER_FLAG_HOSTNAME
        )) {
            return null;
        }

        return $input;
    }
}
