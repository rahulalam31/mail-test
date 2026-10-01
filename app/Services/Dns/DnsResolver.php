<?php

namespace App\Services\Dns;

use RuntimeException;
use Throwable;

class DnsResolver
{
    public function records(string $hostname, int $type): array
    {
        $attempts = max(1, (int) config('dns.retries', 2) + 1);

        $lastError = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $records = dns_get_record($hostname, $type);

                if ($records === false) {
                    throw new RuntimeException(
                        "DNS lookup failed for {$hostname}."
                    );
                }

                return $records;
            } catch (Throwable $e) {
                $lastError = $e;

                if ($attempt < $attempts) {
                    usleep(100_000 * $attempt);
                }
            }
        }

        throw new RuntimeException(
            "DNS lookup failed for {$hostname} after {$attempts} attempts.",
            previous: $lastError
        );
    }

    public function safeRecords(string $hostname, int $type): array
    {
        try {
            return [
                'success' => true,
                'records' => $this->records($hostname, $type),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'records' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function a(string $domain): array
    {
        return $this->records($domain, DNS_A);
    }

    public function aaaa(string $domain): array
    {
        return $this->records($domain, DNS_AAAA);
    }

    public function mx(string $domain): array
    {
        return $this->records($domain, DNS_MX);
    }

    public function txt(string $domain): array
    {
        return $this->records($domain, DNS_TXT);
    }

    public function ns(string $domain): array
    {
        return $this->records($domain, DNS_NS);
    }

    public function cname(string $domain): array
    {
        return $this->records($domain, DNS_CNAME);
    }
}
