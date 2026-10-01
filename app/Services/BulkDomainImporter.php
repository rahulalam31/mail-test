<?php

namespace App\Services;

use App\Jobs\ProcessDomainCheck;
use App\Models\BulkCheck;
use App\Models\DomainCheck;
use App\Services\Domain\DomainNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BulkDomainImporter
{
    public function __construct(
        private readonly DomainNormalizer $normalizer,
    ) {}

    public function import(
        UploadedFile $file
    ): BulkCheck {
        $bulkCheck = null;

        DB::transaction(function () use (
            $file,
            &$bulkCheck
        ) {
            $bulkCheck = BulkCheck::create([
                'filename' => $file->getClientOriginalName(),
                'total' => 0,
                'queued' => 0,
                'checking' => 0,
                'completed' => 0,
                'failed' => 0,
            ]);

            $domains = $this->readFile(
                $file
            );
            $seen = [];

            foreach ($domains as $input) {
                $domain = $this->normalizer->normalize($input);

                if (! $domain) {
                    continue;
                }

                if (isset($seen[$domain])) {
                    continue;
                }

                $seen[$domain] = true;

                $check = DomainCheck::create([
                    'bulk_check_id' => $bulkCheck->id,
                    'input' => $input,
                    'domain' => $domain,
                    'status' => 'queued',
                ]);

                ProcessDomainCheck::dispatch($check->id);
            }

            $total = $bulkCheck
                ->domainChecks()
                ->count();

            $bulkCheck->update([
                'total' => $total,
                'queued' => $total,
            ]);
        });

        return $bulkCheck;
    }

    private function readFile(
        UploadedFile $file
    ): array {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        return match ($extension) {
            'txt' => $this->readTxt($file),
            'csv' => $this->readCsv($file),

            default => throw new RuntimeException(
                'Only CSV and TXT files are supported.'
            ),
        };
    }

    private function readTxt(
        UploadedFile $file
    ): array {
        $lines = file(
            $file->getRealPath(),
            FILE_IGNORE_NEW_LINES
                | FILE_SKIP_EMPTY_LINES
        );

        return array_map(
            'trim',
            $lines ?: []
        );
    }

    private function readCsv(
        UploadedFile $file
    ): array {
        $handle = fopen(
            $file->getRealPath(),
            'r'
        );

        if (! $handle) {
            throw new RuntimeException(
                'Unable to read CSV file.'
            );
        }

        $values = [];

        while (($row = fgetcsv($handle)) !== false) {
            foreach ($row as $value) {
                $value = trim($value);

                if ($value !== '') {
                    $values[] = $value;
                }
            }
        }

        fclose($handle);

        return $values;
    }
}
