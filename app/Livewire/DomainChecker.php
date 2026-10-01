<?php

namespace App\Livewire;

use App\Models\BulkCheck;
use App\Models\DomainCheck;
use App\Services\BulkDomainImporter;
use App\Services\Domain\DomainNormalizer;
use App\Services\DomainCheckService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class DomainChecker extends Component
{
    use WithFileUploads;

    /*
    |--------------------------------------------------------------------------
    | Single Check
    |--------------------------------------------------------------------------
    */

    public string $input = '';

    public string $dkimSelector = '';

    public ?array $singleResult = null;

    public ?string $singleError = null;

    public bool $checkingSingle = false;

    /*
    |--------------------------------------------------------------------------
    | Bulk Check
    |--------------------------------------------------------------------------
    */

    public ?UploadedFile $file = null;

    public ?int $bulkCheckId = null;

    public ?BulkCheck $bulkCheck = null;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public string $filter = 'all';

    public string $search = '';

    /*
    |--------------------------------------------------------------------------
    | UI
    |--------------------------------------------------------------------------
    */

    public string $activeTab = 'dns';

    public bool $showDetails = false;

    public ?int $selectedCheckId = null;

    /*
    |--------------------------------------------------------------------------
    | Single Check
    |--------------------------------------------------------------------------
    */


    private function resetSingleState(): void
    {
        $this->singleResult = null;
        $this->singleError = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Upload
    |--------------------------------------------------------------------------
    */

    public function uploadBulk(
        BulkDomainImporter $importer
    ): void {
        $this->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240',
            ],
        ]);

        $bulk = $importer->import(
            $this->file
        );

        $this->bulkCheckId = $bulk->id;

        $this->bulkCheck = $bulk->fresh();

        $this->file = null;

        $this->filter = 'all';
        $this->search = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Bulk Results
    |--------------------------------------------------------------------------
    */

    public function refreshBulk(): void
    {
        if (! $this->bulkCheckId) {
            return;
        }

        $this->bulkCheck = BulkCheck::find(
            $this->bulkCheckId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Result Rows
    |--------------------------------------------------------------------------
    */

    public function getResultsProperty()
    {
        if (! $this->bulkCheckId) {
            return collect();
        }

        $query = DomainCheck::query()
            ->where(
                'bulk_check_id',
                $this->bulkCheckId
            );

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('domain', 'like', $search)
                    ->orWhere('input', 'like', $search);
            });
        }

        match ($this->filter) {
            'clean' => $query->where('blacklist_status', 'clean'),

            'blacklisted' => $query->where(
                'blacklist_status',
                'listed'
            ),

            'google' => $query->where(
                'mail_provider',
                'google'
            ),

            'microsoft' => $query->where(
                'mail_provider',
                'microsoft'
            ),

            'other' => $query->where(
                'mail_provider',
                'other'
            ),

            'not_detected' => $query->where(
                'mail_provider',
                'not_detected'
            ),

            'failed' => $query->where(
                'status',
                'failed'
            ),

            default => null,
        };

        return $query
            ->latest('id')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Selected Result
    |--------------------------------------------------------------------------
    */

    public function selectCheck(
        int $id
    ): void {
        $this->selectedCheckId = $id;

        $this->showDetails = true;
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;

        $this->selectedCheckId = null;
    }

    public function getSelectedCheckProperty()
    {
        if (! $this->selectedCheckId) {
            return null;
        }

        return DomainCheck::find(
            $this->selectedCheckId
        );
    }

    public function checkSingle(
        DomainCheckService $service,
        DomainNormalizer $normalizer
    ): void {
        $this->singleResult = null;
        $this->singleError = null;

        $this->validate([
            'input' => ['required', 'string', 'max:320'],
            'dkimSelector' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9._-]+$/',
            ],
        ]);

        $domain = $normalizer->normalize($this->input);

        if (! $domain) {
            $this->singleError = 'Please enter a valid domain or email address.';
            return;
        }

        $this->checkingSingle = true;

        try {
            $result = $service->check(
                $domain,
                $this->dkimSelector !== ''
                    ? trim($this->dkimSelector)
                    : null
            );

            $this->singleResult = [
                'input' => $this->input,
                'domain' => $domain,
                ...$result,
            ];
        } catch (\Throwable $e) {
            report($e);

            $this->singleError = app()->hasDebugModeEnabled()
                ? $e->getMessage()
                : 'Unable to complete the domain check.';
        } finally {
            $this->checkingSingle = false;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        return view(
            'livewire.domain-checker'
        )->layout('layouts.app');;
    }
}
