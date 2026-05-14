<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class PlatformPermission extends Model
{
    protected $fillable = [
        'name',
        'group_key',
        'group_label',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'platform_permission_user')->withTimestamps();
    }

    public static function grouped(): Collection
    {
        return static::query()
            ->orderBy('group_label')
            ->orderBy('description')
            ->get()
            ->groupBy('group_key')
            ->map(function (Collection $permissions, string $groupKey) {
                $firstPermission = $permissions->first();

                return [
                    'group_key' => $groupKey,
                    'group_label' => $firstPermission?->group_label,
                    'permissions' => $permissions->values(),
                ];
            })
            ->values();
    }
}
