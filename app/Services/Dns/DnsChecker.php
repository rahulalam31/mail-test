<?php

namespace App\Services\Dns;

class DnsChecker
{
    public function __construct(
        private readonly DnsResolver $resolver,
    ) {}

    public function check(string $domain): array
    {
        $aResult = $this->resolver->safeRecords(
            $domain,
            DNS_A
        );

        $aaaaResult = $this->resolver->safeRecords(
            $domain,
            DNS_AAAA
        );

        $mxResult = $this->resolver->safeRecords(
            $domain,
            DNS_MX
        );

        $txtResult = $this->resolver->safeRecords(
            $domain,
            DNS_TXT
        );

        $nsResult = $this->resolver->safeRecords(
            $domain,
            DNS_NS
        );

        $cnameResult = $this->resolver->safeRecords(
            $domain,
            DNS_CNAME
        );

        $a = $aResult['records'];
        $aaaa = $aaaaResult['records'];
        $mx = $mxResult['records'];
        $txt = $txtResult['records'];
        $ns = $nsResult['records'];
        $cname = $cnameResult['records'];

        return [
            'a' => $a,
            'aaaa' => $aaaa,

            'mx' => $this->normalizeMx($mx),

            'txt' => $txt,
            'ns' => $ns,
            'cname' => $cname,

            'spf' => $this->extractSpf($txt),

            'dmarc' => $this->extractDmarc($domain),

            'ptr' => $this->extractPtr([
                ...$a,
                ...$aaaa,
            ]),

            'errors' => array_values(array_filter([
                'a' => $aResult['error'],
                'aaaa' => $aaaaResult['error'],
                'mx' => $mxResult['error'],
                'txt' => $txtResult['error'],
                'ns' => $nsResult['error'],
                'cname' => $cnameResult['error'],
            ])),
        ];
    }

    private function normalizeMx(array $records): array
    {
        return collect($records)
            ->map(function (array $record) {
                return [
                    'host' => rtrim(
                        $record['target'] ?? '',
                        '.'
                    ),
                    'priority' => $record['pri'] ?? null,
                ];
            })
            ->sortBy('priority')
            ->values()
            ->all();
    }

    private function extractSpf(array $records): array
    {
        $value = collect($records)
            ->pluck('txt')
            ->first(function ($txt) {
                return str_starts_with(
                    strtolower(trim($txt)),
                    'v=spf1'
                );
            });

        return [
            'present' => $value !== null,
            'value' => $value,
        ];
    }

    private function extractDmarc(string $domain): array
    {
        $result = $this->resolver->safeRecords(
            "_dmarc.{$domain}",
            DNS_TXT
        );

        if (! $result['success']) {
            return [
                'present' => false,
                'value' => null,
                'error' => $result['error'],
            ];
        }

        $value = collect($result['records'])
            ->pluck('txt')
            ->first(function ($txt) {
                return str_starts_with(
                    strtolower(trim($txt)),
                    'v=dmarc1'
                );
            });

        return [
            'present' => $value !== null,
            'value' => $value,
            'error' => null,
        ];
    }

    private function extractPtr(array $records): array
    {
        $result = [];

        foreach ($records as $record) {
            $ip = $record['ip']
                ?? $record['ipv6']
                ?? null;

            if (! $ip) {
                continue;
            }

            $hostname = gethostbyaddr($ip);

            $result[] = [
                'ip' => $ip,
                'hostname' => $hostname !== $ip
                    ? $hostname
                    : null,
            ];
        }

        return $result;
    }
}
