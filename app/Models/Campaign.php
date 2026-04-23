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

    public function foreign_referents(){
        return User::whereIn('id', function ($q) {
            $q->select('reffer_by')->from('campaign_user')->where('campaign_id', $this->id)->whereNotNull('reffer_by');
        })->distinct();
}
}
