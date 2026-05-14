<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotationPoint extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'municipality',
        'stand',
        'address',
        'table'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
