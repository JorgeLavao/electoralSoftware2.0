<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportBatch extends Model
{
    public const SCOPE_CURRENT_PAGE = 'current_page';
    public const SCOPE_ALL_FILTERED = 'all_filtered';

    protected $fillable = [
        'user_id',
        'campaign_id',
        'type',
        'scope',
        'format',
        'status',
        'filters',
        'columns',
        'page',
        'per_page',
        'total_rows',
        'processed_rows',
        'file_path',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
