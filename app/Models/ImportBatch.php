<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'campaign_id',
        'type',
        'status',
        'total_rows',
        'processed_rows',
        'counts',
        'last_errors',
        'source_path',
        'errors_csv_path',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'counts' => 'array',
        'last_errors' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getProgressPercentAttribute(): int
    {
        $total = (int)($this->total_rows ?? 0);
        $processed = (int)($this->processed_rows ?? 0);

        if ($total <= 0) return 0;

        $pct = (int)floor(($processed / $total) * 100);
        return max(0, min(100, $pct));
    }
}
