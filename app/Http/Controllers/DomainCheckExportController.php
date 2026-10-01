<?php

namespace App\Http\Controllers;

use App\Models\DomainCheck;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DomainCheckExportController extends Controller
{
    public function __invoke(
        int $bulkCheckId
    ): StreamedResponse {
        $filename = 'domain-checks-' .
            $bulkCheckId .
            '.csv';

        return response()->streamDownload(
            function () use ($bulkCheckId) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                fputcsv($handle, [
                    'Domain',
                    'Input',
                    'Status',
                    'Blacklist Status',
                    'Blacklists',
                    'Mail Provider',
                    'MX Records',
                    'SPF',
                    'DMARC',
                    'DKIM',
                    'Nameservers',
                    'Error',
                ]);

                DomainCheck::query()
                    ->where(
                        'bulk_check_id',
                        $bulkCheckId
                    )
                    ->whereIn('status', [
                        'completed',
                        'failed',
                    ])
                    ->orderBy('id')
                    ->cursor()
                    ->each(function (
                        DomainCheck $check
                    ) use ($handle) {

                        fputcsv($handle, [
                            $check->domain,

                            $check->input,

                            $check->status,

                            $check->blacklist_status,

                            json_encode(
                                $check->blacklists
                            ),

                            $check->mail_provider,

                            json_encode(
                                $check->mx_records
                            ),

                            data_get(
                                $check->spf,
                                'value'
                            ),

                            data_get(
                                $check->dmarc,
                                'value'
                            ),

                            json_encode(
                                $check->dkim
                            ),

                            json_encode(
                                $check->nameservers
                            ),

                            $check->error,
                        ]);
                    });

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',
            ]
        );
    }
}
