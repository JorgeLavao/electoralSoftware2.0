<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'published_at',
        'user_id',
        'campaign_id',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scopeVisibleForUserInCampaign(Builder $query, User $user, ?Campaign $campaign = null): Builder
    {
        if ($user->is_super_admin) {
            return $query->where(function (Builder $visibleNews) use ($user, $campaign) {
                $visibleNews->where('user_id', $user->id);

                if ($campaign) {
                    $visibleNews->orWhere('campaign_id', $campaign->id);
                }
            });
        }

        return $query->where(function (Builder $visibleNews) use ($campaign) {
            $visibleNews->whereNull('campaign_id');

            if ($campaign) {
                $visibleNews->orWhere('campaign_id', $campaign->id);
            }
        });
    }
}
