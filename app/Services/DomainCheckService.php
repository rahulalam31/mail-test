<?php

namespace App\Services;

use App\Services\Blacklist\DnsBlacklistChecker;
use App\Services\Dns\DkimChecker;
use App\Services\Dns\DnsChecker;
use App\Services\MailProvider\MailProviderDetector;

class DomainCheckService
{
    public function __construct(
        private readonly DnsChecker $dnsChecker,
        private readonly DkimChecker $dkimChecker,
        private readonly DnsBlacklistChecker $blacklistChecker,
        private readonly MailProviderDetector $providerDetector,
    ) {}

    public function check(
        string $domain,
        ?string $dkimSelector = null
    ): array {
        /*
        |--------------------------------------------------------------------------
        | DNS
        |--------------------------------------------------------------------------
        */

        $dns = $this->dnsChecker->check(
            $domain
        );

        /*
        |--------------------------------------------------------------------------
        | Blacklist
        |--------------------------------------------------------------------------
        */

        $blacklist = $this->blacklistChecker->check(
            $dns['a'],
            $dns['aaaa']
        );

        /*
        |--------------------------------------------------------------------------
        | DKIM
        |--------------------------------------------------------------------------
        */

        $dkim = $this->dkimChecker->check(
            $domain,
            $dkimSelector
        );

        /*
        |--------------------------------------------------------------------------
        | Mail provider
        |--------------------------------------------------------------------------
        */

        $provider = $this->providerDetector->detect(
            $domain,
            $dns['mx'],
            $dns['txt']
        );

        /*
        |--------------------------------------------------------------------------
        | Final result
        |--------------------------------------------------------------------------
        */

        return [
            'blacklist_status' => $blacklist['status'],

            'blacklists' => [
                'listed' => $blacklist['listed'],
                'errors' => $blacklist['errors'],
                'checked_ips' => $blacklist['checked_ips'],
            ],

            'dns_records' => [
                'a' => $dns['a'],
                'aaaa' => $dns['aaaa'],
                'mx' => $dns['mx'],
                'txt' => $dns['txt'],
                'cname' => $dns['cname'],
                'ns' => $dns['ns'],
                'ptr' => $dns['ptr'],
            ],

            'mx_records' => $dns['mx'],

            'spf' => $dns['spf'],

            'dmarc' => $dns['dmarc'],

            'dkim' => $dkim,

            'nameservers' => $dns['ns'],

            'mail_provider' => $provider['provider'],

            'detection_evidence' => $provider['evidence'],

            'errors' => [
                'dnsbl' => $blacklist['errors'],
            ],
        ];
    }
}
