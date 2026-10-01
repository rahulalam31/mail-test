<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainCheck extends Model
{

    protected $fillable = [
        'bulk_check_id',
        'input',
        'domain',
        'status',
        'blacklist_status',
        'mail_provider',
        'blacklists',
        'dns_records',
        'mx_records',
        'spf',
        'dmarc',
        'dkim',
        'nameservers',
        'detection_evidence',
        'error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'detection_evidence' => 'array',
        'blacklists' => 'array',
        'dns_records' => 'array',
        'mx_records' => 'array',
        'spf' => 'array',
        'dmarc' => 'array',
        'dkim' => 'array',
        'nameservers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function bulkCheck(): BelongsTo
    {
        return $this->belongsTo(
            BulkCheck::class
        );
    }
}
