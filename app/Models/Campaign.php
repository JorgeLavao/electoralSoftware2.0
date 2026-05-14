<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'candidate_name',
        'position',
        'code',
        'start_date',
        'end_date',
        'status'
    ];

    public function foreign_users(){
        return $this->belongsToMany(User::class, 'campaign_user', 'campaign_id', 'user_id')
            ->withPivot('reffer_by', 'approach', 'validate')
            ->withTimestamps();
    }

    public function staff_users(){
        return $this->belongsToMany(User::class, 'campaign_staff', 'campaign_id', 'user_id')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function foreign_lists(){
        return $this->hasMany(CampaignList::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function committees()
    {
        return $this->hasMany(Committee::class);
    }

    public function groupAssignableUsers()
    {
        return User::query()
            ->where(function ($query) {
                $query->whereHas('supporter_campaigns', function ($campaignQuery) {
                    $campaignQuery->where('campaigns.id', $this->id)
                        ->where('campaign_user.validate', 1);
                })->orWhereHas('foreign_campaings', function ($campaignQuery) {
                    $campaignQuery->where('campaigns.id', $this->id)
                        ->where('campaign_staff.status', true);
                });
            })
            ->select('users.*')
            ->distinct();
    }

    public function syncStaffAsSupporters(iterable $userIds): void
    {
        foreach (collect($userIds)->map(fn ($id) => (int) $id)->unique() as $userId) {
            $alreadySupporter = $this->foreign_users()->where('users.id', $userId)->exists();

            if ($alreadySupporter) {
                $this->foreign_users()->updateExistingPivot($userId, [
                    'validate' => 1,
                ]);

                continue;
            }

            $this->foreign_users()->attach($userId, [
                'reffer_by' => null,
                'approach' => 4,
                'validate' => 1,
            ]);
        }
    }

    public function foreign_referents(){
        return User::whereIn('id', function ($q) {
            $q->select('reffer_by')->from('campaign_user')->where('campaign_id', $this->id)->whereNotNull('reffer_by');
        })->distinct();
}
}
