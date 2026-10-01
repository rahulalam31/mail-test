<?php

namespace App\Services\MailProvider;

class MailProviderDetector
{
    public function detect(
        string $domain,
        array $mxRecords,
        array $txtRecords = []
    ): array {
        if (empty($mxRecords)) {
            return [
                'provider' => 'not_detected',
                'status' => 'not_detected',
                'evidence' => [
                    'No MX records were found for this domain.',
                ],
            ];
        }

        $hosts = collect($mxRecords)
            ->pluck('host')
            ->filter()
            ->map(fn($host) => strtolower(rtrim($host, '.')))
            ->values();

        $googleMatches = $hosts
            ->filter(fn($host) => $this->isGoogleMx($host))
            ->values();

        if ($googleMatches->isNotEmpty()) {
            return [
                'provider' => 'google',
                'status' => 'detected',
                'evidence' => [
                    'Google Workspace mail infrastructure detected.',
                    'Matching MX: ' . $googleMatches->implode(', '),
                ],
            ];
        }

        $microsoftMatches = $hosts
            ->filter(fn($host) => $this->isMicrosoftMx($host))
            ->values();

        if ($microsoftMatches->isNotEmpty()) {
            return [
                'provider' => 'microsoft',
                'status' => 'detected',
                'evidence' => [
                    'Microsoft 365 mail infrastructure detected.',
                    'Matching MX: ' . $microsoftMatches->implode(', '),
                ],
            ];
        }

        return [
            'provider' => 'other',
            'status' => 'detected',
            'evidence' => [
                'MX records exist but do not match the known Google Workspace or Microsoft 365 patterns.',
                'MX: ' . $hosts->implode(', '),
            ],
        ];
    }

    private function isGoogleMx(string $host): bool
    {
        return in_array($host, [
            'aspmx.l.google.com',
            'alt1.aspmx.l.google.com',
            'alt2.aspmx.l.google.com',
            'alt3.aspmx.l.google.com',
            'alt4.aspmx.l.google.com',
        ], true);
    }

    private function isMicrosoftMx(string $host): bool
    {
        return str_ends_with(
            $host,
            '.mail.protection.outlook.com'
        );
    }
}
