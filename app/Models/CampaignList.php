<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignList extends Model
{
    protected $table = 'lists';

     protected $fillable = [
        'campaign_id',
        'name',
        'status',
    ];

    public function foreign_users(){
        return $this->belongsToMany(User::class, 'list_user','list_id', 'user_id')->withTimestamps();
    }

    public function foreign_campaign(){
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }
}
