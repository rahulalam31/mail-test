<?php

namespace App\Services\Dns;

class DkimChecker
{
    public function __construct(
        private readonly DnsResolver $resolver,
    ) {}

    public function check(
        string $domain,
        ?string $selector = null
    ): array {
        $selectors = $selector
            ? [$selector]
            : config('dns.dkim_selectors', []);

        $results = [];

        foreach ($selectors as $selectorName) {
            $selectorName = trim(
                strtolower($selectorName)
            );

            if ($selectorName === '') {
                continue;
            }

            $host = "{$selectorName}._domainkey.{$domain}";

            $records = $this->resolver->txt($host);

            if (empty($records)) {
                continue;
            }

            $results[] = [
                'selector' => $selectorName,
                'host' => $host,
                'records' => collect($records)
                    ->pluck('txt')
                    ->values()
                    ->all(),
            ];
        }

        return $results;
    }
}
