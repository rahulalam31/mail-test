<?php

namespace App\Jobs;

use App\Models\DomainCheck;
use App\Services\DomainCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDomainCheck implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of attempts.
     */
    public int $tries = 3;

    /**
     * Retry delays.
     */
    public array $backoff = [
        5,
        15,
        30,
    ];

    /**
     * Maximum execution time.
     */
    public int $timeout = 60;

    public function __construct(
        public int $domainCheckId,
        public ?string $dkimSelector = null,
    ) {
        $this->onQueue('domain-checks');
    }

    public function handle(
        DomainCheckService $service
    ): void {
        $check = DomainCheck::find(
            $this->domainCheckId
        );

        if (! $check) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate processing
        |--------------------------------------------------------------------------
        */

        if ($check->status === 'completed') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Mark as checking
        |--------------------------------------------------------------------------
        */

        $check->update([
            'status' => 'checking',
            'started_at' => now(),
            'error' => null,
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Perform the actual domain check
            |--------------------------------------------------------------------------
            */

            $result = $service->check(
                $check->domain,
                $this->dkimSelector
            );

            /*
            |--------------------------------------------------------------------------
            | Persist everything atomically
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $check,
                $result
            ) {
                $check->update([
                    'status' => 'completed',

                    'blacklist_status' =>
                    $result['blacklist_status'],

                    'blacklists' =>
                    $result['blacklists'],

                    'dns_records' =>
                    $result['dns_records'],

                    'mx_records' =>
                    $result['mx_records'],

                    'spf' =>
                    $result['spf'],

                    'dmarc' =>
                    $result['dmarc'],

                    'dkim' =>
                    $result['dkim'],

                    'nameservers' =>
                    $result['nameservers'],

                    'mail_provider' =>
                    $result['mail_provider'],

                    'detection_evidence' =>
                    $result['detection_evidence'],

                    'completed_at' => now(),
                ]);
            });

            /*
            |--------------------------------------------------------------------------
            | Update bulk counters
            |--------------------------------------------------------------------------
            */

            $this->updateBulkCounters(
                $check
            );
        } catch (Throwable $e) {
            Log::error(
                'Domain check failed',
                [
                    'domain_check_id' =>
                    $check->id,

                    'domain' =>
                    $check->domain,

                    'attempt' =>
                    $this->attempts(),

                    'exception' => $e,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Final failure
            |--------------------------------------------------------------------------
            |
            | If Laravel is going to retry, leave the record available for
            | another attempt. failed() will handle the final failure.
            |
            */

            throw $e;
        }
    }

    public function failed(
        ?Throwable $exception
    ): void {
        $check = DomainCheck::find(
            $this->domainCheckId
        );

        if (! $check) {
            return;
        }

        $check->update([
            'status' => 'failed',

            'error' => $exception?->getMessage()
                ?? 'Domain check failed.',

            'completed_at' => now(),
        ]);

        $this->updateBulkCounters(
            $check
        );
    }
    private function updateBulkCounters(
        DomainCheck $check
    ): void {
        if (! $check->bulk_check_id) {
            return;
        }

        $counts = DomainCheck::query()
            ->where('bulk_check_id', $check->bulk_check_id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $check->bulkCheck()->update([
            'queued' => (int) $counts->get('queued', 0),
            'checking' => (int) $counts->get('checking', 0),
            'completed' => (int) $counts->get('completed', 0),
            'failed' => (int) $counts->get('failed', 0),
        ]);
    }
}
