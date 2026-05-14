<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'committee_user', 'committee_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function administrators()
    {
        return $this->users()->wherePivot('role', 'administrator');
    }

    public function members()
    {
        return $this->users()->wherePivot('role', 'member');
    }
}
