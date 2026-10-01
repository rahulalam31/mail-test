<?php

namespace App\Services\Blacklist;

use App\Services\Dns\DnsResolver;
use Illuminate\Support\Facades\Log;

class DnsBlacklistChecker
{
    public function __construct(
        private readonly DnsResolver $resolver,
    ) {}

    /**
     * Check all resolved IP addresses against configured DNSBLs.
     */
    public function check(
        array $aRecords,
        array $aaaaRecords
    ): array {
        $ips = $this->extractIps(
            $aRecords,
            $aaaaRecords
        );

        $listed = [];
        $errors = [];

        foreach ($ips as $ip) {
            foreach (config('dns.blacklists', []) as $blacklist) {
                try {
                    $result = $this->checkIp(
                        $ip,
                        $blacklist
                    );

                    if ($result['listed']) {
                        $listed[] = $result;
                    }
                } catch (\Throwable $e) {
                    $errors[] = [
                        'ip' => $ip,
                        'blacklist' => $blacklist,
                        'error' => $e->getMessage(),
                    ];

                    Log::warning(
                        'DNSBL check failed',
                        [
                            'ip' => $ip,
                            'blacklist' => $blacklist,
                            'exception' => $e,
                        ]
                    );
                }
            }
        }

        return [
            'status' => $this->determineStatus(
                $ips,
                $listed,
                $errors
            ),

            'listed' => $listed,

            'errors' => $errors,

            'checked_ips' => $ips,
        ];
    }

    private function checkIp(
        string $ip,
        string $blacklist
    ): array {
        $query = $this->buildQuery(
            $ip,
            $blacklist
        );

        $result = $this->resolver->safeRecords(
            $query,
            DNS_A
        );

        if (! $result['success']) {
            return [
                'ip' => $ip,
                'blacklist' => $blacklist,
                'listed' => false,
                'error' => $result['error'],
                'records' => [],
            ];
        }

        return [
            'ip' => $ip,
            'blacklist' => $blacklist,
            'listed' => ! empty($result['records']),
            'error' => null,
            'records' => $result['records'],
        ];
    }

    private function buildQuery(
        string $ip,
        string $blacklist
    ): string {
        if (filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4
        )) {
            return implode(
                '.',
                array_reverse(
                    explode('.', $ip)
                )
            ) . ".{$blacklist}";
        }

        return $this->buildIpv6Query(
            $ip,
            $blacklist
        );
    }

    private function buildIpv6Query(
        string $ip,
        string $blacklist
    ): string {
        $packed = inet_pton($ip);

        if ($packed === false) {
            throw new \InvalidArgumentException(
                "Invalid IPv6 address: {$ip}"
            );
        }

        $hex = unpack(
            'H*',
            $packed
        )[1];

        $nibbles = str_split(
            strtolower($hex)
        );

        return implode(
            '.',
            array_reverse($nibbles)
        ) . ".{$blacklist}";
    }

    private function extractIps(
        array $aRecords,
        array $aaaaRecords
    ): array {
        $ipv4 = collect($aRecords)
            ->pluck('ip');

        $ipv6 = collect($aaaaRecords)
            ->pluck('ipv6');

        return $ipv4
            ->merge($ipv6)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function determineStatus(
        array $ips,
        array $listed,
        array $errors
    ): string {
        if (! empty($listed)) {
            return 'listed';
        }

        /*
         * If we had IPs and successfully completed the checks,
         * we can safely call the domain clean.
         */
        if (! empty($ips) && empty($errors)) {
            return 'clean';
        }

        /*
         * No resolved IP or one/more DNSBL checks failed.
         * We cannot confidently say "clean".
         */
        return 'unknown';
    }
}
