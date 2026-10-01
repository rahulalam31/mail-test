<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkCheck extends Model
{

    protected $fillable = [
        'filename',
        'total',
        'queued',
        'checking',
        'completed',
        'failed',
    ];

    protected $casts = [
        'total' => 'integer',
        // 'queued' => 'integer',
        // 'checking' => 'integer',
        // 'completed' => 'integer',
        // 'failed' => 'integer',
    ];

    public function domainChecks(): HasMany
    {
        return $this->hasMany(DomainCheck::class);
    }
    public function getProcessedAttribute(): int
    {
        return (int) (
            $this->completed +
            $this->failed
        );
    }

    public function getProgressAttribute(): float
    {
        if ($this->total <= 0) {
            return 0;
        }

        return round(
            ($this->processed / $this->total) * 100,
            2
        );
    }
}
