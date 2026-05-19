<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'reffer_id',
        'expires_at',
        'accepted_at',
        'active'
    ];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function foreign_user(){
        return $this->belongsTo(User::class);
    }

    public function foreing_campaign(){
        return $this->belongsTo(Campaign::class);
    }
}
